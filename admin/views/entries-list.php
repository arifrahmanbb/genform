<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'genform' ) );
}

global $wpdb;
$genform_entries_table = $wpdb->prefix . 'genform_entries';
$genform_forms_table = $wpdb->prefix . 'genform_forms';

$genform_form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

// Get entries
if ( $genform_form_id ) {
    $genform_entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $genform_entries_table WHERE form_id = %d ORDER BY created_at DESC", $genform_form_id ) );
    $genform_form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $genform_forms_table WHERE id = %d", $genform_form_id ) );
} else {
    $genform_entries = $wpdb->get_results( "SELECT * FROM $genform_entries_table ORDER BY created_at DESC LIMIT 100" );
    $genform_form = null;
}
?>

<div class="wrap genform-admin-wrap">
    <h1><?php esc_html_e( 'Form Entries', 'genform' ); ?></h1>
    
    <?php if ( $genform_form ) : ?>
        <p><?php 
            /* translators: %s: Form name */
            printf( esc_html__( 'Showing entries for: %s', 'genform' ), '<strong>' . esc_html( $genform_form->form_name ) . '</strong>' ); 
        ?></p>
    <?php endif; ?>
    
    <?php if ( empty( $genform_entries ) ) : ?>
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
                <?php foreach ( $genform_entries as $genform_entry ) : 
                    $genform_entry_data = json_decode( $genform_entry->entry_data, true );
                    $genform_entry_form = $wpdb->get_row( $wpdb->prepare( "SELECT form_name FROM $genform_forms_table WHERE id = %d", $genform_entry->form_id ) );
                ?>
                    <tr>
                        <td><?php echo esc_html( $genform_entry->id ); ?></td>
                        <td><?php echo $genform_entry_form ? esc_html( $genform_entry_form->form_name ) : esc_html__( 'Unknown', 'genform' ); ?></td>
                        <td>
                            <details>
                                <summary><?php esc_html_e( 'View Data', 'genform' ); ?></summary>
                                <table class="widefat">
                                    <?php if ( is_array( $genform_entry_data ) ) : ?>
                                        <?php foreach ( $genform_entry_data as $genform_key => $genform_value ) : ?>
                                            <tr>
                                                <th><?php echo esc_html( ucfirst( str_replace( '_', ' ', $genform_key ) ) ); ?></th>
                                                <td><?php echo esc_html( is_array( $genform_value ) ? implode( ', ', $genform_value ) : $genform_value ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </table>
                            </details>
                        </td>
                        <td><?php echo esc_html( $genform_entry->user_ip ); ?></td>
                        <td><?php echo esc_html( ucfirst( $genform_entry->status ) ); ?></td>
                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $genform_entry->created_at ) ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
