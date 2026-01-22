<?php
if (!defined('ABSPATH')) exit;
?>
<div class="gfm-form-container" id="gfm-form-<?php echo esc_attr($form->id); ?>">
    <form class="gfm-form-js" method="post" data-id="<?php echo esc_attr($form->id); ?>">
        <input type="hidden" name="genform_id" value="<?php echo esc_attr($form->id); ?>">
        <input type="hidden" name="genform_nonce" value="<?php echo esc_attr($nonce); ?>">
        <input type="hidden" name="action" value="genform_submit">

        <div class="gfm-fields-wrapper">
            <?php if (!empty($data['fields'])): foreach ($data['fields'] as $field): ?>
                    <div class="gfm-form-field">
                        <label class="gfm-label"><?php echo esc_html($field['label']); ?></label>
                        <?php
                        $name = 'gfm_' . sanitize_title($field['name']);
                        $placeholder = esc_attr($field['placeholder'] ?? '');
                        $required = !empty($field['required']) ? 'required' : '';

                        switch ($field['type']) {
                            case 'textarea':
                                echo "<textarea name='$name' class='gfm-textarea' placeholder='$placeholder' $required></textarea>";
                                break;
                            case 'select':
                                echo "<select name='$name' class='gfm-select' $required>";
                                foreach ($field['options'] as $opt) {
                                    echo "<option value='" . esc_attr($opt['value']) . "'>" . esc_html($opt['label']) . "</option>";
                                }
                                echo "</select>";
                                break;
                            default:
                                echo "<input type='" . esc_attr($field['type']) . "' name='$name' class='gfm-input' placeholder='$placeholder' $required>";
                                break;
                        }
                        ?>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>

        <button type="submit" class="gfm-submit">
            <?php echo esc_html($settings['submit_text'] ?? __('Submit', 'genform')); ?>
        </button>

        <div class="gfm-message" style="display:none;"></div>
    </form>
</div>