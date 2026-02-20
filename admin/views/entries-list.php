<?php
/**
 * Admin View: Submissions List
 *
 * Displays all form entries using the WP_List_Table component.
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
}

use GenForm\Admin\EntriesTable;

$genform_table_component = new EntriesTable();
$genform_table_component->prepare_items();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$genform_fid = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php esc_html_e( 'Form Entries', 'genform' ); ?></h1>
		<div class="gfm-actions">
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-entries&action=genform_export' . ( $genform_fid ? "&form_id=$genform_fid" : '' ) ), 'genform_export_entries' ) ); ?>" class="gfm-btn gfm-btn-primary gfm-btn-secondary-style">
				<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export to CSV', 'genform' ); ?>
			</a>
			<?php if ( $genform_fid ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-entries' ) ); ?>" class="gfm-btn gfm-btn-outline">
					<?php esc_html_e( 'View All', 'genform' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php
	// Display entry action notifications.
	$genform_msgs = array(
		/* translators: %d: number of items deleted */
		'deleted'  => esc_html__( '%d deleted.', 'genform' ),
		/* translators: %d: number of items trashed */
		'trashed'  => esc_html__( '%d trashed.', 'genform' ),
		/* translators: %d: number of items restored */
		'restored' => esc_html__( '%d restored.', 'genform' ),
	);
	foreach ( $genform_msgs as $genform_key => $genform_msg_raw ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ $genform_key ] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$genform_count = absint( wp_unslash( $_GET[ $genform_key ] ) );
			echo "<div class='notice notice-success is-dismissible'><p>" . sprintf( esc_html( $genform_msg_raw ), esc_html( $genform_count ) ) . '</p></div>';
		}
	}
	?>

	<div class="gfm-card">
		<form method="get">
			<input type="hidden" name="page" value="genform-entries">
			<?php
			wp_nonce_field( 'bulk-entries' );
			$genform_table_component->views();
			$genform_table_component->search_box( esc_html__( 'Search', 'genform' ), 's' );
			$genform_table_component->display();
			?>
		</form>
	</div>
</div>
