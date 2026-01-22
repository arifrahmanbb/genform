<?php

declare(strict_types=1);

namespace GenForm\Integrations;

final class Email
{
    public static function send(int $entry_id, int $form_id, array $data): void
    {
        global $wpdb;
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id));
        $settings = json_decode($form->settings, true);

        if (!empty($settings['disable_admin_notification'])) {
            return;
        }

        $to      = $settings['admin_email'] ?: get_option('admin_email');
        $subject = sprintf(__('New Submission: %s', 'genform'), $form->name);
        $body    = self::buildBody($data);

        wp_mail($to, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
    }

    private static function buildBody(array $data): string
    {
        $html = "<h3>" . __('Submission Details', 'genform') . "</h3><table>";
        foreach ($data as $key => $val) {
            $html .= "<tr><td><strong>" . esc_html(ucfirst($key)) . "</strong></td><td>" . esc_html($val) . "</td></tr>";
        }
        return $html . "</table>";
    }
}
