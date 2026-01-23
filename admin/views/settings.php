<?php
/**
 * Admin View: Settings
 *
 * Displays the plugin settings page.
 *
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check user capabilities.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'genform' ) );
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
