<?php

/**
 * Frontend Shortcode Handler
 *
 * Implements the [genform id="X"] shortcode for embedding forms.
 *
 * @package GenForm
 */

namespace GenForm\Integrations;

if (! defined('ABSPATH')) {
	exit;
}

final class Shortcode
{


	/**
	 * Map the shortcode to the render function.
	 */
	public function __construct()
	{
		add_shortcode('genform', array($this, 'render'));
	}

	/**
	 * Process shortcode attributes and fetch the corresponding form record.
	 */
	public function render(array $atts): string
	{
		$atts = shortcode_atts(array('id' => 0), $atts, 'genform');
		$id   = (int) $atts['id'];

		if (! $id) {
			return '<p>' . esc_html__('No form ID provided.', 'genform') . '</p>';
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d AND status = 'active'", $id));

		if (! $form) {
			return '<p>' . esc_html__('Form not found.', 'genform') . '</p>';
		}

		ob_start();
		$this->displayForm($form);
		return ob_get_clean();
	}

	/**
	 * Prepare dynamic data and include the frontend template.
	 */
	private function displayForm(object $form): void
	{
		$data     = json_decode($form->form_data, true);
		$settings = json_decode($form->form_settings, true);
		$nonce    = wp_create_nonce("genform_submit_{$form->id}");

		// Dynamic styles for this specific form instance.
		$base_font_size   = (int) ($settings['gfm_base_font_size'] ?? 16);
		$base_font_weight = (int) ($settings['gfm_base_font_weight'] ?? 400);
		$custom_css       = "
			#gfm-form-{$form->id} {
				--gfm-base-size: {$base_font_size}px;
				font-weight: {$base_font_weight};
			}
		";
		wp_add_inline_style('genform-frontend', $custom_css);

		include GENFORM_PATH . 'public/views/form-template.php';
	}
}
