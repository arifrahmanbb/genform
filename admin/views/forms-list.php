<?php
if (! defined('ABSPATH')) {
    exit;
}

// Check user capabilities
if (! current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'genform'));
}

global $wpdb;
$genform_forms_table = $wpdb->prefix . 'genform_forms';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['form_id']) && isset($_GET['_wpnonce'])) {
    if (wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'genform_delete_form')) {
        $genform_form_id = absint($_GET['form_id']);
        $wpdb->delete($genform_forms_table, array('id' => $genform_form_id), array('%d'));
        echo '<div class="notice notice-success"><p>' . esc_html__('Form deleted successfully.', 'genform') . '</p></div>';
    }
}

// Get all forms
$genform_forms = $wpdb->get_results("SELECT * FROM $genform_forms_table ORDER BY created_at DESC");
?>

<div class="wrap genform-admin-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('All Forms', 'genform'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=genform-add-new')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'genform'); ?>
    </a>
    <hr class="wp-header-end">

    <?php if (empty($genform_forms)) : ?>
        <div class="genform-empty-state">
            <p><?php esc_html_e('No forms found. Create your first form to get started!', 'genform'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=genform-add-new')); ?>" class="button button-primary">
                <?php esc_html_e('Create Your First Form', 'genform'); ?>
            </a>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Form Name', 'genform'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'genform'); ?></th>
                    <th><?php esc_html_e('Entries', 'genform'); ?></th>
                    <th><?php esc_html_e('Status', 'genform'); ?></th>
                    <th><?php esc_html_e('Created', 'genform'); ?></th>
                    <th><?php esc_html_e('Actions', 'genform'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($genform_forms as $genform_form) :
                    $genform_entries_table = $wpdb->prefix . 'genform_entries';
                    $genform_entries_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $genform_entries_table WHERE form_id = %d", $genform_form->id));
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($genform_form->form_name); ?></strong>
                        </td>
                        <td>
                            <code>[genform id="<?php echo esc_attr($genform_form->id); ?>"]</code>
                            <button class="button button-small genform-copy-shortcode" data-shortcode='[genform id="<?php echo esc_attr($genform_form->id); ?>"]'>
                                <?php esc_html_e('Copy', 'genform'); ?>
                            </button>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform-entries&form_id=' . $genform_form->id), 'genform_view_entries')); ?>">
                                <?php echo esc_html($genform_entries_count); ?>
                            </a>
                        </td>
                        <td>
                            <span class="genform-status genform-status-<?php echo esc_attr($genform_form->status); ?>">
                                <?php echo esc_html(ucfirst($genform_form->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($genform_form->created_at))); ?></td>
                        <td>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform-add-new&action=edit&form_id=' . $genform_form->id), 'genform_edit_form')); ?>" class="button button-small">
                                <?php esc_html_e('Edit', 'genform'); ?>
                            </a>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=genform&action=delete&form_id=' . $genform_form->id), 'genform_delete_form')); ?>"
                                class="button button-small genform-delete-form"
                                onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this form?', 'genform'); ?>');">
                                <?php esc_html_e('Delete', 'genform'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>