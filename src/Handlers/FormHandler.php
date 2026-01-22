<?php

declare(strict_types=1);

namespace GenForm\Handlers;

use GenForm\Integrations\Email;

final class FormHandler
{
    public function __construct()
    {
        add_action('wp_ajax_genform_submit', [$this, 'handleSubmission']);
        add_action('wp_ajax_nopriv_genform_submit', [$this, 'handleSubmission']);
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
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $entry_id = $wpdb->insert_id;

        Email::send($entry_id, $form_id, $entry_data);

        $settings = json_decode($form->settings, true);
        wp_send_json_success([
            'message'  => $settings['success_message'] ?? __('Submitted!', 'genform'),
            'redirect' => $settings['redirect_url'] ?? '',
        ]);
    }

    private function getSanitizedData(): array
    {
        $data = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'gfm_')) {
                $data[str_replace('gfm_', '', $key)] = sanitize_text_field(wp_unslash($value));
            }
        }
        return $data;
    }
}
