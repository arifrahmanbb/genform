<?php
/**
 * Admin View: Form Builder Interface
 *
 * Provides the interactive UI for designing form fields and configuring settings.
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$id = absint( $_GET['form_id'] ?? 0 );
$f  = $id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id ) ) : null;
$s  = $f ? json_decode( $f->form_settings, true ) : array();
?>

<div class="wrap genform-admin-wrap">
	<div class="gfm-builder-header-main">
		<h1><?php echo $id ? esc_html__( 'Edit Form', 'genform' ) : esc_html__( 'Create Form', 'genform' ); ?></h1>
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
				<label><?php esc_html_e( 'Form Title', 'genform' ); ?></label>
				<input type="text" name="form_name" value="<?php echo esc_attr( $f->form_name ?? '' ); ?>" required placeholder="<?php esc_attr_e( 'e.g. Contact Us', 'genform' ); ?>">
			</div>
			<div class="gfm-save-area">
				<button type="submit" name="genform_save" class="gfm-btn gfm-btn-primary gfm-btn-large">
					<?php esc_html_e( 'Save Form', 'genform' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform' ) ); ?>" class="gfm-btn gfm-btn-outline">
					<?php esc_html_e( 'Cancel', 'genform' ); ?>
				</a>
			</div>
		</div>

		<!-- Fields Configuration Tab -->
		<div id="gfm-tab-fields" class="gfm-tab-content">
			<div class="gfm-builder-layout">
				<div class="gfm-sidebar">
					<div class="gfm-card">
						<h3><?php esc_html_e( 'Available Fields', 'genform' ); ?></h3>
						<div class="gfm-field-buttons">
							<?php
							$fields = array(
								'text'     => 'edit',
								'email'    => 'email',
								'textarea' => 'text',
								'number'   => 'calculator',
								'select'   => 'menu-alt',
								'radio'    => 'marker',
								'checkbox' => 'yes',
								'date'     => 'calendar-alt',
								'url'      => 'admin-links',
								'tel'      => 'phone',
							);
							foreach ( $fields as $t => $i ) :
								?>
								<button type="button" class="gfm-add-field" data-type="<?php echo esc_attr( $t ); ?>">
									<span class="dashicons dashicons-<?php echo esc_attr( $i ); ?>"></span>
									<?php echo esc_html( ucfirst( $t ) ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="gfm-canvas">
					<div id="gfm-fields-container">
						<div class="gfm-empty-canvas">
							<span class="dashicons dashicons-plus-alt"></span>
							<p><?php esc_html_e( 'Add fields here.', 'genform' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Global Settings Tab -->
		<div id="gfm-tab-settings" class="gfm-tab-content gfm-hidden">
			<div class="gfm-card">
				<h3><?php esc_html_e( 'Confirmation', 'genform' ); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e( 'Type', 'genform' ); ?></label>
						<select id="gfm-con-type">
							<option value="message" <?php selected( $s['con_type'] ?? '', 'message' ); ?>><?php esc_html_e( 'Message', 'genform' ); ?></option>
							<option value="redirect" <?php selected( $s['con_type'] ?? '', 'redirect' ); ?>><?php esc_html_e( 'Redirect', 'genform' ); ?></option>
						</select>
					</div>
					<div class="gfm-col gfm-con-field gfm-con-redirect gfm-hidden">
						<label><?php esc_html_e( 'URL', 'genform' ); ?></label>
						<input type="url" id="gfm-redirect-url" value="<?php echo esc_url( $s['redirect_url'] ?? '' ); ?>">
					</div>
				</div>
				<div class="gfm-grid gfm-con-field gfm-con-message">
					<div class="gfm-col">
						<label><?php esc_html_e( 'Success', 'genform' ); ?></label>
						<textarea id="gfm-success-message"><?php echo esc_textarea( $s['success_message'] ?? '' ); ?></textarea>
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e( 'Error', 'genform' ); ?></label>
						<textarea id="gfm-error-message"><?php echo esc_textarea( $s['error_message'] ?? '' ); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<!-- Email Notifications Tab -->
		<div id="gfm-tab-notifications" class="gfm-tab-content gfm-hidden">
			<div class="gfm-card">
				<h3><?php esc_html_e( 'Email', 'genform' ); ?></h3>
				<div class="gfm-grid">
					<div class="gfm-col">
						<label><?php esc_html_e( 'To', 'genform' ); ?></label>
						<input type="text" id="gfm-admin-email" value="<?php echo esc_attr( $s['admin_email'] ?? '{admin_email}' ); ?>">
					</div>
					<div class="gfm-col">
						<label><?php esc_html_e( 'From Name', 'genform' ); ?></label>
						<input type="text" id="gfm-from-name" value="<?php echo esc_attr( $s['from_name'] ?? '' ); ?>">
					</div>
				</div>
			</div>
		</div>

		<!-- Hidden data stores used by the builder script -->
		<input type="hidden" name="form_data" id="gfm-data-input">
		<input type="hidden" name="form_settings" id="gfm-settings-input">
	</form>
</div>