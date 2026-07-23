<?php
/**
 * Registers all hooks for the plugin.
 *
 * @package My_Calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class My_Calculator_Loader
 */
class My_Calculator_Loader {

	/**
	 * Run the loader: wire up i18n, settings, shortcodes, and assets.
	 */
	public function run() {
		$settings = new My_Calculator_Settings();
		add_action( 'admin_menu', array( $settings, 'add_settings_page' ) );
		add_action( 'admin_init', array( $settings, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . MY_CALCULATOR_BASENAME, array( $settings, 'add_settings_link' ) );

		$shortcode = new My_Calculator_Shortcode();
		add_action( 'init', array( $shortcode, 'register_shortcodes' ) );

		$assets = new My_Calculator_Assets();
		add_action( 'wp_enqueue_scripts', array( $assets, 'enqueue_public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $assets, 'enqueue_admin_assets' ) );

		// Lightweight, non-blocking compatibility notice (no external calls).
		add_action( 'admin_notices', array( $this, 'maybe_show_compat_notice' ) );
	}

	/**
	 * Show an admin notice only if running on an unusually old PHP version,
	 * to help admins keep the site compatible with current and upcoming releases.
	 */
	public function maybe_show_compat_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			echo '<div class="notice notice-warning"><p>' .
				esc_html__( 'My Calculator works best on PHP 7.4 or newer. Please ask your host to upgrade PHP for better security and performance.', 'my-calculators' ) .
				'</p></div>';
		}
	}
}
