<?php
/**
 * Shortcode Handler Class
 * 
 * @package GenForm
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GenForm_Shortcode Class
 */
class GenForm_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode( 'genform', array( $this, 'render_form' ) );
    }
    
    /**
     * Render form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_form( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'genform'
        );
        
        $form_id = absint( $atts['id'] );
        
        if ( ! $form_id ) {
            return '<p>' . esc_html__( 'Please provide a valid form ID.', 'genform' ) . '</p>';
        }
        
        // Get form data
        global $wpdb;
        $forms_table = $wpdb->prefix . 'genform_forms';
        $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $forms_table WHERE id = %d AND status = 'active'", $form_id ) );
        
        if ( ! $form ) {
            return '<p>' . esc_html__( 'Form not found.', 'genform' ) . '</p>';
        }
        
        // Parse form data
        $form_data = json_decode( $form->form_data, true );
        $form_settings = json_decode( $form->form_settings, true );
        
        if ( ! $form_data ) {
            return '<p>' . esc_html__( 'Invalid form data.', 'genform' ) . '</p>';
        }
        
        // Start output buffering
        ob_start();
        
        // Apply filter to allow customization
        do_action( 'genform/before_form_render', $form_id, $form );
        
        ?>
        <div class="genform-wrapper" data-form-id="<?php echo esc_attr( $form_id ); ?>">
            <form class="genform-form" method="post" action="" data-form-id="<?php echo esc_attr( $form_id ); ?>">
                <?php wp_nonce_field( 'genform_submit_' . $form_id, 'genform_nonce' ); ?>
                <input type="hidden" name="genform_id" value="<?php echo esc_attr( $form_id ); ?>" />
                <input type="hidden" name="action" value="genform_submit" />
                
                <div class="genform-fields">
                    <?php
                    if ( isset( $form_data['fields'] ) && is_array( $form_data['fields'] ) ) {
                        foreach ( $form_data['fields'] as $field ) {
                            $this->render_field( $field );
                        }
                    }
                    ?>
                </div>
                
                <div class="genform-submit-wrapper">
                    <button type="submit" class="genform-submit-btn">
                        <?php echo esc_html( isset( $form_settings['submit_text'] ) ? $form_settings['submit_text'] : __( 'Submit', 'genform' ) ); ?>
                    </button>
                </div>
                
                <div class="genform-message" style="display: none;"></div>
            </form>
        </div>
        <?php
        
        do_action( 'genform/after_form_render', $form_id, $form );
        
        return ob_get_clean();
    }
    
    /**
     * Render individual field
     *
     * @param array $field Field data
     */
    private function render_field( $field ) {
        $field_type = isset( $field['type'] ) ? $field['type'] : 'text';
        $field_label = isset( $field['label'] ) ? $field['label'] : '';
        $field_name = isset( $field['name'] ) ? $field['name'] : '';
        $field_required = isset( $field['required'] ) && $field['required'];
        $field_placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
        $field_class = isset( $field['class'] ) ? $field['class'] : '';
        
        ?>
        <div class="genform-field genform-field-<?php echo esc_attr( $field_type ); ?> <?php echo esc_attr( $field_class ); ?>">
            <?php if ( $field_label ) : ?>
                <label class="genform-label">
                    <?php echo esc_html( $field_label ); ?>
                    <?php if ( $field_required ) : ?>
                        <span class="genform-required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <?php
            switch ( $field_type ) {
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'url':
                    ?>
                    <input 
                        type="<?php echo esc_attr( $field_type ); ?>" 
                        name="<?php echo esc_attr( $field_name ); ?>" 
                        placeholder="<?php echo esc_attr( $field_placeholder ); ?>"
                        class="genform-input"
                        <?php echo $field_required ? 'required' : ''; ?>
                    />
                    <?php
                    break;
                    
                case 'textarea':
                    ?>
                    <textarea 
                        name="<?php echo esc_attr( $field_name ); ?>" 
                        placeholder="<?php echo esc_attr( $field_placeholder ); ?>"
                        class="genform-textarea"
                        rows="5"
                        <?php echo $field_required ? 'required' : ''; ?>
                    ></textarea>
                    <?php
                    break;
                    
                case 'select':
                    $options = isset( $field['options'] ) ? $field['options'] : array();
                    ?>
                    <select 
                        name="<?php echo esc_attr( $field_name ); ?>" 
                        class="genform-select"
                        <?php echo $field_required ? 'required' : ''; ?>
                    >
                        <option value=""><?php esc_html_e( 'Select an option', 'genform' ); ?></option>
                        <?php foreach ( $options as $option ) : ?>
                            <option value="<?php echo esc_attr( $option['value'] ); ?>">
                                <?php echo esc_html( $option['label'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    break;
                    
                case 'checkbox':
                    $options = isset( $field['options'] ) ? $field['options'] : array();
                    foreach ( $options as $option ) :
                        ?>
                        <label class="genform-checkbox-label">
                            <input 
                                type="checkbox" 
                                name="<?php echo esc_attr( $field_name ); ?>[]" 
                                value="<?php echo esc_attr( $option['value'] ); ?>"
                                class="genform-checkbox"
                            />
                            <?php echo esc_html( $option['label'] ); ?>
                        </label>
                        <?php
                    endforeach;
                    break;
                    
                case 'radio':
                    $options = isset( $field['options'] ) ? $field['options'] : array();
                    foreach ( $options as $option ) :
                        ?>
                        <label class="genform-radio-label">
                            <input 
                                type="radio" 
                                name="<?php echo esc_attr( $field_name ); ?>" 
                                value="<?php echo esc_attr( $option['value'] ); ?>"
                                class="genform-radio"
                                <?php echo $field_required ? 'required' : ''; ?>
                            />
                            <?php echo esc_html( $option['label'] ); ?>
                        </label>
                        <?php
                    endforeach;
                    break;
            }
            
            // Field description
            if ( isset( $field['description'] ) && $field['description'] ) {
                echo '<p class="genform-field-description">' . esc_html( $field['description'] ) . '</p>';
            }
            ?>
        </div>
        <?php
    }
}
