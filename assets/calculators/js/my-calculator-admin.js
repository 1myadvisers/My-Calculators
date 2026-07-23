/**
 * My Calculator — admin scripts.
 */
( function ( $ ) {
	'use strict';
	$( function () {
		if ( $.fn.wpColorPicker ) {
			$( '.my-calculator-color-field' ).wpColorPicker();
		}
	} );
} )( jQuery );
