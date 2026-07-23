/**
 * My Calculator — front-end engine.
 * Vanilla JS only (no external chart library), so the plugin makes
 * zero third-party requests and stays lightweight & review-friendly.
 */
( function () {
	'use strict';

	var DATA = window.myCalculatorData || {
		currency: '₹',
		color: '#0F6E4F',
		rates: {},
		i18n: {},
	};

	var PALETTE = [ DATA.color || '#0F6E4F', '#e07a3f', '#3f7ce0', '#9b5de5', '#f15bb5', '#00bbf9' ];

	/* ----------------------------- Helpers ------------------------------ */

	function formatCurrency( value ) {
		if ( ! isFinite( value ) ) {
			return DATA.currency + '0';
		}
		var num2 = Math.round( value * 100 ) / 100;
		var parts = num2.toFixed( 2 ).split( '.' );
		var intPart = parts[ 0 ];
		var sign = '';
		if ( intPart.charAt( 0 ) === '-' ) {
			sign = '-';
			intPart = intPart.substring( 1 );
		}
		var lastThree = intPart.substring( intPart.length - 3 );
		var other = intPart.substring( 0, intPart.length - 3 );
		if ( other !== '' ) {
			lastThree = ',' + lastThree;
		}
		var formattedOther = other.replace( /\B(?=(\d{2})+(?!\d))/g, ',' );
		return sign + DATA.currency + formattedOther + lastThree;
	}

	function num( val ) {
		var n = parseFloat( val );
		return isFinite( n ) ? n : NaN;
	}

	/* --------------------------- Donut chart ----------------------------- */

	function drawDonutChart( canvas, segments ) {
		var ctx = canvas.getContext( '2d' );
		var dpr = window.devicePixelRatio || 1;
		var size = canvas.clientWidth || 280;
		canvas.width = size * dpr;
		canvas.height = size * dpr;
		ctx.scale( dpr, dpr );
		ctx.clearRect( 0, 0, size, size );

		var total = segments.reduce( function ( sum, s ) {
			return sum + Math.max( s.value, 0 );
		}, 0 );

		var cx = size / 2;
		var cy = size / 2;
		var outerR = size / 2 - 10;
		var innerR = outerR * 0.6;

		if ( total <= 0 ) {
			ctx.beginPath();
			ctx.arc( cx, cy, outerR, 0, Math.PI * 2 );
			ctx.fillStyle = '#eee';
			ctx.fill();
			return;
		}

		var start = -Math.PI / 2;
		segments.forEach( function ( seg ) {
			var slice = ( Math.max( seg.value, 0 ) / total ) * Math.PI * 2;
			var end = start + slice;
			ctx.beginPath();
			ctx.moveTo( cx, cy );
			ctx.arc( cx, cy, outerR, start, end );
			ctx.closePath();
			ctx.fillStyle = seg.color;
			ctx.fill();
			start = end;
		} );

		ctx.globalCompositeOperation = 'destination-out';
		ctx.beginPath();
		ctx.arc( cx, cy, innerR, 0, Math.PI * 2 );
		ctx.fill();
		ctx.globalCompositeOperation = 'source-over';
	}

	function renderLegend( container, segments ) {
		container.innerHTML = '';
		segments.forEach( function ( seg ) {
			var li = document.createElement( 'li' );
			var swatch = document.createElement( 'span' );
			swatch.className = 'swatch';
			swatch.style.background = seg.color;
			li.appendChild( swatch );
			li.appendChild( document.createTextNode( seg.label + ': ' + formatCurrency( seg.value ) ) );
			container.appendChild( li );
		} );
	}

	function renderSummary( container, items ) {
		container.innerHTML = '';
		items.forEach( function ( item, idx ) {
			var div = document.createElement( 'div' );
			div.className = 'my-calculator-summary-item' + ( 0 === idx ? ' is-primary' : '' );
			var label = document.createElement( 'span' );
			label.className = 'label';
			label.textContent = item.label;
			var value = document.createElement( 'span' );
			value.className = 'value';
			value.textContent = item.value;
			div.appendChild( label );
			div.appendChild( value );
			container.appendChild( div );
		} );
	}

	function renderTable( container, headers, rows ) {
		if ( ! rows || ! rows.length ) {
			container.innerHTML = '';
			return;
		}
		var html = '<table><thead><tr>';
		headers.forEach( function ( h ) {
			html += '<th>' + h + '</th>';
		} );
		html += '</tr></thead><tbody>';
		rows.forEach( function ( row ) {
			html += '<tr>';
			row.forEach( function ( cell ) {
				html += '<td>' + cell + '</td>';
			} );
			html += '</tr>';
		} );
		html += '</tbody></table>';
		container.innerHTML = html;
	}

	/* --------------------------- Calculators ----------------------------- */

	var i18n = DATA.i18n || {};

	function calcEmi( v ) {
		var P = v.principal;
		var r = v.rate / 12 / 100;
		var n = v.years * 12;
		var emi = r === 0 ? P / n : ( P * r * Math.pow( 1 + r, n ) ) / ( Math.pow( 1 + r, n ) - 1 );
		var totalPayment = emi * n;
		var totalInterest = totalPayment - P;

		var rows = [];
		var balance = P;
		for ( var y = 1; y <= v.years; y++ ) {
			var yearlyPrincipal = 0;
			var yearlyInterest = 0;
			for ( var m = 0; m < 12 && balance > 0; m++ ) {
				var interestPortion = balance * r;
				var principalPortion = emi - interestPortion;
				balance -= principalPortion;
				yearlyPrincipal += principalPortion;
				yearlyInterest += interestPortion;
			}
			rows.push( [ y, formatCurrency( yearlyPrincipal ), formatCurrency( yearlyInterest ), formatCurrency( Math.max( balance, 0 ) ) ] );
		}

		return {
			summary: [
				{ label: i18n.monthlyEmi || 'Monthly EMI', value: formatCurrency( emi ) },
				{ label: i18n.totalPayable || 'Total Payable', value: formatCurrency( totalPayment ) },
				{ label: i18n.interest || 'Total Interest', value: formatCurrency( totalInterest ) },
			],
			segments: [
				{ label: i18n.principal || 'Principal', value: P, color: PALETTE[ 0 ] },
				{ label: i18n.interest || 'Interest', value: totalInterest, color: PALETTE[ 1 ] },
			],
			tableHeaders: [ 'Year', 'Principal Paid', 'Interest Paid', 'Balance' ],
			tableRows: rows,
		};
	}

	function calcHomeLoanEligibility( v ) {
		var maxEmi = Math.max( v.monthlyIncome * ( v.foirPercent / 100 ) - v.existingEmi, 0 );
		var r = v.rate / 12 / 100;
		var n = v.years * 12;
		var eligibleLoan = r === 0 ? maxEmi * n : ( maxEmi * ( Math.pow( 1 + r, n ) - 1 ) ) / ( r * Math.pow( 1 + r, n ) );
		return {
			summary: [
				{ label: 'Eligible Loan Amount', value: formatCurrency( eligibleLoan ) },
				{ label: 'Max Affordable EMI', value: formatCurrency( maxEmi ) },
			],
			segments: [
				{ label: 'Existing EMI', value: v.existingEmi, color: PALETTE[ 0 ] },
				{ label: 'Available EMI Capacity', value: maxEmi, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcFdTdr( v ) {
		var quarterlyPayout = ( v.principal * v.rate / 100 ) / 4;
		var totalInterest = v.principal * v.rate * v.years / 100;
		return {
			summary: [
				{ label: 'Quarterly Interest Payout', value: formatCurrency( quarterlyPayout ) },
				{ label: 'Total Interest Over Tenure', value: formatCurrency( totalInterest ) },
				{ label: 'Principal Returned at Maturity', value: formatCurrency( v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Total Interest', value: totalInterest, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcFdStdr( v ) {
		var maturity = v.principal * Math.pow( 1 + ( v.rate / 100 ) / 4, 4 * v.years );
		var gain = maturity - v.principal;
		return {
			summary: [
				{ label: i18n.maturity || 'Maturity Amount', value: formatCurrency( maturity ) },
				{ label: 'Total Interest Earned', value: formatCurrency( gain ) },
				{ label: 'Total Deposited', value: formatCurrency( v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: gain, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcRd( v ) {
		var months = v.years * 12;
		var i = v.rate / 400;
		var n = months / 3;
		var maturity;
		if ( i === 0 ) {
			maturity = v.monthly * months;
		} else {
			var factor = Math.pow( 1 + i, n ) - 1;
			var denom = 1 - Math.pow( 1 + i, -1 / 3 );
			maturity = v.monthly * factor / denom;
		}
		var deposited = v.monthly * months;
		return {
			summary: [
				{ label: i18n.maturity || 'Maturity Amount', value: formatCurrency( maturity ) },
				{ label: 'Total Deposited', value: formatCurrency( deposited ) },
				{ label: 'Total Interest Earned', value: formatCurrency( maturity - deposited ) },
			],
			segments: [
				{ label: 'Deposited', value: deposited, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: maturity - deposited, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcPpf( v ) {
		var r = v.rate / 100;
		var fv = 0;
		var rows = [];
		var invested = 0;
		for ( var y = 1; y <= v.years; y++ ) {
			fv = ( fv + v.yearly ) * ( 1 + r );
			invested += v.yearly;
			rows.push( [ y, formatCurrency( invested ), formatCurrency( fv - invested ), formatCurrency( fv ) ] );
		}
		var gain = fv - invested;
		return {
			summary: [
				{ label: i18n.maturity || 'Maturity Value', value: formatCurrency( fv ) },
				{ label: i18n.principal || 'Invested Amount', value: formatCurrency( invested ) },
				{ label: i18n.interest || 'Estimated Returns', value: formatCurrency( gain ) },
			],
			segments: [
				{ label: 'Invested', value: invested, color: PALETTE[ 0 ] },
				{ label: 'Returns', value: gain, color: PALETTE[ 1 ] },
			],
			tableHeaders: [ 'Year', 'Invested', 'Growth', 'Balance' ],
			tableRows: rows,
		};
	}

	function calcSsa( v ) {
		var r = v.rate / 100;
		var fvAtDepositEnd = 0;
		for ( var y = 1; y <= v.depositYears; y++ ) {
			fvAtDepositEnd = ( fvAtDepositEnd + v.yearly ) * ( 1 + r );
		}
		var remainingYears = Math.max( v.totalYears - v.depositYears, 0 );
		var maturity = fvAtDepositEnd * Math.pow( 1 + r, remainingYears );
		var invested = v.yearly * v.depositYears;
		var gain = maturity - invested;
		return {
			summary: [
				{ label: i18n.maturity || 'Maturity Value', value: formatCurrency( maturity ) },
				{ label: 'Total Deposited', value: formatCurrency( invested ) },
				{ label: 'Total Interest Earned', value: formatCurrency( gain ) },
			],
			segments: [
				{ label: 'Deposited', value: invested, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: gain, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcScss( v ) {
		var quarterlyPayout = ( v.principal * v.rate / 100 ) / 4;
		var totalInterest = v.principal * v.rate / 100 * 5;
		return {
			summary: [
				{ label: 'Quarterly Interest Payout', value: formatCurrency( quarterlyPayout ) },
				{ label: 'Total Interest Over 5 Years', value: formatCurrency( totalInterest ) },
				{ label: 'Principal Returned at Maturity', value: formatCurrency( v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Total Interest', value: totalInterest, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcKvp( v ) {
		var r = v.rate / 100;
		var doublingYears = r <= 0 ? 0 : Math.log( 2 ) / Math.log( 1 + r );
		var maturity = v.principal * 2;
		return {
			summary: [
				{ label: 'Maturity Value (Doubles)', value: formatCurrency( maturity ) },
				{ label: 'Approx. Doubling Period (Years)', value: doublingYears.toFixed( 1 ) + ' yrs' },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Growth', value: v.principal, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcMis( v ) {
		var monthlyPayout = ( v.principal * v.rate / 100 ) / 12;
		var totalInterest = v.principal * v.rate / 100 * 5;
		return {
			summary: [
				{ label: 'Monthly Income', value: formatCurrency( monthlyPayout ) },
				{ label: 'Total Interest Over 5 Years', value: formatCurrency( totalInterest ) },
				{ label: 'Principal Returned at Maturity', value: formatCurrency( v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Total Interest', value: totalInterest, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcTd( v ) {
		var maturity = v.principal * Math.pow( 1 + ( v.rate / 100 ) / 4, 4 * v.years );
		return {
			summary: [
				{ label: i18n.maturity || 'Maturity Amount', value: formatCurrency( maturity ) },
				{ label: 'Total Interest Earned', value: formatCurrency( maturity - v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: maturity - v.principal, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcNsc( v ) {
		var maturity = v.principal * Math.pow( 1 + v.rate / 100, 5 );
		return {
			summary: [
				{ label: 'Maturity Value (5 Years)', value: formatCurrency( maturity ) },
				{ label: 'Total Interest Earned', value: formatCurrency( maturity - v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: maturity - v.principal, color: PALETTE[ 1 ] },
			],
		};
	}

	function fvSeries( periodic, rate, periods ) {
		var rows = [];
		var balance = 0;
		var invested = 0;
		for ( var p = 1; p <= periods; p++ ) {
			balance = ( balance + periodic ) * ( 1 + rate );
			invested += periodic;
			rows.push( { period: p, balance: balance, invested: invested } );
		}
		return rows;
	}

	function calcSip( v ) {
		var i = v.rate / 12 / 100;
		var n = v.years * 12;
		var series = fvSeries( v.monthly, i, n );
		var fv = series.length ? series[ series.length - 1 ].balance : 0;
		var invested = v.monthly * n;
		var gain = fv - invested;

		var rows = [];
		for ( var y = 1; y <= v.years; y++ ) {
			var idx = y * 12 - 1;
			if ( series[ idx ] ) {
				rows.push( [ y, formatCurrency( series[ idx ].invested ), formatCurrency( series[ idx ].balance - series[ idx ].invested ), formatCurrency( series[ idx ].balance ) ] );
			}
		}

		return {
			summary: [
				{ label: i18n.maturity || 'Maturity Value', value: formatCurrency( fv ) },
				{ label: i18n.principal || 'Invested Amount', value: formatCurrency( invested ) },
				{ label: i18n.interest || 'Estimated Returns', value: formatCurrency( gain ) },
			],
			segments: [
				{ label: 'Invested', value: invested, color: PALETTE[ 0 ] },
				{ label: 'Returns', value: gain, color: PALETTE[ 1 ] },
			],
			tableHeaders: [ 'Year', 'Invested', 'Returns', 'Balance' ],
			tableRows: rows,
		};
	}

	function calcSwp( v ) {
		var r = v.rate / 12 / 100;
		var balance = v.corpus;
		var withdrawn = 0;
		var months = 0;
		var capMonths = 480;
		for ( var m = 1; m <= capMonths; m++ ) {
			balance = balance * ( 1 + r ) - v.withdrawal;
			withdrawn += v.withdrawal;
			months = m;
			if ( balance <= 0 ) {
				balance = 0;
				break;
			}
		}
		return {
			summary: [
				{ label: 'Corpus Lasts (Months)', value: String( months ) },
				{ label: 'Corpus Lasts (Years, approx.)', value: ( months / 12 ).toFixed( 1 ) + ' yrs' },
				{ label: 'Remaining Balance (if capped at 40 yrs)', value: formatCurrency( balance ) },
			],
			segments: [
				{ label: 'Withdrawn', value: withdrawn, color: PALETTE[ 0 ] },
				{ label: 'Remaining', value: balance, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcLumpsum( v ) {
		var fv = v.principal * Math.pow( 1 + v.rate / 100, v.years );
		var gain = fv - v.principal;
		return {
			summary: [
				{ label: i18n.maturity || 'Future Value', value: formatCurrency( fv ) },
				{ label: i18n.principal || 'Invested Amount', value: formatCurrency( v.principal ) },
				{ label: i18n.interest || 'Estimated Returns', value: formatCurrency( gain ) },
			],
			segments: [
				{ label: 'Invested', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Returns', value: gain, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcRetirementCorpus( v ) {
		var yearsToRetire = Math.max( v.retireAge - v.currentAge, 0 );
		var yearsInRetirement = Math.max( v.lifeExpectancy - v.retireAge, 1 );
		var futureMonthlyExpense = v.currentExpense * Math.pow( 1 + v.inflation / 100, yearsToRetire );
		var futureAnnualExpense = futureMonthlyExpense * 12;
		var realReturn = ( ( 1 + v.postReturn / 100 ) / ( 1 + v.inflation / 100 ) ) - 1;
		var corpus;
		if ( Math.abs( realReturn ) < 0.0001 ) {
			corpus = futureAnnualExpense * yearsInRetirement;
		} else {
			corpus = futureAnnualExpense * ( ( 1 - Math.pow( 1 + realReturn, -yearsInRetirement ) ) / realReturn ) * ( 1 + realReturn );
		}
		return {
			summary: [
				{ label: 'Retirement Corpus Needed', value: formatCurrency( corpus ) },
				{ label: 'Future Monthly Expense', value: formatCurrency( futureMonthlyExpense ) },
				{ label: 'Years to Retirement', value: String( yearsToRetire ) },
			],
			segments: [
				{ label: 'Current Annual Expense', value: v.currentExpense * 12, color: PALETTE[ 0 ] },
				{ label: 'Future Annual Expense', value: futureAnnualExpense, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcNps( v ) {
		var i = v.rate / 12 / 100;
		var n = v.years * 12;
		var series = fvSeries( v.monthly, i, n );
		var corpus = series.length ? series[ series.length - 1 ].balance : 0;
		var annuityAmount = corpus * ( v.annuityPct / 100 );
		var lumpSum = corpus - annuityAmount;
		var monthlyPension = ( annuityAmount * v.annuityRate / 100 ) / 12;
		return {
			summary: [
				{ label: 'Total Corpus at Retirement', value: formatCurrency( corpus ) },
				{ label: 'Lump Sum Withdrawable', value: formatCurrency( lumpSum ) },
				{ label: 'Amount Used for Annuity', value: formatCurrency( annuityAmount ) },
				{ label: 'Estimated Monthly Pension', value: formatCurrency( monthlyPension ) },
			],
			segments: [
				{ label: 'Lump Sum', value: lumpSum, color: PALETTE[ 0 ] },
				{ label: 'Annuity Portion', value: annuityAmount, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcEpf( v ) {
		var r = v.rate / 100;
		var balance = 0;
		var totalContribution = 0;
		var monthly = v.basic * ( v.employeePct + v.employerPct ) / 100;
		for ( var y = 1; y <= v.years; y++ ) {
			var yearlyContribution = monthly * 12;
			balance = ( balance + yearlyContribution ) * ( 1 + r );
			totalContribution += yearlyContribution;
			monthly *= ( 1 + ( v.increment || 0 ) / 100 );
		}
		return {
			summary: [
				{ label: 'EPF Corpus at Retirement', value: formatCurrency( balance ) },
				{ label: 'Total Contribution', value: formatCurrency( totalContribution ) },
				{ label: 'Interest Earned', value: formatCurrency( balance - totalContribution ) },
			],
			segments: [
				{ label: 'Contribution', value: totalContribution, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: balance - totalContribution, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcVpf( v ) {
		var r = v.rate / 100;
		var balance = 0;
		var totalContribution = 0;
		for ( var y = 1; y <= v.years; y++ ) {
			var yearlyContribution = v.monthly * 12;
			balance = ( balance + yearlyContribution ) * ( 1 + r );
			totalContribution += yearlyContribution;
		}
		return {
			summary: [
				{ label: 'VPF Corpus at Retirement', value: formatCurrency( balance ) },
				{ label: 'Total Voluntary Contribution', value: formatCurrency( totalContribution ) },
				{ label: 'Interest Earned', value: formatCurrency( balance - totalContribution ) },
			],
			segments: [
				{ label: 'Contribution', value: totalContribution, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: balance - totalContribution, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcApy( v ) {
		var age = Math.min( Math.max( Math.round( v.age ), 18 ), 40 );
		var years = 60 - age;
		var requiredCorpus = ( v.targetPension * 12 ) / 0.085;
		var months = years * 12;
		var r = v.rate / 12 / 100;
		var monthly = 0;
		if ( months > 0 ) {
			var factor = r === 0 ? months : ( 1 + r ) * ( Math.pow( 1 + r, months ) - 1 ) / r;
			monthly = requiredCorpus / factor;
		}
		return {
			summary: [
				{ label: 'Approx. Monthly Contribution Needed', value: formatCurrency( monthly ) },
				{ label: 'Estimated Corpus at Age 60', value: formatCurrency( requiredCorpus ) },
				{ label: 'Years of Contribution', value: String( years ) },
			],
			segments: [
				{ label: 'Target Corpus', value: requiredCorpus, color: PALETTE[ 0 ] },
			],
		};
	}

	function calcPmsym( v ) {
		var age = Math.min( Math.max( Math.round( v.age ), 18 ), 40 );
		var years = 60 - age;
		var approxMonthly = 55 + ( age - 18 ) * 4.2;
		var totalMonthly = approxMonthly * 2;
		var months = years * 12;
		var r = v.rate / 12 / 100;
		var corpus = 0;
		if ( months > 0 ) {
			corpus = totalMonthly * ( 1 + r ) * ( Math.pow( 1 + r, months ) - 1 ) / ( r === 0 ? 1 : r );
		}
		return {
			summary: [
				{ label: 'Your Approx. Monthly Contribution', value: formatCurrency( approxMonthly ) },
				{ label: 'Government Matching Contribution', value: formatCurrency( approxMonthly ) },
				{ label: 'Guaranteed Monthly Pension from Age 60', value: formatCurrency( 3000 ) },
				{ label: 'Illustrative Fund Corpus at 60', value: formatCurrency( corpus ) },
			],
			segments: [
				{ label: 'Your Contribution', value: approxMonthly, color: PALETTE[ 0 ] },
				{ label: 'Govt. Matching', value: approxMonthly, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcPmvvy( v ) {
		var monthlyPension = ( v.purchasePrice * v.rate / 100 ) / 12;
		return {
			summary: [
				{ label: 'Estimated Monthly Pension', value: formatCurrency( monthlyPension ) },
				{ label: 'Purchase Price', value: formatCurrency( v.purchasePrice ) },
				{ label: 'Total Pension Over 10 Years', value: formatCurrency( monthlyPension * 120 ) },
			],
			segments: [
				{ label: 'Purchase Price', value: v.purchasePrice, color: PALETTE[ 0 ] },
				{ label: '10yr Pension Total', value: monthlyPension * 120, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcGratuity( v ) {
		var roundedYears = Math.floor( v.years ) + ( ( v.years - Math.floor( v.years ) ) >= 0.5 ? 1 : 0 );
		var g = ( 15 * v.salary * roundedYears ) / 26;
		var capped = Math.min( g, 2000000 );
		return {
			summary: [
				{ label: 'Gratuity Payable', value: formatCurrency( capped ) },
			],
			segments: [
				{ label: 'Gratuity', value: capped, color: PALETTE[ 0 ] },
			],
		};
	}

	function slabTax( taxableIncome, slabs ) {
		var tax = 0;
		var lower = 0;
		for ( var i = 0; i < slabs.length; i++ ) {
			if ( taxableIncome <= lower ) {
				break;
			}
			var upper = slabs[ i ][ 0 ];
			var rate = slabs[ i ][ 1 ];
			var taxableInSlab = Math.min( taxableIncome, upper ) - lower;
			if ( taxableInSlab > 0 ) {
				tax += taxableInSlab * rate / 100;
			}
			lower = upper;
		}
		return tax;
	}

	var NEW_REGIME_SLABS = [
		[ 400000, 0 ], [ 800000, 5 ], [ 1200000, 10 ], [ 1600000, 15 ], [ 2000000, 20 ], [ 2400000, 25 ], [ Infinity, 30 ],
	];
	var OLD_REGIME_SLABS = [
		[ 250000, 0 ], [ 500000, 5 ], [ 1000000, 20 ], [ Infinity, 30 ],
	];

	function calcIncomeTax( v ) {
		var isNewRegime = v.regime >= 0.5;
		var otherDeductions = isNewRegime ? 0 : v.deductions;
		var taxableIncome = Math.max( v.income - v.stdDeduction - otherDeductions, 0 );
		var slabs = isNewRegime ? NEW_REGIME_SLABS : OLD_REGIME_SLABS;
		var tax = slabTax( taxableIncome, slabs );

		var rebateThreshold = isNewRegime ? 1200000 : 500000;
		if ( taxableIncome <= rebateThreshold ) {
			tax = 0;
		}

		var cess = tax * v.cess / 100;
		var totalTax = tax + cess;

		return {
			summary: [
				{ label: 'Total Tax Payable', value: formatCurrency( totalTax ) },
				{ label: 'Taxable Income', value: formatCurrency( taxableIncome ) },
				{ label: 'Income Tax (before cess)', value: formatCurrency( tax ) },
				{ label: 'Cess', value: formatCurrency( cess ) },
				{ label: 'Net Take-Home (Post-Tax)', value: formatCurrency( v.income - totalTax ) },
			],
			segments: [
				{ label: 'Take-Home', value: v.income - totalTax, color: PALETTE[ 0 ] },
				{ label: 'Tax Paid', value: totalTax, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcCapitalGains( v ) {
		var gain = v.saleValue - v.purchaseValue;
		var isLongTerm = v.holdingMonths > 12;
		var tax = 0;
		if ( gain > 0 ) {
			if ( isLongTerm ) {
				var taxableGain = Math.max( gain - v.exemption, 0 );
				tax = taxableGain * v.ltcgRate / 100;
			} else {
				tax = gain * v.stcgRate / 100;
			}
		}
		return {
			summary: [
				{ label: 'Estimated Tax Payable', value: formatCurrency( tax ) },
				{ label: 'Capital Gain', value: formatCurrency( gain ) },
				{ label: isLongTerm ? 'Classified as Long-Term' : 'Classified as Short-Term', value: '' },
			],
			segments: [
				{ label: 'Net Gain After Tax', value: gain - tax, color: PALETTE[ 0 ] },
				{ label: 'Tax', value: tax, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcLifeInsurance( v ) {
		var bonus = v.sumAssured * ( v.bonusRate / 100 ) * v.years;
		var maturity = v.sumAssured + bonus;
		var totalPremium = v.premium * v.years;
		return {
			summary: [
				{ label: i18n.maturity || 'Estimated Maturity', value: formatCurrency( maturity ) },
				{ label: 'Total Premium Paid', value: formatCurrency( totalPremium ) },
				{ label: 'Total Bonus', value: formatCurrency( bonus ) },
			],
			segments: [
				{ label: 'Sum Assured', value: v.sumAssured, color: PALETTE[ 0 ] },
				{ label: 'Bonus', value: bonus, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcTermLife( v ) {
		var yearsLeft = Math.max( v.retireAge - v.age, 0 );
		var hlv = v.annualIncome * yearsLeft * 0.8;
		var gap = Math.max( hlv - v.existingCover, 0 );
		return {
			summary: [
				{ label: 'Recommended Life Cover', value: formatCurrency( hlv ) },
				{ label: 'Existing Cover', value: formatCurrency( v.existingCover ) },
				{ label: 'Additional Cover Needed', value: formatCurrency( gap ) },
			],
			segments: [
				{ label: 'Existing Cover', value: v.existingCover, color: PALETTE[ 0 ] },
				{ label: 'Additional Cover Needed', value: gap, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcEndowment( v ) {
		var r = v.returnRate / 100;
		var fv = r === 0 ? v.premium * v.years : v.premium * ( ( Math.pow( 1 + r, v.years ) - 1 ) / r ) * ( 1 + r );
		var invested = v.premium * v.years;
		var gain = fv - invested;
		return {
			summary: [
				{ label: i18n.maturity || 'Maturity Value', value: formatCurrency( fv ) },
				{ label: 'Total Premium Paid', value: formatCurrency( invested ) },
				{ label: i18n.interest || 'Estimated Returns', value: formatCurrency( gain ) },
			],
			segments: [
				{ label: 'Premium Paid', value: invested, color: PALETTE[ 0 ] },
				{ label: 'Returns', value: gain, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcPliLike( v ) {
		var annualPremium = ( v.sumAssured / 1000 ) * v.ratePer1000;
		return {
			summary: [
				{ label: 'Approx. Annual Premium', value: formatCurrency( annualPremium ) },
				{ label: 'Approx. Monthly Equivalent', value: formatCurrency( annualPremium / 12 ) },
			],
			segments: [
				{ label: 'Annual Premium', value: annualPremium, color: PALETTE[ 0 ] },
			],
		};
	}

	function calcPmjjby( v ) {
		var annualPremium = 436;
		var cover = 200000;
		var years = v.years || 1;
		return {
			summary: [
				{ label: 'Life Cover', value: formatCurrency( cover ) },
				{ label: 'Annual Premium', value: formatCurrency( annualPremium ) },
				{ label: 'Total Premium Over Period', value: formatCurrency( annualPremium * years ) },
			],
			segments: [
				{ label: 'Cover', value: cover, color: PALETTE[ 0 ] },
			],
		};
	}

	function calcPmsby( v ) {
		var annualPremium = 20;
		var cover = 200000;
		var years = v.years || 1;
		return {
			summary: [
				{ label: 'Accidental Cover', value: formatCurrency( cover ) },
				{ label: 'Annual Premium', value: formatCurrency( annualPremium ) },
				{ label: 'Total Premium Over Period', value: formatCurrency( annualPremium * years ) },
			],
			segments: [
				{ label: 'Cover', value: cover, color: PALETTE[ 0 ] },
			],
		};
	}

	function calcFrsb( v ) {
		var halfYearlyPayout = ( v.principal * v.rate / 100 ) / 2;
		var totalInterest = v.principal * v.rate / 100 * v.years;
		return {
			summary: [
				{ label: 'Half-Yearly Interest Payout', value: formatCurrency( halfYearlyPayout ) },
				{ label: 'Total Interest (at current rate)', value: formatCurrency( totalInterest ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Total Interest', value: totalInterest, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcSgb( v ) {
		var halfYearlyInterest = ( v.principal * v.interestRate / 100 ) / 2;
		var totalInterest = v.principal * v.interestRate / 100 * v.years;
		var goldValueAtMaturity = v.principal * Math.pow( 1 + v.goldGrowth / 100, v.years );
		var totalReturn = ( goldValueAtMaturity - v.principal ) + totalInterest;
		return {
			summary: [
				{ label: 'Estimated Redemption Value', value: formatCurrency( goldValueAtMaturity ) },
				{ label: 'Total Interest Received (2.5% p.a.)', value: formatCurrency( totalInterest ) },
				{ label: 'Total Estimated Return', value: formatCurrency( totalReturn ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Total Return', value: totalReturn, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcBond54ec( v ) {
		var annualPayout = v.principal * v.rate / 100;
		var totalInterest = annualPayout * 5;
		return {
			summary: [
				{ label: 'Annual Interest Payout', value: formatCurrency( annualPayout ) },
				{ label: 'Total Interest Over 5 Years', value: formatCurrency( totalInterest ) },
				{ label: 'Principal Returned at Maturity', value: formatCurrency( v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Total Interest', value: totalInterest, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcCompoundInterest( v ) {
		var n = Math.max( Math.round( v.compounding ) || 1, 1 );
		var fv = v.principal * Math.pow( 1 + ( v.rate / 100 ) / n, n * v.years );
		return {
			summary: [
				{ label: 'Future Value', value: formatCurrency( fv ) },
				{ label: 'Total Interest Earned', value: formatCurrency( fv - v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: fv - v.principal, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcSimpleInterest( v ) {
		var interest = v.principal * v.rate * v.years / 100;
		return {
			summary: [
				{ label: 'Total Interest', value: formatCurrency( interest ) },
				{ label: 'Total Amount', value: formatCurrency( interest + v.principal ) },
			],
			segments: [
				{ label: 'Principal', value: v.principal, color: PALETTE[ 0 ] },
				{ label: 'Interest', value: interest, color: PALETTE[ 1 ] },
			],
		};
	}

	function calcInflation( v ) {
		var futureCost = v.amount * Math.pow( 1 + v.rate / 100, v.years );
		var realValue = futureCost > 0 ? ( v.amount * v.amount ) / futureCost : v.amount;
		return {
			summary: [
				{ label: "Future Cost of Today's Amount", value: formatCurrency( futureCost ) },
				{ label: "Today's Value of That Future Amount", value: formatCurrency( realValue ) },
			],
			segments: [
				{ label: 'Current Value', value: v.amount, color: PALETTE[ 0 ] },
				{ label: 'Future Cost', value: futureCost, color: PALETTE[ 1 ] },
			],
		};
	}

	var CALCULATORS = {
		'emi': calcEmi,
		'home-loan-emi': calcEmi,
		'home-loan-eligibility': calcHomeLoanEligibility,
		'fd-tdr': calcFdTdr,
		'fd-stdr': calcFdStdr,
		'rd': calcRd,
		'ppf': calcPpf,
		'ssa': calcSsa,
		'scss': calcScss,
		'kvp': calcKvp,
		'mis': calcMis,
		'td': calcTd,
		'nsc': calcNsc,
		'sip': calcSip,
		'elss': calcSip,
		'swp': calcSwp,
		'lumpsum': calcLumpsum,
		'retirement-corpus': calcRetirementCorpus,
		'nps': calcNps,
		'epf': calcEpf,
		'vpf': calcVpf,
		'apy': calcApy,
		'pmsym': calcPmsym,
		'pmvvy': calcPmvvy,
		'gratuity': calcGratuity,
		'income-tax': calcIncomeTax,
		'capital-gains': calcCapitalGains,
		'life-insurance': calcLifeInsurance,
		'term-life': calcTermLife,
		'endowment': calcEndowment,
		'pli': calcPliLike,
		'rpli': calcPliLike,
		'pmjjby': calcPmjjby,
		'pmsby': calcPmsby,
		'frsb': calcFrsb,
		'sgb': calcSgb,
		'bond-54ec': calcBond54ec,
		'compound-interest': calcCompoundInterest,
		'simple-interest': calcSimpleInterest,
		'inflation': calcInflation,
	};

	/* ----------------------------- Wiring -------------------------------- */

	function collectValues( box ) {
		var inputs = box.querySelectorAll( '[data-field]' );
		var values = {};
		var valid = true;
		inputs.forEach( function ( input ) {
			var v = num( input.value );
			values[ input.getAttribute( 'data-field' ) ] = v;
			if ( isNaN( v ) || v < 0 ) {
				valid = false;
				input.classList.add( 'my-calculator-field-error' );
			} else {
				input.classList.remove( 'my-calculator-field-error' );
			}
		} );
		return { values: values, valid: valid };
	}

	function handleCalculate( box ) {
		var type = box.getAttribute( 'data-calc-type' );
		var fn = CALCULATORS[ type ];
		if ( ! fn ) {
			return;
		}

		var collected = collectValues( box );
		var resultsEl = box.querySelector( '.my-calculator-results' );

		if ( ! collected.valid ) {
			resultsEl.classList.remove( 'is-visible' );
			window.alert( i18n.invalid || 'Please enter valid, positive numbers in all fields.' );
			return;
		}

		var result = fn( collected.values );

		renderSummary( box.querySelector( '.my-calculator-summary' ), result.summary );

		var canvas = box.querySelector( '.my-calculator-chart' );
		var chartWrap = box.querySelector( '.my-calculator-chart-wrap' );
		if ( result.segments && result.segments.length && result.segments.some( function ( s ) { return s.value > 0; } ) ) {
			chartWrap.style.display = '';
			drawDonutChart( canvas, result.segments );
			renderLegend( box.querySelector( '.my-calculator-legend' ), result.segments );
		} else {
			chartWrap.style.display = 'none';
		}

		var tableWrap = box.querySelector( '.my-calculator-table-wrap' );
		if ( result.tableHeaders && result.tableRows ) {
			renderTable( tableWrap, result.tableHeaders, result.tableRows );
		} else {
			tableWrap.innerHTML = '';
		}

		resultsEl.classList.add( 'is-visible' );
	}

	function initBox( box ) {
		if ( DATA.color ) {
			box.style.setProperty( '--my-calculator-color', DATA.color );
		}
		var btn = box.querySelector( '.my-calculator-calculate-btn' );
		if ( btn ) {
			btn.addEventListener( 'click', function () {
				handleCalculate( box );
			} );
		}
	}

	function initCategoryTabs( wrap ) {
		var catButtons = wrap.querySelectorAll( ':scope > .my-calculator-cat-nav > .my-calculator-cat-btn' );
		var catPanels = wrap.querySelectorAll( ':scope > .my-calculator-cat-panel' );
		catButtons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				catButtons.forEach( function ( b ) { b.classList.remove( 'is-active' ); } );
				catPanels.forEach( function ( p ) {
					p.classList.remove( 'is-active' );
					p.style.display = 'none';
				} );
				btn.classList.add( 'is-active' );
				var target = wrap.querySelector( '[data-cat-panel="' + btn.getAttribute( 'data-cat' ) + '"]' );
				if ( target ) {
					target.classList.add( 'is-active' );
					target.style.display = '';
				}
			} );
		} );
	}

	function initCategoryPicker( panel ) {
		var select = panel.querySelector( '.my-calculator-select' );
		var emptyState = panel.querySelector( '.my-calculator-empty-state' );
		var calcPanels = panel.querySelectorAll( '.my-calculator-single-panel' );
		if ( ! select ) {
			return;
		}
		select.addEventListener( 'change', function () {
			var val = select.value;
			calcPanels.forEach( function ( p ) {
				p.classList.remove( 'is-active' );
				p.style.display = 'none';
			} );
			if ( val ) {
				if ( emptyState ) {
					emptyState.style.display = 'none';
				}
				var target = panel.querySelector( '[data-calc-panel="' + val + '"]' );
				if ( target ) {
					target.classList.add( 'is-active' );
					target.style.display = '';
				}
			} else if ( emptyState ) {
				emptyState.style.display = '';
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.my-calculator-box' ).forEach( initBox );
		document.querySelectorAll( '.my-calculator-picker' ).forEach( initCategoryTabs );
		document.querySelectorAll( '.my-calculator-cat-panel' ).forEach( initCategoryPicker );

		var resizeTimer = null;
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( function () {
				document.querySelectorAll( '.my-calculator-results.is-visible .my-calculator-chart' ).forEach( function ( canvas ) {
					var box = canvas.closest( '.my-calculator-box' );
					if ( box ) {
						handleCalculate( box );
					}
				} );
			}, 200 );
		} );
	} );
} )();
