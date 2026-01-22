<?php

declare(strict_types=1);

namespace GenForm\Integrations;

/**
 * Class Shortcode
 */
final class Shortcode
{
    public function __construct()
    {
        add_shortcode('genform', [$this, 'render']);
    }

    public function render(array $atts): string
    {
        $atts = shortcode_atts(['id' => 0], $atts, 'genform');
        $id = (int) $atts['id'];

        if (!$id) {
            return '<p>' . __('No form ID provided.', 'genform') . '</p>';
        }

        global $wpdb;
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d AND status = 'active'", $id));

        if (!$form) {
            return '<p>' . __('Form not found.', 'genform') . '</p>';
        }

        ob_start();
        $this->displayForm($form);
        return ob_get_clean();
    }

    private function displayForm(object $form): void
    {
        $data = json_decode($form->form_data, true);
        $settings = json_decode($form->form_settings, true);
        $nonce = wp_create_nonce("genform_submit_{$form->id}");

        include GENFORM_PATH . 'public/views/form-template.php';
    }
}
