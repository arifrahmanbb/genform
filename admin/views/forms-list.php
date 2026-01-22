<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
	wp_die(__('Unauthorized.', 'genform'));
}

global $wpdb;
$table = $wpdb->prefix . 'genform_forms';
$e_table = $wpdb->prefix . 'genform_entries';

$forms = $wpdb->get_results("SELECT f.*, (SELECT COUNT(*) FROM $e_table WHERE form_id = f.id) as entries_count FROM $table f ORDER BY f.created_at DESC");
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php _e('All Forms', 'genform'); ?></h1>
		<a href="<?php echo admin_url('admin.php?page=genform-builder'); ?>" class="gfm-btn gfm-btn-primary"><?php _e('Add New Form', 'genform'); ?></a>
	</div>

	<div class="gfm-card">
		<?php if (empty($forms)): ?>
			<div class="gfm-empty-state">
				<span class="dashicons dashicons-forms"></span>
				<p><?php _e('You haven\'t created any forms yet.', 'genform'); ?></p>
				<a href="<?php echo admin_url('admin.php?page=genform-builder'); ?>" class="gfm-btn gfm-btn-outline"><?php _e('Create Your First Form', 'genform'); ?></a>
			</div>
		<?php else: ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e('Form Name', 'genform'); ?></th>
						<th width="250"><?php _e('Shortcode', 'genform'); ?></th>
						<th width="100"><?php _e('Entries', 'genform'); ?></th>
						<th><?php _e('Status', 'genform'); ?></th>
						<th><?php _e('Created', 'genform'); ?></th>
						<th width="150"><?php _e('Actions', 'genform'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($forms as $form): ?>
						<tr>
							<td>
								<strong><a href="<?php echo wp_nonce_url(admin_url('admin.php?page=genform-builder&action=edit&form_id=' . $form->id), 'genform_edit_form'); ?>"><?php echo esc_html($form->form_name); ?></a></strong>
							</td>
							<td>
								<div class="gfm-shortcode-copy">
									<code>[genform id="<?php echo $form->id; ?>"]</code>
									<button class="gfm-copy-btn dashicons dashicons-admin-page" data-code='[genform id="<?php echo $form->id; ?>"]'></button>
								</div>
							</td>
							<td>
								<a href="<?php echo wp_nonce_url(admin_url('admin.php?page=genform-entries&form_id=' . $form->id), 'genform_view_entries'); ?>" class="gfm-count-badge">
									<?php echo $form->entries_count; ?>
								</a>
							</td>
							<td>
								<span class="gfm-status gfm-status-<?php echo esc_attr($form->status); ?>">
									<?php echo ucfirst($form->status); ?>
								</span>
							</td>
							<td><?php echo date_i18n(get_option('date_format'), strtotime($form->created_at)); ?></td>
							<td>
								<a href="<?php echo wp_nonce_url(admin_url('admin.php?page=genform-builder&action=edit&form_id=' . $form->id), 'genform_edit_form'); ?>" class="button"><?php _e('Edit', 'genform'); ?></a>
								<a href="<?php echo wp_nonce_url(admin_url('admin.php?page=genform&action=delete&form_id=' . $form->id), 'genform_delete_form'); ?>" class="button button-link-delete" onclick="return confirm('Really delete this form and all its entries?')"><?php _e('Delete', 'genform'); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
		$('.gfm-copy-btn').on('click', function() {
			const code = $(this).data('code');
			const $temp = $("<input>");
			$("body").append($temp);
			$temp.val(code).select();
			document.execCommand("copy");
			$temp.remove();
			$(this).removeClass('dashicons-admin-page').addClass('dashicons-yes');
			setTimeout(() => $(this).removeClass('dashicons-yes').addClass('dashicons-admin-page'), 2000);
		});
	});
</script>

<style>
	.gfm-shortcode-copy {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.gfm-copy-btn {
		background: none;
		border: none;
		cursor: pointer;
		color: #999;
	}

	.gfm-copy-btn:hover {
		color: #666;
	}

	.gfm-count-badge {
		background: #6366f1;
		color: #fff;
		padding: 2px 8px;
		border-radius: 99px;
		text-decoration: none;
		font-size: 11px;
		font-weight: 700;
	}

	.gfm-count-badge:hover {
		background: #4f46e5;
	}
</style>