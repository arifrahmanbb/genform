<?php
/**
 * Admin View: Form Preview
 *
 * Renders the form in an isolated preview shell that does NOT inherit
 * .genform-admin-wrap, so no admin.css rules can override frontend styling.
 * Asset loading: this page loads ONLY frontend.css + preview.css (see Core::enqueueAdminAssets).
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$genform_preview_id = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;

if ( ! $genform_preview_id ) {
	wp_die( esc_html__( 'No form ID provided.', 'genform' ) );
}

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$genform_preview_form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $genform_preview_id ) );

if ( ! $genform_preview_form ) {
	wp_die( esc_html__( 'Form not found.', 'genform' ) );
}
?>

<div class="wrap">
	<div class="gfm-preview">

		<header class="gfm-preview__header">
			<h1 class="gfm-preview__title">
				<?php
				/* translators: %s: Form name */
				printf( esc_html__( 'Preview: %s', 'genform' ), esc_html( $genform_preview_form->form_name ) );
				?>
			</h1>
			<div class="gfm-preview__actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform' ) ); ?>" class="gfm-preview__btn gfm-preview__btn--outline">
					<span class="dashicons dashicons-arrow-left-alt2"></span>
					<?php esc_html_e( 'Back to All Forms', 'genform' ); ?>
				</a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-builder&action=edit&form_id=' . $genform_preview_id ), 'genform_edit_form' ) ); ?>" class="gfm-preview__btn gfm-preview__btn--primary">
					<span class="dashicons dashicons-edit"></span>
					<?php esc_html_e( 'Edit Form', 'genform' ); ?>
				</a>
			</div>
		</header>

		<div class="gfm-preview__body">
			<div class="gfm-preview__badge">
				<span class="dashicons dashicons-visibility"></span>
				<?php esc_html_e( 'Preview Mode — Submissions are disabled', 'genform' ); ?>
			</div>

			<div class="gfm-preview__frame">
				<?php echo do_shortcode( '[genform id="' . (int) $genform_preview_id . '"]' ); ?>
			</div>
		</div>

	</div>
</div>
