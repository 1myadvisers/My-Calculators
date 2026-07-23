<?php
/**
 * Plugin Name:       My Calculators
 * Plugin URI:        https://myadvisers.in/my-calculators
 * Description:       All-in-one financial toolkit for India — EMI, SIP, PPF, FD, RD, Insurance, Annuity, Retirement, Home Loan calculators and more. Everything under one settings dashboard.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Biswajit Barman
 * Author URI:        https://myadvisers.in
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-calculators
 * Domain Path:       /languages
 *
 * @package My_Calculators
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin constants, shared by both the Calculators module and the
 * Credit Score module.
 */
define( 'MY_CALCULATORS_VERSION', '1.2.0' );
define( 'MY_CALCULATORS_FILE', __FILE__ );
define( 'MY_CALCULATORS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MY_CALCULATORS_URL', plugin_dir_url( __FILE__ ) );
define( 'MY_CALCULATORS_BASENAME', plugin_basename( __FILE__ ) );

// Back-compat aliases: the Calculators module's classes still reference
// these older constant names internally.
define( 'MY_CALCULATOR_VERSION', MY_CALCULATORS_VERSION );
define( 'MY_CALCULATOR_FILE', MY_CALCULATORS_FILE );
define( 'MY_CALCULATOR_PATH', MY_CALCULATORS_PATH );
define( 'MY_CALCULATOR_URL', MY_CALCULATORS_URL );
define( 'MY_CALCULATOR_BASENAME', MY_CALCULATORS_BASENAME );

/**
 * Module 1: Financial Calculators (EMI, SIP, PPF, FD, RD, etc.)
 */
require_once MY_CALCULATORS_PATH . 'includes/calculators/class-my-calculator-loader.php';
require_once MY_CALCULATORS_PATH . 'includes/calculators/class-my-calculator-settings.php';
require_once MY_CALCULATORS_PATH . 'includes/calculators/class-my-calculator-shortcode.php';
require_once MY_CALCULATORS_PATH . 'includes/calculators/class-my-calculator-assets.php';

/**
 * Begins execution of the plugin: wires up the financial calculators module
 * under a "My Calculators" admin menu.
 *
 * This plugin does not alter WordPress's built-in update routines in any way.
 * It relies entirely on core's native plugin update mechanism.
 *
 * @since 1.0.0
 */
function my_calculators_run() {
	// Calculators module.
	$calculator_plugin = new My_Calculator_Loader();
	$calculator_plugin->run();
}
add_action( 'plugins_loaded', 'my_calculators_run' );

/**
 * Re-run schema setup for sites that already had the plugin active.
 * Currently empty for calculators-only mode (no database tables required).
 * Kept for future extension if calculator features add database components.
 */
function my_calculators_maybe_upgrade() {
	$installed_version = get_option( 'my_calculators_db_version', '0' );
	if ( version_compare( $installed_version, MY_CALCULATORS_VERSION, '>=' ) ) {
		return;
	}
	// No database tables needed for calculators module.
	update_option( 'my_calculators_db_version', MY_CALCULATORS_VERSION );
}

/**
 * Code that runs on plugin activation: sets defaults for the Calculators module.
 */
function my_calculators_activate() {
	// Calculators module defaults.
	if ( ! get_option( 'my_calculator_settings' ) ) {
		add_option( 'my_calculator_settings', My_Calculator_Settings::get_default_settings() );
	}

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'my_calculators_activate' );

/**
 * Code that runs on plugin deactivation.
 */
function my_calculators_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'my_calculators_deactivate' );
