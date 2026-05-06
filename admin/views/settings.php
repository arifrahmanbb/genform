<?php
/**
 * Admin View: General Settings (Tabbed)
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
}

use GenForm\Admin\Settings as GenFormSettings;
use GenForm\Pro\FeatureGate;

global $wp_settings_sections, $wp_settings_fields;

$gfm_tabs       = GenFormSettings::tabs();
$gfm_section_to = GenFormSettings::sectionTabMap();
$gfm_all_secs   = $wp_settings_sections['genform_settings'] ?? array();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$gfm_active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
if ( ! isset( $gfm_tabs[ $gfm_active_tab ] ) ) {
	$gfm_active_tab = 'general';
}
?>

<div class="genform-admin-wrap gfm-settings-page">
	<div class="gfm-settings-header">
		<div>
			<h1><?php esc_html_e( 'GenForm Settings', 'genform' ); ?></h1>
			<p class="gfm-page-subtitle"><?php esc_html_e( 'Configure global plugin behavior. Per-form settings live inside each form\'s builder.', 'genform' ); ?></p>
		</div>
	</div>

	<?php settings_errors(); ?>

	<div class="gfm-settings-layout">

		<nav class="gfm-settings-nav" aria-label="<?php esc_attr_e( 'Settings sections', 'genform' ); ?>">
			<?php foreach ( $gfm_tabs as $gfm_tab_id => $gfm_tab ) : ?>
				<?php
				$gfm_tab_url = add_query_arg(
					array(
						'page' => 'genform-settings',
						'tab'  => $gfm_tab_id,
					),
					admin_url( 'admin.php' )
				);
				$gfm_is_active = ( $gfm_active_tab === $gfm_tab_id );
				?>
				<a
					href="<?php echo esc_url( $gfm_tab_url ); ?>"
					class="gfm-settings-nav-item <?php echo $gfm_is_active ? 'is-active' : ''; ?>"
					aria-current="<?php echo $gfm_is_active ? 'page' : 'false'; ?>"
				>
					<span class="dashicons dashicons-<?php echo esc_attr( $gfm_tab['icon'] ); ?>"></span>
					<span class="gfm-settings-nav-label">
						<strong><?php echo esc_html( $gfm_tab['label'] ); ?></strong>
						<small><?php echo esc_html( $gfm_tab['desc'] ); ?></small>
					</span>
				</a>
			<?php endforeach; ?>

			<?php
			/** Allow Pro to add tabs */
			do_action( 'genform_settings_nav', $gfm_active_tab );
			?>
		</nav>

		<div class="gfm-settings-content">
			<?php if ( 'tools' === $gfm_active_tab ) : ?>

				<div class="gfm-card gfm-tools-card">
					<h2><?php esc_html_e( 'Setup Wizard', 'genform' ); ?></h2>
					<p><?php esc_html_e( 'Guided walkthrough that creates a starter form with recommended fields and settings.', 'genform' ); ?></p>
					<button type="button" id="gfm-relaunch-wizard-btn" class="gfm-btn gfm-btn-primary">
						<span class="dashicons dashicons-redo"></span>
						<?php esc_html_e( 'Relaunch Setup Wizard', 'genform' ); ?>
					</button>
				</div>

				<div class="gfm-card gfm-tools-card">
					<h2><?php esc_html_e( 'Import a Form', 'genform' ); ?></h2>
					<p><?php esc_html_e( 'Upload a JSON file exported from another GenForm site to recreate the form here. Existing forms are never overwritten — imports always create new forms.', 'genform' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform' ) ); ?>" class="gfm-btn gfm-btn-outline">
						<span class="dashicons dashicons-upload"></span>
						<?php esc_html_e( 'Go to Forms List', 'genform' ); ?>
					</a>
				</div>

				<?php if ( ! FeatureGate::isProActive() ) : ?>
				<div class="gfm-card gfm-pro-settings-card">
					<h2>
						<span class="dashicons dashicons-superhero-alt"></span>
						<?php esc_html_e( 'Unlock Pro Features', 'genform' ); ?>
					</h2>
					<p><?php esc_html_e( 'Conditional logic, payments, file uploads, integrations, visual reports, and more.', 'genform' ); ?></p>
					<?php
					FeatureGate::previewGrid(
						array(
							'conditional_logic',
							'multi_step',
							'file_upload',
							'payments',
							'visual_reports',
							'signature',
							'integrations',
							'calculations',
						)
					);
					?>
				</div>
				<?php endif; ?>

			<?php else : ?>

				<form method="post" action="options.php" class="genform-settings-form">
					<?php settings_fields( 'genform_settings' ); ?>

					<?php
					$gfm_rendered_any = false;
					foreach ( $gfm_all_secs as $gfm_sec_id => $gfm_section ) {
						$gfm_sec_tab = $gfm_section_to[ $gfm_sec_id ] ?? null;
						if ( $gfm_sec_tab !== $gfm_active_tab ) {
							continue;
						}
						$gfm_rendered_any = true;
						?>
						<div class="gfm-card gfm-settings-section">
							<?php if ( $gfm_section['title'] ) : ?>
								<h2><?php echo esc_html( $gfm_section['title'] ); ?></h2>
							<?php endif; ?>
							<?php
							if ( $gfm_section['callback'] ) {
								call_user_func( $gfm_section['callback'], $gfm_section );
							}
							if ( ! empty( $wp_settings_fields['genform_settings'][ $gfm_sec_id ] ) ) {
								echo '<table class="form-table" role="presentation"><tbody>';
								do_settings_fields( 'genform_settings', $gfm_sec_id );
								echo '</tbody></table>';
							}
							?>
						</div>
						<?php
					}

					/** Pro can render its own tab content */
					do_action( 'genform_settings_tab_' . $gfm_active_tab );

					if ( $gfm_rendered_any ) {
						submit_button( __( 'Save Changes', 'genform' ), 'primary gfm-settings-submit' );
					}
					?>
				</form>

			<?php endif; ?>
		</div>
	</div>
</div>
