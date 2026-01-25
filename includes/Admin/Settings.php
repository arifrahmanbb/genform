<?php
/**
 * Plugin Settings Page Manager
 *
 * Handles registration, sanitization, and rendering of global plugin options.
 *
 * @package GenForm
 */

namespace GenForm\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {

	/**
	 * Setup the hooks for settings registration.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
	}

	/**
	 * Render the administrative settings view.
	 */
	public static function render(): void {
		include GENFORM_PATH . 'admin/views/settings.php';
	}

	/**
	 * Defines sections and fields for the settings API.
	 */
	public function registerSettings(): void {
		register_setting( 'genform_settings', 'genform_general', array( $this, 'sanitize' ) );

		// Section: General Branding & Security
		add_settings_section(
			'genform_main',
			esc_html__( 'General Configuration', 'genform' ),
			fn() => print( '<p class="gfm-section-desc">' . esc_html__( 'Manage branding and security keys.', 'genform' ) . '</p>' ),
			'genform_settings'
		);

		add_settings_field(
			'genform_recaptcha_site',
			esc_html__( 'reCAPTCHA Site Key', 'genform' ),
			fn() => $this->renderField( 'recaptcha_site_key', esc_html__( 'Enter your Google reCAPTCHA site key.', 'genform' ) ),
			'genform_settings',
			'genform_main'
		);

		add_settings_field(
			'genform_recaptcha_secret',
			esc_html__( 'reCAPTCHA Secret Key', 'genform' ),
			fn() => $this->renderField( 'recaptcha_secret_key', esc_html__( 'Secret key for server-side verification.', 'genform' ) ),
			'genform_settings',
			'genform_main'
		);

		add_settings_field(
			'genform_primary_color',
			esc_html__( 'Brand Primary Color', 'genform' ),
			fn() => $this->renderColorField( 'primary_color', esc_html__( 'Choose the accent color for buttons and active states.', 'genform' ) ),
			'genform_settings',
			'genform_main'
		);

		// Section: Default Email Fallbacks
		add_settings_section(
			'genform_email_sec',
			esc_html__( 'Default Email Identity', 'genform' ),
			fn() => print( '<p class="gfm-section-desc">' . esc_html__( 'Global fallbacks for email notifications.', 'genform' ) . '</p>' ),
			'genform_settings'
		);

		add_settings_field(
			'genform_from_name',
			esc_html__( 'Global Sender Name', 'genform' ),
			fn() => $this->renderField( 'from_name', esc_html__( 'e.g. Your Business Name', 'genform' ) ),
			'genform_settings',
			'genform_email_sec'
		);

		add_settings_field(
			'genform_from_email',
			esc_html__( 'Global Sender Email', 'genform' ),
			fn() => $this->renderField( 'from_email', esc_html__( 'e.g. support@yourdomain.com', 'genform' ) ),
			'genform_settings',
			'genform_email_sec'
		);

		// Section: Performance Improvements
		add_settings_section(
			'genform_advanced',
			esc_html__( 'Advanced & Performance', 'genform' ),
			fn() => print( '<p class="gfm-section-desc">' . esc_html__( 'Technical optimizations.', 'genform' ) . '</p>' ),
			'genform_settings' 
		);

		add_settings_field(
			'genform_disable_assets',
			esc_html__( 'Asset Optimization', 'genform' ),
			fn() => $this->renderCheckboxField( 'disable_assets', esc_html__( "Don't load default CSS/JS (theme mode).", 'genform' ) ),
			'genform_settings',
			'genform_advanced'
		);
	}

	/**
	 * Sanitizes all input fields before storage.
	 */
	public function sanitize( array $input ): array {
		return array(
			'recaptcha_site_key'   => sanitize_text_field( $input['recaptcha_site_key'] ?? '' ),
			'recaptcha_secret_key' => sanitize_text_field( $input['recaptcha_secret_key'] ?? '' ),
			'primary_color'        => sanitize_hex_color( $input['primary_color'] ?? '#6366f1' ),
			'from_name'            => sanitize_text_field( $input['from_name'] ?? get_bloginfo( 'name' ) ),
			'from_email'           => sanitize_email( $input['from_email'] ?? get_bloginfo( 'admin_email' ) ),
			'disable_assets'       => isset( $input['disable_assets'] ) ? 1 : 0,
		);
	}

	/**
	 * Helper function to render a standard text input field.
	 */
	private function renderField( string $key, string $desc = '' ): void {
		$v = get_option( 'genform_general', array() )[ $key ] ?? '';
		printf( '<input type="text" name="genform_general[%1$s]" value="%2$s" class="regular-text" />', esc_attr( $key ), esc_attr( $v ) );
		if ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}
	}

	/**
	 * Helper function to render a checkbox setting.
	 */
	private function renderCheckboxField( string $key, string $desc = '' ): void {
		$c = ! empty( get_option( 'genform_general', array() )[ $key ] ) ? 'checked' : '';
		printf( '<label><input type="checkbox" name="genform_general[%1$s]" value="1" %2$s /> %3$s</label>', esc_attr( $key ), $c, esc_html( $desc ) );
	}

	/**
	 * Helper function to render a color picker field.
	 */
	private function renderColorField( string $key, string $desc = '' ): void {
		$v = get_option( 'genform_general', array() )[ $key ] ?? '#6366f1';
		printf( '<input type="color" name="genform_general[%1$s]" value="%2$s" class="gfm-color-picker" />', esc_attr( $key ), esc_attr( $v ) );
		if ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}
	}
}
