<?php
/**
 * Admin View: Form Builder
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'genform' ) );
}

global $wpdb;
$forms_table = $wpdb->prefix . 'genform_forms';
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
$is_edit = $action === 'edit' && isset( $_GET['form_id'] );
$form = null;
$form_data = array();
$form_settings = array();

if ( $is_edit ) {
    $form_id = absint( $_GET['form_id'] );
    $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $forms_table WHERE id = %d", $form_id ) );
    
    if ( $form ) {
        $form_data = json_decode( $form->form_data, true );
        $form_settings = json_decode( $form->form_settings, true );
    }
}

// Handle form save
if ( isset( $_POST['genform_save'] ) && isset( $_POST['genform_builder_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['genform_builder_nonce'] ) ), 'genform_save_form' ) ) {
    $form_name = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : '';
    $form_fields = isset( $_POST['form_data'] ) ? wp_unslash( $_POST['form_data'] ) : '';
    $form_settings_data = isset( $_POST['form_settings'] ) ? wp_unslash( $_POST['form_settings'] ) : '';
    
    if ( $form_name && $form_fields ) {
        $data = array(
            'form_name' => $form_name,
            'form_data' => $form_fields,
            'form_settings' => $form_settings_data,
            'status' => 'active',
        );
        
        if ( $is_edit && $form ) {
            $wpdb->update( $forms_table, $data, array( 'id' => $form->id ), array( '%s', '%s', '%s', '%s' ), array( '%d' ) );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Form updated successfully.', 'genform' ) . '</p></div>';
        } else {
            $wpdb->insert( $forms_table, $data, array( '%s', '%s', '%s', '%s' ) );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Form created successfully.', 'genform' ) . '</p></div>';
            $form_id = $wpdb->insert_id;
            wp_safe_redirect( admin_url( 'admin.php?page=genform-add-new&action=edit&form_id=' . $form_id ) );
            exit;
        }
    }
}
?>

<div class="wrap genform-builder-wrap">
    <h1><?php echo $is_edit ? esc_html__( 'Edit Form', 'genform' ) : esc_html__( 'Add New Form', 'genform' ); ?></h1>
    
    <form method="post" action="" id="genform-builder-form">
        <?php wp_nonce_field( 'genform_save_form', 'genform_builder_nonce' ); ?>
        
        <div class="genform-builder-header">
            <div class="genform-form-name-wrapper">
                <label for="form_name"><?php esc_html_e( 'Form Name:', 'genform' ); ?></label>
                <input type="text" name="form_name" id="form_name" value="<?php echo $form ? esc_attr( $form->form_name ) : ''; ?>" required class="regular-text" />
            </div>
            
            <div class="genform-builder-actions">
                <button type="submit" name="genform_save" class="button button-primary button-large">
                    <?php esc_html_e( 'Save Form', 'genform' ); ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=genform' ) ); ?>" class="button button-large">
                    <?php esc_html_e( 'Cancel', 'genform' ); ?>
                </a>
            </div>
        </div>
        
        <div class="genform-builder-container">
            <div class="genform-builder-sidebar">
                <h3><?php esc_html_e( 'Form Fields', 'genform' ); ?></h3>
                <div class="genform-field-types">
                    <button type="button" class="genform-add-field" data-field-type="text">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e( 'Text Field', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="email">
                        <span class="dashicons dashicons-email"></span>
                        <?php esc_html_e( 'Email Field', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="textarea">
                        <span class="dashicons dashicons-text"></span>
                        <?php esc_html_e( 'Textarea', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="number">
                        <span class="dashicons dashicons-calculator"></span>
                        <?php esc_html_e( 'Number Field', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="tel">
                        <span class="dashicons dashicons-phone"></span>
                        <?php esc_html_e( 'Phone Field', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="url">
                        <span class="dashicons dashicons-admin-links"></span>
                        <?php esc_html_e( 'URL Field', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="select">
                        <span class="dashicons dashicons-menu-alt"></span>
                        <?php esc_html_e( 'Dropdown', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="radio">
                        <span class="dashicons dashicons-marker"></span>
                        <?php esc_html_e( 'Radio Buttons', 'genform' ); ?>
                    </button>
                    <button type="button" class="genform-add-field" data-field-type="checkbox">
                        <span class="dashicons dashicons-yes"></span>
                        <?php esc_html_e( 'Checkboxes', 'genform' ); ?>
                    </button>
                </div>
            </div>
            
            <div class="genform-builder-canvas">
                <h3><?php esc_html_e( 'Form Preview', 'genform' ); ?></h3>
                <div id="genform-fields-container" class="genform-fields-container">
                    <!-- Fields will be added here dynamically -->
                </div>
            </div>
            
            <div class="genform-builder-settings">
                <h3><?php esc_html_e( 'Form Settings', 'genform' ); ?></h3>
                
                <div class="genform-setting-group">
                    <label for="genform-success-message"><?php esc_html_e( 'Success Message', 'genform' ); ?></label>
                    <textarea id="genform-success-message" class="regular-text" rows="3"><?php echo isset( $form_settings['success_message'] ) ? esc_textarea( $form_settings['success_message'] ) : esc_textarea__( 'Thank you! Your form has been submitted successfully.', 'genform' ); ?></textarea>
                </div>
                
                <div class="genform-setting-group">
                    <label for="genform-redirect-url"><?php esc_html_e( 'Redirect URL (Optional)', 'genform' ); ?></label>
                    <input type="url" id="genform-redirect-url" class="regular-text" value="<?php echo isset( $form_settings['redirect_url'] ) ? esc_url( $form_settings['redirect_url'] ) : ''; ?>" placeholder="https://example.com/thank-you" />
                    <p class="description"><?php esc_html_e( 'Redirect users to this URL after successful submission.', 'genform' ); ?></p>
                </div>
                
                <div class="genform-setting-group">
                    <label for="genform-submit-text"><?php esc_html_e( 'Submit Button Text', 'genform' ); ?></label>
                    <input type="text" id="genform-submit-text" class="regular-text" value="<?php echo isset( $form_settings['submit_text'] ) ? esc_attr( $form_settings['submit_text'] ) : esc_attr__( 'Submit', 'genform' ); ?>" />
                </div>
                
                <hr />
                
                <h4><?php esc_html_e( 'Email Notifications', 'genform' ); ?></h4>
                
                <div class="genform-setting-group">
                    <label>
                        <input type="checkbox" id="genform-disable-admin-notification" <?php checked( isset( $form_settings['disable_admin_notification'] ) && $form_settings['disable_admin_notification'] ); ?> />
                        <?php esc_html_e( 'Disable admin notification', 'genform' ); ?>
                    </label>
                </div>
                
                <div class="genform-setting-group">
                    <label for="genform-admin-email"><?php esc_html_e( 'Admin Email', 'genform' ); ?></label>
                    <input type="email" id="genform-admin-email" class="regular-text" value="<?php echo isset( $form_settings['admin_email'] ) ? esc_attr( $form_settings['admin_email'] ) : esc_attr( get_option( 'admin_email' ) ); ?>" />
                    <p class="description"><?php esc_html_e( 'Email address to receive form submissions.', 'genform' ); ?></p>
                </div>
                
                <div class="genform-setting-group">
                    <label>
                        <input type="checkbox" id="genform-enable-user-confirmation" <?php checked( isset( $form_settings['enable_user_confirmation'] ) && $form_settings['enable_user_confirmation'] ); ?> />
                        <?php esc_html_e( 'Send confirmation email to user', 'genform' ); ?>
                    </label>
                </div>
                
                <div class="genform-setting-group">
                    <label for="genform-user-email-subject"><?php esc_html_e( 'User Email Subject', 'genform' ); ?></label>
                    <input type="text" id="genform-user-email-subject" class="regular-text" value="<?php echo isset( $form_settings['user_email_subject'] ) ? esc_attr( $form_settings['user_email_subject'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Thank you for your submission', 'genform' ); ?>" />
                </div>
                
                <div class="genform-setting-group">
                    <label for="genform-user-email-message"><?php esc_html_e( 'User Email Message', 'genform' ); ?></label>
                    <textarea id="genform-user-email-message" class="regular-text" rows="4"><?php echo isset( $form_settings['user_email_message'] ) ? esc_textarea( $form_settings['user_email_message'] ) : ''; ?></textarea>
                    <p class="description"><?php esc_html_e( 'Custom message for user confirmation email.', 'genform' ); ?></p>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="form_data" id="genform-data-input" value="" />
        <input type="hidden" name="form_settings" id="genform-settings-input" value="" />
    </form>
</div>

<script type="text/javascript">
    var genformInitialData = <?php echo wp_json_encode( $form_data ); ?>;
    var genformInitialSettings = <?php echo wp_json_encode( $form_settings ); ?>;
</script>
