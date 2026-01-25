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

$t   = new EntriesTable();
$t->prepare_items();
$fid = absint( $_GET['form_id'] ?? 0 );
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php esc_html_e( 'Form Entries', 'genform' ); ?></h1>
		<div class="gfm-actions">
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-entries&action=genform_export' . ( $fid ? "&form_id=$fid" : '' ) ), 'genform_export_entries' ) ); ?>" class="gfm-btn gfm-btn-primary gfm-btn-secondary-style">
				<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Export to CSV', 'genform' ); ?>
			</a>
			<?php if ( $fid ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-entries' ) ); ?>" class="gfm-btn gfm-btn-outline">
					<?php esc_html_e( 'View All', 'genform' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php
	// Display entry action notifications.
	foreach ( array( 'deleted' => esc_html__( '%d deleted.', 'genform' ), 'trashed' => esc_html__( '%d trashed.', 'genform' ), 'restored' => esc_html__( '%d restored.', 'genform' ) ) as $k => $m ) {
		if ( isset( $_GET[ $k ] ) ) {
			echo "<div class='notice notice-success is-dismissible'><p>" . sprintf( esc_html( $m ), absint( $_GET[ $k ] ) ) . '</p></div>';
		}
	}
	?>

	<div class="gfm-card">
		<form method="get">
			<input type="hidden" name="page" value="genform-entries">
			<?php
			wp_nonce_field( 'bulk-entries' );
			$t->views();
			$t->search_box( esc_html__( 'Search', 'genform' ), 's' );
			$t->display();
			?>
		</form>
	</div>
</div>

<!-- Modal Structure for Row Preview -->
<div id="gfm-entry-modal" class="gfm-modal gfm-hidden">
	<div class="gfm-modal-content">
		<div class="gfm-modal-header">
			<h3><?php esc_html_e( 'Entry Details', 'genform' ); ?></h3>
			<span class="gfm-close-modal">&times;</span>
		</div>
		<div class="gfm-modal-body" id="gfm-modal-body"></div>
	</div>
</div>