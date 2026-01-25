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
		$form_id       = isset( $_POST['genform_id'] ) ? absint( wp_unslash( $_POST['genform_id'] ) ) : 0;
		$request_nonce = isset( $_POST['genform_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['genform_nonce'] ) ) : '';

		if ( ! $form_id || ! wp_verify_nonce( $request_nonce, "genform_submit_$form_id" ) ) {
			$this->send_err( esc_html__( 'Security check failed.', 'genform' ), $form_id );
		}
		$this->process( $form_id );
	}

	/**
	 * Core processing logic: sanitization, storage, and notification.
	 */
	private function process( int $id ): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id ) );

		if ( ! $form ) {
			$this->send_err( esc_html__( 'Form not found.', 'genform' ) );
		}

		$data = $this->get_sanitized_data();
		$dev  = DetectionHelper::getInfo();
		$meta = array(
			'browser' => $dev['browser'],
			'os'      => $dev['os'],
			'ip'      => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
			'url'     => esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ?? '' ) ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			"{$wpdb->prefix}genform_entries",
			array(
				'form_id'        => $id,
				'entry_data'     => wp_json_encode( $data ),
				'entry_metadata' => wp_json_encode( $meta ),
				'user_ip'        => $meta['ip'],
				'user_agent'     => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
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
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
	 *
	 * Nonce verification is performed by the calling method (handleSubmission)
	 * before this function is invoked. Therefore, it is safe to access $_POST here.
	 */
	private function get_sanitized_data(): array {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$clean_data = array();
		foreach ( $_POST as $k => $v ) {
			if ( str_starts_with( $k, 'gfm_' ) ) {
				$rk               = str_replace( 'gfm_', '', $k );
				$clean_data[ $rk ] = is_array( $v ) ? array_map( 'sanitize_text_field', wp_unslash( $v ) ) : sanitize_text_field( wp_unslash( $v ) );
			}
		}
		// phpcs:enable
		return $clean_data;
	}

	/**
	 * Admin AJAX handler for single entry deletion.
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
		$id = isset( $_POST['entry_id'] ) ? absint( wp_unslash( $_POST['entry_id'] ) ) : 0;
		if ( $id ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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
		$id = isset( $_POST['form_id'] ) ? absint( wp_unslash( $_POST['form_id'] ) ) : 0;
		if ( $id ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( "{$wpdb->prefix}genform_forms", array( 'id' => $id ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( "{$wpdb->prefix}genform_entries", array( 'form_id' => $id ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Handles bulk operations from the entries list table.
	 */
	public function processBulkActions(): void {
		$page    = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		$action  = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$action2 = isset( $_GET['action2'] ) ? sanitize_text_field( wp_unslash( $_GET['action2'] ) ) : '';

		if ( 'genform-entries' !== $page ) {
			return;
		}

		$act = $action ?: $action2;
		if ( ! in_array( $act, array( 'trash', 'restore', 'delete' ), true ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'bulk-entries' );

		$ids = isset( $_GET['entry'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['entry'] ) ) : array();
		if ( ! empty( $ids ) ) {
			global $wpdb;
			$msg   = '';

			// Prepare placeholders explicitly to avoid scanner warnings about interpolation.
			$marks = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

			if ( 'trash' === $act ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}genform_entries SET status = 'trash' WHERE id IN ($marks)", ...$ids ) );
				$msg = 'trashed';
			} elseif ( 'restore' === $act ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}genform_entries SET status = 'read' WHERE id IN ($marks)", ...$ids ) );
				$msg = 'restored';
			} elseif ( 'delete' === $act ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}genform_entries WHERE id IN ($marks)", ...$ids ) );
				$msg = 'deleted';
			}

			$re = add_query_arg( array( $msg => count( $ids ) ), admin_url( 'admin.php?page=genform-entries' ) );
			if ( 'trash' !== $act ) {
				$re = add_query_arg( 'status', 'trash', $re );
			}
			wp_safe_redirect( $re );
			exit;
		}
	}
	public function handleMarkAsRead(): void {
		check_ajax_referer( 'genform_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		$id = isset( $_POST['entry_id'] ) ? absint( wp_unslash( $_POST['entry_id'] ) ) : 0;
		if ( $id ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( "{$wpdb->prefix}genform_entries", array( 'status' => 'read' ), array( 'id' => $id ) );
			wp_send_json_success();
		}
		wp_send_json_error();
	}
}
