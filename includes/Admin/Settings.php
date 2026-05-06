<?php
/**
 * Plugin Settings Page Manager
 *
 * Handles registration, sanitization, and rendering of global plugin options.
 * Sections are grouped by tab for the tabbed settings UI.
 *
 * @package GenForm
 */

namespace GenForm\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {

	/**
	 * Tabs definition: id => [label, icon, description].
	 * Tab order here is the rendered order.
	 */
	public static function tabs(): array {
		return array(
			'general'     => array(
				'label' => __( 'General', 'genform' ),
				'icon'  => 'admin-customizer',
				'desc'  => __( 'Branding and global appearance.', 'genform' ),
			),
			'email'       => array(
				'label' => __( 'Email', 'genform' ),
				'icon'  => 'email-alt',
				'desc'  => __( 'Default sender identity for all form notifications.', 'genform' ),
			),
			'security'    => array(
				'label' => __( 'Security', 'genform' ),
				'icon'  => 'shield-alt',
				'desc'  => __( 'Spam protection and abuse prevention.', 'genform' ),
			),
			'performance' => array(
				'label' => __( 'Performance', 'genform' ),
				'icon'  => 'performance',
				'desc'  => __( 'Asset loading and caching options.', 'genform' ),
			),
			'tools'       => array(
				'label' => __( 'Tools', 'genform' ),
				'icon'  => 'admin-tools',
				'desc'  => __( 'Setup wizard, import/export, and quick actions.', 'genform' ),
			),
		);
	}

	/**
	 * Map of section id => tab id.
	 */
	public static function sectionTabMap(): array {
		return array(
			'genform_branding'   => 'general',
			'genform_email_sec'  => 'email',
			'genform_security'   => 'security',
			'genform_advanced'   => 'performance',
		);
	}

	public function __construct() {
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
	}

	public static function render(): void {
		include GENFORM_PATH . 'admin/views/settings.php';
	}

	public function registerSettings(): void {
		register_setting( 'genform_settings', 'genform_general', array( $this, 'sanitize' ) );

		// === Tab: General — Branding ===
		add_settings_section(
			'genform_branding',
			esc_html__( 'Branding', 'genform' ),
			fn() => print( '<p class="gfm-section-desc">' . esc_html__( 'Set the accent color used for buttons, focus states, and active elements across all your forms.', 'genform' ) . '</p>' ),
			'genform_settings'
		);

		add_settings_field(
			'genform_primary_color',
			esc_html__( 'Brand Primary Color', 'genform' ),
			fn() => $this->renderColorField(
				'primary_color',
				esc_html__( 'Recommended: pick a color that matches your brand. Used for buttons, links, and active states.', 'genform' )
			),
			'genform_settings',
			'genform_branding'
		);

		// === Tab: Email — Sender Identity ===
		add_settings_section(
			'genform_email_sec',
			esc_html__( 'Default Sender Identity', 'genform' ),
			fn() => print( '<p class="gfm-section-desc">' . esc_html__( 'These values are used as the "From" name and email when forms send notifications. Per-form overrides take priority.', 'genform' ) . '</p>' ),
			'genform_settings'
		);

		add_settings_field(
			'gfm_from_name',
			esc_html__( 'Global Sender Name', 'genform' ),
			fn() => $this->renderField(
				'from_name',
				esc_html__( 'Example: "Acme Support" or your business name. Recipients see this as the sender.', 'genform' ),
				get_bloginfo( 'name' )
			),
			'genform_settings',
			'genform_email_sec'
		);

		add_settings_field(
			'gfm_from_email',
			esc_html__( 'Global Sender Email', 'genform' ),
			fn() => $this->renderField(
				'from_email',
				esc_html__( 'Tip: use an address on your site\'s domain (e.g. support@yourdomain.com) to avoid emails landing in spam.', 'genform' ),
				get_bloginfo( 'admin_email' ),
				'email'
			),
			'genform_settings',
			'genform_email_sec'
		);

		// === Tab: Security — Anti-spam ===
		add_settings_section(
			'genform_security',
			esc_html__( 'Google reCAPTCHA v2', 'genform' ),
			fn() => print(
				'<p class="gfm-section-desc">' .
				esc_html__( 'Add reCAPTCHA to any form from the form builder Settings tab once keys are configured here.', 'genform' ) .
				' <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">' . esc_html__( 'Get keys from Google →', 'genform' ) . '</a></p>'
			),
			'genform_settings'
		);

		add_settings_field(
			'genform_recaptcha_site',
			esc_html__( 'reCAPTCHA Site Key', 'genform' ),
			fn() => $this->renderField(
				'recaptcha_site_key',
				esc_html__( 'Public key shown to visitors. Starts with "6L".', 'genform' )
			),
			'genform_settings',
			'genform_security'
		);

		add_settings_field(
			'genform_recaptcha_secret',
			esc_html__( 'reCAPTCHA Secret Key', 'genform' ),
			fn() => $this->renderField(
				'recaptcha_secret_key',
				esc_html__( 'Private key for server-side verification. Never share publicly.', 'genform' ),
				'',
				'password'
			),
			'genform_settings',
			'genform_security'
		);

		// === Tab: Performance ===
		add_settings_section(
			'genform_advanced',
			esc_html__( 'Asset Loading', 'genform' ),
			fn() => print( '<p class="gfm-section-desc">' . esc_html__( 'Control how GenForm\'s CSS and JavaScript files are loaded on your site.', 'genform' ) . '</p>' ),
			'genform_settings'
		);

		add_settings_field(
			'genform_disable_assets',
			esc_html__( 'Theme Mode', 'genform' ),
			fn() => $this->renderCheckboxField(
				'disable_assets',
				esc_html__( "Don't load default CSS/JS", 'genform' ),
				esc_html__( 'Enable only if your theme provides custom styling for forms. Forms will inherit your theme\'s typography and form styles.', 'genform' )
			),
			'genform_settings',
			'genform_advanced'
		);
	}

