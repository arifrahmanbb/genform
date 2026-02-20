<?php

/**
 * Admin View: Form Builder Interface
 *
 * Provides the interactive UI for designing form fields and configuring settings.
 *
 * @package GenForm
 */

if (! defined('ABSPATH')) {
	exit;
}

global $wpdb;
// Nonce is verified in handlers for all write operations. This view is for display only.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$genform_id = isset($_GET['form_id']) ? absint(wp_unslash($_GET['form_id'])) : 0;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$genform_form     = $genform_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $genform_id)) : null;
$genform_settings = $genform_form ? json_decode($genform_form->form_settings, true) : array();
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-builder-header-main">
		<h1><?php echo $genform_id ? esc_html__('Edit Form', 'genform') : esc_html__('Create Form', 'genform'); ?></h1>
		<div class="gfm-builder-tabs">
			<button type="button" class="gfm-tab-link active" data-tab="fields"><?php esc_html_e('Fields', 'genform'); ?></button>
			<button type="button" class="gfm-tab-link" data-tab="settings"><?php esc_html_e('Settings', 'genform'); ?></button>
			<button type="button" class="gfm-tab-link" data-tab="notifications"><?php esc_html_e('Notifications', 'genform'); ?></button>
		</div>
	</div>

	<form method="post" id="gfm-builder-form">
		<?php wp_nonce_field('genform_save_form', 'genform_builder_nonce'); ?>

		<div class="gfm-builder-top">
			<div class="gfm-card gfm-name-card">
				<label><?php esc_html_e('Form Title', 'genform'); ?></label>
				<input type="text" name="form_name" value="<?php echo esc_attr($genform_form->form_name ?? ''); ?>" required placeholder="<?php esc_attr_e('e.g. Contact Us', 'genform'); ?>">
			</div>
			<div class="gfm-save-area">
				<button type="submit" name="genform_save" class="gfm-btn gfm-btn-primary gfm-btn-large">
					<?php esc_html_e('Save Form', 'genform'); ?>
				</button>
				<?php if ($genform_id) : ?>
					<a href="<?php echo esc_url(admin_url('admin.php?page=genform-preview&form_id=' . $genform_id)); ?>" class="gfm-btn gfm-btn-outline" target="_blank">
						<span class="dashicons dashicons-visibility" style="margin-top: 3px;"></span>
						<?php esc_html_e('Preview', 'genform'); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=genform')); ?>" class="gfm-btn gfm-btn-outline">
					<?php esc_html_e('Cancel', 'genform'); ?>
				</a>
			</div>
		</div>

		<!-- Fields Configuration Tab -->
		<div id="gfm-tab-fields" class="gfm-tab-content">
			<div class="gfm-builder-layout">
				<div class="gfm-sidebar">
					<div class="gfm-card">
						<h3><?php esc_html_e('Available Fields', 'genform'); ?></h3>
						<div class="gfm-field-buttons">
							<?php
							$genform_fields = array(
								'text'     => 'edit',
								'email'    => 'email',
								'textarea' => 'text',
								'number'   => 'calculator',
								'select'   => 'menu-alt',
								'radio'    => 'marker',
								'checkbox' => 'yes',
								'date'     => 'calendar-alt',
								'url'      => 'admin-links',
								'tel'      => 'phone',
								'hidden'   => 'hidden',
								'password' => 'lock',
							);
							foreach ($genform_fields as $genform_type => $genform_icon) :
							?>
								<button type="button" class="gfm-add-field" data-type="<?php echo esc_attr($genform_type); ?>">
									<span class="dashicons dashicons-<?php echo esc_attr($genform_icon); ?>"></span>
									<?php echo esc_html(ucfirst($genform_type)); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="gfm-canvas">
					<div id="gfm-fields-container">
						<div class="gfm-empty-canvas">
							<span class="dashicons dashicons-plus-alt"></span>
							<p><?php esc_html_e('Add fields here.', 'genform'); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Global Settings Tab -->
		<div id="gfm-tab-settings" class="gfm-tab-content gfm-hidden">
			<div class="gfm-card">
				<h3><?php esc_html_e('Confirmation', 'genform'); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e('Type', 'genform'); ?></label>
						<select id="gfm-con-type">
							<option value="message" <?php selected($genform_settings['gfm_con_type'] ?? '', 'message'); ?>><?php esc_html_e('Message', 'genform'); ?></option>
							<option value="redirect" <?php selected($genform_settings['gfm_con_type'] ?? '', 'redirect'); ?>><?php esc_html_e('Redirect', 'genform'); ?></option>
						</select>
					</div>
					<div class="gfm-col gfm-con-field gfm-con-redirect gfm-hidden">
						<label><?php esc_html_e('URL', 'genform'); ?></label>
						<input type="url" id="gfm-redirect-url" value="<?php echo esc_url($genform_settings['gfm_redirect_url'] ?? ''); ?>">
					</div>
				</div>
				<div class="gfm-grid gfm-con-field gfm-con-message">
					<div class="gfm-col">
						<label><?php esc_html_e('Success Message', 'genform'); ?></label>
						<textarea id="gfm-success-message"><?php echo esc_textarea($genform_settings['gfm_success_message'] ?? ''); ?></textarea>
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e('Error Message', 'genform'); ?></label>
						<textarea id="gfm-error-message"><?php echo esc_textarea($genform_settings['gfm_error_message'] ?? ''); ?></textarea>
					</div>
				</div>
			</div>

			<div class="gfm-card">
				<h3><?php esc_html_e('Submit Button', 'genform'); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e('Button Text', 'genform'); ?></label>
						<input type="text" id="gfm-submit-text" value="<?php echo esc_attr($genform_settings['gfm_submit_text'] ?? esc_html__('Submit', 'genform')); ?>">
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e('Button Alignment', 'genform'); ?></label>
						<select id="gfm-submit-align">
							<option value="left" <?php selected($genform_settings['gfm_submit_align'] ?? 'left', 'left'); ?>><?php esc_html_e('Left', 'genform'); ?></option>
							<option value="center" <?php selected($genform_settings['gfm_submit_align'] ?? 'left', 'center'); ?>><?php esc_html_e('Center', 'genform'); ?></option>
							<option value="right" <?php selected($genform_settings['gfm_submit_align'] ?? 'left', 'right'); ?>><?php esc_html_e('Right', 'genform'); ?></option>
							<option value="full" <?php selected($genform_settings['gfm_submit_align'] ?? 'left', 'full'); ?>><?php esc_html_e('Full Width', 'genform'); ?></option>
						</select>
					</div>
				</div>
			</div>

			<div class="gfm-card">
				<h3><?php esc_html_e('Typography & Spacing', 'genform'); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e('Base Font Size (px)', 'genform'); ?></label>
						<input type="number" id="gfm-base-font-size" value="<?php echo esc_attr($genform_settings['gfm_base_font_size'] ?? '16'); ?>" min="12" max="24">
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e('Font Weight', 'genform'); ?></label>
						<select id="gfm-base-font-weight">
							<option value="300" <?php selected($genform_settings['gfm_base_font_weight'] ?? '400', '300'); ?>><?php esc_html_e('Light (300)', 'genform'); ?></option>
							<option value="400" <?php selected($genform_settings['gfm_base_font_weight'] ?? '400', '400'); ?>><?php esc_html_e('Regular (400)', 'genform'); ?></option>
							<option value="500" <?php selected($genform_settings['gfm_base_font_weight'] ?? '400', '500'); ?>><?php esc_html_e('Medium (500)', 'genform'); ?></option>
							<option value="600" <?php selected($genform_settings['gfm_base_font_weight'] ?? '400', '600'); ?>><?php esc_html_e('Semi-Bold (600)', 'genform'); ?></option>
							<option value="700" <?php selected($genform_settings['gfm_base_font_weight'] ?? '400', '700'); ?>><?php esc_html_e('Bold (700)', 'genform'); ?></option>
						</select>
					</div>
				</div>
			</div>

			<div class="gfm-card">
				<h3><?php esc_html_e('GDPR / Consent', 'genform'); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label class="gfm-choice-label">
							<input type="checkbox" id="gfm-gdpr-enabled" value="1" <?php checked(! empty($genform_settings['gfm_gdpr_enabled'])); ?>>
							<span class="gfm-choice-text"><?php esc_html_e('Enable GDPR Consent Checkbox', 'genform'); ?></span>
						</label>
					</div>
				</div>
				<div class="gfm-grid gfm-gdpr-options" style="margin-top: 12px;">
					<div class="gfm-col">
						<label><?php esc_html_e('Consent Text', 'genform'); ?></label>
						<textarea id="gfm-gdpr-text" rows="3" class="widefat"><?php echo esc_textarea($genform_settings['gfm_gdpr_text'] ?? esc_html__('I consent to having this website store my submitted information.', 'genform')); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- Email Notifications Tab -->
		<div id="gfm-tab-notifications" class="gfm-tab-content gfm-hidden">
			<div class="gfm-card">
				<h3><?php esc_html_e('Email Notifications', 'genform'); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e('Send To Email', 'genform'); ?></label>
						<input type="text" id="gfm-admin-email" value="<?php echo esc_attr($genform_settings['gfm_admin_email'] ?? '{admin_email}'); ?>">
						<span class="gfm-setting-desc"><?php esc_html_e('Use {admin_email} for site admin email.', 'genform'); ?></span>
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e('From Name', 'genform'); ?></label>
						<input type="text" id="gfm-from-name" value="<?php echo esc_attr($genform_settings['gfm_from_name'] ?? ''); ?>" placeholder="{global_from_name}">
					</div>
				</div>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e('From Email', 'genform'); ?></label>
						<input type="text" id="gfm-from-email" value="<?php echo esc_attr($genform_settings['gfm_from_email'] ?? ''); ?>" placeholder="{global_from_email}">
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e('Reply-To Email', 'genform'); ?></label>
						<input type="text" id="gfm-reply-to" value="<?php echo esc_attr($genform_settings['gfm_reply_to'] ?? '{field_email}'); ?>">
					</div>
				</div>
				<div class="gfm-setting-row gfm-mt-md">
					<label><?php esc_html_e('Email Subject', 'genform'); ?></label>
					<input type="text" id="gfm-email-subject" value="<?php echo esc_attr($genform_settings['gfm_email_subject'] ?? ''); ?>" class="widefat" placeholder="<?php esc_attr_e('New Submission: {form_name}', 'genform'); ?>">
				</div>
				<div class="gfm-setting-row gfm-mt-md">
					<label><?php esc_html_e('Email Body', 'genform'); ?></label>
					<textarea id="gfm-email-body" rows="8" class="widefat" placeholder="<?php esc_attr_e("{all_fields}\n\nSent from {site_title}", 'genform'); ?>"><?php echo esc_textarea($genform_settings['gfm_email_body'] ?? ''); ?></textarea>
					<span class="gfm-setting-desc"><?php esc_html_e('Tags: {all_fields}, {form_name}, {site_title}, {field_ID}', 'genform'); ?></span>
				</div>
			</div>
		</div>

		<!-- Hidden data stores used by the builder script -->
		<input type="hidden" name="form_data" id="gfm-data-input">
		<input type="hidden" name="form_settings" id="gfm-settings-input">
	</form>
</div>