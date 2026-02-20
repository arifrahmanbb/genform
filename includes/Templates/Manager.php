<?php

/**
 * Template Manager
 *
 * Central registry that collects templates from every category provider
 * inside Library/, applies WordPress filters, and exposes a clean API
 * for retrieval, filtering, and AJAX handling.
 *
 * Architecture inspired by top 3 WP form builders (WPForms, Gravity
 * Forms, Formidable) but uses a category-provider pattern instead of
 * a monolithic class, XML import, or database storage. Each Library/*
 * file is a self-contained category that returns its own templates,
 * making it trivial to add or remove entire categories in the future.
 *
 * @package GenForm
 * @since   1.1.0
 */

namespace GenForm\Templates;

if (! defined('ABSPATH')) {
	exit;
}

use GenForm\Templates\Library\General;
use GenForm\Templates\Library\Business;
use GenForm\Templates\Library\Booking;
use GenForm\Templates\Library\Feedback;
use GenForm\Templates\Library\Marketing;
use GenForm\Templates\Library\Education;
use GenForm\Templates\Library\Healthcare;

final class Manager
{



	/**
	 * Cached templates array after first load.
	 *
	 * @var array<int, array<string, mixed>>|null
	 */
	private static ?array $cache = null;

	/**
	 * Registered category providers (FQCN list).
	 *
	 * Order here determines the order in which categories appear.
	 * Every class must expose a public static `get(): array` method
	 * and a `SLUG` constant.
	 *
	 * @var array<int, class-string>
	 */
	private const PROVIDERS = array(
		General::class,
		Business::class,
		Booking::class,
		Feedback::class,
		Marketing::class,
		Education::class,
		Healthcare::class,
	);



	/**
	 * Get all available form template categories.
	 *
	 * Static "All" entry is always first. Remaining categories are
	 * derived from the PROVIDERS list so adding a new Library file
	 * is the only change needed to introduce a new category.
	 *
	 * @return array<string, string>  slug => label
	 */
	public static function getCategories(): array
	{
		$categories = array(
			'all' => esc_html__('All Templates', 'genform'),
		);

		foreach (self::PROVIDERS as $provider) {
			$slug                = $provider::SLUG;
			$categories[$slug] = esc_html__(ucfirst($slug), 'genform');
		}

		/**
		 * Filter the template category labels.
		 *
		 * @param array<string, string> $categories slug => label pairs.
		 */
		return apply_filters('genform_template_categories', $categories);
	}



	/**
	 * Get all registered form templates across every category.
	 *
	 * Results are cached in memory for the duration of the request
	 * so repeated calls (modal render + JS payload) don't re-build
	 * the array.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function getAll(): array
	{
		if (self::$cache !== null) {
			return self::$cache;
		}

		$templates = array();

		foreach (self::PROVIDERS as $provider) {
			$templates = array_merge($templates, $provider::get());
		}

		/**
		 * Filter the full list of form templates.
		 *
		 * Third-party plugins or themes can append, modify, or remove
		 * templates at this stage.
		 *
		 * @param array<int, array<string, mixed>> $templates
		 */
		self::$cache = apply_filters('genform_form_templates', $templates);

