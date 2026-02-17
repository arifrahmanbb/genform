<?php

/**
 * Form Builder Engine
 *
 * Handles backend operations for adding, editing, saving, and duplicating forms.
 *
 * @package GenForm
 */

namespace GenForm\Admin;

if (! defined('ABSPATH')) {
	exit;
}

final class Builder
{

	/**
	 * Builder constructor to initialize admin hooks.
	 */
	public function __construct()
	{
		add_action('admin_init', [$this, 'handleSave']);
		add_action('admin_init', [$this, 'handleDeleteForm']);
		add_action('admin_init', [$this, 'handleDuplicate']);
	}

	/**
	 * Handles cloning an existing form into a new entry.
	 */
	public function handleDuplicate(): void
	{
		$page      = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		$action    = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
		$form_id   = isset($_GET['form_id']) ? absint(wp_unslash($_GET['form_id'])) : 0;
		$wp_nonce  = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

		if ('genform' !== $page || 'duplicate' !== $action || ! $form_id) {
			return;
		}

		if (! wp_verify_nonce($wp_nonce, 'genform_duplicate_form')) {
			return;
		}

		if (! current_user_can('manage_options')) {
			return;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $form_id));

		if ($form) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				"{$wpdb->prefix}genform_forms",
				[
					'form_name'     => $form->form_name . ' (' . esc_html__('Copy', 'genform') . ')',
					'form_data'     => $form->form_data,
					'form_settings' => $form->form_settings,
					'status'        => 'active',
				]
			);

			wp_safe_redirect(admin_url('admin.php?page=genform&duplicated=1'));
			exit;
		}
	}

	/**
	 * Renders the form builder interface view.
	 */
	public static function render(): void
	{
		include GENFORM_PATH . 'admin/views/form-builder.php';
	}

	/**
	 * Main save logic handler with security verification.
	 */
	public function handleSave(): void
	{
		if (! isset($_POST['genform_save'], $_POST['genform_builder_nonce'])) {
			return;
		}

		$gen_nonce = sanitize_text_field(wp_unslash($_POST['genform_builder_nonce']));
		if (! wp_verify_nonce($gen_nonce, 'genform_save_form')) {
			wp_die(esc_html__('Security check failed.', 'genform'));
		}

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized.', 'genform'));
		}

		$this->saveForm();
	}

	/**
	 * Recursively sanitizes an array to ensure no malicious code is persisted.
	 *
	 * @param mixed $data The data to sanitize.
	 * @return mixed
	 */
	private function sanitizeRecursive($data)
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->sanitizeRecursive($value);
			}
		} else {
			$data = sanitize_text_field($data);
		}
		return $data;
	}

	/**
	 * Core saving procedure and JSON validation.
	 */
	private function saveForm(): void
	{
		$gen_nonce = isset($_POST['genform_builder_nonce']) ? sanitize_text_field(wp_unslash($_POST['genform_builder_nonce'])) : '';
		if (! wp_verify_nonce($gen_nonce, 'genform_save_form')) {
			return;
		}

		global $wpdb;
		$form_id       = isset($_GET['form_id']) ? absint(wp_unslash($_GET['form_id'])) : 0;
		$form_name     = isset($_POST['form_name']) ? sanitize_text_field(wp_unslash($_POST['form_name'])) : '';
		// Input is JSON; it is unslashed here and recursively sanitized after decoding below.
		$raw_data      = isset($_POST['form_data']) ? wp_unslash($_POST['form_data']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$raw_settings  = isset($_POST['form_settings']) ? wp_unslash($_POST['form_settings']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if (! $form_name || ! $raw_data) {
			return;
		}

		$decoded_data = json_decode((string) $raw_data, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_die(esc_html__('Invalid JSON in form field data.', 'genform'));
		}

		$decoded_settings = json_decode((string) $raw_settings, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_die(esc_html__('Invalid JSON in form settings.', 'genform'));
		}

		// Sanitize structured data before DB storage.
		$clean_data     = wp_json_encode($this->sanitizeRecursive($decoded_data));
		$clean_settings = wp_json_encode($this->sanitizeRecursive($decoded_settings));

		$form_params = [
			'form_name'     => $form_name,
			'form_data'     => $clean_data,
			'form_settings' => $clean_settings,
			'status'        => 'active',
		];

		if ($form_id) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update("{$wpdb->prefix}genform_forms", $form_params, ['id' => $form_id]);
			add_settings_error('genform_messages', 'f_upd', esc_html__('Form updated successfully.', 'genform'), 'success');
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert("{$wpdb->prefix}genform_forms", $form_params);
			$new_id = $wpdb->insert_id;
			wp_safe_redirect(admin_url("admin.php?page=genform-builder&action=edit&form_id=$new_id&_wpnonce=" . wp_create_nonce('genform_edit_form')));
			exit;
		}
	}

	/**
	 * Permanently removes a form and its associated entry records.
	 */
	public function handleDeleteForm(): void
	{
		$page      = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		$action    = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
		$form_id   = isset($_GET['form_id']) ? absint(wp_unslash($_GET['form_id'])) : 0;
		$wp_nonce  = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

		if ('genform' !== $page || 'delete' !== $action || ! $form_id) {
			return;
		}

		if (! wp_verify_nonce($wp_nonce, 'genform_delete_form')) {
			return;
		}

		if (! current_user_can('manage_options')) {
			return;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete("{$wpdb->prefix}genform_forms", ['id' => $form_id]);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete("{$wpdb->prefix}genform_entries", ['form_id' => $form_id]);

		wp_safe_redirect(admin_url('admin.php?page=genform'));
		exit;
	}
}
