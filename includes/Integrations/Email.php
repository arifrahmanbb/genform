<?php


namespace GenForm\Integrations;
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Email
 * Handles dynamic email notifications.
 */
final class Email {

	/**
	 * Send email.
	 *
	 * @param int   $entry_id
	 * @param int   $form_id
	 * @param array $data
	 */
	public static function send( int $entry_id, int $form_id, array $data ): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id ) );
		if ( ! $form ) {
			return;
		}

		$settings = json_decode( $form->form_settings, true );

        // Prep tags
        $tags = [
            '{form_name}'  => $form->form_name,
            '{entry_id}'   => (string) $entry_id,
            '{admin_email}' => get_option('admin_email'),
            '{site_title}' => get_bloginfo('name'),
            '{all_fields}' => self::buildFieldsHtml($data),
        ];

        // Process Admin Email
        $to = $settings['admin_email'] ?: '{admin_email}';
        $to = str_replace(array_keys($tags), array_values($tags), $to);

        $subject = $settings['email_subject'] ?: 'New Submission: {form_name}';
        $subject = str_replace(array_keys($tags), array_values($tags), $subject);

        $body = $settings['email_body'] ?: "{all_fields}";
        $body = str_replace(array_keys($tags), array_values($tags), $body);
        $body = wpautop($body);

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($to, $subject, $body, $headers);
    }

    private static function buildFieldsHtml(array $data): string
    {
        $html = '<table style="width:100%; border-collapse: collapse; font-family: sans-serif;">';
        foreach ($data as $key => $val) {
            $label = ucfirst(str_replace(['_', '-'], ' ', $key));
            if (is_array($val)) $val = implode(', ', $val);

            $html .= sprintf(
                '<tr>
                    <td style="padding: 10px; border: 1px solid #eee; background: #f9f9f9; width: 30%%;"><strong>%s</strong></td>
                    <td style="padding: 10px; border: 1px solid #eee;">%s</td>
                </tr>',
                esc_html($label),
                nl2br(esc_html((string)$val))
            );
        }
        $html .= '</table>';
        return $html;
    }
}