		return self::$cache;
	}

	/**
	 * Retrieve a single template by slug.
	 *
	 * @param string $slug Template slug.
	 * @return array<string, mixed>|null Template data or null if not found.
	 */
	public static function getBySlug(string $slug): ?array
	{
		foreach (self::getAll() as $template) {
			if ($template['slug'] === $slug) {
				return $template;
			}
		}
		return null;
	}

	/**
	 * Retrieve templates filtered by category slug.
	 *
	 * @param string $category Category slug.
	 * @return array<int, array<string, mixed>>
	 */
	public static function getByCategory(string $category): array
	{
		if ('all' === $category) {
			return self::getAll();
		}

		return array_values(
			array_filter(
				self::getAll(),
				static fn(array $tpl): bool => $tpl['category'] === $category
			)
		);
	}

	/**
	 * Get the total number of registered templates.
	 *
	 * @return int
	 */
	public static function count(): int
	{
		return count(self::getAll());
	}



	/**
	 * Register the AJAX hook for creating a form from a template.
	 *
	 * Called once during plugin bootstrap (from Core::loadComponents
	 * or similar).
	 */
	public static function registerHooks(): void
	{
		add_action('wp_ajax_genform_create_from_template', array(self::class, 'ajaxCreateFromTemplate'));
	}

	/**
	 * AJAX handler: create a new form from template data.
	 *
	 * Validates nonce + capability, sanitizes inputs, normalizes the
	 * template fields into the builder's expected format, inserts a
	 * new form row, then returns the redirect URL to the builder page.
	 */
	public static function ajaxCreateFromTemplate(): void
	{
		check_ajax_referer('genform_admin_nonce', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Unauthorized', 'genform')));
		}

		$form_name = isset($_POST['template_name'])
			? sanitize_text_field(wp_unslash($_POST['template_name']))
			: '';

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$fields_raw   = isset($_POST['template_fields']) ? wp_unslash($_POST['template_fields']) : '';
		$settings_raw = isset($_POST['template_settings']) ? wp_unslash($_POST['template_settings']) : '';
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if (! $form_name || ! $fields_raw) {
			wp_send_json_error(array('message' => esc_html__('Invalid template data.', 'genform')));
		}

		$fields   = json_decode($fields_raw, true);
		$settings = json_decode($settings_raw, true);

		if (! is_array($fields)) {
			wp_send_json_error(array('message' => esc_html__('Invalid field configuration.', 'genform')));
		}

		// Normalize template fields into the builder's expected format.
		$normalized = self::normalizeFields($fields);

		global $wpdb;

		// The builder stores form data as { "fields": [...] }.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->insert(
			"{$wpdb->prefix}genform_forms",
			array(
				'form_name'     => $form_name,
				'form_data'     => wp_json_encode(array('fields' => $normalized)),
				'form_settings' => is_array($settings) ? wp_json_encode($settings) : null,
				'status'        => 'active',
			)
		);

		if (! $inserted) {
			wp_send_json_error(array('message' => esc_html__('Failed to create form.', 'genform')));
		}

		$new_form_id = absint($wpdb->insert_id);
		$redirect    = admin_url("admin.php?page=genform-builder&form_id={$new_form_id}");

		wp_send_json_success(array('redirect' => $redirect));
	}



	/**
	 * Normalize template fields into the builder's expected format.
	 *
	 * Template Library files use a minimal format:
	 *   { type, label, required, placeholder, options? }
	 *
	 * The builder expects every field to have:
	 *   { id, type, label, name, placeholder, required, css_class,
	 *     default_value, width, options: [{label, value}] }
	 *
	 * This method bridges the gap so templates load correctly in the
	 * builder.
	 *
	 * @param array<int, array<string, mixed>> $fields Raw template fields.
	 * @return array<int, array<string, mixed>> Builder-ready fields.
	 */
	private static function normalizeFields(array $fields): array
	{
		$normalized = array();
		$counter    = 0;

		foreach ($fields as $field) {
			++$counter;
			$label = $field['label'] ?? esc_html__('New Field', 'genform');
			$type  = $field['type'] ?? 'text';

			// Build a machine-safe name from the label.
			$name = sanitize_title($label) . '_' . $counter;

			// Convert simple string options to {label, value} objects.
			$options = array();
			if (! empty($field['options']) && is_array($field['options'])) {
				foreach ($field['options'] as $opt) {
					if (is_array($opt) && isset($opt['label'])) {
						// Already in builder format.
						$options[] = $opt;
					} else {
						$options[] = array(
							'label' => (string) $opt,
							'value' => sanitize_title((string) $opt),
						);
					}
				}
			}

			$normalized[] = array(
				'id'            => 'field_' . $counter,
				'type'          => $type,
				'label'         => $label,
				'name'          => $name,
				'placeholder'   => $field['placeholder'] ?? '',
				'required'      => ! empty($field['required']),
				'css_class'     => '',
				'default_value' => '',
				'width'         => '100',
				'options'       => $options,
			);
		}

		return $normalized;
	}
}
