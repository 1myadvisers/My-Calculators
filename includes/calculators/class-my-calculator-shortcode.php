<?php
/**
 * Shortcode handling: renders calculator markup; all math runs in JS.
 *
 * @package My_Calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class My_Calculator_Shortcode
 */
class My_Calculator_Shortcode {

	/**
	 * Register the [my_calculator] shortcode.
	 */
	public function register_shortcodes() {
		add_shortcode( 'my_calculator', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Field configuration for every calculator type.
	 *
	 * @return array
	 */
	private function get_field_config() {
		$s = My_Calculator_Settings::get_settings();

		return array(
			// ---------------- Bank & Loans ----------------
			'emi' => array(
				'label'  => __( 'EMI Calculator', 'my-calculators' ),
				'note'   => __( 'Standard reducing-balance EMI for any loan.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Loan Amount', 'my-calculators' ), 'number', 1000000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_loan'], 0.01, 0.01 ),
					'years'     => array( __( 'Loan Tenure (Years)', 'my-calculators' ), 'number', 5, 1, 1 ),
				),
			),
			'home-loan-emi' => array(
				'label'  => __( 'Home Loan EMI Calculator', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Home Loan Amount', 'my-calculators' ), 'number', 3500000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_loan'], 0.01, 0.01 ),
					'years'     => array( __( 'Loan Tenure (Years)', 'my-calculators' ), 'number', 20, 1, 1 ),
				),
			),
			'home-loan-eligibility' => array(
				'label'  => __( 'Home Loan Eligibility Calculator', 'my-calculators' ),
				'fields' => array(
					'monthlyIncome' => array( __( 'Net Monthly Income', 'my-calculators' ), 'number', 80000, 1, 1 ),
					'existingEmi'   => array( __( 'Existing Monthly EMI (if any)', 'my-calculators' ), 'number', 0, 0, 1 ),
					'rate'          => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_loan'], 0.01, 0.01 ),
					'years'         => array( __( 'Loan Tenure (Years)', 'my-calculators' ), 'number', 20, 1, 1 ),
					'foirPercent'   => array( __( 'Max EMI as % of Income (FOIR)', 'my-calculators' ), 'number', 50, 1, 1 ),
				),
			),
			'fd-tdr' => array(
				'label'  => __( 'Fixed Deposit — TDR (Interest Payout)', 'my-calculators' ),
				'note'   => __( 'Non-cumulative FD; interest is paid out periodically.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_fd'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'my-calculators' ), 'number', 5, 1, 0.5 ),
				),
			),
			'fd-stdr' => array(
				'label'  => __( 'Fixed Deposit — STDR (Cumulative)', 'my-calculators' ),
				'note'   => __( 'Cumulative FD, quarterly compounding.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_fd'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'my-calculators' ), 'number', 5, 1, 0.5 ),
				),
			),
			'rd' => array(
				'label'  => __( 'Recurring Deposit Calculator', 'my-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Monthly Deposit', 'my-calculators' ), 'number', 5000, 1, 1 ),
					'rate'    => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_rd'], 0.01, 0.01 ),
					'years'   => array( __( 'Tenure (Years)', 'my-calculators' ), 'number', 5, 1, 1 ),
				),
			),

			// ---------------- Post Office & Govt Savings ----------------
			'ppf' => array(
				'label'  => __( 'PPF Calculator', 'my-calculators' ),
				'note'   => __( 'Annual deposit limit is ₹1,50,000. Minimum lock-in is 15 years.', 'my-calculators' ),
				'fields' => array(
					'yearly' => array( __( 'Yearly Deposit', 'my-calculators' ), 'number', 150000, 1, 1 ),
					'rate'   => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_ppf'], 0.01, 0.01 ),
					'years'  => array( __( 'Duration (Years)', 'my-calculators' ), 'number', 15, 1, 1 ),
				),
			),
			'ssa' => array(
				'label'  => __( 'Sukanya Samriddhi Account Calculator', 'my-calculators' ),
				'note'   => __( 'Deposits made for 15 years; matures 21 years from account opening.', 'my-calculators' ),
				'fields' => array(
					'yearly'      => array( __( 'Yearly Deposit', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'        => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_ssa'], 0.01, 0.01 ),
					'depositYears'=> array( __( 'Deposit Period (Years)', 'my-calculators' ), 'number', 15, 1, 1 ),
					'totalYears'  => array( __( 'Total Maturity Period (Years)', 'my-calculators' ), 'number', 21, 1, 1 ),
				),
			),
			'scss' => array(
				'label'  => __( 'Senior Citizens Savings Scheme Calculator', 'my-calculators' ),
				'note'   => __( 'Fixed 5-year tenure, quarterly interest payout. Max deposit ₹30,00,000.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'my-calculators' ), 'number', 1500000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_scss'], 0.01, 0.01 ),
				),
			),
			'kvp' => array(
				'label'  => __( 'Kisan Vikas Patra Calculator', 'my-calculators' ),
				'note'   => __( 'Investment doubles over a fixed government-notified period.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_kvp'], 0.01, 0.01 ),
				),
			),
			'mis' => array(
				'label'  => __( 'Monthly Income Scheme Calculator', 'my-calculators' ),
				'note'   => __( '5-year post office scheme. Max deposit ₹9,00,000 single / ₹15,00,000 joint.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'my-calculators' ), 'number', 900000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_mis'], 0.01, 0.01 ),
				),
			),
			'td' => array(
				'label'  => __( 'Post Office Time Deposit Calculator', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_td'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'my-calculators' ), 'number', 5, 1, 1 ),
				),
			),
			'nsc' => array(
				'label'  => __( 'National Savings Certificate Calculator', 'my-calculators' ),
				'note'   => __( 'Fixed 5-year tenure, compounded annually.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_nsc'], 0.01, 0.01 ),
				),
			),

			// ---------------- Mutual Funds ----------------
			'sip' => array(
				'label'  => __( 'SIP Calculator', 'my-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Monthly Investment', 'my-calculators' ), 'number', 10000, 1, 1 ),
					'rate'    => array( __( 'Expected Annual Return (%)', 'my-calculators' ), 'number', $s['default_rate_sip'], 0.01, 0.01 ),
					'years'   => array( __( 'Investment Period (Years)', 'my-calculators' ), 'number', 10, 1, 1 ),
				),
			),
			'elss' => array(
				'label'  => __( 'ELSS Calculator', 'my-calculators' ),
				'note'   => __( 'Mandatory 3-year lock-in. Qualifies for Section 80C under the old tax regime.', 'my-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Monthly SIP Investment', 'my-calculators' ), 'number', 12500, 1, 1 ),
					'rate'    => array( __( 'Expected Annual Return (%)', 'my-calculators' ), 'number', $s['default_rate_elss'], 0.01, 0.01 ),
					'years'   => array( __( 'Investment Period (Years)', 'my-calculators' ), 'number', 3, 1, 1 ),
				),
			),
			'swp' => array(
				'label'  => __( 'SWP Calculator', 'my-calculators' ),
				'note'   => __( 'Estimates how long a corpus lasts with fixed monthly withdrawals.', 'my-calculators' ),
				'fields' => array(
					'corpus'     => array( __( 'Starting Corpus', 'my-calculators' ), 'number', 2000000, 1, 1 ),
					'withdrawal' => array( __( 'Monthly Withdrawal', 'my-calculators' ), 'number', 15000, 1, 1 ),
					'rate'       => array( __( 'Expected Annual Return (%)', 'my-calculators' ), 'number', $s['default_rate_swp'], 0.01, 0.01 ),
				),
			),
			'lumpsum' => array(
				'label'  => __( 'Lumpsum Investment Calculator', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Expected Annual Return (%)', 'my-calculators' ), 'number', $s['default_rate_lumpsum'], 0.01, 0.01 ),
					'years'     => array( __( 'Investment Period (Years)', 'my-calculators' ), 'number', 10, 1, 1 ),
				),
			),

			// ---------------- Retirement ----------------
			'retirement-corpus' => array(
				'label'  => __( 'Retirement Corpus Calculator', 'my-calculators' ),
				'fields' => array(
					'currentExpense' => array( __( 'Current Monthly Expense', 'my-calculators' ), 'number', 40000, 1, 1 ),
					'currentAge'     => array( __( 'Current Age', 'my-calculators' ), 'number', 30, 1, 1 ),
					'retireAge'      => array( __( 'Retirement Age', 'my-calculators' ), 'number', 60, 1, 1 ),
					'lifeExpectancy' => array( __( 'Life Expectancy (Age)', 'my-calculators' ), 'number', 85, 1, 1 ),
					'inflation'      => array( __( 'Expected Inflation (%)', 'my-calculators' ), 'number', $s['default_inflation'], 0.01, 0.01 ),
					'postReturn'     => array( __( 'Post-Retirement Return Rate (%)', 'my-calculators' ), 'number', 7, 0.01, 0.01 ),
				),
			),
			'nps' => array(
				'label'  => __( 'NPS Calculator', 'my-calculators' ),
				'note'   => __( 'At least 40% of the corpus must be used to buy an annuity.', 'my-calculators' ),
				'fields' => array(
					'monthly'     => array( __( 'Monthly Contribution', 'my-calculators' ), 'number', 5000, 1, 1 ),
					'rate'        => array( __( 'Expected Annual Return (%)', 'my-calculators' ), 'number', $s['default_rate_nps'], 0.01, 0.01 ),
					'years'       => array( __( 'Years to Retirement', 'my-calculators' ), 'number', 25, 1, 1 ),
					'annuityPct'  => array( __( 'Annuity Purchase (%, min 40)', 'my-calculators' ), 'number', 40, 40, 1 ),
					'annuityRate' => array( __( 'Assumed Annuity Rate (%)', 'my-calculators' ), 'number', 6, 0.01, 0.01 ),
				),
			),
			'epf' => array(
				'label'  => __( 'EPF Calculator', 'my-calculators' ),
				'fields' => array(
					'basic'         => array( __( 'Basic + DA (Monthly)', 'my-calculators' ), 'number', 30000, 1, 1 ),
					'employeePct'   => array( __( 'Employee Contribution (%)', 'my-calculators' ), 'number', 12, 0.01, 0.01 ),
					'employerPct'   => array( __( 'Employer Contribution to EPF (%)', 'my-calculators' ), 'number', 3.67, 0.01, 0.01 ),
					'rate'          => array( __( 'EPF Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_epf'], 0.01, 0.01 ),
					'years'         => array( __( 'Years to Retirement', 'my-calculators' ), 'number', 25, 1, 1 ),
					'increment'     => array( __( 'Annual Salary Increment (%)', 'my-calculators' ), 'number', 5, 0.01, 0.01 ),
				),
			),
			'vpf' => array(
				'label'  => __( 'VPF Calculator', 'my-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Voluntary Monthly Contribution', 'my-calculators' ), 'number', 10000, 1, 1 ),
					'rate'    => array( __( 'VPF Interest Rate (%)', 'my-calculators' ), 'number', $s['default_rate_vpf'], 0.01, 0.01 ),
					'years'   => array( __( 'Years to Retirement', 'my-calculators' ), 'number', 20, 1, 1 ),
				),
			),
			'apy' => array(
				'label'  => __( 'Atal Pension Yojana Calculator', 'my-calculators' ),
				'note'   => __( 'Approximate model — actual APY contribution slabs are fixed by government notification. Check pfrda.org.in for the exact figure.', 'my-calculators' ),
				'fields' => array(
					'targetPension' => array( __( 'Target Monthly Pension (₹1000–5000)', 'my-calculators' ), 'number', 5000, 1000, 1 ),
					'age'           => array( __( 'Current Age', 'my-calculators' ), 'number', 30, 18, 1 ),
					'rate'          => array( __( 'Assumed Return (%)', 'my-calculators' ), 'number', $s['default_rate_apy'], 0.01, 0.01 ),
				),
			),
			'pmsym' => array(
				'label'  => __( 'PM-SYM Calculator', 'my-calculators' ),
				'note'   => __( 'Approximate model — actual PM-SYM contribution slabs (₹55–₹200/month) are fixed by age.', 'my-calculators' ),
				'fields' => array(
					'age'  => array( __( 'Current Age (18–40)', 'my-calculators' ), 'number', 25, 18, 1 ),
					'rate' => array( __( 'Assumed Fund Return (%)', 'my-calculators' ), 'number', 8, 0.01, 0.01 ),
				),
			),
			'pmvvy' => array(
				'label'  => __( 'PM Vaya Vandana Yojana Calculator', 'my-calculators' ),
				'note'   => __( 'Administered by LIC; 10-year policy term, purchase price refunded on maturity.', 'my-calculators' ),
				'fields' => array(
					'purchasePrice' => array( __( 'Purchase Price (Lump Sum)', 'my-calculators' ), 'number', 1500000, 1, 1 ),
					'rate'          => array( __( 'Assured Return Rate (%)', 'my-calculators' ), 'number', 7.4, 0.01, 0.01 ),
				),
			),
			'gratuity' => array(
				'label'  => __( 'Gratuity Calculator', 'my-calculators' ),
				'note'   => __( 'Formula: (15 × last drawn salary × years of service) / 26. Exemption capped at ₹20,00,000.', 'my-calculators' ),
				'fields' => array(
					'salary' => array( __( 'Last Drawn Monthly Salary (Basic + DA)', 'my-calculators' ), 'number', 60000, 1, 1 ),
					'years'  => array( __( 'Years of Service', 'my-calculators' ), 'number', 10, 1, 1 ),
				),
			),

			// ---------------- Tax ----------------
			'income-tax' => array(
				'label'  => __( 'Income Tax Calculator', 'my-calculators' ),
				'note'   => __( 'Illustrative slabs — verify current rates against the latest CBDT notification. Not tax advice.', 'my-calculators' ),
				'fields' => array(
					'income'      => array( __( 'Gross Annual Income', 'my-calculators' ), 'number', 1500000, 1, 1 ),
					'stdDeduction'=> array( __( 'Standard Deduction', 'my-calculators' ), 'number', 75000, 0, 1 ),
					'deductions'  => array( __( 'Other Deductions (80C, 80D, HRA — Old Regime only)', 'my-calculators' ), 'number', 150000, 0, 1 ),
					'regime'      => array( __( 'Regime: 1 = New, 0 = Old', 'my-calculators' ), 'number', 1, 0, 1 ),
					'cess'        => array( __( 'Health &amp; Education Cess (%)', 'my-calculators' ), 'number', $s['default_cess_rate'], 0.01, 0.01 ),
				),
			),
			'capital-gains' => array(
				'label'  => __( 'Capital Gains Tax Calculator', 'my-calculators' ),
				'note'   => __( 'Assumes listed equity / equity mutual fund treatment (&gt;12 months = long-term). Not tax advice.', 'my-calculators' ),
				'fields' => array(
					'purchaseValue' => array( __( 'Purchase Value', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'saleValue'     => array( __( 'Sale Value', 'my-calculators' ), 'number', 180000, 1, 1 ),
					'holdingMonths' => array( __( 'Holding Period (Months)', 'my-calculators' ), 'number', 18, 1, 1 ),
					'ltcgRate'      => array( __( 'LTCG Rate — Equity (%)', 'my-calculators' ), 'number', $s['default_ltcg_rate'], 0.01, 0.01 ),
					'stcgRate'      => array( __( 'STCG Rate — Equity (%)', 'my-calculators' ), 'number', $s['default_stcg_rate'], 0.01, 0.01 ),
					'exemption'     => array( __( 'LTCG Exemption Limit (₹/year)', 'my-calculators' ), 'number', 125000, 0, 1 ),
				),
			),

			// ---------------- Insurance ----------------
			'life-insurance' => array(
				'label'  => __( 'Life Insurance Maturity Calculator', 'my-calculators' ),
				'fields' => array(
					'sumAssured' => array( __( 'Sum Assured', 'my-calculators' ), 'number', 1000000, 1, 1 ),
					'premium'    => array( __( 'Annual Premium', 'my-calculators' ), 'number', 25000, 1, 1 ),
					'years'      => array( __( 'Policy Term (Years)', 'my-calculators' ), 'number', 20, 1, 1 ),
					'bonusRate'  => array( __( 'Expected Annual Bonus Rate (%)', 'my-calculators' ), 'number', $s['default_rate_annuity'], 0.01, 0.01 ),
				),
			),
			'term-life' => array(
				'label'  => __( 'Term Life Policy Calculator (Human Life Value)', 'my-calculators' ),
				'fields' => array(
					'annualIncome'  => array( __( 'Current Annual Income', 'my-calculators' ), 'number', 800000, 1, 1 ),
					'age'           => array( __( 'Current Age', 'my-calculators' ), 'number', 30, 1, 1 ),
					'retireAge'     => array( __( 'Expected Retirement Age', 'my-calculators' ), 'number', 60, 1, 1 ),
					'existingCover' => array( __( 'Existing Life Cover (if any)', 'my-calculators' ), 'number', 0, 0, 1 ),
				),
			),
			'endowment' => array(
				'label'  => __( 'Endowment Policy Return Calculator', 'my-calculators' ),
				'fields' => array(
					'premium'    => array( __( 'Annual Premium', 'my-calculators' ), 'number', 30000, 1, 1 ),
					'years'      => array( __( 'Policy Term (Years)', 'my-calculators' ), 'number', 15, 1, 1 ),
					'returnRate' => array( __( 'Expected Annual Return (%)', 'my-calculators' ), 'number', 5.5, 0.01, 0.01 ),
				),
			),
			'pli' => array(
				'label'  => __( 'Postal Life Insurance (PLI) Estimator', 'my-calculators' ),
				'note'   => __( 'Approximate — actual premiums depend on age and policy type. Use the official India Post calculator for an exact quote.', 'my-calculators' ),
				'fields' => array(
					'sumAssured' => array( __( 'Sum Assured', 'my-calculators' ), 'number', 500000, 1, 1 ),
					'ratePer1000'=> array( __( 'Premium Rate per ₹1000/year', 'my-calculators' ), 'number', $s['default_pli_rate'], 0.01, 0.01 ),
				),
			),
			'rpli' => array(
				'label'  => __( 'Rural PLI (RPLI) Estimator', 'my-calculators' ),
				'note'   => __( 'Approximate — use the official India Post calculator for an exact quote.', 'my-calculators' ),
				'fields' => array(
					'sumAssured' => array( __( 'Sum Assured', 'my-calculators' ), 'number', 300000, 1, 1 ),
					'ratePer1000'=> array( __( 'Premium Rate per ₹1000/year', 'my-calculators' ), 'number', $s['default_rpli_rate'], 0.01, 0.01 ),
				),
			),
			'pmjjby' => array(
				'label'  => __( 'PM Jeevan Jyoti Bima Yojana Overview', 'my-calculators' ),
				'note'   => __( 'Fixed ₹2,00,000 cover for a fixed ₹436/year premium, ages 18–50 (renewable to 55).', 'my-calculators' ),
				'fields' => array(
					'years' => array( __( 'Number of Years Enrolled', 'my-calculators' ), 'number', 1, 1, 1 ),
				),
			),
			'pmsby' => array(
				'label'  => __( 'PM Suraksha Bima Yojana Overview', 'my-calculators' ),
				'note'   => __( 'Fixed ₹2,00,000 accidental cover for a fixed ₹20/year premium, ages 18–70.', 'my-calculators' ),
				'fields' => array(
					'years' => array( __( 'Number of Years Enrolled', 'my-calculators' ), 'number', 1, 1, 1 ),
				),
			),

			// ---------------- Bonds ----------------
			'frsb' => array(
				'label'  => __( 'Floating Rate Savings Bonds Calculator', 'my-calculators' ),
				'note'   => __( 'Rate resets every 6 months in line with NSC rate + spread; actual payouts will vary.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Current Floating Rate (%)', 'my-calculators' ), 'number', $s['default_frsb_rate'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'my-calculators' ), 'number', 7, 1, 1 ),
				),
			),
			'sgb' => array(
				'label'  => __( 'Sovereign Gold Bond Calculator', 'my-calculators' ),
				'note'   => __( 'Redemption value tracks the gold price at maturity — unpredictable. Capital gains at maturity are tax-exempt for individuals.', 'my-calculators' ),
				'fields' => array(
					'principal'   => array( __( 'Investment Amount', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'interestRate'=> array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_sgb_rate'], 0.01, 0.01 ),
					'goldGrowth'  => array( __( 'Assumed Gold Price Growth (%/yr)', 'my-calculators' ), 'number', $s['default_sgb_gold_growth'], 0.01, 0.01 ),
					'years'       => array( __( 'Holding Period (Years)', 'my-calculators' ), 'number', 8, 1, 1 ),
				),
			),
			'bond-54ec' => array(
				'label'  => __( '54EC Capital Gains Bonds Calculator', 'my-calculators' ),
				'note'   => __( '5-year lock-in; exempts invested capital gain up to ₹50,00,000. Interest earned is taxable.', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'my-calculators' ), 'number', 5000000, 1, 1 ),
					'rate'      => array( __( 'Interest Rate (%)', 'my-calculators' ), 'number', $s['default_54ec_rate'], 0.01, 0.01 ),
				),
			),

			// ---------------- General ----------------
			'compound-interest' => array(
				'label'  => __( 'Compound Interest Calculator', 'my-calculators' ),
				'fields' => array(
					'principal'    => array( __( 'Principal', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'         => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_compound_rate'], 0.01, 0.01 ),
					'years'        => array( __( 'Duration (Years)', 'my-calculators' ), 'number', 5, 1, 1 ),
					'compounding'  => array( __( 'Compoundings per Year (1/2/4/12)', 'my-calculators' ), 'number', 4, 1, 1 ),
				),
			),
			'simple-interest' => array(
				'label'  => __( 'Simple Interest Calculator', 'my-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Principal', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'my-calculators' ), 'number', $s['default_simple_rate'], 0.01, 0.01 ),
					'years'     => array( __( 'Duration (Years)', 'my-calculators' ), 'number', 5, 1, 1 ),
				),
			),
			'inflation' => array(
				'label'  => __( 'Inflation Calculator', 'my-calculators' ),
				'fields' => array(
					'amount' => array( __( 'Current Amount / Cost', 'my-calculators' ), 'number', 100000, 1, 1 ),
					'rate'   => array( __( 'Assumed Inflation Rate (%)', 'my-calculators' ), 'number', $s['default_inflation'], 0.01, 0.01 ),
					'years'  => array( __( 'Duration (Years)', 'my-calculators' ), 'number', 10, 1, 1 ),
				),
			),
		);
	}

	/**
	 * Render the [my_calculator] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'type' => '',
			),
			$atts,
			'my_calculator'
		);

		$settings   = My_Calculator_Settings::get_settings();
		$all_fields = $this->get_field_config();
		$enabled    = $settings['enabled'];
		$type       = sanitize_key( $atts['type'] );

		ob_start();

		echo '<div class="my-calculator-wrap">';

		if ( '' !== $type ) {
			if ( ! isset( $all_fields[ $type ] ) || ! in_array( $type, $enabled, true ) ) {
				echo '<p class="my-calculator-error">' . esc_html__( 'This calculator is not available.', 'my-calculators' ) . '</p>';
				echo '</div>';
				return ob_get_clean();
			}
			$this->render_single_calculator( $type, $all_fields[ $type ] );
		} else {
			$this->render_categorized_calculators( $all_fields, $enabled );
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Render a single calculator block.
	 *
	 * @param string $type   Calculator slug.
	 * @param array  $config Field configuration for the calculator.
	 */
	private function render_single_calculator( $type, $config ) {
		$uid = 'mc-' . $type . '-' . wp_unique_id();
		?>
		<div class="my-calculator-box" id="<?php echo esc_attr( $uid ); ?>" data-calc-type="<?php echo esc_attr( $type ); ?>">
			<h3 class="my-calculator-title"><?php echo esc_html( $config['label'] ); ?></h3>
			<?php if ( ! empty( $config['note'] ) ) : ?>
				<p class="my-calculator-note"><?php echo wp_kses_post( $config['note'] ); ?></p>
			<?php endif; ?>
			<form class="my-calculator-form" onsubmit="return false;">
				<?php foreach ( $config['fields'] as $key => $field ) :
					list( $label, $input_type, $default, $min, $step ) = $field;
					$field_id = $uid . '-' . $key;
					?>
					<div class="my-calculator-field">
						<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
						<input
							type="<?php echo esc_attr( $input_type ); ?>"
							id="<?php echo esc_attr( $field_id ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							data-field="<?php echo esc_attr( $key ); ?>"
							value="<?php echo esc_attr( $default ); ?>"
							min="<?php echo esc_attr( $min ); ?>"
							step="<?php echo esc_attr( $step ); ?>"
							inputmode="decimal"
						/>
					</div>
				<?php endforeach; ?>
				<button type="button" class="my-calculator-submit my-calculator-calculate-btn">
					<?php esc_html_e( 'Calculate', 'my-calculators' ); ?>
				</button>
			</form>

			<div class="my-calculator-results" aria-live="polite">
				<div class="my-calculator-summary"></div>
				<div class="my-calculator-chart-wrap">
					<canvas class="my-calculator-chart" width="280" height="280"></canvas>
					<ul class="my-calculator-legend"></ul>
				</div>
				<div class="my-calculator-table-wrap"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render category tabs; clicking a category reveals a dropdown scoped to
	 * that category's calculators, and only the chosen calculator's panel is
	 * shown at any time. Panels also carry an inline `display:none` so they
	 * stay hidden even if a stale/cached copy of the CSS is being served.
	 *
	 * @param array $all_fields Field config for every calculator type.
	 * @param array $enabled    List of enabled calculator slugs.
	 */
	private function render_categorized_calculators( $all_fields, $enabled ) {
		$uid        = 'mc-picker-' . wp_unique_id();
		$categories = My_Calculator_Settings::get_calculator_categories();
		$first_cat  = true;
		?>
		<div class="my-calculator-picker" id="<?php echo esc_attr( $uid ); ?>">
			<div class="my-calculator-cat-nav" role="tablist">
				<?php foreach ( $categories as $cat_label => $calcs ) :
					$has_enabled = false;
					foreach ( $calcs as $slug => $label ) {
						if ( in_array( $slug, $enabled, true ) && isset( $all_fields[ $slug ] ) ) {
							$has_enabled = true;
							break;
						}
					}
					if ( ! $has_enabled ) {
						continue;
					}
					$cat_slug = sanitize_title( $cat_label );
					?>
					<button type="button" class="my-calculator-cat-btn<?php echo $first_cat ? ' is-active' : ''; ?>" data-cat="<?php echo esc_attr( $cat_slug ); ?>">
						<?php echo esc_html( $cat_label ); ?>
					</button>
					<?php $first_cat = false; ?>
				<?php endforeach; ?>
			</div>

			<?php
			$first_cat = true;
			foreach ( $categories as $cat_label => $calcs ) :
				$cat_calcs = array();
				foreach ( $calcs as $slug => $label ) {
					if ( in_array( $slug, $enabled, true ) && isset( $all_fields[ $slug ] ) ) {
						$cat_calcs[ $slug ] = $all_fields[ $slug ];
					}
				}
				if ( empty( $cat_calcs ) ) {
					continue;
				}
				$cat_slug   = sanitize_title( $cat_label );
				$select_id  = $uid . '-' . $cat_slug . '-select';
				?>
				<div class="my-calculator-cat-panel<?php echo $first_cat ? ' is-active' : ''; ?>"
					data-cat-panel="<?php echo esc_attr( $cat_slug ); ?>"
					<?php echo $first_cat ? '' : 'style="display:none;"'; ?>>

					<label class="my-calculator-picker-label" for="<?php echo esc_attr( $select_id ); ?>">
						<?php esc_html_e( 'Choose a calculator', 'my-calculators' ); ?>
					</label>
					<select class="my-calculator-select" id="<?php echo esc_attr( $select_id ); ?>">
						<option value="" selected="selected" disabled="disabled">
							<?php esc_html_e( '— Select a calculator —', 'my-calculators' ); ?>
						</option>
						<?php foreach ( $cat_calcs as $slug => $config ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $config['label'] ); ?></option>
						<?php endforeach; ?>
					</select>

					<div class="my-calculator-empty-state">
						<?php esc_html_e( 'Pick a calculator above to get started.', 'my-calculators' ); ?>
					</div>

					<div class="my-calculator-panels">
						<?php foreach ( $cat_calcs as $slug => $config ) : ?>
							<div class="my-calculator-single-panel" data-calc-panel="<?php echo esc_attr( $slug ); ?>" style="display:none;">
								<?php $this->render_single_calculator( $slug, $config ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php $first_cat = false; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
