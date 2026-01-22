<?php

declare(strict_types=1);

namespace GenForm\Admin;

final class Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public static function render(): void
    {
        include GENFORM_PATH . 'admin/views/settings.php';
    }

    public function registerSettings(): void
    {
        register_setting('genform_settings', 'genform_general', [$this, 'sanitize']);

        add_settings_section('genform_main', __('General Settings', 'genform'), null, 'genform_settings');

        add_settings_field(
            'genform_recaptcha_site',
            __('reCAPTCHA Site Key', 'genform'),
            fn() => $this->renderField('recaptcha_site_key'),
            'genform_settings',
            'genform_main'
        );
    }

    public function sanitize(array $input): array
    {
        return [
            'recaptcha_site_key'   => sanitize_text_field($input['recaptcha_site_key'] ?? ''),
            'recaptcha_secret_key' => sanitize_text_field($input['recaptcha_secret_key'] ?? ''),
        ];
    }

    private function renderField(string $key): void
    {
        $options = get_option('genform_general', []);
        $value   = esc_attr($options[$key] ?? '');
        echo "<input type='text' name='genform_general[$key]' value='$value' class='regular-text' />";
    }
}
