<?php
/**
 * Admin View: General Settings
 *
 * Provides a clean wrapper for the WordPress Settings API implementation.
 * Includes Pro feature preview grid for upselling.
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
}

use GenForm\Pro\FeatureGate;
?>

<div class="genform-admin-wrap">
	<div class="gfm-builder-header-main">
		<h1><?php esc_html_e( 'GenForm Settings', 'genform' ); ?></h1>
	</div>

	<div class="gfm-card">
		<form method="post" action="options.php">
			<?php
			settings_fields( 'genform_settings' );
			do_settings_sections( 'genform_settings' );

			/**
			 * Fires after the settings sections for Pro to insert its own fields.
			 */
			do_action( 'genform_settings_sections' );

			submit_button();
			?>
		</form>
	</div>

	<?php if ( ! FeatureGate::isProActive() ) : ?>
	<div class="gfm-card gfm-pro-settings-card">
		<div style="padding: 20px 24px 0;">
			<h2 style="margin: 0 0 4px; font-size: 18px; font-weight: 800; color: #1e293b;">
				<span class="dashicons dashicons-superhero-alt" style="color: #6366f1; margin-right: 4px;"></span>
				<?php esc_html_e( 'Unlock Pro Features', 'genform' ); ?>
			</h2>
			<p style="color: #64748b; font-size: 14px; margin: 0 0 4px;">
				<?php esc_html_e( 'Supercharge your forms with advanced features, integrations, and analytics.', 'genform' ); ?>
			</p>
		</div>
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
				'save_resume',
				'form_abandonment',
			)
		);
		?>
	</div>
	<?php endif; ?>
</div>
