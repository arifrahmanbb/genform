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

        // Main Section
        add_settings_section(
            'genform_main', 
            esc_html__('General Configuration', 'genform'), 
            fn() => print('<p class="gfm-section-desc">' . esc_html__('Configure your primary branding and security keys here.', 'genform') . '</p>'),
            'genform_settings'
        );

        add_settings_field(
            'genform_recaptcha_site',
            esc_html__('reCAPTCHA Site Key', 'genform'),
            fn() => $this->renderField('recaptcha_site_key', esc_html__('Enter your Google reCAPTCHA v2 (Checkbox) site key to protect your forms from bots.', 'genform')),
            'genform_settings',
            'genform_main'
        );

        add_settings_field(
            'genform_recaptcha_secret',
            esc_html__('reCAPTCHA Secret Key', 'genform'),
            fn() => $this->renderField('recaptcha_secret_key', esc_html__('The secret key is required for server-side verification. Keep this private.', 'genform')),
            'genform_settings',
            'genform_main'
        );

        add_settings_field(
            'genform_primary_color',
            esc_html__('Brand Primary Color', 'genform'),
            fn() => $this->renderColorField('primary_color', esc_html__('Choose the main accent color for your buttons and active fields across all forms.', 'genform')),
            'genform_settings',
            'genform_main'
        );

        // Email Section
        add_settings_section(
            'genform_email_sec', 
            esc_html__('Default Email Identity', 'genform'), 
            fn() => print('<p class="gfm-section-desc">' . esc_html__('These settings act as global fallbacks. If a specific form does not have a "From" name or email set in the builder, these will be used automatically.', 'genform') . '</p>'),
            'genform_settings'
        );

        add_settings_field(
            'genform_from_name',
            esc_html__('Global Sender Name', 'genform'),
            fn() => $this->renderField('from_name', esc_html__('e.g. Your Business Name', 'genform')),
            'genform_settings',
            'genform_email_sec'
        );

        add_settings_field(
            'genform_from_email',
            esc_html__('Global Sender Email', 'genform'),
            fn() => $this->renderField('from_email', esc_html__('e.g. support@yourdomain.com', 'genform')),
            'genform_settings',
            'genform_email_sec'
        );

        // Advanced Section
        add_settings_section(
            'genform_advanced', 
            esc_html__('Advanced & Performance', 'genform'), 
            fn() => print('<p class="gfm-section-desc">' . esc_html__('Optimize how the plugin interacts with your site theme.', 'genform') . '</p>'),
            'genform_settings'
        );

        add_settings_field(
            'genform_disable_assets',
            esc_html__('Optimization Mode', 'genform'),
            fn() => $this->renderCheckboxField('disable_assets', esc_html__('Don\'t load default CSS/JS. Enable this only if you want to provide your own custom styling and scripts for the forms.', 'genform')),
            'genform_settings',
            'genform_advanced'
        );
    }

    public function sanitize(array $input): array
    {
        return [
            'recaptcha_site_key'   => sanitize_text_field($input['recaptcha_site_key'] ?? ''),
            'recaptcha_secret_key' => sanitize_text_field($input['recaptcha_secret_key'] ?? ''),
            'primary_color'        => sanitize_hex_color($input['primary_color'] ?? '#6366f1'),
            'from_name'            => sanitize_text_field($input['from_name'] ?? get_bloginfo('name')),
            'from_email'           => sanitize_email($input['from_email'] ?? get_bloginfo('admin_email')),
            'disable_assets'       => isset($input['disable_assets']) ? 1 : 0,
        ];
    }

    private function renderField(string $key, string $desc = ''): void
    {
        $options = get_option('genform_general', []);
        $value   = $options[$key] ?? '';
        printf(
            '<input type="text" name="genform_general[%1$s]" value="%2$s" class="regular-text" />',
            esc_attr($key),
            esc_attr($value)
        );
        if ($desc) {
            printf('<p class="description">%s</p>', esc_html($desc));
        }
    }

    private function renderCheckboxField(string $key, string $desc = ''): void
    {
        $options = get_option('genform_general', []);
        $checked = !empty($options[$key]) ? 'checked' : '';
        printf(
            '<label><input type="checkbox" name="genform_general[%1$s]" value="1" %2$s /> %3$s</label>',
            esc_attr($key),
            $checked,
            esc_html($desc)
        );
    }

    private function renderColorField(string $key, string $desc = ''): void
    {
        $options = get_option('genform_general', []);
        $value   = $options[$key] ?? '#6366f1';
        printf(
            '<input type="color" name="genform_general[%1$s]" value="%2$s" style="height:40px; width:60px; padding:2px; border:1px solid #ccc; cursor:pointer;" />',
            esc_attr($key),
            esc_attr($value)
        );
        if ($desc) {
            printf('<p class="description">%s</p>', esc_html($desc));
        }
    }
}
