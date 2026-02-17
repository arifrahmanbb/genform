<?php

/**
 * GenForm Uninstall Handler
 *
 * Fires when the plugin is deleted from the WordPress admin.
 * Cleans up all plugin data including database tables and options.
 *
 * @package GenForm
 */

// Exit if not called by WordPress uninstall process.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Drop custom database tables.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}genform_entries");

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}genform_forms");

// Remove plugin options.
delete_option('genform_general');
delete_option('genform_version');

// Clean up any transients used for rate limiting.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_genform_rate_%' OR option_name LIKE '_transient_timeout_genform_rate_%'");
