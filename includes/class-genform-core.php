<?php
/**
 * Core plugin class
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main GenForm_Core Class
 */
class GenForm_Core {
    
    /**
     * Single instance of the class
     *
     * @var GenForm_Core
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return GenForm_Core
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Admin classes
        if ( is_admin() ) {
            new GenForm_Admin_Settings();
        }
        
        // Frontend classes
        new GenForm_Shortcode();
        new GenForm_Form_Handler();
    }
    
    /**
     * Register custom post types if needed
     */
    public function register_post_types() {
        // Can be used for future extensions
        do_action( 'genform/register_post_types' );
    }
    
    /**
     * Register admin menu
     */
    public function register_admin_menu() {
        add_menu_page(
            __( 'GenForm', 'genform' ),
            __( 'GenForm', 'genform' ),
            'manage_options',
            'genform',
            array( $this, 'render_admin_page' ),
            'dashicons-feedback',
            30
        );
        
        add_submenu_page(
            'genform',
            __( 'All Forms', 'genform' ),
            __( 'All Forms', 'genform' ),
            'manage_options',
            'genform',
            array( $this, 'render_admin_page' )
        );
        
        add_submenu_page(
            'genform',
            __( 'Add New', 'genform' ),
            __( 'Add New', 'genform' ),
            'manage_options',
            'genform-add-new',
            array( $this, 'render_add_new_page' )
        );
        
        add_submenu_page(
            'genform',
            __( 'Entries', 'genform' ),
            __( 'Entries', 'genform' ),
            'manage_options',
            'genform-entries',
            array( $this, 'render_entries_page' )
        );
        
        add_submenu_page(
            'genform',
            __( 'Settings', 'genform' ),
            __( 'Settings', 'genform' ),
            'manage_options',
            'genform-settings',
            array( $this, 'render_settings_page' )
        );
    }
    
    /**
     * Render main admin page
     */
    public function render_admin_page() {
        include GENFORM_PLUGIN_PATH . 'admin/views/forms-list.php';
    }
    
    /**
     * Render add new form page
     */
    public function render_add_new_page() {
        include GENFORM_PLUGIN_PATH . 'admin/views/form-builder.php';
    }
    
    /**
     * Render entries page
     */
    public function render_entries_page() {
        include GENFORM_PLUGIN_PATH . 'admin/views/entries-list.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include GENFORM_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on GenForm admin pages
        if ( strpos( $hook, 'genform' ) === false ) {
            return;
        }
        
        // Admin CSS
        wp_enqueue_style(
            'genform-admin-css',
            GENFORM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            GENFORM_VERSION
        );
        
        // Admin JS
        wp_enqueue_script(
            'genform-admin-js',
            GENFORM_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'wp-element', 'wp-components' ),
            GENFORM_VERSION,
            true
        );
        
        // Form Builder JS (only on builder page)
        if ( strpos( $hook, 'genform-add-new' ) !== false || isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
            wp_enqueue_script(
                'genform-builder-js',
                GENFORM_PLUGIN_URL . 'assets/js/form-builder.js',
                array( 'jquery', 'jquery-ui-sortable', 'wp-element' ),
                GENFORM_VERSION,
                true
            );
            
            wp_enqueue_style(
                'genform-builder-css',
                GENFORM_PLUGIN_URL . 'assets/css/form-builder.css',
                array(),
                GENFORM_VERSION
            );
        }
        
        // Localize script
        wp_localize_script(
            'genform-admin-js',
            'genformAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'genform_admin_nonce' ),
                'strings' => array(
                    'confirmDelete' => __( 'Are you sure you want to delete this item?', 'genform' ),
                    'error' => __( 'An error occurred. Please try again.', 'genform' ),
                ),
            )
        );
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load if shortcode is present
        global $post;
        
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'genform' ) ) {
            wp_enqueue_style(
                'genform-frontend-css',
                GENFORM_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                GENFORM_VERSION
            );
            
            wp_enqueue_script(
                'genform-frontend-js',
                GENFORM_PLUGIN_URL . 'assets/js/frontend.js',
                array( 'jquery' ),
                GENFORM_VERSION,
                true
            );
            
            wp_localize_script(
                'genform-frontend-js',
                'genformFrontend',
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'genform_frontend_nonce' ),
                )
            );
        }
    }
}
