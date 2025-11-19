<?php
/**
 * Admin View: Entries List
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'genform' ) );
}

global $wpdb;
$entries_table = $wpdb->prefix . 'genform_entries';
$forms_table = $wpdb->prefix . 'genform_forms';

$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

// Get entries
if ( $form_id ) {
    $entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $entries_table WHERE form_id = %d ORDER BY created_at DESC", $form_id ) );
    $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $forms_table WHERE id = %d", $form_id ) );
} else {
    $entries = $wpdb->get_results( "SELECT * FROM $entries_table ORDER BY created_at DESC LIMIT 100" );
    $form = null;
}
?>

<div class="wrap genform-admin-wrap">
    <h1><?php esc_html_e( 'Form Entries', 'genform' ); ?></h1>
    
    <?php if ( $form ) : ?>
        <p><?php printf( esc_html__( 'Showing entries for: %s', 'genform' ), '<strong>' . esc_html( $form->form_name ) . '</strong>' ); ?></p>
    <?php endif; ?>
    
    <?php if ( empty( $entries ) ) : ?>
        <p><?php esc_html_e( 'No entries found.', 'genform' ); ?></p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'ID', 'genform' ); ?></th>
                    <th><?php esc_html_e( 'Form', 'genform' ); ?></th>
                    <th><?php esc_html_e( 'Data', 'genform' ); ?></th>
                    <th><?php esc_html_e( 'IP Address', 'genform' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'genform' ); ?></th>
                    <th><?php esc_html_e( 'Submitted', 'genform' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $entries as $entry ) : 
                    $entry_data = json_decode( $entry->entry_data, true );
                    $entry_form = $wpdb->get_row( $wpdb->prepare( "SELECT form_name FROM $forms_table WHERE id = %d", $entry->form_id ) );
                ?>
                    <tr>
                        <td><?php echo esc_html( $entry->id ); ?></td>
                        <td><?php echo $entry_form ? esc_html( $entry_form->form_name ) : esc_html__( 'Unknown', 'genform' ); ?></td>
                        <td>
                            <details>
                                <summary><?php esc_html_e( 'View Data', 'genform' ); ?></summary>
                                <pre><?php echo esc_html( print_r( $entry_data, true ) ); ?></pre>
                            </details>
                        </td>
                        <td><?php echo esc_html( $entry->user_ip ); ?></td>
                        <td><?php echo esc_html( ucfirst( $entry->status ) ); ?></td>
                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->created_at ) ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
