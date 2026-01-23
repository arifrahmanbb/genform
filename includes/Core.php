<?php
/**
 * Core Class for GenForm
 *
 * @package GenForm
 */


namespace GenForm;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GenForm\Admin\Builder;
use GenForm\Admin\Settings;
use GenForm\Handlers\FormHandler;
use GenForm\Integrations\Block;
use GenForm\Integrations\Shortcode;

/**
 * Core Class for GenForm
 */
final class Core {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Initialize.
	 */
	private function init(): void {
		add_action( 'admin_menu', array( $this, 'registerMenus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminAssets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendAssets' ) );

		$this->loadComponents();
	}

	/**
	 * Load components.
	 */
	private function loadComponents(): void {
		new Builder();
		new Settings();
		new FormHandler();
		new Block();
		new Shortcode();
	}

	/**
	 * Register menus.
	 */
	public function registerMenus(): void {
		add_menu_page(
			esc_html__( 'GenForm', 'genform' ),
			esc_html__( 'GenForm', 'genform' ),
			'manage_options',
			'genform',
			array( $this, 'renderFormsList' ),
			'dashicons-feedback',
			30
		);

		add_submenu_page( 'genform', esc_html__( 'All Forms', 'genform' ), esc_html__( 'All Forms', 'genform' ), 'manage_options', 'genform', array( $this, 'renderFormsList' ) );
		add_submenu_page( 'genform', esc_html__( 'Add New', 'genform' ), esc_html__( 'Add New', 'genform' ), 'manage_options', 'genform-builder', array( Builder::class, 'render' ) );
		add_submenu_page( 'genform', esc_html__( 'Entries', 'genform' ), esc_html__( 'Entries', 'genform' ), 'manage_options', 'genform-entries', array( $this, 'renderEntries' ) );
		add_submenu_page( 'genform', esc_html__( 'Settings', 'genform' ), esc_html__( 'Settings', 'genform' ), 'manage_options', 'genform-settings', array( Settings::class, 'render' ) );
	}

	/**
	 * Render forms list.
	 */
	public function renderFormsList(): void {
		include GENFORM_PATH . 'admin/views/forms-list.php';
	}

	/**
	 * Render entries list.
	 */
	public function renderEntries(): void {
		include GENFORM_PATH . 'admin/views/entries-list.php';
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook The current hook.
	 */
	public function enqueueAdminAssets( string $hook ): void {
		// Register block script separately so it can be used by register_block_type
		wp_register_script(
			'genform-block',
			GENFORM_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ),
			GENFORM_VERSION,
			true
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$forms = $wpdb->get_results( "SELECT id, form_name FROM {$wpdb->prefix}genform_forms WHERE status = 'active'" );
		$form_options = array();
		foreach ( $forms as $form ) {
			$form_options[] = array(
				'label' => $form->form_name,
				'value' => (int) $form->id,
			);
		}

		wp_localize_script(
			'genform-block',
			'genformBlockData',
			array(
				'forms' => $form_options,
				'i18n'  => array(
					'title'         => esc_html__( 'GenForm', 'genform' ),
					'selectForm'    => esc_html__( 'Select Form', 'genform' ),
					'selectDefault' => esc_html__( 'Select a form', 'genform' ),
					'formSettings'  => esc_html__( 'Form Settings', 'genform' ),
					'formIdLabel'   => esc_html__( 'Form ID:', 'genform' ),
					'selectError'   => esc_html__( 'Please select a form from the sidebar.', 'genform' ),
				),
			)
		);

		if ( ! str_contains( $hook, 'genform' ) ) {
			return;
		}

		wp_enqueue_style( 'genform-admin', GENFORM_URL . 'assets/css/admin.css', array(), GENFORM_VERSION );
		wp_enqueue_script(
			'genform-admin',
			GENFORM_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			GENFORM_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		if ( str_contains( $hook, 'genform-builder' ) ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script(
				'genform-builder',
				GENFORM_URL . 'assets/js/form-builder.js',
				array( 'jquery', 'jquery-ui-sortable' ),
				GENFORM_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);

			// Nonce not required for simple GET asset localization in admin.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
			global $wpdb;

			// Direct database query for asset localization.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$form = $id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $id ) ) : null;

			wp_localize_script(
				'genform-builder',
				'genformBuilder',
				array(
					'initialData'     => $form ? json_decode( $form->form_data, true ) : null,
					'initialSettings' => $form ? json_decode( $form->form_settings, true ) : null,
					'i18n'            => array(
						'text'     => esc_html__( 'Text Field', 'genform' ),
						'email'    => esc_html__( 'Email Address', 'genform' ),
						'textarea' => esc_html__( 'Paragraph Text', 'genform' ),
						'number'   => esc_html__( 'Number', 'genform' ),
						'select'   => esc_html__( 'Dropdown', 'genform' ),
						'radio'    => esc_html__( 'Multiple Choice', 'genform' ),
						'checkbox' => esc_html__( 'Checkboxes', 'genform' ),
						'date'     => esc_html__( 'Date', 'genform' ),
						'url'      => esc_html__( 'Website', 'genform' ),
						'tel'      => esc_html__( 'Phone', 'genform' ),
						'newField' => esc_html__( 'New Field', 'genform' ),
						'option'   => esc_html__( 'Option', 'genform' ),
						'value'    => esc_html__( 'Value', 'genform' ),
						'label'    => esc_html__( 'Label', 'genform' ),
						'required' => esc_html__( 'Required', 'genform' ),
						'settings' => esc_html__( 'Settings', 'genform' ),
						'delete'   => esc_html__( 'Delete', 'genform' ),
					),
				)
			);
		}

		wp_localize_script(
			'genform-admin',
			'genform',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'genform_admin_nonce' ),
				'i18n'     => array(
					'confirm_delete' => esc_html__( 'Are you sure?', 'genform' ),
				),
			)
		);
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueueFrontendAssets(): void {
		wp_enqueue_style( 'genform-frontend', GENFORM_URL . 'assets/css/frontend.css', array(), GENFORM_VERSION );
		wp_enqueue_script(
			'genform-frontend',
			GENFORM_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			GENFORM_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_localize_script(
			'genform-frontend',
			'genform',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Activation hook.
	 */
	public static function activate(): void {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$table_forms = $wpdb->prefix . 'genform_forms';
		$sql_forms   = "CREATE TABLE $table_forms (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_name varchar(255) NOT NULL,
            form_data longtext NOT NULL,
            form_settings longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

		$table_entries = $wpdb->prefix . 'genform_entries';
		$sql_entries   = "CREATE TABLE $table_entries (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_id bigint(20) NOT NULL,
            entry_data longtext NOT NULL,
            user_ip varchar(100),
            user_agent varchar(255),
            status varchar(20) DEFAULT 'unread',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_forms );
		dbDelta( $sql_entries );
	}
}
