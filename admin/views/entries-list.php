<?php
/**
 * View for form entries list
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
}

global $wpdb;
$genform_form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

// Fetch entries.
if ( $genform_form_id ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'genform_view_entries' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'genform' ) );
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$genform_entries = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT e.*, f.form_name FROM {$wpdb->prefix}genform_entries e JOIN {$wpdb->prefix}genform_forms f ON e.form_id = f.id WHERE e.form_id = %d ORDER BY e.created_at DESC",
			$genform_form_id
		)
	);
} else {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$genform_entries = $wpdb->get_results(
		"SELECT e.*, f.form_name FROM {$wpdb->prefix}genform_entries e LEFT JOIN {$wpdb->prefix}genform_forms f ON e.form_id = f.id ORDER BY e.created_at DESC LIMIT 100"
	);
}
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php esc_html_e( 'Form Entries', 'genform' ); ?></h1>
		<?php if ( $genform_form_id ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-entries' ) ); ?>" class="gfm-btn gfm-btn-outline"><?php esc_html_e( 'View All Entries', 'genform' ); ?></a>
		<?php endif; ?>
	</div>

	<div class="gfm-card">
		<?php if ( empty( $genform_entries ) ) : ?>
			<div class="gfm-empty-state">
				<span class="dashicons dashicons-database"></span>
				<p><?php esc_html_e( 'No entries found yet.', 'genform' ); ?></p>
			</div>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th width="80"><?php esc_html_e( 'ID', 'genform' ); ?></th>
						<th><?php esc_html_e( 'Form Name', 'genform' ); ?></th>
						<th><?php esc_html_e( 'Submission Highlights', 'genform' ); ?></th>
						<th><?php esc_html_e( 'IP Address', 'genform' ); ?></th>
						<th><?php esc_html_e( 'Date', 'genform' ); ?></th>
						<th width="120"><?php esc_html_e( 'Actions', 'genform' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $genform_entries as $genform_entry ) :
						$genform_entry_data = json_decode( $genform_entry->entry_data, true );
						$genform_highlight  = ! empty( $genform_entry_data ) ? implode( ', ', array_slice( array_values( $genform_entry_data ), 0, 2 ) ) : '-';
						?>
						<tr id="gfm-entry-<?php echo esc_attr( $genform_entry->id ); ?>">
							<td>#<?php echo esc_html( $genform_entry->id ); ?></td>
							<td><strong><?php echo esc_html( $genform_entry->form_name ?: esc_html__( 'Deleted Form', 'genform' ) ); ?></strong></td>
							<td><span class="gfm-highlights"><?php echo esc_html( wp_trim_words( $genform_highlight, 10 ) ); ?></span></td>
							<td><code><?php echo esc_html( $genform_entry->user_ip ); ?></code></td>
							<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $genform_entry->created_at ) ) ); ?></td>
							<td>
								<button type="button" class="button gfm-view-entry" data-id="<?php echo esc_attr( $genform_entry->id ); ?>" data-payload='<?php echo esc_attr( $genform_entry->entry_data ); ?>'><?php esc_html_e( 'View', 'genform' ); ?></button>
								<button type="button" class="button button-link-delete gfm-delete-entry" data-id="<?php echo esc_attr( $genform_entry->id ); ?>"><?php esc_html_e( 'Delete', 'genform' ); ?></button>
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
			<h3><?php esc_html_e( 'Entry Details', 'genform' ); ?></h3>
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
				nonce: '<?php echo esc_js( wp_create_nonce( 'genform_admin_nonce' ) ); ?>'
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