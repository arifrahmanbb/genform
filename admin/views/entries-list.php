<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
	wp_die(__('Unauthorized.', 'genform'));
}

global $wpdb;
$id      = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
$table   = "{$wpdb->prefix}genform_entries";
$f_table = "{$wpdb->prefix}genform_forms";

if ($id) {
	if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'genform_view_entries')) {
		wp_die(__('Security check failed.', 'genform'));
	}
	$entries = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE form_id = %d ORDER BY created_at DESC", $id));
	$form    = $wpdb->get_row($wpdb->prepare("SELECT name FROM $f_table WHERE id = %d", $id));
} else {
	$entries = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100");
	$form    = null;
}
?>

<div class="wrap genform-admin-wrap">
	<h1><?php _e('Form Entries', 'genform'); ?></h1>
	<?php if ($form): ?>
		<p><?php printf(__('Showing entries for: <strong>%s</strong>', 'genform'), esc_html($form->name)); ?></p>
	<?php endif; ?>

	<div class="gfm-card">
		<?php if (empty($entries)): ?>
			<p><?php _e('No entries found.', 'genform'); ?></p>
		<?php else: ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>Form</th>
						<th>Data</th>
						<th>IP</th>
						<th>Date</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($entries as $entry):
						$data = json_decode($entry->entry_data, true);
						$f = $wpdb->get_var($wpdb->prepare("SELECT name FROM $f_table WHERE id = %d", $entry->form_id));
					?>
						<tr>
							<td><?php echo $entry->id; ?></td>
							<td><?php echo esc_html($f ?: __('ID: ', 'genform') . $entry->form_id); ?></td>
							<td>
								<details>
									<summary><?php _e('View Details', 'genform'); ?></summary>
									<div class="gfm-entry-details">
										<?php foreach ($data as $k => $v): ?>
											<div><strong><?php echo esc_html(ucfirst($k)); ?>:</strong> <?php echo esc_html($v); ?></div>
										<?php endforeach; ?>
									</div>
								</details>
							</td>
							<td><?php echo esc_html($entry->ip); ?></td>
							<td><?php echo esc_html($entry->created_at); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>