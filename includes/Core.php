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
		add_action( 'admin_init', array( self::class, 'activate' ) );
		add_action( 'admin_menu', array( $this, 'registerMenus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminAssets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendAssets' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'registerDashboardWidget' ) );
		add_action( 'admin_bar_menu', array( $this, 'addAdminBarMenu' ), 999 );
		add_action( 'admin_footer', array( $this, 'outputGlobalModals' ) );

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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$forms_count   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}genform_forms" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$entries_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}genform_entries" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$recent_entries = $wpdb->get_results( "SELECT e.*, f.form_name FROM {$wpdb->prefix}genform_entries e LEFT JOIN {$wpdb->prefix}genform_forms f ON e.form_id = f.id ORDER BY e.created_at DESC LIMIT 5" );
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
	}

	public function renderFormsList(): void {
		include GENFORM_PATH . 'admin/views/forms-list.php';
	}

	public function renderEntries(): void {
		include GENFORM_PATH . 'admin/views/entries-list.php';
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
		$forms = $wpdb->get_results( "SELECT id, form_name FROM {$wpdb->prefix}genform_forms WHERE status = 'active'" );
		$options = array();
		foreach ( $forms as $f ) {
			$options[] = array( 'label' => $f->form_name, 'value' => (int) $f->id );
		}

		wp_localize_script(
			'genform-block',
			'genformBlockData',
			array(
				'forms' => $options,
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
		wp_enqueue_script( 'genform-admin', GENFORM_URL . 'assets/js/admin.js', array( 'wp-lists', 'common' ), GENFORM_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

		if ( str_contains( $hook, 'genform-builder' ) ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'genform-builder', GENFORM_URL . 'assets/js/form-builder.js', array( 'jquery-ui-sortable' ), GENFORM_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

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
						'required' => esc_html__( 'Required', 'genform' ),
						'label'    => esc_html__( 'Label', 'genform' ),
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
					'entry_details'  => esc_html__( 'Entry Details', 'genform' ),
				),
			)
		);
	}

	/**
	 * Get CSS for custom branding based on global settings.
	 */
	private function getDynamicStylesCss(): string {
		$opts = get_option( 'genform_general', array() );
		$p    = $opts['primary_color'] ?? '#6366f1';
		$d    = $this->adjustBrightness( $p, -20 );
		return ":root{--gfm-primary:{$p}!important;--gfm-primary-dark:{$d}!important}";
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
	 */
	public function enqueueFrontendAssets(): void {
		wp_enqueue_style( 'genform-frontend', GENFORM_URL . 'assets/css/frontend.css', array(), GENFORM_VERSION );
		wp_add_inline_style( 'genform-frontend', $this->getDynamicStylesCss() );
		wp_enqueue_script( 'genform-frontend', GENFORM_URL . 'assets/js/frontend.js', array(), GENFORM_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script( 'genform-frontend', 'genform', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
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
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_forms );
		dbDelta( $sql_entries );
	}
}
