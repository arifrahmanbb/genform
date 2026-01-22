<?php
if (!defined('ABSPATH')) exit;

/**
 * Frontend Form Template
 */
?>
<div class="gfm-form-container gfm-premium-look" id="gfm-form-<?php echo esc_attr($form->id); ?>">
    <form class="gfm-form-js" method="post" data-id="<?php echo esc_attr($form->id); ?>">
        <input type="hidden" name="genform_id" value="<?php echo esc_attr($form->id); ?>">
        <input type="hidden" name="genform_nonce" value="<?php echo esc_attr($nonce); ?>">
        <input type="hidden" name="action" value="genform_submit">

        <div class="gfm-fields-wrapper">
            <?php if (!empty($data['fields'])): foreach ($data['fields'] as $field): ?>
                    <?php
                    $name = 'gfm_' . sanitize_title($field['name']);
                    $placeholder = esc_attr($field['placeholder'] ?? '');
                    $required = !empty($field['required']) ? 'required' : '';
                    $css_class = esc_attr($field['css_class'] ?? '');
                    $default = esc_attr($field['default_value'] ?? '');
                    ?>
                    <div class="gfm-form-field <?php echo $css_class; ?> gfm-type-<?php echo esc_attr($field['type']); ?>">
                        <?php if ($field['type'] !== 'hidden'): ?>
                            <label class="gfm-label">
                                <?php echo esc_html($field['label']); ?>
                                <?php if ($required): ?><span class="gfm-required-mark">*</span><?php endif; ?>
                            </label>
                        <?php endif; ?>

                        <div class="gfm-input-control">
                            <?php
                            switch ($field['type']) {
                                case 'textarea':
                                    echo "<textarea name='$name' class='gfm-textarea' placeholder='$placeholder' $required>$default</textarea>";
                                    break;
                                case 'select':
                                    echo "<select name='$name' class='gfm-select' $required>";
                                    if ($placeholder) echo "<option value='' disabled selected>$placeholder</option>";
                                    if (!empty($field['options'])) {
                                        foreach ($field['options'] as $opt) {
                                            $sel = ($default === $opt['value']) ? 'selected' : '';
                                            echo "<option value='" . esc_attr($opt['value']) . "' $sel>" . esc_html($opt['label']) . "</option>";
                                        }
                                    }
                                    echo "</select>";
                                    break;
                                case 'radio':
                                case 'checkbox':
                                    if (!empty($field['options'])) {
                                        echo "<div class='gfm-options-group'>";
                                        foreach ($field['options'] as $opt) {
                                            $type = $field['type'];
                                            $input_name = ($type === 'checkbox') ? "{$name}[]" : $name;
                                            echo "<label class='gfm-option-label'><input type='$type' name='$input_name' value='" . esc_attr($opt['value']) . "' $required> " . esc_html($opt['label']) . "</label>";
                                        }
                                        echo "</div>";
                                    }
                                    break;
                                default:
                                    echo "<input type='" . esc_attr($field['type']) . "' name='$name' class='gfm-input' placeholder='$placeholder' value='$default' $required>";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>

        <div class="gfm-submit-area">
            <button type="submit" class="gfm-submit">
                <?php echo esc_html($settings['submit_text'] ?? __('Submit', 'genform')); ?>
            </button>
        </div>

        <div class="gfm-message" style="display:none;"></div>
    </form>
</div>