<?php
/**
 * Form Handler Class
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GenForm_Form_Handler Class
 */
class GenForm_Form_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_genform_submit', array( $this, 'handle_submission' ) );
        add_action( 'wp_ajax_nopriv_genform_submit', array( $this, 'handle_submission' ) );
    }
    
    /**
     * Handle form submission
     */
    public function handle_submission() {
        // Verify nonce
        $form_id = isset( $_POST['genform_id'] ) ? absint( $_POST['genform_id'] ) : 0;
        
        if ( ! $form_id || ! isset( $_POST['genform_nonce'] ) || ! wp_verify_nonce( $_POST['genform_nonce'], 'genform_submit_' . $form_id ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security check failed.', 'genform' ),
            ) );
        }
        
        // Get form data
        global $wpdb;
        $forms_table = $wpdb->prefix . 'genform_forms';
        $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $forms_table WHERE id = %d AND status = 'active'", $form_id ) );
        
        if ( ! $form ) {
            wp_send_json_error( array(
                'message' => __( 'Form not found.', 'genform' ),
            ) );
        }
        
        // Sanitize and validate form data
        $form_data = json_decode( $form->form_data, true );
        $entry_data = array();
        
        if ( isset( $form_data['fields'] ) && is_array( $form_data['fields'] ) ) {
            foreach ( $form_data['fields'] as $field ) {
                $field_name = isset( $field['name'] ) ? $field['name'] : '';
                $field_type = isset( $field['type'] ) ? $field['type'] : 'text';
                $field_required = isset( $field['required'] ) && $field['required'];
                
                if ( ! $field_name ) {
                    continue;
                }
                
                $value = isset( $_POST[ $field_name ] ) ? $_POST[ $field_name ] : '';
                
                // Validate required fields
                if ( $field_required && empty( $value ) ) {
                    wp_send_json_error( array(
                        'message' => sprintf( __( 'Field "%s" is required.', 'genform' ), $field['label'] ),
                    ) );
                }
                
                // Sanitize based on field type
                $sanitized_value = $this->sanitize_field_value( $value, $field_type );
                
                // Validate email
                if ( $field_type === 'email' && ! empty( $sanitized_value ) && ! is_email( $sanitized_value ) ) {
                    wp_send_json_error( array(
                        'message' => sprintf( __( 'Please enter a valid email address for "%s".', 'genform' ), $field['label'] ),
                    ) );
                }
                
                $entry_data[ $field_name ] = $sanitized_value;
            }
        }
        
        // Apply filter for custom validation
        $entry_data = apply_filters( 'genform/form_data_sanitization', $entry_data, $form_id );
        
        // Save entry to database
        $entries_table = $wpdb->prefix . 'genform_entries';
        $inserted = $wpdb->insert(
            $entries_table,
            array(
                'form_id' => $form_id,
                'entry_data' => wp_json_encode( $entry_data ),
                'user_ip' => $this->get_user_ip(),
                'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
                'created_at' => current_time( 'mysql' ),
                'status' => 'unread',
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s' )
        );
        
        if ( ! $inserted ) {
            wp_send_json_error( array(
                'message' => __( 'Failed to save form submission. Please try again.', 'genform' ),
            ) );
        }
        
        $entry_id = $wpdb->insert_id;
        
        // Fire action after submission
        do_action( 'genform/after_submission', $entry_id, $form_id, $entry_data );
        
        // Get success message
        $form_settings = json_decode( $form->form_settings, true );
        $success_message = isset( $form_settings['success_message'] ) ? $form_settings['success_message'] : __( 'Thank you! Your form has been submitted successfully.', 'genform' );
        
        wp_send_json_success( array(
            'message' => $success_message,
            'entry_id' => $entry_id,
        ) );
    }
    
    /**
     * Sanitize field value based on type
     *
     * @param mixed $value Field value
     * @param string $type Field type
     * @return mixed
     */
    private function sanitize_field_value( $value, $type ) {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }
        
        switch ( $type ) {
            case 'email':
                return sanitize_email( $value );
            case 'url':
                return esc_url_raw( $value );
            case 'number':
                return floatval( $value );
            case 'textarea':
                return sanitize_textarea_field( $value );
            default:
                return sanitize_text_field( $value );
        }
    }
    
    /**
     * Get user IP address
     *
     * @return string
     */
    private function get_user_ip() {
        $ip = '';
        
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field( $ip );
    }
}
