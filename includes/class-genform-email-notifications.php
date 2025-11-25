<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GenForm_Email_Notifications {
    
    public static function send_admin_notification( $entry_id, $form_id, $entry_data ) {
        global $wpdb;
        $forms_table = $wpdb->prefix . 'genform_forms';
        $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $forms_table WHERE id = %d", $form_id ) );
        
        if ( ! $form ) {
            return;
        }
        
        $form_settings = json_decode( $form->form_settings, true );
        
        // Get admin email
        $admin_email = isset( $form_settings['admin_email'] ) && ! empty( $form_settings['admin_email'] ) 
            ? $form_settings['admin_email'] 
            : get_option( 'admin_email' );
        
        // Check if admin notifications are enabled
        if ( isset( $form_settings['disable_admin_notification'] ) && $form_settings['disable_admin_notification'] ) {
            return;
        }
        
        // Build email content
        /* translators: %s: Form name */
        $subject = sprintf( __( 'New Form Submission: %s', 'genform' ), $form->form_name );
        
        /* translators: %s: Form name */
        $message = sprintf( __( 'You have received a new form submission for "%s"', 'genform' ), $form->form_name ) . "\n\n";
        $message .= __( 'Submission Details:', 'genform' ) . "\n";
        $message .= str_repeat( '-', 50 ) . "\n\n";
        
        foreach ( $entry_data as $field_name => $value ) {
            if ( is_array( $value ) ) {
                $value = implode( ', ', $value );
            }
            $message .= ucfirst( str_replace( '_', ' ', $field_name ) ) . ': ' . $value . "\n";
        }
        
        $message .= "\n" . str_repeat( '-', 50 ) . "\n";
        /* translators: %d: Entry ID number */
        $message .= sprintf( __( 'Entry ID: %d', 'genform' ), $entry_id ) . "\n";
        /* translators: %s: Submission timestamp */
        $message .= sprintf( __( 'Submitted: %s', 'genform' ), current_time( 'mysql' ) ) . "\n";
        /* translators: %s: URL to view the entry */
        $message .= sprintf( __( 'View Entry: %s', 'genform' ), admin_url( 'admin.php?page=genform-entries&form_id=' . $form_id ) ) . "\n";
        
        // Send email
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        
        wp_mail( $admin_email, $subject, $message, $headers );
        
        do_action( 'genform/after_admin_notification', $entry_id, $form_id, $entry_data );
    }
    
    /**
     * Send user confirmation email
     *
     * @param int $entry_id Entry ID
     * @param int $form_id Form ID
     * @param array $entry_data Entry data
     */
    public static function send_user_confirmation( $entry_id, $form_id, $entry_data ) {
        global $wpdb;
        $forms_table = $wpdb->prefix . 'genform_forms';
        $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $forms_table WHERE id = %d", $form_id ) );
        
        if ( ! $form ) {
            return;
        }
        
        $form_settings = json_decode( $form->form_settings, true );
        
        if ( ! isset( $form_settings['enable_user_confirmation'] ) || ! $form_settings['enable_user_confirmation'] ) {
            return;
        }
        
        $user_email = '';
        foreach ( $entry_data as $field_name => $value ) {
            if ( is_email( $value ) ) {
                $user_email = $value;
                break;
            }
        }
        
        if ( empty( $user_email ) ) {
            return;
        }
        
        // Build email content
        $subject = isset( $form_settings['user_email_subject'] ) && ! empty( $form_settings['user_email_subject'] )
            ? $form_settings['user_email_subject']
            /* translators: %s: Form name */
            : sprintf( __( 'Thank you for your submission: %s', 'genform' ), $form->form_name );
        
        $message = isset( $form_settings['user_email_message'] ) && ! empty( $form_settings['user_email_message'] )
            ? $form_settings['user_email_message']
            : __( 'Thank you for your submission. We have received your information and will get back to you soon.', 'genform' );
        
        $message .= "\n\n" . __( 'Your submission details:', 'genform' ) . "\n";
        $message .= str_repeat( '-', 50 ) . "\n\n";
        
        foreach ( $entry_data as $field_name => $value ) {
            if ( is_array( $value ) ) {
                $value = implode( ', ', $value );
            }
            $message .= ucfirst( str_replace( '_', ' ', $field_name ) ) . ': ' . $value . "\n";
        }
        
        // Send email
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        
        wp_mail( $user_email, $subject, $message, $headers );
        
        do_action( 'genform/after_user_confirmation', $entry_id, $form_id, $entry_data, $user_email );
    }
}

// Hook into form submission
add_action( 'genform/after_submission', array( 'GenForm_Email_Notifications', 'send_admin_notification' ), 10, 3 );
add_action( 'genform/after_submission', array( 'GenForm_Email_Notifications', 'send_user_confirmation' ), 10, 3 );
