<?php
/**
 * Plugin Name: GenForm
 * Plugin URI: https://example.com/genform
 * Description: A powerful form builder plugin for WordPress with drag-and-drop interface.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: genform
 * Domain Path: /i18n
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Constants
if ( ! defined( 'GENFORM_VERSION' ) ) {
    define( 'GENFORM_VERSION', '1.0.0' );
}
if ( ! defined( 'GENFORM_PLUGIN_FILE' ) ) {
    define( 'GENFORM_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'GENFORM_PLUGIN_PATH' ) ) {
    define( 'GENFORM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'GENFORM_PLUGIN_URL' ) ) {
    define( 'GENFORM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'GENFORM_PLUGIN_DIRNAME' ) ) {
    define( 'GENFORM_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
}

/**
 * Load text domain for internationalization
 */
function genform_load_textdomain() {
    load_plugin_textdomain( 'genform', false, GENFORM_PLUGIN_DIRNAME . '/i18n/' );
}
add_action( 'plugins_loaded', 'genform_load_textdomain' );

/**
 * Plugin activation hook
 * Creates necessary database tables and sets up initial options
 */
function genform_install() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create forms table
    $forms_table = $wpdb->prefix . 'genform_forms';
    $forms_sql = "CREATE TABLE IF NOT EXISTS $forms_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        form_name varchar(255) NOT NULL,
        form_data longtext NOT NULL,
        form_settings longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status varchar(20) DEFAULT 'active',
        PRIMARY KEY  (id),
        KEY status (status)
    ) $charset_collate;";
    
    // Create entries table
    $entries_table = $wpdb->prefix . 'genform_entries';
    $entries_sql = "CREATE TABLE IF NOT EXISTS $entries_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        form_id bigint(20) UNSIGNED NOT NULL,
        entry_data longtext NOT NULL,
        user_ip varchar(100),
        user_agent varchar(255),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        status varchar(20) DEFAULT 'unread',
        PRIMARY KEY  (id),
        KEY form_id (form_id),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $forms_sql );
    dbDelta( $entries_sql );
    
    // Set plugin version
    update_option( 'genform_version', GENFORM_VERSION );
    update_option( 'genform_installed_at', current_time( 'mysql' ) );
}
register_activation_hook( GENFORM_PLUGIN_FILE, 'genform_install' );

/**
 * Autoloader for plugin classes
 */
function genform_autoloader( $class_name ) {
    // Check if class starts with GenForm_
    if ( strpos( $class_name, 'GenForm_' ) !== 0 ) {
        return;
    }
    
    // Convert class name to file name
    $class_file = str_replace( '_', '-', strtolower( $class_name ) );
    $class_file = 'class-' . $class_file . '.php';
    
    // Define possible directories
    $directories = array(
        GENFORM_PLUGIN_PATH . 'includes/',
        GENFORM_PLUGIN_PATH . 'admin/',
        GENFORM_PLUGIN_PATH . 'public/',
    );
    
    // Try to load the class file
    foreach ( $directories as $directory ) {
        $file_path = $directory . $class_file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
            return;
        }
    }
}
spl_autoload_register( 'genform_autoloader' );

/**
 * Initialize the plugin
 */
function genform_init() {
    // Initialize core classes
    if ( class_exists( 'GenForm_Core' ) ) {
        GenForm_Core::get_instance();
    }
}
add_action( 'plugins_loaded', 'genform_init', 20 );
