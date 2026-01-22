<?php

declare(strict_types=1);

namespace GenForm\Admin;

final class Builder
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'handleSave']);
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
        $data     = wp_kses_post(wp_unslash($_POST['form_data'] ?? ''));
        $settings = wp_kses_post(wp_unslash($_POST['form_settings'] ?? ''));

        if (!$name || !$data) {
            return;
        }

        $payload = [
            'name'     => $name,
            'data'     => $data,
            'settings' => $settings,
            'status'   => 'active',
        ];

        if ($id) {
            $wpdb->update($table, $payload, ['id' => $id]);
        } else {
            $wpdb->insert($table, $payload);
            $id = $wpdb->insert_id;
            wp_safe_redirect(admin_url("admin.php?page=genform-builder&action=edit&form_id=$id&_wpnonce=" . wp_create_nonce('genform_edit_form')));
            exit;
        }
    }
}
