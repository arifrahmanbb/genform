<?php
/**
 * Admin View: General Settings
 *
 * Provides a clean wrapper for the WordPress Settings API implementation.
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
}
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
			submit_button();
			?>
		</form>
	</div>
</div>
