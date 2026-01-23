<?php

namespace GenForm\Admin;
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

        add_settings_section('genform_main', esc_html__('General Settings', 'genform'), null, 'genform_settings');

        add_settings_field(
            'genform_recaptcha_site',
            esc_html__('reCAPTCHA Site Key', 'genform'),
            fn() => $this->renderField('recaptcha_site_key'),
            'genform_settings',
            'genform_main'
        );

        add_settings_field(
            'genform_primary_color',
            esc_html__('Primary Color', 'genform'),
            fn() => $this->renderColorField('primary_color'),
            'genform_settings',
            'genform_main'
        );
    }

    public function sanitize(array $input): array
    {
        return [
            'recaptcha_site_key'   => sanitize_text_field($input['recaptcha_site_key'] ?? ''),
            'recaptcha_secret_key' => sanitize_text_field($input['recaptcha_secret_key'] ?? ''),
            'primary_color'        => sanitize_hex_color($input['primary_color'] ?? '#6366f1'),
        ];
    }

    private function renderField(string $key): void
    {
        $options = get_option('genform_general', []);
        $value   = $options[$key] ?? '';
        printf(
            '<input type="text" name="genform_general[%1$s]" value="%2$s" class="regular-text" />',
            esc_attr($key),
            esc_attr($value)
        );
    }

    private function renderColorField(string $key): void
    {
        $options = get_option('genform_general', []);
        $value   = $options[$key] ?? '#6366f1';
        printf(
            '<input type="color" name="genform_general[%1$s]" value="%2$s" style="height:40px; width:60px; padding:2px; border:1px solid #ccc; cursor:pointer;" />',
            esc_attr($key),
            esc_attr($value)
        );
    }
}
