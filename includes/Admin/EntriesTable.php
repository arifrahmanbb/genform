<?php
/**
 * Entries List Table Handler
 *
 * Implements the WP_List_Table for displaying form submissions.
 *
 * @package GenForm
 */

namespace GenForm\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class EntriesTable extends \WP_List_Table {

	/**
	 * Setup singular and plural names and AJAX capability.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'entry',
				'plural'   => 'entries',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Prepare the list items, including querying for data and preparing pagination.
	 */
	public function prepare_items() {
		global $wpdb;

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$where = array();

		// Handle filtering by status (trash vs active).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		if ( 'trash' === $status ) {
			$where[] = $wpdb->prepare( 'e.status = %s', 'trash' );
		} elseif ( 'unread' === $status ) {
			$where[] = $wpdb->prepare( 'e.status = %s', 'unread' );
		} else {
			$where[] = $wpdb->prepare( 'e.status != %s', 'trash' );
		}

		// Filter by specific Form ID.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
		if ( $form_id ) {
			$where[] = $wpdb->prepare( 'e.form_id = %d', $form_id );
		}

		// Search input.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		if ( $search ) {
			$where[] = $wpdb->prepare( 'e.entry_data LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}

		$where_sql = '';
		if ( ! empty( $where ) ) {
			$where_sql = ' WHERE ' . implode( ' AND ', $where );
		}

		// Sorting validation - strictly whitelisted column names only.
		$allowed_orderby = array( 'id', 'created_at', 'form_name' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby_input = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : '';
		$orderby_col   = in_array( $orderby_input, $allowed_orderby, true ) ? $orderby_input : 'created_at';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_input = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : '';
		$order_dir   = ( 'ASC' === $order_input ) ? 'ASC' : 'DESC';

		// Build the full SQL query.
		// The $where_sql contains only properly prepared segments from $wpdb->prepare().
		// The $orderby_col and $order_dir are strictly validated against hardcoded whitelists.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared

		// Using $wpdb->prefix directly is acceptable as it's a core WordPress property.
		// The ORDER BY clause uses only whitelisted literal column names (id, created_at, form_name).
		// The WHERE clause is built entirely from $wpdb->prepare() segments.
		// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT e.*, f.form_name FROM {$wpdb->prefix}genform_entries e LEFT JOIN {$wpdb->prefix}genform_forms f ON e.form_id = f.id {$where_sql} ORDER BY {$orderby_col} {$order_dir} LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		// Count query - $where_sql contains only prepared segments.
		// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
		$total = $wpdb->get_var( "SELECT COUNT(e.id) FROM {$wpdb->prefix}genform_entries e {$where_sql}" );
		// phpcs:enable

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
			)
		);

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
	}

	/**
	 * Generate row status links (All, Unread, Trash).
	 */
	protected function get_views() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$all = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}genform_entries WHERE status != %s", 'trash' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$unr = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}genform_entries WHERE status = %s", 'unread' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$trs = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}genform_entries WHERE status = %s", 'trash' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cur = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		return array(
			'all'    => sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', esc_url( admin_url( 'admin.php?page=genform-entries' ) ), ( '' === $cur ? 'current' : '' ), esc_html__( 'All', 'genform' ), (int) $all ),
			'unread' => sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', esc_url( admin_url( 'admin.php?page=genform-entries&status=unread' ) ), ( 'unread' === $cur ? 'current' : '' ), esc_html__( 'Unread', 'genform' ), (int) $unr ),
			'trash'  => sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', esc_url( admin_url( 'admin.php?page=genform-entries&status=trash' ) ), ( 'trash' === $cur ? 'current' : '' ), esc_html__( 'Trash', 'genform' ), (int) $trs ),
		);
	}

	/**
	 * Defines columns for the table.
	 */
	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'id'            => esc_html__( 'ID', 'genform' ),
			'form_name'     => esc_html__( 'Form Name', 'genform' ),
			'entry_preview' => esc_html__( 'Entry Preview', 'genform' ),
			'created_at'    => esc_html__( 'Date', 'genform' ),
			'actions'       => esc_html__( 'Actions', 'genform' ),
		);
	}

	/**
	 * Configures sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'id'         => array( 'id', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Configures bulk action options.
	 */
	public function get_bulk_actions() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		if ( 'trash' === $status ) {
			return array(
				'restore' => esc_html__( 'Restore', 'genform' ),
				'delete'  => esc_html__( 'Delete permanently', 'genform' ),
			);
		}
		return array( 'trash' => esc_html__( 'Move to Trash', 'genform' ) );
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="entry[]" value="%s" />', esc_attr( $item->id ) );
	}

	/**
	 * Renders the ID and status indicator column.
	 */
	public function column_id( $item ) {
		return sprintf(
			'<div class="gfm-id-cell">%s<span class="gfm-muted-id">#%d</span></div>',
			( $item->status === 'unread' ? '<span class="gfm-unread-dot-badge"></span>' : '' ),
			(int) $item->id
		);
	}

	public function column_form_name( $item ) {
		return sprintf( '<strong>%s</strong>', esc_html( $item->form_name ?: esc_html__( 'Deleted Form', 'genform' ) ) );
	}

	/**
	 * Displays a snippet of the submission data.
	 */
	public function column_entry_preview( $item ) {
		$d = json_decode( $item->entry_data, true );
		if ( empty( $d ) || ! is_array( $d ) ) {
			return '—';
		}
		$first_val = reset( $d );
		$display_val = is_array( $first_val ) ? implode( ', ', $first_val ) : $first_val;

		return esc_html( wp_trim_words( $display_val, 10 ) );
	}

	public function column_created_at( $item ) {
		return esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->created_at ) ) );
	}

	/**
	 * Renders action row buttons (View, Trash, Restore, Delete).
	 */
	public function column_actions( $item ) {
		$d_e = json_decode( $item->entry_data, true ) ?: array();
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT form_data FROM {$wpdb->prefix}genform_forms WHERE id = %d", $item->form_id ) );
		$r_d = array();

		// Attempt to map data back to user-friendly field labels.
		if ( $row && $row->form_data ) {
			$cfg = json_decode( $row->form_data, true );
			if ( isset( $cfg['fields'] ) && is_array( $cfg['fields'] ) ) {
				foreach ( $cfg['fields'] as $f ) {
					$n = sanitize_title( $f['name'] ?? '' );
					if ( $n && isset( $f['label'], $d_e[ $n ] ) ) {
						$r_d[ $f['label'] ] = $d_e[ $n ];
					}
				}
			}
		}

		if ( empty( $r_d ) ) {
			foreach ( $d_e as $k => $v ) {
				$r_d[ ucwords( str_replace( array( '_', '-' ), ' ', $k ) ) ] = $v;
			}
		}

		if ( $item->status === 'trash' ) {
			return sprintf(
				'<div class="gfm-table-actions"><a href="%1$s" class="gfm-action-icon" title="%2$s"><span class="dashicons dashicons-undo"></span></a><a href="#" class="gfm-action-icon gfm-delete-entry-permanent" data-id="%3$d" title="%4$s"><span class="dashicons dashicons-trash"></span></a></div>',
				esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-entries&status=trash&action=restore&entry=' . $item->id ), 'bulk-entries' ) ),
				esc_attr__( 'Restore', 'genform' ),
				(int) $item->id,
				esc_attr__( 'Delete Permanently', 'genform' )
			);
		}

		return sprintf(
			'<div class="gfm-table-actions"><a href="#" class="gfm-view-entry gfm-action-icon" data-payload=\'%1$s\' data-metadata=\'%2$s\' data-form=\'%3$s\' title="%4$s"><span class="dashicons dashicons-visibility"></span></a><a href="%5$s" class="gfm-action-icon gfm-action-trash-simple" title="%6$s"><span class="dashicons dashicons-trash"></span></a></div>',
			esc_attr( wp_json_encode( $r_d ) ),
			esc_attr( $item->entry_metadata ?: '{}' ),
			esc_attr( $item->form_name ?: esc_html__( 'Deleted Form', 'genform' ) ),
			esc_attr__( 'View details', 'genform' ),
			esc_url( wp_nonce_url( admin_url( 'admin.php?page=genform-entries&action=trash&entry=' . $item->id ), 'bulk-entries' ) ),
			esc_attr__( 'Move to Trash', 'genform' )
		);
	}

	/**
	 * Renders form selection filters in the table header.
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$fs  = $wpdb->get_results( "SELECT id, form_name FROM {$wpdb->prefix}genform_forms ORDER BY form_name ASC" );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cur = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
		?>
		<div class="alignleft actions">
			<select name="form_id" id="filter-by-form">
				<option value="0"><?php esc_html_e( 'All Forms', 'genform' ); ?></option>
				<?php foreach ( $fs as $f ) : ?>
					<option value="<?php echo esc_attr( $f->id ); ?>" <?php selected( $cur, $f->id ); ?>><?php echo esc_html( $f->form_name ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php esc_attr_e( 'Filter', 'genform' ); ?>">
		</div>
		<?php
	}

	/**
	 * Renders the empty list placeholder.
	 */
	public function no_items() {
		?>
		<div class="gfm-empty-state">
			<span class="dashicons dashicons-database"></span>
			<h2><?php esc_html_e( 'No submissions yet', 'genform' ); ?></h2>
			<p><?php esc_html_e( 'Once people start filling out your forms, they will appear here.', 'genform' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-builder' ) ); ?>" class="gfm-btn gfm-btn-primary">
				<?php esc_html_e( 'Add New Form', 'genform' ); ?>
			</a>
		</div>
		<?php
	}
}
