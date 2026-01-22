<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$id       = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
$form     = $id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id)) : null;
$settings = $form ? json_decode($form->form_settings, true) : [];
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-builder-header-main">
		<h1><?php echo $id ? __('Edit Form', 'genform') : __('Create New Form', 'genform'); ?></h1>
		<div class="gfm-builder-tabs">
			<button type="button" class="gfm-tab-link active" data-tab="fields"><?php _e('Fields', 'genform'); ?></button>
			<button type="button" class="gfm-tab-link" data-tab="settings"><?php _e('Settings', 'genform'); ?></button>
			<button type="button" class="gfm-tab-link" data-tab="notifications"><?php _e('Notifications', 'genform'); ?></button>
		</div>
	</div>

	<form method="post" id="gfm-builder-form">
		<?php wp_nonce_field('genform_save_form', 'genform_builder_nonce'); ?>

		<div class="gfm-builder-top">
			<div class="gfm-card gfm-name-card">
				<label for="form_name"><?php _e('Form Name', 'genform'); ?></label>
				<input type="text" name="form_name" id="form_name" value="<?php echo esc_attr($form->form_name ?? ''); ?>" required class="widefat" placeholder="e.g. Contact Us" />
			</div>
			<div class="gfm-save-area">
				<button type="submit" name="genform_save" class="gfm-btn gfm-btn-large">
					<?php _e('Save Form', 'genform'); ?>
				</button>
				<a href="<?php echo admin_url('admin.php?page=genform'); ?>" class="gfm-btn gfm-btn-outline"><?php _e('Cancel', 'genform'); ?></a>
			</div>
		</div>

		<!-- Fields Tab -->
		<div id="gfm-tab-fields" class="gfm-tab-content">
			<div class="gfm-builder-layout">
				<div class="gfm-sidebar">
					<div class="gfm-card">
						<h3><?php _e('Add Fields', 'genform'); ?></h3>
						<div class="gfm-field-buttons">
							<?php
							$fields = [
								'text' => ['label' => 'Single Line Text', 'icon' => 'edit'],
								'email' => ['label' => 'Email', 'icon' => 'email'],
								'textarea' => ['label' => 'Paragraph Text', 'icon' => 'text'],
								'number' => ['label' => 'Number', 'icon' => 'calculator'],
								'select' => ['label' => 'Dropdown', 'icon' => 'menu-alt'],
								'radio' => ['label' => 'Multiple Choice', 'icon' => 'marker'],
								'checkbox' => ['label' => 'Checkboxes', 'icon' => 'yes'],
								'date' => ['label' => 'Date', 'icon' => 'calendar-alt'],
								'url' => ['label' => 'Website', 'icon' => 'admin-links'],
								'tel' => ['label' => 'Phone', 'icon' => 'phone'],
							];
							foreach ($fields as $type => $meta): ?>
								<button type="button" class="gfm-add-field" data-type="<?php echo $type; ?>">
									<span class="dashicons dashicons-<?php echo $meta['icon']; ?>"></span>
									<?php echo esc_html($meta['label']); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="gfm-canvas">
					<div id="gfm-fields-container">
						<!-- Fields populated via JS -->
						<div class="gfm-empty-canvas">
							<span class="dashicons dashicons-plus-alt"></span>
							<p><?php _e('Click a field on the left to add it to your form.', 'genform'); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Settings Tab -->
		<div id="gfm-tab-settings" class="gfm-tab-content" style="display:none;">
			<div class="gfm-card">
				<h3><?php _e('Form Settings', 'genform'); ?></h3>
				<div class="gfm-setting-row">
					<label><?php _e('Submit Button Text', 'genform'); ?></label>
					<input type="text" id="gfm-submit-text" class="widefat" value="<?php echo esc_attr($settings['submit_text'] ?? 'Submit'); ?>">
				</div>
				<div class="gfm-setting-row">
					<label><?php _e('Success Message', 'genform'); ?></label>
					<textarea id="gfm-success-message" class="widefat"><?php echo esc_textarea($settings['success_message'] ?? 'Thank you for your message. We will get back to you soon.'); ?></textarea>
				</div>
				<div class="gfm-setting-row">
					<label><?php _e('Redirect URL (Optional)', 'genform'); ?></label>
					<input type="url" id="gfm-redirect-url" class="widefat" value="<?php echo esc_attr($settings['redirect_url'] ?? ''); ?>" placeholder="https://yoursite.com/thank-you">
				</div>
			</div>
		</div>

		<!-- Notifications Tab -->
		<div id="gfm-tab-notifications" class="gfm-tab-content" style="display:none;">
			<div class="gfm-card">
				<h3><?php _e('Email Notifications', 'genform'); ?></h3>
				<div class="gfm-setting-row">
					<label><?php _e('Send To Email', 'genform'); ?></label>
					<input type="text" id="gfm-admin-email" class="widefat" value="<?php echo esc_attr($settings['admin_email'] ?? '{admin_email}'); ?>">
					<p class="description"><?php _e('You can use {admin_email} for the site admin.', 'genform'); ?></p>
				</div>
				<div class="gfm-setting-row">
					<label><?php _e('Email Subject', 'genform'); ?></label>
					<input type="text" id="gfm-email-subject" class="widefat" value="<?php echo esc_attr($settings['email_subject'] ?? 'New Submission: {form_name}'); ?>">
				</div>
				<div class="gfm-setting-row">
					<label><?php _e('Email Body', 'genform'); ?></label>
					<textarea id="gfm-email-body" class="widefat" rows="10"><?php echo esc_textarea($settings['email_body'] ?? "{all_fields}\n\nSent from {site_title}"); ?></textarea>
					<p class="description"><?php _e('Use {all_fields} to include all form data.', 'genform'); ?></p>
				</div>
			</div>
		</div>

		<input type="hidden" name="form_data" id="gfm-data-input" value="" />
		<input type="hidden" name="form_settings" id="gfm-settings-input" value="" />
	</form>
</div>