<?php
/**
 * Form Builder Engine
 *
 * Handles backend operations for adding, editing, saving, and duplicating forms.
 *
 * @package GenForm
 */

namespace GenForm\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Builder {

	/**
	 * Builder constructor to initialize admin hooks.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handleSave' ) );
		add_action( 'admin_init', array( $this, 'handleDeleteForm' ) );
		add_action( 'admin_init', array( $this, 'handleDuplicate' ) );
	}

	/**
	 * Handles cloning an existing form into a new entry.
	 */
	public function handleDuplicate(): void {
		if ( ( $_GET['page'] ?? '' ) !== 'genform' || ( $_GET['action'] ?? '' ) !== 'duplicate' ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'genform_duplicate_form' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$id = absint( $_GET['form_id'] ?? 0 );
		if ( ! $id ) {
			return;
		}

		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id ) );

		if ( $form ) {
			$wpdb->insert(
				"{$wpdb->prefix}genform_forms",
				array(
					'form_name'     => $form->form_name . ' (' . esc_html__( 'Copy', 'genform' ) . ')',
					'form_data'     => $form->form_data,
					'form_settings' => $form->form_settings,
					'status'        => 'active',
				)
			);

			wp_safe_redirect( admin_url( 'admin.php?page=genform&duplicated=1' ) );
			exit;
		}
	}

	/**
	 * Renders the form builder interface view.
	 */
	public static function render(): void {
		include GENFORM_PATH . 'admin/views/form-builder.php';
	}

	/**
	 * Main save logic handler with security verification.
	 */
	public function handleSave(): void {
		if ( ! isset( $_POST['genform_save'], $_POST['genform_builder_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['genform_builder_nonce'], 'genform_save_form' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'genform' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
		}

		$this->save_form();
	}

	/**
	 * Recursively sanitizes an array to ensure no malicious code is persisted.
	 *
	 * @param mixed $data The data to sanitize.
	 * @return mixed
	 */
	private function sanitize_recursive( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = $this->sanitize_recursive( $value );
			}
		} else {
			$data = sanitize_text_field( $data );
		}
		return $data;
	}

	/**
	 * Core saving procedure and JSON validation.
	 */
	private function save_form(): void {
		global $wpdb;
		$t    = "{$wpdb->prefix}genform_forms";
		$id   = absint( $_GET['form_id'] ?? 0 );
		$name = sanitize_text_field( $_POST['form_name'] ?? '' );
		$raw_data = wp_unslash( $_POST['form_data'] ?? '' );
		$raw_sets = wp_unslash( $_POST['form_settings'] ?? '' );

		if ( ! $name || ! $raw_data ) {
			return;
		}

		$decoded_data = json_decode( $raw_data, true );
		$decoded_sets = json_decode( $raw_sets, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_die( esc_html__( 'Invalid JSON data provided.', 'genform' ) );
		}

		// Sanitize structured data before DB storage.
		$data = wp_json_encode( $this->sanitize_recursive( $decoded_data ) );
		$sets = wp_json_encode( $this->sanitize_recursive( $decoded_sets ) );

		$p = array(
			'form_name'     => $name,
			'form_data'     => $data,
			'form_settings' => $sets,
			'status'        => 'active',
		);

		if ( $id ) {
			$wpdb->update( $t, $p, array( 'id' => $id ) );
			add_settings_error( 'genform_messages', 'f_upd', esc_html__( 'Form updated successfully.', 'genform' ), 'success' );
		} else {
			$wpdb->insert( $t, $p );
			$id = $wpdb->insert_id;
			wp_safe_redirect( admin_url( "admin.php?page=genform-builder&action=edit&form_id=$id&_wpnonce=" . wp_create_nonce( 'genform_edit_form' ) ) );
			exit;
		}
	}

	/**
	 * Permanently removes a form and its associated entry records.
	 */
	public function handleDeleteForm(): void {
		if ( ( $_GET['page'] ?? '' ) !== 'genform' || ( $_GET['action'] ?? '' ) !== 'delete' ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'genform_delete_form' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$id = absint( $_GET['form_id'] ?? 0 );
		if ( ! $id ) {
			return;
		}

		$wpdb->delete( "{$wpdb->prefix}genform_forms", array( 'id' => $id ) );
		$wpdb->delete( "{$wpdb->prefix}genform_entries", array( 'form_id' => $id ) );

		wp_safe_redirect( admin_url( 'admin.php?page=genform' ) );
		exit;
	}
}
