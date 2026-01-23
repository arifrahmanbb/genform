<?php
/**
 * View for form builder
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$genform_id = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$genform_form     = $genform_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $genform_id ) ) : null;
$genform_settings = $genform_form ? json_decode( $genform_form->form_settings, true ) : array();
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-builder-header-main">
		<h1><?php echo $genform_id ? esc_html__( 'Edit Form', 'genform' ) : esc_html__( 'Create New Form', 'genform' ); ?></h1>
		<div class="gfm-builder-tabs">
			<button type="button" class="gfm-tab-link active" data-tab="fields"><?php esc_html_e( 'Fields', 'genform' ); ?></button>
			<button type="button" class="gfm-tab-link" data-tab="settings"><?php esc_html_e( 'Settings', 'genform' ); ?></button>
			<button type="button" class="gfm-tab-link" data-tab="notifications"><?php esc_html_e( 'Notifications', 'genform' ); ?></button>
		</div>
	</div>

	<form method="post" id="gfm-builder-form">
		<?php wp_nonce_field( 'genform_save_form', 'genform_builder_nonce' ); ?>

		<div class="gfm-builder-top">
			<div class="gfm-card gfm-name-card">
				<label for="form_name"><?php esc_html_e( 'Form Name', 'genform' ); ?></label>
				<input type="text" name="form_name" id="form_name" value="<?php echo esc_attr( $genform_form->form_name ?? '' ); ?>" required class="widefat" placeholder="<?php esc_attr_e( 'e.g. Contact Us', 'genform' ); ?>" />
			</div>
			<div class="gfm-save-area">
				<button type="submit" name="genform_save" class="gfm-btn gfm-btn-large">
					<?php esc_html_e( 'Save Form', 'genform' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform' ) ); ?>" class="gfm-btn gfm-btn-outline"><?php esc_html_e( 'Cancel', 'genform' ); ?></a>
			</div>
		</div>

		<!-- Fields Tab -->
		<div id="gfm-tab-fields" class="gfm-tab-content">
			<div class="gfm-builder-layout">
				<div class="gfm-sidebar">
					<div class="gfm-card">
						<h3><?php esc_html_e( 'Add Fields', 'genform' ); ?></h3>
						<div class="gfm-field-buttons">
							<?php
							$genform_fields = array(
								'text'     => array(
									'label' => esc_html__( 'Single Line Text', 'genform' ),
									'icon'  => 'edit',
								),
								'email'    => array(
									'label' => esc_html__( 'Email', 'genform' ),
									'icon'  => 'email',
								),
								'textarea' => array(
									'label' => esc_html__( 'Paragraph Text', 'genform' ),
									'icon'  => 'text',
								),
								'number'   => array(
									'label' => esc_html__( 'Number', 'genform' ),
									'icon'  => 'calculator',
								),
								'select'   => array(
									'label' => esc_html__( 'Dropdown', 'genform' ),
									'icon'  => 'menu-alt',
								),
								'radio'    => array(
									'label' => esc_html__( 'Multiple Choice', 'genform' ),
									'icon'  => 'marker',
								),
								'checkbox' => array(
									'label' => esc_html__( 'Checkboxes', 'genform' ),
									'icon'  => 'yes',
								),
								'date'     => array(
									'label' => esc_html__( 'Date', 'genform' ),
									'icon'  => 'calendar-alt',
								),
								'url'      => array(
									'label' => esc_html__( 'Website', 'genform' ),
									'icon'  => 'admin-links',
								),
								'tel'      => array(
									'label' => esc_html__( 'Phone', 'genform' ),
									'icon'  => 'phone',
								),
							);
							foreach ( $genform_fields as $genform_type => $genform_meta ) :
								?>
								<button type="button" class="gfm-add-field" data-type="<?php echo esc_attr( $genform_type ); ?>">
									<span class="dashicons dashicons-<?php echo esc_attr( $genform_meta['icon'] ); ?>"></span>
									<?php echo esc_html( $genform_meta['label'] ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="gfm-canvas">
					<div id="gfm-fields-container">
						<!-- Fields populated via JS -->
						<div class="gfm-empty-canvas">
							<span class="dashicons dashicons-plus-alt"></span>
							<p><?php esc_html_e( 'Click a field on the left to add it to your form.', 'genform' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Settings Tab -->
		<div id="gfm-tab-settings" class="gfm-tab-content" style="display:none;">
			<div class="gfm-card">
				<h3><?php esc_html_e( 'Form Settings', 'genform' ); ?></h3>
				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Submit Button Text', 'genform' ); ?></label>
					<input type="text" id="gfm-submit-text" class="widefat" value="<?php echo esc_attr( $genform_settings['submit_text'] ?? esc_html__( 'Submit', 'genform' ) ); ?>">
				</div>
				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Success Message', 'genform' ); ?></label>
					<textarea id="gfm-success-message" class="widefat"><?php echo esc_textarea( $genform_settings['success_message'] ?? esc_html__( 'Thank you for your message. We will get back to you soon.', 'genform' ) ); ?></textarea>
				</div>
				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Redirect URL (Optional)', 'genform' ); ?></label>
					<input type="url" id="gfm-redirect-url" class="widefat" value="<?php echo esc_attr( $genform_settings['redirect_url'] ?? '' ); ?>" placeholder="https://yoursite.com/thank-you">
				</div>
			</div>
		</div>

		<!-- Notifications Tab -->
		<div id="gfm-tab-notifications" class="gfm-tab-content" style="display:none;">
			<div class="gfm-card">
				<h3><?php esc_html_e( 'Email Notifications', 'genform' ); ?></h3>
				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Send To Email', 'genform' ); ?></label>
					<input type="text" id="gfm-admin-email" class="widefat" value="<?php echo esc_attr( $genform_settings['admin_email'] ?? '{admin_email}' ); ?>">
					<p class="description"><?php esc_html_e( 'You can use {admin_email} for the site admin.', 'genform' ); ?></p>
				</div>
				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Email Subject', 'genform' ); ?></label>
					<input type="text" id="gfm-email-subject" class="widefat" value="<?php echo esc_attr( $genform_settings['email_subject'] ?? esc_html__( 'New Submission: {form_name}', 'genform' ) ); ?>">
				</div>
				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Email Body', 'genform' ); ?></label>
					<textarea id="gfm-email-body" class="widefat" rows="10"><?php echo esc_textarea( $genform_settings['email_body'] ?? esc_html__( "{all_fields}\n\nSent from {site_title}", 'genform' ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Use {all_fields} to include all form data.', 'genform' ); ?></p>
				</div>
			</div>
		</div>

		<input type="hidden" name="form_data" id="gfm-data-input" value="" />
		<input type="hidden" name="form_settings" id="gfm-settings-input" value="" />
	</form>
</div>