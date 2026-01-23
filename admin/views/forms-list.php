<?php
/**
 * View for all forms list
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

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$genform_forms = $wpdb->get_results(
	"SELECT f.*, (SELECT COUNT(*) FROM {$wpdb->prefix}genform_entries WHERE form_id = f.id) as entries_count FROM {$wpdb->prefix}genform_forms f ORDER BY f.created_at DESC"
);
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php esc_html_e( 'All Forms', 'genform' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-builder' ) ); ?>" class="gfm-btn gfm-btn-primary"><?php esc_html_e( 'Add New Form', 'genform' ); ?></a>
	</div>

	<div class="gfm-card">
		<?php if ( empty( $genform_forms ) ) : ?>
			<div class="gfm-empty-state">
				<span class="dashicons dashicons-forms"></span>
				<p><?php esc_html_e( 'You haven\'t created any forms yet.', 'genform' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-builder' ) ); ?>" class="gfm-btn gfm-btn-outline"><?php esc_html_e( 'Create Your First Form', 'genform' ); ?></a>
			</div>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Form Name', 'genform' ); ?></th>
						<th width="250"><?php esc_html_e( 'Shortcode', 'genform' ); ?></th>
						<th width="100"><?php esc_html_e( 'Entries', 'genform' ); ?></th>
						<th><?php esc_html_e( 'Status', 'genform' ); ?></th>
						<th><?php esc_html_e( 'Created', 'genform' ); ?></th>
						<th width="150"><?php esc_html_e( 'Actions', 'genform' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $genform_forms as $genform_form ) : ?>
						<tr>
							<td>
								<strong><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-builder&action=edit&form_id=' . $genform_form->id ), 'genform_edit_form' ) ); ?>"><?php echo esc_html( $genform_form->form_name ); ?></a></strong>
							</td>
							<td>
								<div class="gfm-shortcode-copy">
									<code>[genform id="<?php echo esc_html( $genform_form->id ); ?>"]</code>
									<button class="gfm-copy-btn dashicons dashicons-admin-page" data-code='[genform id="<?php echo esc_attr( $genform_form->id ); ?>"]'></button>
								</div>
							</td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-entries&form_id=' . $genform_form->id ), 'genform_view_entries' ) ); ?>" class="gfm-count-badge">
									<?php echo esc_html( $genform_form->entries_count ); ?>
								</a>
							</td>
							<td>
								<span class="gfm-status gfm-status-<?php echo esc_attr( $genform_form->status ); ?>">
									<?php echo esc_html( ucfirst( $genform_form->status ) ); ?>
								</span>
							</td>
							<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $genform_form->created_at ) ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-builder&action=edit&form_id=' . $genform_form->id ), 'genform_edit_form' ) ); ?>" class="button"><?php esc_html_e( 'Edit', 'genform' ); ?></a>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform&action=delete&form_id=' . $genform_form->id ), 'genform_delete_form' ) ); ?>" class="button button-link-delete" onclick="return confirm('Really delete this form and all its entries?')"><?php esc_html_e( 'Delete', 'genform' ); ?></a>
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