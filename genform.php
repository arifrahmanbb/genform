<?php

/**
 * Plugin Name: GenForm - Drag & Drop Form Builder
 * Plugin URI: https://wordpress.org/plugins/genform/
 * Description: Build beautiful, responsive forms effortlessly with drag-and-drop interface.
 * Version: 1.1.0
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
define('GENFORM_VERSION', '1.1.0');
define('GENFORM_PATH', plugin_dir_path(__FILE__));
define('GENFORM_URL', plugin_dir_url(__FILE__));

/**
 * Handle plugin activation logic.
 */
register_activation_hook(__FILE__, array('GenForm\Core', 'activate'));

/**
 * Load the core plugin engine.
 */
add_action('plugins_loaded', function () {
	GenForm\Core::instance();
});
