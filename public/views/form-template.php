<?php

/**
 * Frontend View: Form Template
 *
 * This file is processed for every [genform] shortcode and handles dynamic field generation.
 *
 * @package GenForm
 */

if (! defined('ABSPATH')) {
	exit;
}

$genform_base_font_size   = esc_attr($settings['base_font_size'] ?? '16');
$genform_base_font_weight = esc_attr($settings['base_font_weight'] ?? '400');
$genform_submit_align     = esc_attr($settings['submit_align'] ?? 'left');
?>

<div class="gfm-form-container" id="gfm-form-<?php echo esc_attr($form->id); ?>">
	<form class="gfm-form gfm-form-js" data-id="<?php echo esc_attr($form->id); ?>">
		<input type="hidden" name="genform_id" value="<?php echo esc_attr($form->id); ?>">
		<input type="hidden" name="genform_nonce" value="<?php echo esc_attr($nonce); ?>">
		<input type="hidden" name="action" value="genform_submit">

		<?php
		if (! empty($data['fields'])) :
			foreach ($data['fields'] as $genform_field) :
				$genform_field_name        = 'gfm_' . sanitize_title($genform_field['name']);
				$genform_field_placeholder = esc_attr($genform_field['placeholder'] ?? '');
				$genform_field_required    = ! empty($genform_field['required']) ? 'required' : '';
				$genform_field_width       = esc_attr($genform_field['width'] ?? '100');
		?>
				<div class="gfm-form-field gfm-w-<?php echo esc_attr($genform_field_width); ?> <?php echo esc_attr($genform_field['css_class'] ?? ''); ?> gfm-type-<?php echo esc_attr($genform_field['type']); ?>">
					<?php if ($genform_field['type'] !== 'hidden') : ?>
						<label class="gfm-label">
							<?php echo esc_html($genform_field['label']); ?>
							<?php if ($genform_field_required) echo '<span class="gfm-required-mark">*</span>'; ?>
						</label>
					<?php endif; ?>

					<div class="gfm-input-control">
						<?php
						switch ($genform_field['type']) {
							case 'textarea':
								printf(
									'<textarea name="%1$s" class="gfm-textarea" rows="4" placeholder="%2$s" %3$s>%4$s</textarea>',
									esc_attr($genform_field_name),
									esc_attr($genform_field_placeholder),
									esc_attr($genform_field_required),
									esc_textarea($genform_field['default_value'] ?? '')
								);
								break;

							case 'select':
								printf('<select name="%1$s" class="gfm-select" %2$s>', esc_attr($genform_field_name), esc_attr($genform_field_required));
								if ($genform_field_placeholder) {
									printf('<option value="" disabled selected>%s</option>', esc_html($genform_field_placeholder));
								}
								if (! empty($genform_field['options'])) {
									foreach ($genform_field['options'] as $genform_option) {
										printf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($genform_option['value']), selected($genform_field['default_value'] ?? '', $genform_option['value'], false), esc_html($genform_option['label']));
									}
								}
								echo '</select>';
								break;

							case 'radio':
							case 'checkbox':
								if (! empty($genform_field['options'])) {
									$genform_data_req = ($genform_field['type'] === 'checkbox' && $genform_field_required) ? ' data-required="1"' : '';
									echo '<div class="gfm-options-list"' . $genform_data_req . '>';
									foreach ($genform_field['options'] as $genform_option) {
										printf(
											'<label class="gfm-choice-label"><input type="%1$s" name="%2$s" value="%3$s" class="gfm-input-choice" %4$s> <span class="gfm-choice-text">%5$s</span></label>',
											esc_attr($genform_field['type']),
											($genform_field['type'] === 'checkbox' ? esc_attr("{$genform_field_name}[]") : esc_attr($genform_field_name)),
											esc_attr($genform_option['value']),
											esc_attr($genform_field['type'] === 'checkbox' ? '' : $genform_field_required),
											esc_html($genform_option['label'])
										);
									}
									echo '</div>';
								}
								break;

							default:
								printf(
									'<input type="%1$s" name="%2$s" class="gfm-input" placeholder="%3$s" value="%4$s" %5$s>',
									esc_attr($genform_field['type']),
									esc_attr($genform_field_name),
									esc_attr($genform_field_placeholder),
									esc_attr($genform_field['default_value'] ?? ''),
									esc_attr($genform_field_required)
								);
								break;
						}
						?>
					</div>
				</div>
		<?php
			endforeach;
		endif;
		?>

		<?php /* Honeypot field for anti-spam – hidden from real users via CSS */ ?>
		<div aria-hidden="true" style="position:absolute;left:-9999px;height:0;overflow:hidden;">
			<label for="genform_website_url_<?php echo esc_attr($form->id); ?>"><?php esc_html_e('Website URL', 'genform'); ?></label>
			<input type="text" name="genform_website_url" id="genform_website_url_<?php echo esc_attr($form->id); ?>" value="" tabindex="-1" autocomplete="off" />
		</div>

		<?php if (! empty($settings['gdpr_enabled'])) : ?>
			<div class="gfm-form-field gfm-w-100 gfm-gdpr-field">
				<label class="gfm-choice-label gfm-gdpr-label">
					<input type="checkbox" name="genform_gdpr_consent" value="1" class="gfm-input-choice gfm-gdpr-checkbox" required>
					<span class="gfm-choice-text">
						<?php echo esc_html($settings['gdpr_text'] ?? esc_html__('I consent to having this website store my submitted information.', 'genform')); ?>
						<span class="gfm-required-mark">*</span>
					</span>
				</label>
			</div>
		<?php endif; ?>

		<div class="gfm-submit-wrap gfm-align-<?php echo esc_attr($genform_submit_align); ?>">
			<button type="submit" class="gfm-submit <?php echo ($genform_submit_align === 'full' ? 'gfm-btn-full' : 'gfm-btn-auto'); ?>">
				<?php echo esc_html($settings['submit_text'] ?? esc_html__('Submit', 'genform')); ?>
			</button>
		</div>

		<div class="gfm-message gfm-hidden"></div>
	</form>
</div>