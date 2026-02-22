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
	private function downloadCsv( int $form_id ): void {
		global $wpdb;
		$column_headers = array();

		// Sample the first 100 entries to dynamically build a column list.
		if ( $form_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$samples = $wpdb->get_col( $wpdb->prepare( "SELECT entry_data FROM {$wpdb->prefix}genform_entries WHERE form_id = %d AND status != 'trash' LIMIT 100", $form_id ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$samples = $wpdb->get_col( $wpdb->prepare( "SELECT entry_data FROM {$wpdb->prefix}genform_entries WHERE status != %s LIMIT 100", 'trash' ) );
		}

		foreach ( $samples as $json_entry ) {
			$decoded = json_decode( $json_entry, true );
			if ( is_array( $decoded ) ) {
				foreach ( array_keys( $decoded ) as $field_key ) {
					if ( ! in_array( $field_key, $column_headers, true ) ) {
						$column_headers[] = $field_key;
					}
				}
			}
		}

		$filename = $form_id ? "genform-entries-$form_id.csv" : 'genform-all-entries.csv';

		// Set download headers.
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		// For streaming to output, fopen is generally permitted if WP_Filesystem isn't applicable for streams.
		$output_stream = fopen( 'php://output', 'w' );
		if ( ! $output_stream ) {
			wp_die( esc_html__( 'Failed to open output stream.', 'genform' ) );
		}

		// Add UTF-8 Byte Order Mark for Excel compatibility.
		fprintf( $output_stream, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// Format headers for display.
		$display_headers   = array_map( fn( $key ) => ucwords( str_replace( '_', ' ', $key ) ), $column_headers );
		$display_headers[] = esc_html__( 'IP Address', 'genform' );
		$display_headers[] = esc_html__( 'Date', 'genform' );

		fputcsv( $output_stream, $display_headers );

		$offset = 0;
		$limit  = 500;

		// Process rows in batches to manage memory overhead.
		while ( true ) {
			if ( $form_id ) {
				$query = $wpdb->prepare( "SELECT entry_data, user_ip, created_at FROM {$wpdb->prefix}genform_entries WHERE form_id = %d AND status != 'trash' ORDER BY created_at DESC LIMIT %d OFFSET %d", $form_id, $limit, $offset );
			} else {
				$query = $wpdb->prepare( "SELECT entry_data, user_ip, created_at FROM {$wpdb->prefix}genform_entries WHERE status != 'trash' ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset );
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$chunk = $wpdb->get_results( $query );
			if ( empty( $chunk ) ) {
				break;
			}

			foreach ( $chunk as $entry ) {
				$decoded = json_decode( $entry->entry_data, true );
				$row     = array();
				foreach ( $column_headers as $field_key ) {
					$field_value = $decoded[ $field_key ] ?? '';
					$row[]       = is_array( $field_value ) ? implode( ', ', $field_value ) : $field_value;
				}
				$row[] = $entry->user_ip;
				$row[] = $entry->created_at;
				fputcsv( $output_stream, $row );
			}

			$offset += $limit;
			if ( ob_get_level() > 0 ) {
				ob_flush();
			}
			flush();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $output_stream );
		exit;
	}
}
