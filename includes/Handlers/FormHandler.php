<?php

declare(strict_types=1);

namespace GenForm\Handlers;

use GenForm\Integrations\Email;

/**
 * Class FormHandler
 * Processes AJAX form submissions.
 */
final class FormHandler
{
    public function __construct()
    {
        add_action('wp_ajax_genform_submit', [$this, 'handleSubmission']);
        add_action('wp_ajax_nopriv_genform_submit', [$this, 'handleSubmission']);
        add_action('wp_ajax_genform_delete_entry', [$this, 'handleDeleteEntry']);
    }

    public function handleSubmission(): void
    {
        $form_id = (int) ($_POST['genform_id'] ?? 0);
        $nonce   = sanitize_text_field(wp_unslash($_POST['genform_nonce'] ?? ''));

        if (!$form_id || !wp_verify_nonce($nonce, "genform_submit_$form_id")) {
            wp_send_json_error(['message' => __('Security check failed.', 'genform')]);
        }

        $this->process($form_id);
    }

    private function process(int $form_id): void
    {
        global $wpdb;
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id));

        if (!$form) {
            wp_send_json_error(['message' => __('Form not found.', 'genform')]);
        }

        $entry_data = $this->getSanitizedData();

        $wpdb->insert("{$wpdb->prefix}genform_entries", [
            'form_id'    => $form_id,
            'entry_data' => wp_json_encode($entry_data),
            'user_ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        $entry_id = $wpdb->insert_id;

        // Handle Notifications
        Email::send($entry_id, $form_id, $entry_data);

        $settings = json_decode($form->form_settings, true);
        wp_send_json_success([
            'message'  => $settings['success_message'] ?? __('Thank you for your submission.', 'genform'),
            'redirect' => $settings['redirect_url'] ?? '',
        ]);
    }

    private function getSanitizedData(): array
    {
        $data = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'gfm_')) {
                $raw_key = str_replace('gfm_', '', $key);
                if (is_array($value)) {
                    $data[$raw_key] = array_map('sanitize_text_field', $value);
                } else {
                    $data[$raw_key] = sanitize_text_field(wp_unslash($value));
                }
            }
        }
        return $data;
    }

    public function handleDeleteEntry(): void
    {
        check_ajax_referer('genform_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'genform')]);
        }

        $id = (int) ($_POST['entry_id'] ?? 0);
        if ($id) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'genform_entries', ['id' => $id]);
            wp_send_json_success();
        }
        wp_send_json_error();
    }
}
