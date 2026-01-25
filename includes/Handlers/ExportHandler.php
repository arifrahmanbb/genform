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
		if ( ( $_GET['action'] ?? '' ) !== 'genform_export' ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'genform' ) );
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'genform_export_entries' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'genform' ) );
		}

		$this->downloadCsv( absint( $_GET['form_id'] ?? 0 ) );
	}

	/**
	 * Generates and streams the CSV file to the browser.
	 */
	private function downloadCsv( int $id ): void {
		global $wpdb;
		$headers = array();
		$w       = $id ? $wpdb->prepare( 'WHERE form_id = %d', $id ) : '';

		// Sample the first 100 entries to dynamically build a column list.
		$samples = $wpdb->get_col( "SELECT entry_data FROM {$wpdb->prefix}genform_entries $w LIMIT 100" );
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

		$o = fopen( 'php://output', 'w' );
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
			$chunk = $wpdb->get_results( $wpdb->prepare( "SELECT entry_data, user_ip, created_at FROM {$wpdb->prefix}genform_entries $w ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ) );
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

		fclose( $o );
		exit;
	}
}
