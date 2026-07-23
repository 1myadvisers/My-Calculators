<?php
/**
 * Handles enqueueing of CSS/JS assets.
 *
 * @package My_Calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class My_Calculator_Assets
 */
class My_Calculator_Assets {

	/**
	 * Enqueue front-end assets only when the shortcode is present on the page.
	 */
	public function enqueue_public_assets() {
		if ( ! is_singular() ) {
			return;
		}

		global $post;
		if ( empty( $post->post_content ) || ! has_shortcode( $post->post_content, 'my_calculator' ) ) {
			return;
		}

		wp_enqueue_style(
			'my-calculator-public',
			MY_CALCULATORS_URL . 'assets/calculators/css/my-calculator.css',
			array(),
			MY_CALCULATORS_VERSION
		);

		wp_enqueue_script(
			'my-calculator-public',
			MY_CALCULATORS_URL . 'assets/calculators/js/my-calculator.js',
			array(),
			MY_CALCULATORS_VERSION,
			true
		);

		$settings = My_Calculator_Settings::get_settings();

		wp_localize_script(
			'my-calculator-public',
			'myCalculatorData',
			array(
				'currency'   => $settings['currency_symbol'],
				'color'      => $settings['primary_color'],
				'rates'      => array(
					'sip'      => (float) $settings['default_rate_sip'],
					'ppf'      => (float) $settings['default_rate_ppf'],
					'fd'       => (float) $settings['default_rate_fd'],
					'rd'       => (float) $settings['default_rate_rd'],
					'loan'     => (float) $settings['default_rate_loan'],
					'annuity'  => (float) $settings['default_rate_annuity'],
					'inflation'=> (float) $settings['default_inflation'],
				),
				'i18n'       => array(
					'principal'   => __( 'Principal / Invested Amount', 'my-calculators' ),
					'interest'    => __( 'Total Interest / Returns', 'my-calculators' ),
					'totalPayable'=> __( 'Total Payable', 'my-calculators' ),
					'maturity'    => __( 'Maturity Value', 'my-calculators' ),
					'monthlyEmi'  => __( 'Monthly EMI', 'my-calculators' ),
					'invalid'     => __( 'Please enter valid, positive numbers in all fields.', 'my-calculators' ),
				),
			)
		);
	}

	/**
	 * Enqueue admin assets only on this plugin's settings screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_my-calculators' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style(
			'my-calculator-admin',
			MY_CALCULATORS_URL . 'assets/calculators/css/my-calculator-admin.css',
			array( 'wp-color-picker' ),
			MY_CALCULATORS_VERSION
		);

		wp_enqueue_script(
			'my-calculator-admin',
			MY_CALCULATORS_URL . 'assets/calculators/js/my-calculator-admin.js',
			array( 'wp-color-picker', 'jquery' ),
			MY_CALCULATORS_VERSION,
			true
		);
	}
}
