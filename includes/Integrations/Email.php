<?php
/**
 * Email Notification Engine
 *
 * Construct and send HTML emails upon form submission.
 *
 * @package GenForm
 */

namespace GenForm\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Email {

	/**
	 * Compile and dispatch the notification email.
	 */
	public static function send( int $entry_id, int $form_id, array $data ): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id ) );
		if ( ! $form ) {
			return;
		}

		$settings = json_decode( $form->form_settings, true );
		$tags     = array(
			'{form_name}'   => $form->form_name,
			'{entry_id}'    => (string) $entry_id,
			'{admin_email}' => get_option( 'admin_email' ),
			'{site_title}'  => get_bloginfo( 'name' ),
			'{all_fields}'  => self::buildFieldsHtml( $data ),
		);

		// Allow dynamic replacements for individual field keys.
		foreach ( $data as $key => $val ) {
			$tags["{field_{$key}}"] = is_array( $val ) ? implode( ', ', $val ) : (string) $val;
		}

		$to      = str_replace( array_keys( $tags ), array_values( $tags ), $settings['admin_email'] ?: '{admin_email}' );
		$subject = str_replace( array_keys( $tags ), array_values( $tags ), $settings['email_subject'] ?: esc_html__( 'New Submission: {form_name}', 'genform' ) );

		// Core email styling – localized within the body as an internal style block.
		$style = '<style>.gfm-mail-table{width:100%;border-collapse:collapse;font-family:sans-serif}.gfm-mail-label{padding:10px;border:1px solid #eee;background:#f9f9f9;width:30%}.gfm-mail-value{padding:10px;border:1px solid #eee}</style>';
		$body  = wpautop( str_replace( array_keys( $tags ), array_values( $tags ), $style . ( $settings['email_body'] ?: '{all_fields}' ) ) );

		$headers        = array( 'Content-Type: text/html; charset=UTF-8' );
		$global_options = get_option( 'genform_general', array() );

		// Determine sender identity.
		$f_name  = str_replace( array_keys( $tags ), array_values( $tags ), ! empty( $settings['from_name'] ) ? $settings['from_name'] : ( $global_options['from_name'] ?? get_bloginfo( 'name' ) ) );
		$f_email = str_replace( array_keys( $tags ), array_values( $tags ), ! empty( $settings['from_email'] ) ? $settings['from_email'] : ( $global_options['from_email'] ?? get_bloginfo( 'admin_email' ) ) );
		$headers[] = "From: {$f_name} <{$f_email}>";

		if ( ! empty( $settings['reply_to'] ) ) {
			$reply = str_replace( array_keys( $tags ), array_values( $tags ), $settings['reply_to'] );
			if ( is_email( $reply ) ) {
				$headers[] = "Reply-To: {$reply}";
			}
		}

		wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Helper to generate the tabular representation of all form fields.
	 */
	private static function buildFieldsHtml( array $data ): string {
		$html = '<table class="gfm-mail-table">';
		foreach ( $data as $key => $val ) {
			$label = ucfirst( str_replace( array( '_', '-' ), ' ', $key ) );
			if ( is_array( $val ) ) {
				$val = implode( ', ', $val );
			}
			$html .= sprintf(
				'<tr><td class="gfm-mail-label"><strong>%s</strong></td><td class="gfm-mail-value">%s</td></tr>',
				esc_html( $label ),
				nl2br( esc_html( (string) $val ) )
			);
		}
		$html .= '</table>';
		return $html;
	}
}
