<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$id       = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
$form     = $id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id)) : null;
$data     = $form ? json_decode($form->data, true) : [];
$settings = $form ? json_decode($form->settings, true) : [];
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-builder-header">
		<h1><?php echo $id ? __('Edit Form', 'genform') : __('Create New Form', 'genform'); ?></h1>
	</div>

	<form method="post" id="gfm-builder-form" class="gfm-card">
		<?php wp_nonce_field('genform_save_form', 'genform_builder_nonce'); ?>

		<div class="gfm-form-group">
			<label for="form_name"><?php _e('Form Name', 'genform'); ?></label>
			<input type="text" name="form_name" id="form_name" value="<?php echo esc_attr($form->name ?? ''); ?>" required class="regular-text" />
		</div>

		<div class="gfm-builder-container">
			<!-- Sidebar: Field Types -->
			<div class="gfm-sidebar">
				<h3><?php _e('Fields', 'genform'); ?></h3>
				<div class="gfm-field-types">
					<?php
					$fields = ['text' => 'edit', 'email' => 'email', 'textarea' => 'text', 'number' => 'calculator', 'select' => 'menu-alt'];
					foreach ($fields as $type => $icon): ?>
						<button type="button" class="gfm-add-field gfm-field-node" data-type="<?php echo $type; ?>">
							<span class="dashicons dashicons-<?php echo $icon; ?>"></span>
							<?php echo ucfirst($type); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Canvas: Form Content -->
			<div class="gfm-canvas">
				<div id="gfm-fields-container">
					<!-- Dynamic Fields -->
				</div>
			</div>

			<!-- Settings Sidebar -->
			<div class="gfm-settings-panel">
				<h3><?php _e('Settings', 'genform'); ?></h3>
				<div class="gfm-setting-group">
					<label><?php _e('Submit Button Text', 'genform'); ?></label>
					<input type="text" id="gfm-submit-text" value="<?php echo esc_attr($settings['submit_text'] ?? 'Submit'); ?>" class="widefat" />
				</div>
				<div class="gfm-setting-group">
					<label><?php _e('Success Message', 'genform'); ?></label>
					<textarea id="gfm-success-message" class="widefat"><?php echo esc_textarea($settings['success_message'] ?? __('Thank you!', 'genform')); ?></textarea>
				</div>
			</div>
		</div>

		<div class="gfm-footer-actions">
			<button type="submit" name="genform_save" class="gfm-btn">
				<?php _e('Save Form', 'genform'); ?>
			</button>
		</div>

		<input type="hidden" name="form_data" id="gfm-data-input" value="" />
		<input type="hidden" name="form_settings" id="gfm-settings-input" value="" />
	</form>
</div>