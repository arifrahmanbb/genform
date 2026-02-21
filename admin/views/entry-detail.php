<?php

/**
 * Admin View: Entry Detail Page
 *
 * Displays a full-page view of a single form entry with all submitted data and metadata.
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
}

$genform_entry_nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
if ( ! wp_verify_nonce( $genform_entry_nonce, 'genform_view_entry' ) ) {
	wp_die( esc_html__( 'Security check failed.', 'genform' ) );
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$genform_entry_id = isset( $_GET['entry_id'] ) ? absint( wp_unslash( $_GET['entry_id'] ) ) : 0;

if ( ! $genform_entry_id ) {
	wp_die( esc_html__( 'No entry ID provided.', 'genform' ) );
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$genform_entry = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT e.*, f.form_name, f.form_data FROM {$wpdb->prefix}genform_entries e LEFT JOIN {$wpdb->prefix}genform_forms f ON e.form_id = f.id WHERE e.id = %d",
		$genform_entry_id
	)
);

if ( ! $genform_entry ) {
	wp_die( esc_html__( 'Entry not found.', 'genform' ) );
}

// Mark as read if currently unread.
if ( 'unread' === $genform_entry->status ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->update( "{$wpdb->prefix}genform_entries", array( 'status' => 'read' ), array( 'id' => $genform_entry_id ) );
}

$genform_entry_data     = json_decode( $genform_entry->entry_data, true ) ?: array();
$genform_entry_metadata = json_decode( $genform_entry->entry_metadata, true ) ?: array();
$genform_form_config    = json_decode( $genform_entry->form_data ?? '{}', true );

// Resolve field labels from form config.
$genform_resolved = array();
if ( isset( $genform_form_config['fields'] ) && is_array( $genform_form_config['fields'] ) ) {
	foreach ( $genform_form_config['fields'] as $genform_field ) {
		$genform_field_name = sanitize_title( $genform_field['name'] ?? '' );
		if ( $genform_field_name && isset( $genform_field['label'], $genform_entry_data[ $genform_field_name ] ) ) {
			$genform_resolved[ $genform_field['label'] ] = $genform_entry_data[ $genform_field_name ];
		}
	}
}

// Fallback: use raw keys if no form config available.
if ( empty( $genform_resolved ) ) {
	foreach ( $genform_entry_data as $genform_key => $genform_value ) {
		$genform_resolved[ ucwords( str_replace( array( '_', '-' ), ' ', $genform_key ) ) ] = $genform_value;
	}
}
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1>
			<?php
			/* translators: %d: Entry ID */
			printf( esc_html__( 'Entry #%d', 'genform' ), (int) $genform_entry_id );
			?>
		</h1>
		<div class="gfm-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-entries' ) ); ?>" class="gfm-btn gfm-btn-outline">
				<?php esc_html_e( 'Back to Entries', 'genform' ); ?>
			</a>
		</div>
	</div>

	<div class="gfm-entry-detail-layout" >
		<!-- Main Content -->
		<div class="gfm-card">
			<h3><?php esc_html_e( 'Submission Data', 'genform' ); ?></h3>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 30%;"><?php esc_html_e( 'Field', 'genform' ); ?></th>
						<th><?php esc_html_e( 'Value', 'genform' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $genform_resolved as $genform_label => $genform_val ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $genform_label ); ?></strong></td>
							<td>
								<?php
								if ( is_array( $genform_val ) ) {
									echo esc_html( implode( ', ', $genform_val ) );
								} else {
									echo nl2br( esc_html( (string) $genform_val ) );
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<!-- Sidebar -->
		<div>
			<div class="gfm-card gfm-entry-sidebar-card">
				<h3><?php esc_html_e( 'Entry Info', 'genform' ); ?></h3>
				<div class="gfm-entry-meta">
					<p>
						<strong><?php esc_html_e( 'Form:', 'genform' ); ?></strong><br>
						<?php echo esc_html( $genform_entry->form_name ?: esc_html__( 'Deleted Form', 'genform' ) ); ?>
					</p>
					<p>
						<strong><?php esc_html_e( 'Date:', 'genform' ); ?></strong><br>
						<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $genform_entry->created_at ) ) ); ?>
					</p>
					<p>
						<strong><?php esc_html_e( 'Status:', 'genform' ); ?></strong><br>
						<span class="gfm-status gfm-status-<?php echo esc_attr( $genform_entry->status ); ?>">
							<?php echo esc_html( ucfirst( $genform_entry->status ) ); ?>
						</span>
					</p>
				</div>
			</div>

			<div class="gfm-card">
				<h3><?php esc_html_e( 'Metadata', 'genform' ); ?></h3>
				<div class="gfm-entry-meta">
					<p>
						<strong><?php esc_html_e( 'IP Address:', 'genform' ); ?></strong><br>
						<?php echo esc_html( $genform_entry->user_ip ); ?>
					</p>
					<?php if ( ! empty( $genform_entry_metadata['browser'] ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Browser:', 'genform' ); ?></strong><br>
							<?php echo esc_html( $genform_entry_metadata['browser'] ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $genform_entry_metadata['os'] ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'OS:', 'genform' ); ?></strong><br>
							<?php echo esc_html( $genform_entry_metadata['os'] ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $genform_entry_metadata['url'] ) ) : ?>
						<p>
							<strong><?php esc_html_e( 'Source Page:', 'genform' ); ?></strong><br>
							<a href="<?php echo esc_url( $genform_entry_metadata['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $genform_entry_metadata['url'] ); ?>
							</a>
						</p>
					<?php endif; ?>
				</div>
			</div>

			<?php
			/**
			 * Fires in the entry detail sidebar.
			 * Pro plugin uses this for file downloads, payment receipts, etc.
			 *
			 * @param object $genform_entry The entry object.
			 */
			do_action( 'genform_entry_detail_sidebar', $genform_entry );
			?>
		</div>
	</div>
</div>