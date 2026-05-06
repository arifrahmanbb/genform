<?php

/**
 * Core Plugin Class
 *
 * This class handles the initialization of the plugin, including menus,
 * assets enqueuing, and component loading.
 *
 * @package GenForm
 */

namespace GenForm;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GenForm\Admin\Builder;
use GenForm\Admin\Settings;
use GenForm\Admin\EntriesTable;
use GenForm\Handlers\FormHandler;
use GenForm\Handlers\ExportHandler;
use GenForm\Integrations\Block;
use GenForm\Integrations\Shortcode;
use GenForm\Templates\Manager as TemplateManager;
use GenForm\Templates\Renderer as TemplateRenderer;
use GenForm\Pro\FeatureGate;

final class Core {


	/**
	 * Unique instance of the class.
	 */
	private static ?self $instance = null;

	/**
	 * Private constructor to enforce Singleton pattern.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Get the singleton instance.
	 */
	public static function instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init(): void {
		$this->runVersionUpgrade();
		add_action( 'admin_menu', array( $this, 'registerMenus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminAssets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendAssets' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'registerDashboardWidget' ) );
		add_action( 'admin_bar_menu', array( $this, 'addAdminBarMenu' ), 999 );
		add_action( 'admin_footer', array( $this, 'outputGlobalModals' ) );

		// Background email delivery via WP-Cron.
		add_action( 'genform_send_email_async',        array( 'GenForm\Integrations\Email', 'sendAsync' ),             10, 2 );
		add_action( 'genform_send_confirmation_async', array( 'GenForm\Integrations\Email', 'sendConfirmationAsync' ), 10, 2 );

		$this->loadComponents();
	}

