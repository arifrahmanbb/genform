<?php
/**
 * Gutenberg Block Integration
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GenForm_Block Class
 */
class GenForm_Block {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }
    
    /**
     * Register Gutenberg block
     */
    public function register_block() {
        // Check if Gutenberg is available
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }
        
        // Register block script
        wp_register_script(
            'genform-block-js',
            GENFORM_PLUGIN_URL . 'assets/js/block.js',
            array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
            GENFORM_VERSION,
            true
        );
        
        // Get all forms for the block
        global $wpdb;
        $forms_table = $wpdb->prefix . 'genform_forms';
        $forms = $wpdb->get_results( "SELECT id, form_name FROM $forms_table WHERE status = 'active' ORDER BY form_name ASC" );
        
        $forms_options = array();
        foreach ( $forms as $form ) {
            $forms_options[] = array(
                'label' => $form->form_name,
                'value' => $form->id,
            );
        }
        
        // Localize script with forms data
        wp_localize_script(
            'genform-block-js',
            'genformBlockData',
            array(
                'forms' => $forms_options,
            )
        );
        
        // Register block
        register_block_type(
            'genform/form-block',
            array(
                'editor_script' => 'genform-block-js',
                'render_callback' => array( $this, 'render_block' ),
                'attributes' => array(
                    'formId' => array(
                        'type' => 'number',
                        'default' => 0,
                    ),
                ),
            )
        );
    }
    
    /**
     * Render block on frontend
     *
     * @param array $attributes Block attributes
     * @return string
     */
    public function render_block( $attributes ) {
        $form_id = isset( $attributes['formId'] ) ? absint( $attributes['formId'] ) : 0;
        
        if ( ! $form_id ) {
            return '<p>' . esc_html__( 'Please select a form.', 'genform' ) . '</p>';
        }
        
        // Use shortcode to render the form
        return do_shortcode( '[genform id="' . $form_id . '"]' );
    }
}

new GenForm_Block();
