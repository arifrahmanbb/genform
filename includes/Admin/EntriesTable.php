<?php
/**
 * Entries Table Class.
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

/**
 * Class EntriesTable.
 */
class EntriesTable extends \WP_List_Table {

	/**
	 * Constructor.
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
	 * Prepare table items.
	 */
	public function prepare_items() {
		global $wpdb;

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$table_entries = $wpdb->prefix . 'genform_entries';
		$table_forms   = $wpdb->prefix . 'genform_forms';

		$query = "SELECT e.*, f.form_name 
                  FROM $table_entries e 
                  LEFT JOIN $table_forms f ON e.form_id = f.id";

		$where = array();
		
		// Status filtering.
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		if ( 'trash' === $status ) {
			$where[] = "e.status = 'trash'";
		} elseif ( 'unread' === $status ) {
			$where[] = "e.status = 'unread'";
		} else {
			$where[] = "e.status != 'trash'";
		}

		// Filter by form.
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		if ( $form_id ) {
			$where[] = $wpdb->prepare( 'e.form_id = %d', $form_id );
		}

		// Search.
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		if ( $search ) {
			$where[] = $wpdb->prepare( 'e.entry_data LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
		}

		if ( ! empty( $where ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}

		// Ordering.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_sql_orderby( $_GET['orderby'] ) : 'created_at';
		$order   = isset( $_GET['order'] ) && 'asc' === strtolower( $_GET['order'] ) ? 'ASC' : 'DESC';
		$query  .= " ORDER BY $orderby $order";

		// Pagination.
		$total_items = $wpdb->get_var( "SELECT COUNT(e.id) FROM $table_entries e WHERE " . ( ! empty( $where ) ? implode( ' AND ', $where ) : '1=1' ) );
		$query      .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, $offset );

		$this->items = $wpdb->get_results( $query );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	/**
	 * Get table status views.
	 */
	protected function get_views() {
		global $wpdb;
		$table = $wpdb->prefix . 'genform_entries';

		$all_count    = $wpdb->get_var( "SELECT COUNT(id) FROM $table WHERE status != 'trash'" );
		$unread_count = $wpdb->get_var( "SELECT COUNT(id) FROM $table WHERE status = 'unread'" );
		$trash_count  = $wpdb->get_var( "SELECT COUNT(id) FROM $table WHERE status = 'trash'" );

		$current = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		$views = array(
			'all'    => sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', admin_url( 'admin.php?page=genform-entries' ), ( '' === $current ? 'current' : '' ), esc_html__( 'All', 'genform' ), $all_count ),
			'unread' => sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', admin_url( 'admin.php?page=genform-entries&status=unread' ), ( 'unread' === $current ? 'current' : '' ), esc_html__( 'Unread', 'genform' ), $unread_count ),
			'trash'  => sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>', admin_url( 'admin.php?page=genform-entries&status=trash' ), ( 'trash' === $current ? 'current' : '' ), esc_html__( 'Trash', 'genform' ), $trash_count ),
		);

		return $views;
	}
	
	/**
	 * Define columns.
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
	 * Sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'id'         => array( 'id', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Bulk actions.
	 */
	public function get_bulk_actions() {
		$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		if ( 'trash' === $status ) {
			return array(
				'restore' => esc_html__( 'Restore', 'genform' ),
				'delete'  => esc_html__( 'Delete permanently', 'genform' ),
			);
		}

		return array(
			'trash' => esc_html__( 'Move to Trash', 'genform' ),
		);
	}

	/**
	 * Checkbox column.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="entry[]" value="%s" />', $item->id );
	}

	/**
	 * ID Column.
	 */
	public function column_id( $item ) {
		$unread_dot = ( 'unread' === $item->status ) ? '<span class="gfm-unread-dot-badge"></span>' : '';
		return sprintf( 
			'<div class="gfm-id-cell">%s <span class="gfm-muted-id">#%d</span></div>', 
			$unread_dot,
			$item->id
		);
	}

	/**
	 * Form Name Column.
	 */
	public function column_form_name( $item ) {
		return sprintf( '<strong>%s</strong>', esc_html( $item->form_name ?: __( 'Deleted Form', 'genform' ) ) );
	}

	/**
	 * Entry Preview Column.
	 * Shows only the first field's value (e.g., user's name).
	 */
	public function column_entry_preview( $item ) {
		$data = json_decode( $item->entry_data, true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return '—';
		}

		// Get the first field's value only.
		$first_value = reset( $data );
		
		// Handle arrays (checkboxes, multi-select).
		$display_val = is_array( $first_value ) ? implode( ', ', $first_value ) : $first_value;
		
		// Trim to 10 words for cleaner display.
		return esc_html( wp_trim_words( $display_val, 10 ) );
	}

	/**
	 * Date Column.
	 */
	public function column_created_at( $item ) {
		$timestamp = strtotime( $item->created_at );
		return esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) );
	}

