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

use GenForm\Admin\EntriesTable;

$table = new EntriesTable();
$table->prepare_items();

$genform_form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-header-flex">
		<h1><?php esc_html_e( 'Form Entries', 'genform' ); ?></h1>
		<div class="gfm-actions">
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-entries&action=genform_export' . ( $genform_form_id ? '&form_id=' . $genform_form_id : '' ) ), 'genform_export_entries' ) ); ?>" class="gfm-btn gfm-btn-primary gfm-btn-secondary-style">
				<span class="dashicons dashicons-download" style="margin-right: 5px;"></span>
				<?php esc_html_e( 'Export to CSV', 'genform' ); ?>
			</a>
			<?php if ( $genform_form_id ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-entries' ) ); ?>" class="gfm-btn gfm-btn-outline"><?php esc_html_e( 'View All Entries', 'genform' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( isset( $_GET['deleted'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo sprintf( esc_html__( '%d entries deleted.', 'genform' ), absint( $_GET['deleted'] ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['trashed'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo sprintf( esc_html__( '%d entries moved to Trash.', 'genform' ), absint( $_GET['trashed'] ) ); ?> <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-entries&action=restore&entry=' . ( isset( $_GET['entry'] ) ? implode( ',', array_map( 'absint', (array) $_GET['entry'] ) ) : '' ) ), 'bulk-entries' ) ); ?>"><?php esc_html_e( 'Undo', 'genform' ); ?></a></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['restored'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo sprintf( esc_html__( '%d entries restored from Trash.', 'genform' ), absint( $_GET['restored'] ) ); ?></p>
		</div>
	<?php endif; ?>

	<div class="gfm-card">
		<form id="genform-entries-filter" method="get">
			<input type="hidden" name="page" value="genform-entries" />
			<?php wp_nonce_field( 'bulk-entries' ); ?>
			<?php $table->views(); ?>
			<?php $table->search_box( esc_html__( 'Search Entries', 'genform' ), 'search-id' ); ?>
			<?php $table->display(); ?>
		</form>
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