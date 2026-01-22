<?php

declare(strict_types=1);

namespace GenForm;

use GenForm\Admin\Builder;
use GenForm\Admin\Settings;
use GenForm\Handlers\FormHandler;
use GenForm\Integrations\Block;
use GenForm\Integrations\Shortcode;

/**
 * Core Class for GenForm
 */
final class Core
{
    private static ?self $instance = null;

    private function __construct()
    {
        $this->init();
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    private function init(): void
    {
        add_action('admin_menu', [$this, 'registerMenus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);

        $this->loadComponents();
    }

    private function loadComponents(): void
    {
        new Builder();
        new Settings();
        new FormHandler();
        new Block();
        new Shortcode();
    }

    public function registerMenus(): void
    {
        add_menu_page(
            __('GenForm', 'genform'),
            __('GenForm', 'genform'),
            'manage_options',
            'genform',
            [$this, 'renderFormsList'],
            'dashicons-feedback',
            30
        );

        add_submenu_page('genform', __('All Forms', 'genform'), __('All Forms', 'genform'), 'manage_options', 'genform', [$this, 'renderFormsList']);
        add_submenu_page('genform', __('Add New', 'genform'), __('Add New', 'genform'), 'manage_options', 'genform-builder', [Builder::class, 'render']);
        add_submenu_page('genform', __('Entries', 'genform'), __('Entries', 'genform'), 'manage_options', 'genform-entries', [$this, 'renderEntries']);
        add_submenu_page('genform', __('Settings', 'genform'), __('Settings', 'genform'), 'manage_options', 'genform-settings', [Settings::class, 'render']);
    }

    public function renderFormsList(): void
    {
        include GENFORM_PATH . 'admin/views/forms-list.php';
    }

    public function renderEntries(): void
    {
        include GENFORM_PATH . 'admin/views/entries-list.php';
    }

    public function enqueueAdminAssets(string $hook): void
    {
        if (!str_contains($hook, 'genform')) {
            return;
        }

        wp_enqueue_style('genform-admin', GENFORM_URL . 'assets/css/admin.css', [], GENFORM_VERSION);
        wp_enqueue_script('genform-admin', GENFORM_URL . 'assets/js/admin.js', ['jquery'], GENFORM_VERSION, ['strategy' => 'defer', 'in_footer' => true]);

        if (str_contains($hook, 'genform-builder')) {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('genform-builder', GENFORM_URL . 'assets/js/form-builder.js', ['jquery', 'jquery-ui-sortable'], GENFORM_VERSION, ['strategy' => 'defer', 'in_footer' => true]);

            $id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
            global $wpdb;
            $form = $id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id)) : null;

            wp_localize_script('genform-builder', 'genformBuilder', [
                'initialData' => $form ? json_decode($form->form_data, true) : null,
                'initialSettings' => $form ? json_decode($form->form_settings, true) : null,
            ]);
        }

        wp_localize_script('genform-admin', 'genform', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('genform_admin_nonce'),
            'i18n'     => [
                'confirm_delete' => __('Are you sure?', 'genform'),
            ]
        ]);
    }

    public function enqueueFrontendAssets(): void
    {
        wp_enqueue_style('genform-frontend', GENFORM_URL . 'assets/css/frontend.css', [], GENFORM_VERSION);
        wp_enqueue_script('genform-frontend', GENFORM_URL . 'assets/js/frontend.js', ['jquery'], GENFORM_VERSION, ['strategy' => 'defer', 'in_footer' => true]);

        wp_localize_script('genform-frontend', 'genform', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public static function activate(): void
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $table_forms = $wpdb->prefix . 'genform_forms';
        $sql_forms = "CREATE TABLE $table_forms (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_name varchar(255) NOT NULL,
            form_data longtext NOT NULL,
            form_settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

        $table_entries = $wpdb->prefix . 'genform_entries';
        $sql_entries = "CREATE TABLE $table_entries (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_id bigint(20) NOT NULL,
            entry_data longtext NOT NULL,
            user_ip varchar(100),
            user_agent varchar(255),
            status varchar(20) DEFAULT 'unread',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_forms);
        dbDelta($sql_entries);
    }
}
