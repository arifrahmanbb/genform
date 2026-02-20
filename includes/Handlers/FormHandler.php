<?php

/**
 * AJAX Form Submission Handler
 *
 * Processes frontend form submissions and administrative entry actions.
 *
 * @package GenForm
 */

namespace GenForm\Handlers;

if (! defined('ABSPATH')) {
	exit;
}

use GenForm\Integrations\Email;
use GenForm\Utils\DetectionHelper;

final class FormHandler
{


	/**
	 * Setup hooks for AJAX actions.
	 */
	public function __construct()
	{
		add_action('wp_ajax_genform_submit', array($this, 'handleSubmission'));
		add_action('wp_ajax_nopriv_genform_submit', array($this, 'handleSubmission'));
		add_action('wp_ajax_genform_delete_entry', array($this, 'handleDeleteEntry'));
		add_action('wp_ajax_genform_trash_entry', array($this, 'handleTrashEntry'));
		add_action('wp_ajax_genform_delete_form', array($this, 'handleDeleteFormAjax'));
		add_action('wp_ajax_genform_mark_as_read', array($this, 'handleMarkAsRead'));
		add_action('admin_init', array($this, 'processBulkActions'));
	}

	/**
	 * Verified entry point for AJAX submissions.
	 */
	public function handleSubmission(): void
	{
		$form_id       = isset($_POST['genform_id']) ? absint(wp_unslash($_POST['genform_id'])) : 0;
		$request_nonce = isset($_POST['genform_nonce']) ? sanitize_text_field(wp_unslash($_POST['genform_nonce'])) : '';

		if (! $form_id || ! wp_verify_nonce($request_nonce, "genform_submit_$form_id")) {
			$this->sendError(esc_html__('Security check failed.', 'genform'), $form_id);
		}

		// Honeypot anti-spam check.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$honeypot = isset($_POST['genform_website_url']) ? sanitize_text_field(wp_unslash($_POST['genform_website_url'])) : '';
		if (! empty($honeypot)) {
			// Silently reject — do NOT reveal bot detection to attackers.
			wp_send_json_success(array('message' => esc_html__('Thank you! Your submission has been received.', 'genform')));
		}

		// Rate limiting (5 submissions per minute per IP).
		$client_ip     = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
		$transient_key = 'genform_rate_' . md5($client_ip);
		$attempts      = (int) get_transient($transient_key);
		if ($attempts >= 5) {
			$this->sendError(esc_html__('Too many submissions. Please try again later.', 'genform'), $form_id);
		}
		set_transient($transient_key, $attempts + 1, MINUTE_IN_SECONDS);

		// GDPR consent validation.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$gdpr_field = isset($_POST['genform_gdpr_consent']) ? sanitize_text_field(wp_unslash($_POST['genform_gdpr_consent'])) : '';
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$gdpr_form = $wpdb->get_row($wpdb->prepare("SELECT form_settings FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id));
		if ($gdpr_form) {
			$gdpr_settings = json_decode($gdpr_form->form_settings, true);
			if (! empty($gdpr_settings['gfm_gdpr_enabled']) && empty($gdpr_field)) {
				$this->sendError(esc_html__('You must agree to the privacy terms to submit this form.', 'genform'), $form_id);
			}
		}

		$this->processSubmission($form_id);
	}

	/**
	 * Core processing logic: sanitization, storage, and notification.
	 */
	private function processSubmission(int $form_id): void
	{
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id));

		if (! $form) {
			$this->sendError(esc_html__('Form not found.', 'genform'));
		}

		$entry_data  = $this->getSanitizedData();
		$device_info = DetectionHelper::getInfo();
		$entry_meta  = array(
			'browser' => $device_info['browser'],
			'os'      => $device_info['os'],
			'ip'      => sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '')),
			'url'     => esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'] ?? '')),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			"{$wpdb->prefix}genform_entries",
			array(
				'form_id'        => $form_id,
				'entry_data'     => wp_json_encode($entry_data),
				'entry_metadata' => wp_json_encode($entry_meta),
				'user_ip'        => $entry_meta['ip'],
				'user_agent'     => sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')),
			)
		);

		// Send email notification.
		Email::send($wpdb->insert_id, $form_id, $entry_data);

		$form_settings = json_decode($form->form_settings, true);
		$response      = array(
			'message' => $form_settings['gfm_success_message'] ?? esc_html__('Thank you for your submission.', 'genform'),
		);

		if (($form_settings['gfm_con_type'] ?? '') === 'redirect' && ! empty($form_settings['gfm_redirect_url'])) {
			$response['redirect'] = esc_url_raw($form_settings['gfm_redirect_url']);
		}

		wp_send_json_success($response);
	}

	/**
	 * Centralized error response helper.
	 */
	private function sendError(string $message, int $form_id = 0): void
	{
		if ($form_id) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$form_row = $wpdb->get_row($wpdb->prepare("SELECT form_settings FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id));
			if ($form_row) {
				$form_settings = json_decode($form_row->form_settings, true);
				if (! empty($form_settings['gfm_error_message'])) {
					$message = $form_settings['gfm_error_message'];
				}
			}
		}
		wp_send_json_error(array('message' => $message));
	}

	/**
	 * Iterates over $_POST to collect and sanitize form keys.
	 *
	 * Nonce verification is performed by the calling method (handleSubmission)
	 * before this function is invoked. Therefore, it is safe to access $_POST here.
	 */
	private function getSanitizedData(): array
	{
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$clean_data = array();
		foreach ($_POST as $key => $value) {
			if (str_starts_with($key, 'gfm_')) {
				$field_key                = str_replace('gfm_', '', $key);
				$clean_data[$field_key] = is_array($value) ? array_map('sanitize_text_field', wp_unslash($value)) : sanitize_text_field(wp_unslash($value));
			}
		}
		// phpcs:enable
		return $clean_data;
	}

	/**
	 * Admin AJAX handler for single entry deletion.
	 */
	public function handleDeleteEntry(): void
	{
		check_ajax_referer('genform_admin_nonce', 'nonce');
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Unauthorized', 'genform')));
		}
		$entry_id = isset($_POST['entry_id']) ? absint(wp_unslash($_POST['entry_id'])) : 0;
		if ($entry_id) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete("{$wpdb->prefix}genform_entries", array('id' => $entry_id));
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Admin AJAX handler for trashing an entry.
	 */
	public function handleTrashEntry(): void
	{
		check_ajax_referer('genform_admin_nonce', 'nonce');
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Unauthorized', 'genform')));
		}
		$entry_id = isset($_POST['entry_id']) ? absint(wp_unslash($_POST['entry_id'])) : 0;
		if ($entry_id) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update("{$wpdb->prefix}genform_entries", array('status' => 'trash'), array('id' => $entry_id));
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Admin AJAX handler for deleting a form.
	 */
	public function handleDeleteFormAjax(): void
	{
		check_ajax_referer('genform_admin_nonce', 'nonce');
		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Unauthorized', 'genform')));
		}
		$form_id = isset($_POST['form_id']) ? absint(wp_unslash($_POST['form_id'])) : 0;
		if ($form_id) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete("{$wpdb->prefix}genform_forms", array('id' => $form_id));
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete("{$wpdb->prefix}genform_entries", array('form_id' => $form_id));
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Handles bulk operations from the entries list table.
	 */
	public function processBulkActions(): void
	{
		$page    = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		$action  = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
		$action2 = isset($_GET['action2']) ? sanitize_text_field(wp_unslash($_GET['action2'])) : '';

		if ('genform-entries' !== $page) {
			return;
		}

		$bulk_action = $action ?: $action2;
		if (! in_array($bulk_action, array('trash', 'restore', 'delete'), true)) {
			return;
		}

		if (! current_user_can('manage_options')) {
			return;
		}

		check_admin_referer('bulk-entries');

		$entry_ids = isset($_GET['entry']) ? array_map('absint', (array) wp_unslash($_GET['entry'])) : array();
		if (! empty($entry_ids)) {
			global $wpdb;
			$status_label = '';

			// Prepare placeholders explicitly to avoid scanner warnings about interpolation.
			$placeholders = implode(',', array_fill(0, count($entry_ids), '%d'));

			if ('trash' === $bulk_action) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}genform_entries SET status = 'trash' WHERE id IN ($placeholders)", ...$entry_ids));
				$status_label = 'trashed';
			} elseif ('restore' === $bulk_action) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}genform_entries SET status = 'read' WHERE id IN ($placeholders)", ...$entry_ids));
				$status_label = 'restored';
			} elseif ('delete' === $bulk_action) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}genform_entries WHERE id IN ($placeholders)", ...$entry_ids));
				$status_label = 'deleted';
			}

			$redirect_url = add_query_arg(array($status_label => count($entry_ids)), admin_url('admin.php?page=genform-entries'));
			if ('trash' !== $bulk_action) {
				$redirect_url = add_query_arg('status', 'trash', $redirect_url);
			}
			wp_safe_redirect($redirect_url);
			exit;
		}
	}

	/**
	 * Admin AJAX handler for marking an entry as read.
	 */
	public function handleMarkAsRead(): void
	{
		check_ajax_referer('genform_admin_nonce', 'nonce');
		if (! current_user_can('manage_options')) {
			wp_send_json_error();
		}
		$entry_id = isset($_POST['entry_id']) ? absint(wp_unslash($_POST['entry_id'])) : 0;
		if ($entry_id) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update("{$wpdb->prefix}genform_entries", array('status' => 'read'), array('id' => $entry_id));
			wp_send_json_success();
		}
		wp_send_json_error();
	}
}
