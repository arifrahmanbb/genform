<?php

declare(strict_types=1);

namespace GenForm\Integrations;

final class Block
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_block_type('genform/form-selector', [
            'editor_script' => 'genform-admin',
            'render_callback' => [$this, 'render'],
        ]);
    }

    public function render(array $attributes): string
    {
        $id = $attributes['formId'] ?? 0;
        return $id ? do_shortcode("[genform id='$id']") : '';
    }
}