	/**
	 * Add short links to the WordPress Admin Bar.
	 */
	public function addAdminBarMenu( $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'genform',
				'title' => '<span class="ab-icon dashicons dashicons-feedback"></span> GenForm',
				'href'  => admin_url( 'admin.php?page=genform' ),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id'     => 'genform-new',
				'parent' => 'genform',
				'title'  => esc_html__( 'Add New Form', 'genform' ),
				'href'   => admin_url( 'admin.php?page=genform-builder' ),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id'     => 'genform-entries',
				'parent' => 'genform',
				'title'  => esc_html__( 'View Entries', 'genform' ),
				'href'   => admin_url( 'admin.php?page=genform-entries' ),
			)
		);
	}

	/**
	 * Register the Dashboard Summary Widget.
	 */
	public function registerDashboardWidget(): void {
		wp_add_dashboard_widget(
			'genform_dashboard_widget',
			esc_html__( 'GenForm Overview', 'genform' ),
			array( $this, 'renderDashboardWidget' )
		);
	}

	/**
	 * Render the content of the Dashboard Widget.
	 */
	public function renderDashboardWidget(): void {
		global $wpdb;

		// Use object cache for dashboard stats.
		$cache_key = 'genform_dashboard_stats';
		$stats = wp_cache_get( $cache_key, 'genform' );

		if ( false === $stats ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$forms_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}genform_forms" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$entries_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}genform_entries" );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$recent_entries = $wpdb->get_results( "SELECT e.*, f.form_name FROM {$wpdb->prefix}genform_entries e LEFT JOIN {$wpdb->prefix}genform_forms f ON e.form_id = f.id ORDER BY e.created_at DESC LIMIT 5" );

			$stats = array(
				'forms'   => absint( $forms_count ),
				'entries' => absint( $entries_count ),
				'recent'  => $recent_entries,
			);

			wp_cache_set( $cache_key, $stats, 'genform', 300 );
		}

		$forms_count    = $stats['forms'];
		$entries_count  = $stats['entries'];
		$recent_entries = $stats['recent'];
		?>
		<div class="gfm-dashboard-widget">
			<div class="gfm-db-stats">
				<div class="stat">
					<strong><?php echo esc_html( $forms_count ); ?></strong>
					<span><?php esc_html_e( 'Total Forms', 'genform' ); ?></span>
				</div>
				<div class="stat">
					<strong><?php echo esc_html( $entries_count ); ?></strong>
					<span><?php esc_html_e( 'Total Entries', 'genform' ); ?></span>
				</div>
			</div>

			<h4><?php esc_html_e( 'Recent Entries', 'genform' ); ?></h4>

			<?php if ( empty( $recent_entries ) ) : ?>
				<p><?php esc_html_e( 'No entries yet.', 'genform' ); ?></p>
			<?php else : ?>
				<ul>
					<?php foreach ( $recent_entries as $genform_e ) : ?>
						<li>
							<div class="entry-info">
								<strong><?php echo esc_html( $genform_e->form_name ?: esc_html__( 'Deleted Form', 'genform' ) ); ?></strong>
								<span>- <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $genform_e->created_at ) ) ); ?></span>
							</div>
							<a href="<?php echo esc_url( admin_url( "admin.php?page=genform-entries&form_id={$genform_e->form_id}" ) ); ?>" class="gfm-view-link">
								<?php esc_html_e( 'View', 'genform' ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="gfm-db-footer">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-entries' ) ); ?>" class="button">
						<?php esc_html_e( 'All Entries', 'genform' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Instantiate plugin sub-components.
	 */
	private function loadComponents(): void {
		new Builder();
		new Settings();
		new FormHandler();
		new ExportHandler();
		new Block();
		new Shortcode();
		TemplateManager::registerHooks();
	}

	/**
	 * Register the main admin menu and submenus.
	 */
	public function registerMenus(): void {
		add_menu_page(
			esc_html__( 'GenForm', 'genform' ),
			'GenForm',
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

		// Hidden pages (no menu items).
		add_submenu_page( null, esc_html__( 'Form Preview', 'genform' ), '', 'manage_options', 'genform-preview', array( $this, 'renderPreview' ) );
		add_submenu_page( null, esc_html__( 'Entry Detail', 'genform' ), '', 'manage_options', 'genform-entry-detail', array( $this, 'renderEntryDetail' ) );
	}

	public function renderFormsList(): void {
		include GENFORM_PATH . 'admin/views/forms-list.php';
	}

	public function renderEntries(): void {
		include GENFORM_PATH . 'admin/views/entries-list.php';
	}

	/**
	 * Render the form preview page.
	 */
	public function renderPreview(): void {
		include GENFORM_PATH . 'admin/views/form-preview.php';
	}

	/**
	 * Render the entry detail page.
	 */
	public function renderEntryDetail(): void {
		include GENFORM_PATH . 'admin/views/entry-detail.php';
	}

	/**
	 * Output common modal structures to the admin footer.
	 */
	public function outputGlobalModals(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! str_contains( $screen->id, 'genform' ) ) {
			return;
		}
		?>
		<!-- Entry Detail Modal -->
		<div id="gfm-entry-modal" class="gfm-modal gfm-hidden">
			<div class="gfm-modal-content gfm-modal-large">
				<div class="gfm-modal-header">
					<h3><!-- JS Dynamic Content --></h3>
					<span class="gfm-close-modal dashicons dashicons-no"></span>
				</div>
				<div class="gfm-modal-body" id="gfm-modal-body"></div>
			</div>
		</div>

		<!-- Custom Confirm Modal -->
		<div id="gfm-confirm-modal" class="gfm-modal gfm-hidden">
			<div class="gfm-modal-content gfm-modal-mini">
				<div class="gfm-modal-body gfm-confirm-body text-center">
					<div class="gfm-confirm-icon-box">
						<span class="dashicons dashicons-warning"></span>
					</div>
					<h3 id="gfm-confirm-title"><?php esc_html_e( 'Are you sure?', 'genform' ); ?></h3>
					<p id="gfm-confirm-desc"><?php esc_html_e( 'This action cannot be undone.', 'genform' ); ?></p>
					<div class="gfm-confirm-actions">
						<button type="button" id="gfm-confirm-cancel" class="gfm-btn gfm-btn-outline"><?php esc_html_e( 'Cancel', 'genform' ); ?></button>
						<button type="button" id="gfm-confirm-ok" class="gfm-btn gfm-btn-danger"><?php esc_html_e( 'Yes, Delete', 'genform' ); ?></button>
					</div>
				</div>
			</div>
		</div>

		<?php
		// Template Library Modals.
		TemplateRenderer::render();
		?>

		<?php if ( ! FeatureGate::isProActive() ) : ?>
	<?php FeatureGate::renderUpgradeModal(); ?>
	<?php endif; ?>
	<?php
	}

	/**
	 * Enqueue styles and scripts for the admin area.
	 */
	public function enqueueAdminAssets( string $hook ): void {
		// Register the block editor script separately.
		wp_register_script(
			'genform-block',
			GENFORM_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ),
			GENFORM_VERSION,
			true
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$forms         = $wpdb->get_results( "SELECT id, form_name FROM {$wpdb->prefix}genform_forms WHERE status = 'active' LIMIT 500" );
		$block_options = array();
		foreach ( $forms as $form_item ) {
			$block_options[] = array(
				'label' => $form_item->form_name,
				'value' => (int) $form_item->id,
			);
		}

		wp_localize_script(
			'genform-block',
			'genformBlockData',
			array(
				'forms' => $block_options,
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
		wp_add_inline_style( 'genform-admin', $this->getDynamicStylesCss() );

		/**
		 * Fires after GenForm admin scripts are enqueued.
		 * Pro plugin uses this to enqueue its own assets.
		 *
		 * @param string $hook The current admin page hook suffix.
		 */
		do_action( 'genform_admin_scripts', $hook );
		wp_enqueue_script(
			'genform-admin',
			GENFORM_URL . 'assets/js/admin.js',
			array( 'wp-lists', 'common' ),
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
				array( 'jquery-ui-sortable', 'genform-admin' ),
				GENFORM_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$genform_requested_id = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$genform_form_data = $genform_requested_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}genform_forms WHERE id = %d", $genform_requested_id ) ) : null;

			wp_localize_script(
				'genform-builder',
				'genformBuilder',
				array(
					'initialData'     => $genform_form_data ? json_decode( $genform_form_data->form_data, true ) : null,
					'initialSettings' => $genform_form_data ? json_decode( $genform_form_data->form_settings, true ) : null,
					'i18n'            => array(
						// Field type labels.
						'label_text'            => esc_html__( 'Text Field', 'genform' ),
						'label_email'           => esc_html__( 'Email Address', 'genform' ),
						'label_textarea'        => esc_html__( 'Paragraph', 'genform' ),
						'label_number'          => esc_html__( 'Number', 'genform' ),
						'label_select'          => esc_html__( 'Dropdown', 'genform' ),
						'label_radio'           => esc_html__( 'Single Choice', 'genform' ),
						'label_checkbox'        => esc_html__( 'Checkboxes', 'genform' ),
						'label_date'            => esc_html__( 'Date', 'genform' ),
						'label_url'             => esc_html__( 'Website', 'genform' ),
						'label_tel'             => esc_html__( 'Phone Number', 'genform' ),
						'label_hidden'          => esc_html__( 'Hidden Field', 'genform' ),
						'label_password'        => esc_html__( 'Password', 'genform' ),
						// Setting labels.
						'label'                 => esc_html__( 'Label', 'genform' ),
						'field_name'            => esc_html__( 'Field Name', 'genform' ),
						'field_name_desc'       => esc_html__( 'Used in submissions and email tags.', 'genform' ),
						'placeholder'           => esc_html__( 'Placeholder', 'genform' ),
						'default_value'         => esc_html__( 'Default Value', 'genform' ),
						'help_text'             => esc_html__( 'Help Text', 'genform' ),
						'help_text_placeholder' => esc_html__( 'Displayed below the field to guide the user.', 'genform' ),
						'width'                 => esc_html__( 'Width', 'genform' ),
						'width_full'            => esc_html__( 'Full', 'genform' ),
						'css_class'             => esc_html__( 'CSS Class', 'genform' ),
						'required'              => esc_html__( 'Required', 'genform' ),
						'settings'              => esc_html__( 'Settings', 'genform' ),
						// Type-specific.
						'rows'                  => esc_html__( 'Rows', 'genform' ),
						'min_value'             => esc_html__( 'Min Value', 'genform' ),
						'max_value'             => esc_html__( 'Max Value', 'genform' ),
						'step'                  => esc_html__( 'Step', 'genform' ),
						'min_length'            => esc_html__( 'Min Length', 'genform' ),
						'max_length'            => esc_html__( 'Max Length', 'genform' ),
						'hidden_desc'           => esc_html__( 'This value is sent with the form but not visible to users.', 'genform' ),
						// Options.
						'options_title'         => esc_html__( 'Options', 'genform' ),
						'add_option'            => esc_html__( 'Add Option', 'genform' ),
						'opt_label'             => esc_html__( 'Label', 'genform' ),
						'opt_value'             => esc_html__( 'Value', 'genform' ),
						'option_1'              => esc_html__( 'Option 1', 'genform' ),
						'option_2'              => esc_html__( 'Option 2', 'genform' ),
						'new_option'            => esc_html__( 'New Option', 'genform' ),
						// Actions.
						'duplicate'             => esc_html__( 'Duplicate', 'genform' ),
						'edit'                  => esc_html__( 'Edit', 'genform' ),
						'delete'                => esc_html__( 'Delete', 'genform' ),
						'field_duplicated'      => esc_html__( 'Field duplicated.', 'genform' ),
						'field_removed'         => esc_html__( 'Field removed successfully.', 'genform' ),
						'delete_field_title'    => esc_html__( 'Delete Field?', 'genform' ),
						'delete_field_desc'     => esc_html__( 'This field and its configuration will be removed from the builder.', 'genform' ),
						// Empty canvas.
						'empty_title'           => esc_html__( 'Start Building Your Form', 'genform' ),
						'empty_desc'            => esc_html__( 'Click a field type from the sidebar to get started.', 'genform' ),
						'example_prefix'        => esc_html__( 'e.g.', 'genform' ),
					),
				)
			);
		}

		wp_localize_script(
			'genform-admin',
			'genform',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'genform_admin_nonce' ),
				'builder_url' => admin_url( 'admin.php?page=genform-builder' ),
				'edit_nonce'  => wp_create_nonce( 'genform_edit_form' ),
				'templates'   => TemplateManager::getAll(),
				'i18n'        => array(
					'confirm_delete' => esc_html__( 'Are you sure?', 'genform' ),
					'entry_details'  => esc_html__( 'Entry Details', 'genform' ),
				),
			)
		);
	}

	/**
	 * Get CSS for custom branding based on global settings.
	 */
	private function getDynamicStylesCss(): string {
		$options       = get_option( 'genform_general', array() );
		$primary_color = $options['primary_color'] ?? '#6366f1';
		$dark_color    = $this->adjustBrightness( $primary_color, -20 );
		return ":root{--gfm-primary:{$primary_color}!important;--gfm-primary-dark:{$dark_color}!important}";
	}

	/**
	 * Helper function to adjust brightness of hex colors for dynamic styling.
	 */
	private function adjustBrightness( string $hex, int $steps ): string {
		$steps = max( -255, min( 255, $steps ) );
		$hex   = str_replace( '#', '', $hex );
		if ( 3 === strlen( $hex ) ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}
		$r = max( 0, min( 255, hexdec( substr( $hex, 0, 2 ) ) + $steps ) );
		$g = max( 0, min( 255, hexdec( substr( $hex, 2, 2 ) ) + $steps ) );
		$b = max( 0, min( 255, hexdec( substr( $hex, 4, 2 ) ) + $steps ) );
		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) . str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) . str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}

	/**
	 * Enqueue assets for the site front end.
	 * Only loads CSS/JS when a form is actually present on the page.
	 */
	public function enqueueFrontendAssets(): void {
		global $post;

		$has_form = false;
		if ( $post instanceof \WP_Post ) {
			$has_form = has_shortcode( $post->post_content, 'genform' ) || has_block( 'genform/form-block', $post );
		}

		if ( ! $has_form ) {
			return;
		}

		wp_enqueue_style( 'genform-frontend', GENFORM_URL . 'assets/css/frontend.css', array(), GENFORM_VERSION );
		wp_add_inline_style( 'genform-frontend', $this->getDynamicStylesCss() );
		wp_enqueue_script(
			'genform-frontend',
			GENFORM_URL . 'assets/js/frontend.js',
			array(),
			GENFORM_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		$genform_options  = get_option( 'genform_general', array() );
		$recaptcha_key    = $genform_options['recaptcha_site_key'] ?? '';
		if ( $recaptcha_key ) {
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js',
				array(),
				null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				array( 'in_footer' => false )
			);
		}

		wp_localize_script(
			'genform-frontend',
			'genform',
			array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'recaptcha_key'  => $recaptcha_key,
				'i18n'           => array(
					'submitting'        => esc_html__( 'Submitting...', 'genform' ),
					'required_error'    => esc_html__( 'This field is required.', 'genform' ),
					'email_error'       => esc_html__( 'Please enter a valid email address.', 'genform' ),
					'url_error'         => esc_html__( 'Please enter a valid URL (e.g. https://example.com).', 'genform' ),
					'tel_error'         => esc_html__( 'Please enter a valid phone number.', 'genform' ),
					'number_error'      => esc_html__( 'Please enter a valid number.', 'genform' ),
					'minlength_error'   => esc_html__( 'Please enter at least {min} characters.', 'genform' ),
					'maxlength_error'   => esc_html__( 'Please enter no more than {max} characters.', 'genform' ),
					'min_error'         => esc_html__( 'Value must be at least {min}.', 'genform' ),
					'max_error'         => esc_html__( 'Value must be no more than {max}.', 'genform' ),
					'checkbox_error'    => esc_html__( 'Please select at least one option for required checkbox fields.', 'genform' ),
					'gdpr_error'        => esc_html__( 'Please accept the consent checkbox to proceed.', 'genform' ),
					'recaptcha_error'   => esc_html__( 'Please complete the reCAPTCHA verification.', 'genform' ),
					'generic_error'     => esc_html__( 'An error occurred. Please try again.', 'genform' ),
					'unknown_error'     => esc_html__( 'An unknown error occurred. Please try again.', 'genform' ),
				),
			)
		);
	}

	/**
	 * Compare stored version with current and run database upgrade if changed.
	 */
	private function runVersionUpgrade(): void {
		$installed_version = get_option( 'genform_version', '' );
		if ( $installed_version !== GENFORM_VERSION ) {
			self::activate();
			update_option( 'genform_version', GENFORM_VERSION );
		}
	}

	/**
	 * Run required installation procedures (table creation).
	 */
	public static function activate(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_forms     = "{$wpdb->prefix}genform_forms";
		$table_entries   = "{$wpdb->prefix}genform_entries";

		// Note: dbDelta requires two spaces before PRIMARY KEY and specific indentation.
		$sql_forms = "CREATE TABLE $table_forms (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_name varchar(255) NOT NULL,
			form_data longtext NOT NULL,
			form_settings longtext,
			status varchar(20) DEFAULT 'active' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sql_entries = "CREATE TABLE $table_entries (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id bigint(20) unsigned NOT NULL,
			entry_data longtext NOT NULL,
			entry_metadata longtext,
			user_ip varchar(100) DEFAULT '' NOT NULL,
			user_agent varchar(255) DEFAULT '' NOT NULL,
			status varchar(20) DEFAULT 'unread' NOT NULL,
			starred tinyint(1) DEFAULT 0 NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id),
			KEY status (status)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_forms );
		dbDelta( $sql_entries );
	}
}
