<?php

/**
 * Gutenberg Block Integration
 *
 * Registers and handles the server-side rendering of the GenForm block.
 *
 * @package GenForm
 */

namespace GenForm\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Block {


	/**
	 * Register block initialization hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the block type with WordPress.
	 */
	public function register(): void {
		register_block_type(
			'genform/form-block',
			array(
				'editor_script'   => 'genform-block',
				'editor_style'    => 'genform-admin',
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Server-side render callback for the block.
	 */
	public function render( array $atts ): string {
		$id = absint( $atts['formId'] ?? 0 );
		return $id ? do_shortcode( "[genform id='$id']" ) : '';
	}
}
