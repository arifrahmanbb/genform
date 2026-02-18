<?php

/**
 * Admin View: All Forms List
 *
 * Displays a sortable table of all forms created with GenForm.
 *
 * @package GenForm
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! current_user_can('manage_options')) {
	wp_die(esc_html__('Unauthorized.', 'genform'));
}

global $wpdb;

// Fetch forms with submission counts.
$genform_fs = $wpdb->get_results("SELECT f.*, (SELECT COUNT(*) FROM {$wpdb->prefix}genform_entries WHERE form_id = f.id) as e_c FROM {$wpdb->prefix}genform_forms f ORDER BY f.created_at DESC"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php esc_html_e('All Forms', 'genform'); ?></h1>
		<div class="gfm-header-actions">
			<button type="button" class="gfm-btn gfm-btn-primary" id="gfm-add-new-form">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e('Add New Form', 'genform'); ?>
			</button>
		</div>
	</div>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if (isset($_GET['duplicated'])) :
	?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('Form duplicated successfully.', 'genform'); ?></p>
		</div>
	<?php endif; ?>

	<div class="gfm-card">
		<?php if (empty($genform_fs)) : ?>
			<div class="gfm-empty-state">
				<span class="dashicons dashicons-forms"></span>
				<p><?php esc_html_e('No forms yet. Start from scratch or pick a ready-made template.', 'genform'); ?></p>
				<div class="gfm-empty-state-actions">
					<button type="button" class="gfm-btn gfm-btn-outline" id="gfm-open-templates">
						<span class="dashicons dashicons-layout"></span>
						<?php esc_html_e('Browse Templates', 'genform'); ?>
					</button>
					<a href="<?php echo esc_url(admin_url('admin.php?page=genform-builder')); ?>" class="gfm-btn gfm-btn-primary">
						<?php esc_html_e('Create Your First Form', 'genform'); ?>
					</a>
				</div>
			</div>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e('Name', 'genform'); ?></th>
						<th width="250"><?php esc_html_e('Shortcode', 'genform'); ?></th>
						<th width="100"><?php esc_html_e('Entries', 'genform'); ?></th>
						<th><?php esc_html_e('Status', 'genform'); ?></th>
						<th><?php esc_html_e('Created', 'genform'); ?></th>
						<th width="150"><?php esc_html_e('Actions', 'genform'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($genform_fs as $genform_f) : ?>
						<tr>
							<td>
								<strong>
									<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform-builder&action=edit&form_id=' . $genform_f->id), 'genform_edit_form')); ?>">
										<?php echo esc_html($genform_f->form_name); ?>
									</a>
								</strong>
							</td>
							<td>
								<div class="gfm-shortcode-copy">
									<code>[genform id="<?php echo (int) $genform_f->id; ?>"]</code>
									<button class="gfm-copy-btn dashicons dashicons-admin-page" data-code='[genform id="<?php echo (int) $genform_f->id; ?>"]'></button>
								</div>
							</td>
							<td>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform-entries&form_id=' . $genform_f->id), 'genform_view_entries')); ?>" class="gfm-count-badge">
									<?php echo (int) $genform_f->e_c; ?>
								</a>
							</td>
							<td>
								<span class="gfm-status gfm-status-<?php echo esc_attr($genform_f->status); ?>">
									<?php echo esc_html(ucfirst($genform_f->status)); ?>
								</span>
							</td>
							<td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($genform_f->created_at))); ?></td>
							<td>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform-builder&action=edit&form_id=' . $genform_f->id), 'genform_edit_form')); ?>" class="button">
									<?php esc_html_e('Edit', 'genform'); ?>
								</a>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform&action=duplicate&form_id=' . $genform_f->id), 'genform_duplicate_form')); ?>" class="button">
									<?php esc_html_e('Duplicate', 'genform'); ?>
								</a>
								<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform&action=delete&form_id=' . $genform_f->id), 'genform_delete_form')); ?>" class="button button-link-delete">
									<?php esc_html_e('Delete', 'genform'); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>