<?php

namespace GenForm\Integrations;
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Block
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_block_type('genform/form-block', [
            'editor_script' => 'genform-block',
            'render_callback' => [$this, 'render'],
        ]);
    }

    public function render(array $attributes): string
    {
        $id = $attributes['formId'] ?? 0;
        return $id ? do_shortcode("[genform id='$id']") : '';
    }
}
