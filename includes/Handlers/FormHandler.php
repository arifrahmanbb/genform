<?php
/**
 * AJAX Form Submission Handler
 *
 * Processes frontend form submissions and administrative entry actions.
 *
 * @package GenForm
 */

namespace GenForm\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GenForm\Integrations\Email;
use GenForm\Utils\DetectionHelper;

final class FormHandler {

	/**
	 * Setup hooks for AJAX actions.
	 */
	public function __construct() {
		add_action( 'wp_ajax_genform_submit', array( $this, 'handleSubmission' ) );
		add_action( 'wp_ajax_nopriv_genform_submit', array( $this, 'handleSubmission' ) );
		add_action( 'wp_ajax_genform_delete_entry', array( $this, 'handleDeleteEntry' ) );
		add_action( 'wp_ajax_genform_trash_entry', array( $this, 'handleTrashEntry' ) );
		add_action( 'wp_ajax_genform_delete_form', array( $this, 'handleDeleteFormAjax' ) );
		add_action( 'wp_ajax_genform_mark_as_read', array( $this, 'handleMarkAsRead' ) );
		add_action( 'admin_init', array( $this, 'processBulkActions' ) );
	}

	/**
	 * Verified entry point for AJAX submissions.
	 */
	public function handleSubmission(): void {
		$id = absint( $_POST['genform_id'] ?? 0 );
		if ( ! $id || ! wp_verify_nonce( $_POST['genform_nonce'] ?? '', "genform_submit_$id" ) ) {
			$this->send_err( esc_html__( 'Security check failed.', 'genform' ), $id );
		}
		$this->process( $id );
	}

	/**
	 * Core processing logic: sanitization, storage, and notification.
	 */
	private function process( int $id ): void {
		global $wpdb;
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id ) );

		if ( ! $form ) {
			$this->send_err( esc_html__( 'Form not found.', 'genform' ) );
		}

		$data = $this->get_sanitized_data();
		$dev  = DetectionHelper::getInfo();
		$meta = array(
			'browser' => $dev['browser'],
			'os'      => $dev['os'],
			'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
			'url'     => $_SERVER['HTTP_REFERER'] ?? '',
		);

		// Persist the entry.
		$wpdb->insert(
			"{$wpdb->prefix}genform_entries",
			array(
				'form_id'        => $id,
				'entry_data'     => wp_json_encode( $data ),
				'entry_metadata' => wp_json_encode( $meta ),
				'user_ip'        => $meta['ip'],
				'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
			)
		);

		// Send email notification.
		Email::send( $wpdb->insert_id, $id, $data );

		$sets = json_decode( $form->form_settings, true );
		$res  = array(
			'message' => $sets['success_message'] ?? esc_html__( 'Thank you for your submission.', 'genform' ),
		);

		if ( ( $sets['con_type'] ?? '' ) === 'redirect' && ! empty( $sets['redirect_url'] ) ) {
			$res['redirect'] = esc_url_raw( $sets['redirect_url'] );
		}

		wp_send_json_success( $res );
	}

	/**
	 * Centralized error response helper.
	 */
	private function send_err( string $m, int $id = 0 ): void {
		if ( $id ) {
			global $wpdb;
			$f = $wpdb->get_row( $wpdb->prepare( "SELECT form_settings FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id ) );
			if ( $f ) {
				$s = json_decode( $f->form_settings, true );
				if ( ! empty( $s['error_message'] ) ) {
					$m = $s['error_message'];
				}
			}
		}
		wp_send_json_error( array( 'message' => $m ) );
	}

	/**
	 * Iterates over $_POST to collect and sanitize form keys.
	 */
	private function get_sanitized_data(): array {
		$d = array();
		foreach ( $_POST as $k => $v ) {
			if ( str_starts_with( $k, 'gfm_' ) ) {
				$rk       = str_replace( 'gfm_', '', $k );
				$d[ $rk ] = is_array( $v ) ? array_map( 'sanitize_text_field', wp_unslash( $v ) ) : sanitize_text_field( wp_unslash( $v ) );
			}
		}
		return $d;
	}

	/**
	 * Admin AJAX handler for single entry deletion.
	 */
	public function handleDeleteEntry(): void {
		check_ajax_referer( 'genform_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', 'genform' ) ) );
		}
		$id = absint( $_POST['entry_id'] ?? 0 );
		if ( $id ) {
			global $wpdb;
			$wpdb->delete( "{$wpdb->prefix}genform_entries", array( 'id' => $id ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Admin AJAX handler for trashing an entry.
	 */
	public function handleTrashEntry(): void {
		check_ajax_referer( 'genform_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', 'genform' ) ) );
		}
		$id = absint( $_POST['entry_id'] ?? 0 );
		if ( $id ) {
			global $wpdb;
			$wpdb->update( "{$wpdb->prefix}genform_entries", array( 'status' => 'trash' ), array( 'id' => $id ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Admin AJAX handler for deleting a form.
	 */
	public function handleDeleteFormAjax(): void {
		check_ajax_referer( 'genform_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized', 'genform' ) ) );
		}
		$id = absint( $_POST['form_id'] ?? 0 );
		if ( $id ) {
			global $wpdb;
			$wpdb->delete( "{$wpdb->prefix}genform_forms", array( 'id' => $id ) );
			$wpdb->delete( "{$wpdb->prefix}genform_entries", array( 'form_id' => $id ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Handles bulk operations from the entries list table.
	 */
	public function processBulkActions(): void {
		if ( ( $_GET['page'] ?? '' ) !== 'genform-entries' ) {
			return;
		}
		$act = $_GET['action'] ?? $_GET['action2'] ?? '';
		if ( ! in_array( $act, array( 'trash', 'restore', 'delete' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'bulk-entries' );

		$ids = array_map( 'absint', (array) ( $_GET['entry'] ?? array() ) );
		if ( ! empty( $ids ) ) {
			global $wpdb;
			$t   = "{$wpdb->prefix}genform_entries";
			$ps  = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$msg = '';

			if ( $act === 'trash' ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $t SET status = 'trash' WHERE id IN ($ps)", ...$ids ) );
				$msg = 'trashed';
			} elseif ( $act === 'restore' ) {
				$wpdb->query( $wpdb->prepare( "UPDATE $t SET status = 'read' WHERE id IN ($ps)", ...$ids ) );
				$msg = 'restored';
			} elseif ( $act === 'delete' ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM $t WHERE id IN ($ps)", ...$ids ) );
				$msg = 'deleted';
			}

			$re = add_query_arg( array( $msg => count( $ids ) ), admin_url( 'admin.php?page=genform-entries' ) );
			if ( $act !== 'trash' ) {
				$re = add_query_arg( 'status', 'trash', $re );
			}
			wp_safe_redirect( $re );
			exit;
		}
	}

	/**
	 * Administrative toggle for marking a submission as read.
	 */
	public function handleMarkAsRead(): void {
		check_ajax_referer( 'genform_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		$id = absint( $_POST['entry_id'] ?? 0 );
		if ( $id ) {
			global $wpdb;
			$wpdb->update( "{$wpdb->prefix}genform_entries", array( 'status' => 'read' ), array( 'id' => $id ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}
}
