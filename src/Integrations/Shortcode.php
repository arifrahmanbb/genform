<?php

declare(strict_types=1);

namespace GenForm\Integrations;

final class Shortcode
{
    public function __construct()
    {
        add_shortcode('genform', [$this, 'render']);
    }

    public function render(array $atts): string
    {
        $id = (int) ($atts['id'] ?? 0);
        if (!$id) return '';

        global $wpdb;
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id));
        if (!$form) return __('Form not found.', 'genform');

        ob_start();
        $this->displayForm($form);
        return ob_get_clean();
    }

    private function displayForm(object $form): void
    {
        $data = json_decode($form->data, true);
        $settings = json_decode($form->settings, true);
        $nonce = wp_create_nonce("genform_submit_{$form->id}");

        include GENFORM_PATH . 'public/views/form-template.php';
    }
}
