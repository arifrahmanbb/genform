<?php

declare(strict_types=1);

namespace GenForm\Admin;

/**
 * Class Builder
 * Handles form builder logic and data persistence.
 */
final class Builder
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'handleSave']);
        add_action('admin_init', [$this, 'handleDeleteForm']);
    }

    public static function render(): void
    {
        include GENFORM_PATH . 'admin/views/form-builder.php';
    }

    public function handleSave(): void
    {
        if (!isset($_POST['genform_save']) || !isset($_POST['genform_builder_nonce'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['genform_builder_nonce'])), 'genform_save_form')) {
            wp_die(__('Security check failed.', 'genform'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized.', 'genform'));
        }

        $this->saveForm();
    }

    private function saveForm(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'genform_forms';

        $id       = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
        $name     = sanitize_text_field(wp_unslash($_POST['form_name'] ?? ''));
        $data     = wp_unslash($_POST['form_data'] ?? ''); // JSON string
        $settings = wp_unslash($_POST['form_settings'] ?? ''); // JSON string

        if (!$name || !$data) {
            return;
        }

        $payload = [
            'form_name'     => $name,
            'form_data'     => $data,
            'form_settings' => $settings,
            'status'        => 'active',
        ];

        if ($id) {
            $wpdb->update($table, $payload, ['id' => $id]);
            add_settings_error('genform_messages', 'form_updated', __('Form updated successfully.', 'genform'), 'success');
        } else {
            $wpdb->insert($table, $payload);
            $id = $wpdb->insert_id;
            wp_safe_redirect(admin_url("admin.php?page=genform-builder&action=edit&form_id=$id&_wpnonce=" . wp_create_nonce('genform_edit_form')));
            exit;
        }
    }

    public function handleDeleteForm(): void
    {
        if (isset($_GET['page']) && $_GET['page'] === 'genform' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['form_id'])) {
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'genform_delete_form')) {
                return;
            }

            global $wpdb;
            $id = absint($_GET['form_id']);
            $wpdb->delete($wpdb->prefix . 'genform_forms', ['id' => $id]);
            $wpdb->delete($wpdb->prefix . 'genform_entries', ['form_id' => $id]);

            wp_safe_redirect(admin_url('admin.php?page=genform'));
            exit;
        }
    }
}
