<?php
/**
 * Plugin Name: GenForm - Drag & Drop Form Builder
 * Plugin URI: https://wordpress.org/plugins/genform/
 * Description: Build beautiful, responsive forms effortlessly with drag-and-drop interface.
 * Version: 1.0.0
 * Author: Arif Rahman
 * Author URI: https://profiles.wordpress.org/arifrahman1/
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: genform
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 8.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
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
 * Plugin activation - creates database tables
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
    
    update_option( 'genform_version', GENFORM_VERSION );
    update_option( 'genform_installed_at', current_time( 'mysql' ) );
}
register_activation_hook( GENFORM_PLUGIN_FILE, 'genform_install' );

/**
 * Autoloader for plugin classes
 */
function genform_autoloader( $class_name ) {
    if ( strpos( $class_name, 'GenForm_' ) !== 0 ) {
        return;
    }
    
    $class_file = str_replace( '_', '-', strtolower( $class_name ) );
    $class_file = 'class-' . $class_file . '.php';
    
    $directories = array(
        GENFORM_PLUGIN_PATH . 'includes/',
        GENFORM_PLUGIN_PATH . 'admin/',
        GENFORM_PLUGIN_PATH . 'public/',
    );
    
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
    if ( class_exists( 'GenForm_Core' ) ) {
        GenForm_Core::get_instance();
    }
}
add_action( 'plugins_loaded', 'genform_init', 20 );