	/**
	 * Actions Column.
	 */
	public function column_actions( $item ) {
		$is_trash = ( 'trash' === $item->status );
		$entries_data = json_decode( $item->entry_data, true ) ?: array();
		
		global $wpdb;
		$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT form_data FROM {$wpdb->prefix}genform_forms WHERE id = %d", $item->form_id ) );
		$readable_data = array();
		
		if ( $form_row && $form_row->form_data ) {
			$form_config = json_decode( $form_row->form_data, true );
			if ( is_array( $form_config ) ) {
				foreach ( $form_config as $field ) {
					if ( isset( $field['id'], $field['label'] ) && isset( $entries_data[ $field['id'] ] ) ) {
						$readable_data[ $field['label'] ] = $entries_data[ $field['id'] ];
					}
				}
			}
		}

		if ( empty( $readable_data ) ) {
			foreach ( $entries_data as $k => $v ) {
				$label = ucwords( str_replace( array( '_', '-' ), ' ', $k ) );
				$readable_data[ $label ] = $v;
			}
		}

		if ( $is_trash ) {
			return sprintf(
				'<div class="gfm-table-actions">
					<a href="%s" class="gfm-action-icon" title="%s"><span class="dashicons dashicons-undo"></span></a>
					<a href="#" class="gfm-action-icon gfm-delete-entry-permanent" data-id="%d" title="%s"><span class="dashicons dashicons-trash"></span></a>
				</div>',
				wp_nonce_url( admin_url( 'admin.php?page=genform-entries&status=trash&action=restore&entry=' . $item->id ), 'bulk-entries' ),
				esc_attr__( 'Restore', 'genform' ),
				$item->id,
				esc_attr__( 'Delete Permanently', 'genform' )
			);
		}

		return sprintf(
			'<div class="gfm-table-actions">
				<a href="#" class="gfm-view-entry gfm-action-icon" data-payload=\'%s\' data-metadata=\'%s\' title="%s"><span class="dashicons dashicons-visibility"></span></a>
				<a href="%s" class="gfm-action-icon gfm-action-trash-simple" title="%s"><span class="dashicons dashicons-trash"></span></a>
			</div>',
			esc_attr( wp_json_encode( $readable_data ) ),
			esc_attr( $item->entry_metadata ?: '{}' ),
			esc_attr__( 'View details', 'genform' ),
			wp_nonce_url( admin_url( 'admin.php?page=genform-entries&action=trash&entry=' . $item->id ), 'bulk-entries' ),
			esc_attr__( 'Move to Trash', 'genform' )
		);
	}

    /**
	 * Extra table navigation.
	 */
    public function extra_tablenav( $which ) {
        if ( 'top' !== $which ) return;
        global $wpdb;
        $forms = $wpdb->get_results( "SELECT id, form_name FROM {$wpdb->prefix}genform_forms ORDER BY form_name ASC" );
        $current = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
        ?>
        <div class="alignleft actions">
            <select name="form_id" id="filter-by-form">
                <option value="0"><?php esc_html_e( 'All Forms', 'genform' ); ?></option>
                <?php foreach ( $forms as $form ) : ?>
                    <option value="<?php echo esc_attr( $form->id ); ?>" <?php selected( $current, $form->id ); ?>><?php echo esc_html( $form->form_name ); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php esc_attr_e( 'Filter', 'genform' ); ?>">
        </div>
        <?php
    }

    /**
	 * No items message.
	 */
    public function no_items() {
        ?>
        <div class="gfm-empty-state">
            <span class="dashicons dashicons-database"></span>
            <p><?php esc_html_e( 'No entries found.', 'genform' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=genform-builder' ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'Create a New Form', 'genform' ); ?>
            </a>
        </div>
        <?php
    }
}
