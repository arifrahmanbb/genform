<?php

namespace GenForm\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Builder
 * Handles form builder logic and data persistence.
 */
final class Builder {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handleSave' ) );
		add_action( 'admin_init', array( $this, 'handleDeleteForm' ) );
	}

	/**
	 * Render the builder view.
	 */
	public static function render(): void {
		include GENFORM_PATH . 'admin/views/form-builder.php';
	}

	/**
	 * Handle form saving.
	 */
	public function handleSave(): void {
		if ( ! isset( $_POST['genform_save'] ) || ! isset( $_POST['genform_builder_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['genform_builder_nonce'] ) ), 'genform_save_form' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'genform' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
		}

		$this->save_form();
	}

	/**
	 * Save form data.
	 */
	private function save_form(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'genform_forms';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified in handleSave.
		$id = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in handleSave.
		$name = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified in handleSave; JSON content.
		$data = isset( $_POST['form_data'] ) ? wp_unslash( $_POST['form_data'] ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified in handleSave; JSON content.
		$settings = isset( $_POST['form_settings'] ) ? wp_unslash( $_POST['form_settings'] ) : '';

		if ( ! $name || ! $data ) {
			return;
		}

		$payload = array(
			'form_name'     => $name,
			'form_data'     => $data,
			'form_settings' => $settings,
			'status'        => 'active',
		);

		if ( $id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( $table, $payload, array( 'id' => $id ) );
			add_settings_error( 'genform_messages', 'form_updated', esc_html__( 'Form updated successfully.', 'genform' ), 'success' );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert( $table, $payload );
			$id = $wpdb->insert_id;
			wp_safe_redirect( admin_url( "admin.php?page=genform-builder&action=edit&form_id=$id&_wpnonce=" . wp_create_nonce( 'genform_edit_form' ) ) );
			exit;
		}
	}

	/**
	 * Handle form deletion.
	 */
	public function handleDeleteForm(): void {
		// Verify page and action first without processing data.
		if ( ! isset( $_GET['page'] ) || 'genform' !== $_GET['page'] || ! isset( $_GET['action'] ) || 'delete' !== $_GET['action'] ) {
			return;
		}

		// Verify nonce before any processing.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'genform_delete_form' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified above.
		$id = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
		if ( ! $id ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( $wpdb->prefix . 'genform_forms', array( 'id' => $id ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( $wpdb->prefix . 'genform_entries', array( 'form_id' => $id ) );

		wp_safe_redirect( admin_url( 'admin.php?page=genform' ) );
		exit;
	}
}

