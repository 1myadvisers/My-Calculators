<?php
/**
 * Fired when the plugin is uninstalled (deleted) via the Plugins screen.
 *
 * @package My_Calculators
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function my_calculators_delete_options() {
	delete_option( 'my_calculator_settings' );
	delete_option( 'csc_settings' );

	// Note: the wp_csc_check_log table (hashed PAN/mobile + score, used for
	// rate-limiting and audit) is intentionally left in place on uninstall,
	// since it may need to be retained for compliance record-keeping
	// periods. Site owners who want it removed can drop the
	// `{$wpdb->prefix}csc_check_log` table manually via phpMyAdmin or WP-CLI.
}

my_calculators_delete_options();

// Clean up for multisite installs.
if ( is_multisite() ) {
	global $wpdb;
	$my_calculators_blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $my_calculators_blog_ids as $my_calculators_blog_id ) {
		switch_to_blog( $my_calculators_blog_id );
		my_calculators_delete_options();
		restore_current_blog();
	}
}
