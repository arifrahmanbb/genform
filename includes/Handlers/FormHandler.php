<?php

namespace GenForm\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GenForm\Integrations\Email;

/**
 * Class FormHandler
 * Processes AJAX form submissions.
 */
final class FormHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_genform_submit', array( $this, 'handleSubmission' ) );
		add_action( 'wp_ajax_nopriv_genform_submit', array( $this, 'handleSubmission' ) );
		add_action( 'wp_ajax_genform_delete_entry', array( $this, 'handleDeleteEntry' ) );
	}

	/**
	 * Handle form submission.
	 */
	public function handleSubmission(): void {
		$form_id = isset( $_POST['genform_id'] ) ? absint( wp_unslash( $_POST['genform_id'] ) ) : 0;
		$nonce   = isset( $_POST['genform_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['genform_nonce'] ) ) : '';

		if ( ! $form_id || ! wp_verify_nonce( $nonce, "genform_submit_$form_id" ) ) {
			$this->send_error( esc_html__( 'Security check failed.', 'genform' ), $form_id );
		}

		$this->process( $form_id );
	}

	/**
	 * Process the form data.
	 */
	private function process( int $form_id ): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id ) );

		if ( ! $form ) {
			$this->send_error( esc_html__( 'Form not found.', 'genform' ) );
		}

		$entry_data = $this->get_sanitized_data();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			"{$wpdb->prefix}genform_entries",
			array(
				'form_id'    => $form_id,
				'entry_data' => wp_json_encode( $entry_data ),
				'user_ip'    => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			)
		);

		$entry_id = $wpdb->insert_id;

		// Handle Notifications.
		Email::send( $entry_id, $form_id, $entry_data );

		$settings = json_decode( $form->form_settings, true );
		$res_data = array(
			'message' => $settings['success_message'] ?? esc_html__( 'Thank you for your submission.', 'genform' ),
		);

		if ( isset( $settings['con_type'] ) && 'redirect' === $settings['con_type'] && ! empty( $settings['redirect_url'] ) ) {
			$res_data['redirect'] = esc_url_raw( $settings['redirect_url'] );
		}

		wp_send_json_success( $res_data );
	}

	/**
	 * Send error response.
	 */
	private function send_error( string $default_msg, int $form_id = 0 ): void {
		$message = $default_msg;
		if ( $form_id ) {
			global $wpdb;
			$form = $wpdb->get_row( $wpdb->prepare( "SELECT form_settings FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id ) );
			if ( $form ) {
				$settings = json_decode( $form->form_settings, true );
				if ( ! empty( $settings['error_message'] ) ) {
					$message = $settings['error_message'];
				}
			}
		}
		wp_send_json_error( array( 'message' => $message ) );
	}

	/**
	 * Sanitize submitted form data.
	 */
	private function get_sanitized_data(): array {
		$data = array();
		// Nonce already verified in handleSubmission.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		foreach ( $_POST as $key => $value ) {
			if ( str_starts_with( $key, 'gfm_' ) ) {
				$raw_key = str_replace( 'gfm_', '', $key );
				if ( is_array( $value ) ) {
					$data[ $raw_key ] = array_map( 'sanitize_text_field', wp_unslash( $value ) );
				} else {
					$data[ $raw_key ] = sanitize_text_field( wp_unslash( $value ) );
				}
			}
		}
		return $data;
	}

	/**
	 * Handle entry deletion.
	 */
	public function handleDeleteEntry(): void {
		check_ajax_referer( 'genform_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', 'genform' ) ) );
		}

		$id = isset( $_POST['entry_id'] ) ? absint( wp_unslash( $_POST['entry_id'] ) ) : 0;
		if ( $id ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( $wpdb->prefix . 'genform_entries', array( 'id' => $id ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}
}
