<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
	wp_die(__('Unauthorized.', 'genform'));
}

global $wpdb;
$form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
$table   = "{$wpdb->prefix}genform_entries";
$f_table = "{$wpdb->prefix}genform_forms";

// Fetch entries
if ($form_id) {
	if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'genform_view_entries')) {
		wp_die(__('Security check failed.', 'genform'));
	}
	$entries = $wpdb->get_results($wpdb->prepare("SELECT e.*, f.form_name FROM $table e JOIN $f_table f ON e.form_id = f.id WHERE e.form_id = %d ORDER BY e.created_at DESC", $form_id));
} else {
	$entries = $wpdb->get_results("SELECT e.*, f.form_name FROM $table e LEFT JOIN $f_table f ON e.form_id = f.id ORDER BY e.created_at DESC LIMIT 100");
}
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php _e('Form Entries', 'genform'); ?></h1>
		<?php if ($form_id): ?>
			<a href="<?php echo admin_url('admin.php?page=genform-entries'); ?>" class="gfm-btn gfm-btn-outline"><?php _e('View All Entries', 'genform'); ?></a>
		<?php endif; ?>
	</div>

	<div class="gfm-card">
		<?php if (empty($entries)): ?>
			<div class="gfm-empty-state">
				<span class="dashicons dashicons-database"></span>
				<p><?php _e('No entries found yet.', 'genform'); ?></p>
			</div>
		<?php else: ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th width="80">ID</th>
						<th>Form Name</th>
						<th>Submission Highlights</th>
						<th>IP Address</th>
						<th>Date</th>
						<th width="120">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $entry):
						$data = json_decode($entry->entry_data, true);
						$highlight = !empty($data) ? implode(', ', array_slice(array_values($data), 0, 2)) : '-';
					?>
						<tr id="gfm-entry-<?php echo $entry->id; ?>">
							<td>#<?php echo $entry->id; ?></td>
							<td><strong><?php echo esc_html($entry->form_name ?: __('Deleted Form', 'genform')); ?></strong></td>
							<td><span class="gfm-highlights"><?php echo esc_html(wp_trim_words($highlight, 10)); ?></span></td>
							<td><code><?php echo esc_html($entry->user_ip); ?></code></td>
							<td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($entry->created_at))); ?></td>
							<td>
								<button type="button" class="button gfm-view-entry" data-id="<?php echo $entry->id; ?>" data-payload='<?php echo esc_attr($entry->entry_data); ?>'><?php _e('View', 'genform'); ?></button>
								<button type="button" class="button button-link-delete gfm-delete-entry" data-id="<?php echo $entry->id; ?>"><?php _e('Delete', 'genform'); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

<!-- Tiny Modal for Viewing Entry -->
<div id="gfm-entry-modal" class="gfm-modal" style="display:none;">
	<div class="gfm-modal-content">
		<div class="gfm-modal-header">
			<h3><?php _e('Entry Details', 'genform'); ?></h3>
			<span class="gfm-close-modal">&times;</span>
		</div>
		<div class="gfm-modal-body" id="gfm-modal-body"></div>
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
		$('.gfm-view-entry').on('click', function() {
			const data = $(this).data('payload');
			let html = '<table class="widefat striped">';
			for (const [key, value] of Object.entries(data)) {
				html += `<tr><th>${key.replace(/_/g, ' ').toUpperCase()}</th><td>${value}</td></tr>`;
			}
			html += '</table>';
			$('#gfm-modal-body').html(html);
			$('#gfm-entry-modal').fadeIn();
		});

		$('.gfm-close-modal').on('click', () => $('#gfm-entry-modal').fadeOut());

		$('.gfm-delete-entry').on('click', function() {
			if (!confirm('Are you sure you want to delete this entry?')) return;
			const id = $(this).data('id');
			$.post(ajaxurl, {
				action: 'genform_delete_entry',
				entry_id: id,
				nonce: '<?php echo wp_create_nonce('genform_admin_nonce'); ?>'
			}, (res) => {
				if (res.success) $(`#gfm-entry-${id}`).fadeOut();
			});
		});
	});
</script>

<style>
	.gfm-modal {
		position: fixed;
		z-index: 99999;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.5);
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.gfm-modal-content {
		background: #fff;
		width: 600px;
		max-width: 90%;
		border-radius: 12px;
		box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
	}

	.gfm-modal-header {
		padding: 20px;
		border-bottom: 1px solid #eee;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.gfm-modal-body {
		padding: 20px;
		max-height: 70vh;
		overflow-y: auto;
	}

	.gfm-close-modal {
		cursor: pointer;
		font-size: 24px;
		opacity: 0.5;
	}

	.gfm-close-modal:hover {
		opacity: 1;
	}

	.gfm-highlights {
		color: #666;
		font-style: italic;
	}

	.gfm-header-flex {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20px;
	}
</style>