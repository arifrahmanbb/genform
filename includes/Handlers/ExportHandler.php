<?php
/**
 * Export Handler for GenForm
 *
 * @package GenForm
 */

namespace GenForm\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExportHandler
 * Handles CSV export of form entries.
 */
final class ExportHandler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handleExport' ) );
	}

	/**
	 * Handle CSV export request.
	 */
	public function handleExport(): void {
		if ( ! isset( $_GET['action'] ) || 'genform_export' !== $_GET['action'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
		}

		$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		if ( ! wp_verify_nonce( $nonce, 'genform_export_entries' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'genform' ) );
		}

		$this->downloadCsv( $form_id );
	}

	/**
	 * Download entries as CSV.
	 *
	 * @param int $form_id The form ID to export.
	 */
	private function downloadCsv( int $form_id ): void {
		global $wpdb;

		// 1. Determine Headers first (Scan initial entries or form schema).
		// For robustness, we scan a small chunk of entries to build the initial header list.
		// However, to be 100% accurate, we would scan all. To be memory efficient, we scan in the first pass.
		
		$headers = array();
		$where_clause = $form_id ? $wpdb->prepare( 'WHERE form_id = %d', $form_id ) : '';
		
		// Get a sample to build headers (or use the first 100).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$sample_entries = $wpdb->get_col( "SELECT entry_data FROM {$wpdb->prefix}genform_entries $where_clause LIMIT 100" );
		
		foreach ( $sample_entries as $e_json ) {
			$e_data = json_decode( $e_json, true );
			if ( is_array( $e_data ) ) {
				foreach ( array_keys( $e_data ) as $key ) {
					if ( ! in_array( $key, $headers, true ) ) {
						$headers[] = $key;
					}
				}
			}
		}

		// Prepare File & Headers.
		$filename = $form_id ? "genform-entries-form-{$form_id}.csv" : 'genform-all-entries.csv';
		
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		
		// UTF-8 BOM for Excel.
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// CSV Vertical Headers (Final displayed labels).
		$display_headers = array_map( fn( $k ) => ucwords( str_replace( '_', ' ', $k ) ), $headers );
		$display_headers[] = esc_html__( 'IP Address', 'genform' );
		$display_headers[] = esc_html__( 'Date', 'genform' );
		
		fputcsv( $output, $display_headers );

		// 2. Stream Entries in Chunks (Memory Efficient).
		$offset = 0;
		$limit  = 500;
		
		while ( true ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$chunk = $wpdb->get_results( 
				$wpdb->prepare( 
					"SELECT entry_data, user_ip, created_at FROM {$wpdb->prefix}genform_entries $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$limit,
					$offset
				)
			);

			if ( empty( $chunk ) ) {
				break;
			}

			foreach ( $chunk as $entry ) {
				$data = json_decode( $entry->entry_data, true );
				$row  = array();

				foreach ( $headers as $key ) {
					$val = $data[ $key ] ?? '';
					$row[] = is_array( $val ) ? implode( ', ', $val ) : $val;
				}

				$row[] = $entry->user_ip;
				$row[] = $entry->created_at;

				fputcsv( $output, $row );
			}

			$offset += $limit;
			
			// Flush the output buffer to free up memory.
			if ( ob_get_level() > 0 ) {
				ob_flush();
			}
			flush();
		}

		fclose( $output );
		exit;
	}
}
