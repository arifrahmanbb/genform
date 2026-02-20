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

$gfm_base_font_size   = esc_attr($settings['gfm_base_font_size'] ?? '16');
$gfm_base_font_weight = esc_attr($settings['gfm_base_font_weight'] ?? '400');
$gfm_submit_align     = esc_attr($settings['gfm_submit_align'] ?? 'left');
?>

<div class="gfm-form-container" id="gfm-form-<?php echo esc_attr($form->id); ?>">
	<form class="gfm-form gfm-form-js" data-id="<?php echo esc_attr($form->id); ?>">
		<input type="hidden" name="genform_id" value="<?php echo esc_attr($form->id); ?>">
		<input type="hidden" name="genform_nonce" value="<?php echo esc_attr($nonce); ?>">
		<input type="hidden" name="action" value="genform_submit">

		<div class="gfm-fields">
			<?php
			if (! empty($data['fields'])) :
				foreach ($data['fields'] as $genform_field) :
					$genform_field_name        = 'gfm_' . sanitize_title($genform_field['name']);
					$genform_field_placeholder = esc_attr($genform_field['placeholder'] ?? '');
					$genform_field_required    = ! empty($genform_field['required']) ? 'required' : '';
					$genform_field_width       = esc_attr($genform_field['width'] ?? '100');
					$genform_field_help_text   = $genform_field['help_text'] ?? '';
			?>
					<?php if ('hidden' === $genform_field['type']) : ?>
						<input
							type="hidden"
							name="<?php echo esc_attr($genform_field_name); ?>"
							value="<?php echo esc_attr($genform_field['default_value'] ?? ''); ?>">
					<?php else : ?>
						<div class="gfm-form-field gfm-w-<?php echo esc_attr($genform_field_width); ?> <?php echo esc_attr($genform_field['css_class'] ?? ''); ?> gfm-type-<?php echo esc_attr($genform_field['type']); ?>">
							<label class="gfm-label">
								<?php echo esc_html($genform_field['label']); ?>
								<?php
								if ($genform_field_required) {
									echo '<span class="gfm-required-mark">*</span>';
								}
								?>
							</label>

							<div class="gfm-input-control">
								<?php
								switch ($genform_field['type']) {
									case 'textarea':
										$genform_rows = absint($genform_field['rows'] ?? 4);
										if ($genform_rows < 2) {
											$genform_rows = 4;
										}
										printf(
											'<textarea name="%1$s" class="gfm-textarea" rows="%2$d" placeholder="%3$s" %4$s>%5$s</textarea>',
											esc_attr($genform_field_name),
											(int) $genform_rows, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
											$genform_data_req = ('checkbox' === $genform_field['type'] && $genform_field_required) ? ' data-required="1"' : '';
											echo '<div class="gfm-options-list"' . $genform_data_req . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											foreach ($genform_field['options'] as $genform_option) {
												printf(
													'<label class="gfm-choice-label"><input type="%1$s" name="%2$s" value="%3$s" class="gfm-input-choice" %4$s> <span class="gfm-choice-text">%5$s</span></label>',
													esc_attr($genform_field['type']),
													('checkbox' === $genform_field['type'] ? esc_attr("{$genform_field_name}[]") : esc_attr($genform_field_name)),
													esc_attr($genform_option['value']),
													esc_attr('checkbox' === $genform_field['type'] ? '' : $genform_field_required),
													esc_html($genform_option['label'])
												);
											}
											echo '</div>';
										}
										break;

									case 'number':
										$genform_num_attrs = '';
										if (isset($genform_field['min']) && '' !== $genform_field['min']) {
											$genform_num_attrs .= ' min="' . esc_attr($genform_field['min']) . '"';
										}
										if (isset($genform_field['max']) && '' !== $genform_field['max']) {
											$genform_num_attrs .= ' max="' . esc_attr($genform_field['max']) . '"';
										}
										if (isset($genform_field['step']) && '' !== $genform_field['step']) {
											$genform_num_attrs .= ' step="' . esc_attr($genform_field['step']) . '"';
										}
										printf(
											'<input type="number" name="%1$s" class="gfm-input" placeholder="%2$s" value="%3$s" %4$s%5$s>',
											esc_attr($genform_field_name),
											esc_attr($genform_field_placeholder),
											esc_attr($genform_field['default_value'] ?? ''),
											esc_attr($genform_field_required),
											$genform_num_attrs // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										);
										break;

									case 'password':
										printf(
											'<input type="password" name="%1$s" class="gfm-input" placeholder="%2$s" %3$s autocomplete="new-password">',
											esc_attr($genform_field_name),
											esc_attr($genform_field_placeholder),
											esc_attr($genform_field_required)
										);
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
							<?php if (! empty($genform_field_help_text)) : ?>
								<p class="gfm-field-description"><?php echo esc_html($genform_field_help_text); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
			<?php
				endforeach;
			endif;
			?>
		</div>

		<?php /* Honeypot field for anti-spam – hidden from real users via CSS */ ?>
		<div aria-hidden="true" style="position:absolute;left:-9999px;height:0;overflow:hidden;">
			<label for="genform_website_url_<?php echo esc_attr($form->id); ?>"><?php esc_html_e('Website URL', 'genform'); ?></label>
			<input type="text" name="genform_website_url" id="genform_website_url_<?php echo esc_attr($form->id); ?>" value="" tabindex="-1" autocomplete="off" />
		</div>

		<?php if (! empty($settings['gfm_gdpr_enabled'])) : ?>
			<div class="gfm-form-field gfm-w-100 gfm-gdpr-field">
				<label class="gfm-choice-label gfm-gdpr-label">
					<input type="checkbox" name="genform_gdpr_consent" value="1" class="gfm-input-choice gfm-gdpr-checkbox" required>
					<span class="gfm-choice-text">
						<?php echo esc_html($settings['gfm_gdpr_text'] ?? esc_html__('I consent to having this website store my submitted information.', 'genform')); ?>
						<span class="gfm-required-mark">*</span>
					</span>
				</label>
			</div>
		<?php endif; ?>

		<div class="gfm-submit-wrap gfm-align-<?php echo esc_attr($gfm_submit_align); ?>">
			<button type="submit" class="gfm-submit <?php echo esc_attr('full' === $gfm_submit_align ? 'gfm-btn-full' : 'gfm-btn-auto'); ?>">
				<?php echo esc_html($settings['gfm_submit_text'] ?? esc_html__('Submit', 'genform')); ?>
			</button>
		</div>

		<div class="gfm-message gfm-hidden"></div>
	</form>
</div>