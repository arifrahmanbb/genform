<?php
/**
 * Admin View: Settings
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap genform-settings-wrap">
    <h1><?php esc_html_e( 'GenForm Settings', 'genform' ); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields( 'genform_settings' );
        do_settings_sections( 'genform_settings' );
        submit_button();
        ?>
    </form>
</div>
