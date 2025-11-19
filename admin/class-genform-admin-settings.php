<?php
/**
 * Admin Settings Class
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GenForm_Admin_Settings Class
 */
class GenForm_Admin_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // General settings
        register_setting(
            'genform_settings',
            'genform_general_settings',
            array( $this, 'sanitize_settings' )
        );
        
        add_settings_section(
            'genform_general_section',
            __( 'General Settings', 'genform' ),
            array( $this, 'general_section_callback' ),
            'genform_settings'
        );
        
        add_settings_field(
            'genform_enable_recaptcha',
            __( 'Enable reCAPTCHA', 'genform' ),
            array( $this, 'enable_recaptcha_callback' ),
            'genform_settings',
            'genform_general_section'
        );
        
        add_settings_field(
            'genform_recaptcha_site_key',
            __( 'reCAPTCHA Site Key', 'genform' ),
            array( $this, 'recaptcha_site_key_callback' ),
            'genform_settings',
            'genform_general_section'
        );
        
        add_settings_field(
            'genform_recaptcha_secret_key',
            __( 'reCAPTCHA Secret Key', 'genform' ),
            array( $this, 'recaptcha_secret_key_callback' ),
            'genform_settings',
            'genform_general_section'
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();
        
        if ( isset( $input['enable_recaptcha'] ) ) {
            $sanitized['enable_recaptcha'] = (bool) $input['enable_recaptcha'];
        }
        
        if ( isset( $input['recaptcha_site_key'] ) ) {
            $sanitized['recaptcha_site_key'] = sanitize_text_field( $input['recaptcha_site_key'] );
        }
        
        if ( isset( $input['recaptcha_secret_key'] ) ) {
            $sanitized['recaptcha_secret_key'] = sanitize_text_field( $input['recaptcha_secret_key'] );
        }
        
        return apply_filters( 'genform/settings_sanitization', $sanitized, $input );
    }
    
    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__( 'Configure general settings for GenForm.', 'genform' ) . '</p>';
    }
    
    /**
     * Enable reCAPTCHA callback
     */
    public function enable_recaptcha_callback() {
        $options = get_option( 'genform_general_settings', array() );
        $checked = isset( $options['enable_recaptcha'] ) ? $options['enable_recaptcha'] : false;
        ?>
        <label>
            <input type="checkbox" name="genform_general_settings[enable_recaptcha]" value="1" <?php checked( $checked, true ); ?> />
            <?php esc_html_e( 'Enable Google reCAPTCHA for form submissions', 'genform' ); ?>
        </label>
        <?php
    }
    
    /**
     * reCAPTCHA site key callback
     */
    public function recaptcha_site_key_callback() {
        $options = get_option( 'genform_general_settings', array() );
        $value = isset( $options['recaptcha_site_key'] ) ? $options['recaptcha_site_key'] : '';
        ?>
        <input type="text" name="genform_general_settings[recaptcha_site_key]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php
    }
    
    /**
     * reCAPTCHA secret key callback
     */
    public function recaptcha_secret_key_callback() {
        $options = get_option( 'genform_general_settings', array() );
        $value = isset( $options['recaptcha_secret_key'] ) ? $options['recaptcha_secret_key'] : '';
        ?>
        <input type="text" name="genform_general_settings[recaptcha_secret_key]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php
    }
}
