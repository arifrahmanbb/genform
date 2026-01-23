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
				<button type="submit" name="genform_save" class="gfm-btn gfm-btn-primary gfm-btn-large">
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
				<h3><?php esc_html_e( 'Confirmation', 'genform' ); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e( 'Confirmation Type', 'genform' ); ?></label>
						<select id="gfm-con-type">
							<option value="message" <?php selected( $genform_settings['con_type'] ?? 'message', 'message' ); ?>><?php esc_html_e( 'Show Message', 'genform' ); ?></option>
							<option value="redirect" <?php selected( $genform_settings['con_type'] ?? 'message', 'redirect' ); ?>><?php esc_html_e( 'Redirect to URL', 'genform' ); ?></option>
						</select>
					</div>
					<div class="gfm-col gfm-con-field gfm-con-redirect" style="display:none;">
						<label><?php esc_html_e( 'Redirect URL', 'genform' ); ?></label>
						<input type="url" id="gfm-redirect-url" value="<?php echo esc_attr( $genform_settings['redirect_url'] ?? '' ); ?>" placeholder="https://yoursite.com/thank-you">
					</div>
				</div>

				<div class="gfm-grid gfm-con-field gfm-con-message">
					<div class="gfm-col">
						<label><?php esc_html_e( 'Success Message', 'genform' ); ?></label>
						<textarea id="gfm-success-message" rows="2" placeholder="<?php esc_attr_e( 'Thank you for your message!', 'genform' ); ?>"><?php echo esc_textarea( $genform_settings['success_message'] ?? '' ); ?></textarea>
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e( 'Error Message', 'genform' ); ?></label>
						<textarea id="gfm-error-message" rows="2" placeholder="<?php esc_attr_e( 'Something went wrong. Please try again.', 'genform' ); ?>"><?php echo esc_textarea( $genform_settings['error_message'] ?? '' ); ?></textarea>
					</div>
				</div>
			</div>

			<div class="gfm-card">
				<h3><?php esc_html_e( 'Submit Button', 'genform' ); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e( 'Button Text', 'genform' ); ?></label>
						<input type="text" id="gfm-submit-text" value="<?php echo esc_attr( $genform_settings['submit_text'] ?? esc_html__( 'Submit', 'genform' ) ); ?>">
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e( 'Button Alignment', 'genform' ); ?></label>
						<select id="gfm-submit-align">
							<option value="left" <?php selected( $genform_settings['submit_align'] ?? 'left', 'left' ); ?>><?php esc_html_e( 'Left', 'genform' ); ?></option>
							<option value="center" <?php selected( $genform_settings['submit_align'] ?? 'left', 'center' ); ?>><?php esc_html_e( 'Center', 'genform' ); ?></option>
							<option value="right" <?php selected( $genform_settings['submit_align'] ?? 'left', 'right' ); ?>><?php esc_html_e( 'Right', 'genform' ); ?></option>
							<option value="full" <?php selected( $genform_settings['submit_align'] ?? 'left', 'full' ); ?>><?php esc_html_e( 'Full Width', 'genform' ); ?></option>
						</select>
					</div>
				</div>
				<div class="gfm-setting-row" style="margin-top: 20px;">
					<label style="font-weight: 400;">
						<input type="checkbox" id="gfm-honeypot" <?php checked( $genform_settings['honeypot'] ?? true ); ?>> <?php esc_html_e( 'Enable Honeypot Anti-Spam Protection', 'genform' ); ?>
					</label>
				</div>
			</div>

			<div class="gfm-card">
				<h3><?php esc_html_e( 'Typography', 'genform' ); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e( 'Base Font Size (px)', 'genform' ); ?></label>
						<input type="number" id="gfm-base-font-size" value="<?php echo esc_attr( $genform_settings['base_font_size'] ?? '16' ); ?>" min="12" max="24">
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e( 'Font Weight', 'genform' ); ?></label>
						<select id="gfm-base-font-weight">
							<option value="300" <?php selected( $genform_settings['base_font_weight'] ?? '400', '300' ); ?>><?php esc_html_e( 'Light (300)', 'genform' ); ?></option>
							<option value="400" <?php selected( $genform_settings['base_font_weight'] ?? '400', '400' ); ?>><?php esc_html_e( 'Regular (400)', 'genform' ); ?></option>
							<option value="500" <?php selected( $genform_settings['base_font_weight'] ?? '400', '500' ); ?>><?php esc_html_e( 'Medium (500)', 'genform' ); ?></option>
							<option value="600" <?php selected( $genform_settings['base_font_weight'] ?? '400', '600' ); ?>><?php esc_html_e( 'Semi-Bold (600)', 'genform' ); ?></option>
							<option value="700" <?php selected( $genform_settings['base_font_weight'] ?? '400', '700' ); ?>><?php esc_html_e( 'Bold (700)', 'genform' ); ?></option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- Notifications Tab -->
		<div id="gfm-tab-notifications" class="gfm-tab-content" style="display:none;">
			<div class="gfm-card">
				<h3><?php esc_html_e( 'Email Notifications', 'genform' ); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e( 'Send To Email', 'genform' ); ?></label>
						<input type="text" id="gfm-admin-email" value="<?php echo esc_attr( $genform_settings['admin_email'] ?? '{admin_email}' ); ?>">
						<span class="gfm-setting-desc"><?php esc_html_e( 'Use {admin_email} for site admin email.', 'genform' ); ?></span>
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e( 'Reply-To Email', 'genform' ); ?></label>
						<input type="text" id="gfm-reply-to" value="<?php echo esc_attr( $genform_settings['reply_to'] ?? '{field_email}' ); ?>">
						<span class="gfm-setting-desc"><?php esc_html_e( 'Use {field_ID} to use a form field value.', 'genform' ); ?></span>
					</div>
				</div>

				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e( 'From Name', 'genform' ); ?></label>
						<input type="text" id="gfm-from-name" value="<?php echo esc_attr( $genform_settings['from_name'] ?? '' ); ?>" placeholder="{global_from_name}">
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e( 'From Email', 'genform' ); ?></label>
						<input type="text" id="gfm-from-email" value="<?php echo esc_attr( $genform_settings['from_email'] ?? '' ); ?>" placeholder="{global_from_email}">
					</div>
				</div>

				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Email Subject', 'genform' ); ?></label>
					<input type="text" id="gfm-email-subject" value="<?php echo esc_attr( $genform_settings['email_subject'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'New Submission: {form_name}', 'genform' ); ?>">
				</div>
				<div class="gfm-setting-row">
					<label><?php esc_html_e( 'Email Body', 'genform' ); ?></label>
					<textarea id="gfm-email-body" rows="8" placeholder="<?php esc_attr_e( "{all_fields}\n\nSent from {site_title}", 'genform' ); ?>"><?php echo esc_textarea( $genform_settings['email_body'] ?? '' ); ?></textarea>
					<span class="gfm-setting-desc"><?php esc_html_e( 'Use {all_fields} to include all form data.', 'genform' ); ?></span>
				</div>
			</div>
		</div>

		<input type="hidden" name="form_data" id="gfm-data-input" value="" />
		<input type="hidden" name="form_settings" id="gfm-settings-input" value="" />
	</form>
</div>