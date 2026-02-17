<?php

/**
 * Admin View: Form Preview
 *
 * Renders a form in the admin area for preview purposes.
 *
 * @package GenForm
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! current_user_can('manage_options')) {
    wp_die(esc_html__('Unauthorized.', 'genform'));
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$genform_preview_id = isset($_GET['form_id']) ? absint(wp_unslash($_GET['form_id'])) : 0;

if (! $genform_preview_id) {
    wp_die(esc_html__('No form ID provided.', 'genform'));
}

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$genform_preview_form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $genform_preview_id));

if (! $genform_preview_form) {
    wp_die(esc_html__('Form not found.', 'genform'));
}

$genform_preview_data     = json_decode($genform_preview_form->form_data, true);
$genform_preview_settings = json_decode($genform_preview_form->form_settings, true);
?>

<div class="wrap genform-admin-wrap">
    <div class="gfm-header-flex">
        <h1>
            <?php
            /* translators: %s: Form name */
            printf(esc_html__('Preview: %s', 'genform'), esc_html($genform_preview_form->form_name));
            ?>
        </h1>
        <div class="gfm-actions">
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform-builder&action=edit&form_id=' . $genform_preview_id), 'genform_edit_form')); ?>" class="gfm-btn gfm-btn-primary">
                <?php esc_html_e('Edit Form', 'genform'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=genform')); ?>" class="gfm-btn gfm-btn-outline">
                <?php esc_html_e('Back to All Forms', 'genform'); ?>
            </a>
        </div>
    </div>

    <div class="gfm-card">
        <div class="gfm-preview-container" style="max-width: 700px; margin: 30px auto; padding: 30px; border: 1px dashed #c3c4c7; border-radius: 8px; background: #fff;">
            <div class="gfm-preview-badge" style="text-align: center; margin-bottom: 20px;">
                <span style="display: inline-block; background: #f0f0f1; color: #50575e; padding: 4px 12px; border-radius: 4px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <?php esc_html_e('Preview Mode – Submissions are disabled', 'genform'); ?>
                </span>
            </div>
            <?php echo do_shortcode('[genform id="' . $genform_preview_id . '"]'); ?>
        </div>
    </div>
</div>