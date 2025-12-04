<?php
if (! defined('ABSPATH')) {
    exit;
}

class GenForm_Core
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
        $this->load_dependencies();
    }

    private function init_hooks()
    {
        add_action('init', array($this, 'register_post_types'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    private function load_dependencies()
    {
        if (is_admin()) {
            new GenForm_Admin_Settings();
        }

        new GenForm_Shortcode();
        new GenForm_Form_Handler();
    }

    public function register_post_types()
    {
        do_action('genform/register_post_types');
    }

    public function register_admin_menu()
    {
        add_menu_page(
            __('GenForm', 'genform'),
            __('GenForm', 'genform'),
            'manage_options',
            'genform',
            array($this, 'render_admin_page'),
            'dashicons-feedback',
            30
        );

        add_submenu_page(
            'genform',
            __('All Forms', 'genform'),
            __('All Forms', 'genform'),
            'manage_options',
            'genform',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'genform',
            __('Add New', 'genform'),
            __('Add New', 'genform'),
            'manage_options',
            'genform-add-new',
            array($this, 'render_add_new_page')
        );

        add_submenu_page(
            'genform',
            __('Entries', 'genform'),
            __('Entries', 'genform'),
            'manage_options',
            'genform-entries',
            array($this, 'render_entries_page')
        );

        add_submenu_page(
            'genform',
            __('Settings', 'genform'),
            __('Settings', 'genform'),
            'manage_options',
            'genform-settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_admin_page()
    {
        include GENFORM_PLUGIN_PATH . 'admin/views/forms-list.php';
    }

    public function render_add_new_page()
    {
        include GENFORM_PLUGIN_PATH . 'admin/views/form-builder.php';
    }

    public function render_entries_page()
    {
        include GENFORM_PLUGIN_PATH . 'admin/views/entries-list.php';
    }

    public function render_settings_page()
    {
        include GENFORM_PLUGIN_PATH . 'admin/views/settings.php';
    }

    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'genform') === false) {
            return;
        }

        wp_enqueue_style(
            'genform-admin-css',
            GENFORM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            GENFORM_VERSION
        );

        wp_enqueue_script(
            'genform-admin-js',
            GENFORM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-element', 'wp-components'),
            GENFORM_VERSION,
            true
        );

        // Note: $_GET['action'] is used only for conditional script loading (read-only)
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';

        $form_data = array();
        $form_settings = array();

        if (strpos($hook, 'genform-add-new') !== false || $action === 'edit') {
            // If editing, fetch form data to pass to JS
            if ($action === 'edit' && isset($_GET['form_id'])) {
                $form_id = absint($_GET['form_id']);
                global $wpdb;
                $forms_table = $wpdb->prefix . 'genform_forms';
                $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM $forms_table WHERE id = %d", $form_id));

                if ($form) {
                    $form_data = json_decode($form->form_data, true);
                    $form_settings = json_decode($form->form_settings, true);
                }
            }

            wp_enqueue_script(
                'genform-builder-js',
                GENFORM_PLUGIN_URL . 'assets/js/form-builder.js',
                array('jquery', 'jquery-ui-sortable', 'wp-element'),
                GENFORM_VERSION,
                true
            );

            wp_localize_script(
                'genform-builder-js',
                'genformBuilder',
                array(
                    'initialData' => $form_data,
                    'initialSettings' => $form_settings
                )
            );

            wp_enqueue_style(
                'genform-builder-css',
                GENFORM_PLUGIN_URL . 'assets/css/form-builder.css',
                array(),
                GENFORM_VERSION
            );
        }

        wp_localize_script(
            'genform-admin-js',
            'genformAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('genform_admin_nonce'),
                'strings' => array(
                    'confirmDelete' => __('Are you sure you want to delete this item?', 'genform'),
                    'error' => __('An error occurred. Please try again.', 'genform'),
                ),
            )
        );
    }

    public function enqueue_frontend_assets()
    {
        global $post;

        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'genform')) {
            wp_enqueue_style(
                'genform-frontend-css',
                GENFORM_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                GENFORM_VERSION
            );

            wp_enqueue_script(
                'genform-frontend-js',
                GENFORM_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                GENFORM_VERSION,
                true
            );

            wp_localize_script(
                'genform-frontend-js',
                'genformFrontend',
                array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('genform_frontend_nonce'),
                )
            );
        }
    }
}
