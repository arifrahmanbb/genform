<?php
/**
 * Data Export Manager
 *
 * Facilitates the generation and download of form submission data in CSV format.
 *
 * @package GenForm
 */

namespace GenForm\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExportHandler {

	/**
	 * Register the initialization hook for export detection.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handleExport' ) );
	}

	/**
	 * Entry point to detect and authorize CSV export requests.
	 */
	public function handleExport(): void {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'genform_export' !== $action ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'genform_export_entries' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'genform' ) );
		}

		// Safe to access after nonce verification.
		$form_id = isset( $_GET['form_id'] ) ? absint( wp_unslash( $_GET['form_id'] ) ) : 0;
		$this->downloadCsv( $form_id );
	}

	/**
	 * Generates and streams the CSV file to the browser.
	 */
	private function downloadCsv( int $id ): void {
		global $wpdb;
		$headers = array();
		$table   = $wpdb->prefix . 'genform_entries';

		// Sample the first 100 entries to dynamically build a column list.
		if ( $id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$samples = $wpdb->get_col( $wpdb->prepare( "SELECT entry_data FROM {$wpdb->prefix}genform_entries WHERE form_id = %d LIMIT 100", $id ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$samples = $wpdb->get_col( "SELECT entry_data FROM {$wpdb->prefix}genform_entries LIMIT 100" );
		}

		foreach ( $samples as $j ) {
			$d = json_decode( $j, true );
			if ( is_array( $d ) ) {
				foreach ( array_keys( $d ) as $k ) {
					if ( ! in_array( $k, $headers, true ) ) {
						$headers[] = $k;
					}
				}
			}
		}

		$f = $id ? "genform-entries-$id.csv" : 'genform-all-entries.csv';

		// Set download headers.
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename=' . $f );

		// For streaming to output, fopen is generally permitted if WP_Filesystem isn't applicable for streams.
		$o = fopen( 'php://output', 'w' );
		if ( ! $o ) {
			wp_die( esc_html__( 'Failed to open output stream.', 'genform' ) );
		}

		// Add UTF-8 Byte Order Mark for Excel compatibility.
		fprintf( $o, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// Format headers for display.
		$dh = array_map( fn( $k ) => ucwords( str_replace( '_', ' ', $k ) ), $headers );
		$dh[] = esc_html__( 'IP Address', 'genform' );
		$dh[] = esc_html__( 'Date', 'genform' );

		fputcsv( $o, $dh );

		$offset = 0;
		$limit  = 500;

		// Process rows in batches to manage memory overhead.
		while ( true ) {
			if ( $id ) {
				$query = $wpdb->prepare( "SELECT entry_data, user_ip, created_at FROM {$wpdb->prefix}genform_entries WHERE form_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d", $id, $limit, $offset );
			} else {
				$query = $wpdb->prepare( "SELECT entry_data, user_ip, created_at FROM {$wpdb->prefix}genform_entries ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset );
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$chunk = $wpdb->get_results( $query );
			if ( empty( $chunk ) ) {
				break;
			}

			foreach ( $chunk as $e ) {
				$d = json_decode( $e->entry_data, true );
				$r = array();
				foreach ( $headers as $k ) {
					$v   = $d[ $k ] ?? '';
					$r[] = is_array( $v ) ? implode( ', ', $v ) : $v;
				}
				$r[] = $e->user_ip;
				$r[] = $e->created_at;
				fputcsv( $o, $r );
			}

			$offset += $limit;
			if ( ob_get_level() > 0 ) {
				ob_flush();
			}
			flush();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $o );
		exit;
	}
}
