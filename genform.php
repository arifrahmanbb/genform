<?php

/**
 * Plugin Name: GenForm - Drag & Drop Form Builder
 * Plugin URI: https://wordpress.org/plugins/genform/
 * Description: Build beautiful, responsive forms effortlessly with drag-and-drop interface.
 * Version: 1.2.0
 * Author: Arif Rahman
 * Author URI: https://profiles.wordpress.org/arifrahman1/
 * License: GPL v3 or later
 * Text Domain: genform
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 8.3
 */

if (! defined('ABSPATH')) {
	exit;
}

// Load Composer Autoloader if available.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Define plugin constants.
 */
define('GENFORM_VERSION', '1.2.0');
define('GENFORM_PATH', plugin_dir_path(__FILE__));
define('GENFORM_URL', plugin_dir_url(__FILE__));

/**
 * Initialize Freemius SDK.
 * Must be loaded before 'plugins_loaded' hook per Freemius docs.
 */
if ( file_exists( GENFORM_PATH . 'includes/freemius-init.php' ) ) {
	require_once GENFORM_PATH . 'includes/freemius-init.php';
}

/**
 * Handle plugin activation logic.
 */
register_activation_hook(__FILE__, array('GenForm\\Core', 'activate'));

/**
 * Freemius uninstall cleanup hook.
 */
if ( function_exists( 'genform_fs' ) ) {
	genform_fs()->add_action( 'after_uninstall', function () {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}genform_entries" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}genform_forms" );
		delete_option( 'genform_version' );
		delete_option( 'genform_settings' );
	});
}

/**
 * Load the core plugin engine.
 */
add_action(
	'plugins_loaded',
	function () {
		GenForm\Core::instance();
	}
);