	public function sanitize( array $input ): array {
		return array(
			'recaptcha_site_key'   => sanitize_text_field( $input['recaptcha_site_key'] ?? '' ),
			'recaptcha_secret_key' => sanitize_text_field( $input['recaptcha_secret_key'] ?? '' ),
			'primary_color'        => sanitize_hex_color( $input['primary_color'] ?? '#4F46E5' ),
			'from_name'            => sanitize_text_field( $input['from_name'] ?? get_bloginfo( 'name' ) ),
			'from_email'           => sanitize_email( $input['from_email'] ?? get_bloginfo( 'admin_email' ) ),
			'disable_assets'       => isset( $input['disable_assets'] ) ? 1 : 0,
		);
	}

	private function renderField( string $key, string $desc = '', string $placeholder = '', string $type = 'text' ): void {
		$v = get_option( 'genform_general', array() )[ $key ] ?? '';
		printf(
			'<input type="%1$s" name="genform_general[%2$s]" value="%3$s" placeholder="%4$s" class="regular-text" autocomplete="off" />',
			esc_attr( $type ),
			esc_attr( $key ),
			esc_attr( $v ),
			esc_attr( $placeholder )
		);
		if ( $desc ) {
			printf( '<p class="description">%s</p>', wp_kses_post( $desc ) );
		}
	}

	private function renderCheckboxField( string $key, string $label, string $desc = '' ): void {
		$v = get_option( 'genform_general', array() )[ $key ] ?? 0;
		printf(
			'<label class="gfm-inline-label gfm-checkbox-label"><input type="checkbox" name="genform_general[%1$s]" value="1" %2$s /><span>%3$s</span></label>',
			esc_attr( $key ),
			checked( 1, $v, false ),
			esc_html( $label )
		);
		if ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}
	}

	private function renderColorField( string $key, string $desc = '' ): void {
		$v = get_option( 'genform_general', array() )[ $key ] ?? '#4F46E5';
		printf(
			'<div class="gfm-color-field"><input type="color" name="genform_general[%1$s]" value="%2$s" class="gfm-color-picker" /><code class="gfm-color-value">%2$s</code></div>',
			esc_attr( $key ),
			esc_attr( $v )
		);
		if ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}
	}
}
