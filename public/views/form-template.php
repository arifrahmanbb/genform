<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Form Template
 */
?>
<div class="gfm-form-container" id="gfm-form-<?php echo esc_attr( $form->id ); ?>">
	<form class="gfm-form gfm-form-js" method="post" data-id="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_id" value="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_nonce" value="<?php echo esc_attr( $nonce ); ?>">
		<input type="hidden" name="action" value="genform_submit">

		<?php
		if ( ! empty( $data['fields'] ) ) :
			foreach ( $data['fields'] as $genform_field ) :
				$genform_name        = 'gfm_' . sanitize_title( $genform_field['name'] );
				$genform_placeholder = esc_attr( $genform_field['placeholder'] ?? '' );
				$genform_required    = ! empty( $genform_field['required'] ) ? 'required' : '';
				$genform_css_class   = esc_attr( $genform_field['css_class'] ?? '' );
				$genform_default     = esc_attr( $genform_field['default_value'] ?? '' );
				$genform_width       = esc_attr( $genform_field['width'] ?? '100' );
				$genform_font_size   = esc_attr( $genform_field['font_size'] ?? '16' );
				$genform_font_weight = esc_attr( $genform_field['font_weight'] ?? '400' );
				$genform_style       = "font-size: {$genform_font_size}px; font-weight: {$genform_font_weight};";
				?>
				<div class="gfm-form-field gfm-w-<?php echo $genform_width; ?> <?php echo $genform_css_class; ?> gfm-type-<?php echo esc_attr( $genform_field['type'] ); ?>">
					<?php if ( 'hidden' !== $genform_field['type'] ) : ?>
						<label class="gfm-label">
							<?php echo esc_html( $genform_field['label'] ); ?>
							<?php if ( $genform_required ) : ?>
								<span class="gfm-required-mark" style="color:#ef4444;">*</span>
							<?php endif; ?>
						</label>
					<?php endif; ?>

					<div class="gfm-input-control" style="<?php echo esc_attr( $genform_style ); ?>">
						<?php
						switch ( $genform_field['type'] ) {
							case 'textarea':
								printf(
									'<textarea name="%1$s" class="gfm-textarea" rows="4" placeholder="%2$s" %3$s style="%4$s">%5$s</textarea>',
									esc_attr( $genform_name ),
									esc_attr( $genform_placeholder ),
									esc_attr( $genform_required ),
									esc_attr( $genform_style ),
									esc_textarea( $genform_default )
								);
								break;
							case 'select':
								printf(
									'<select name="%1$s" class="gfm-select" %2$s style="%3$s">',
									esc_attr( $genform_name ),
									esc_attr( $genform_required ),
									esc_attr( $genform_style )
								);
								if ( $genform_placeholder ) {
									printf(
										'<option value="" disabled selected>%s</option>',
										esc_html( $genform_placeholder )
									);
								}
								if ( ! empty( $genform_field['options'] ) ) {
									foreach ( $genform_field['options'] as $genform_opt ) {
										$genform_sel = ( $genform_default === $genform_opt['value'] ) ? 'selected' : '';
										printf(
											'<option value="%1$s" %2$s>%3$s</option>',
											esc_attr( $genform_opt['value'] ),
											esc_attr( $genform_sel ),
											esc_html( $genform_opt['label'] )
										);
									}
								}
								echo '</select>';
								break;
							case 'radio':
							case 'checkbox':
								if ( ! empty( $genform_field['options'] ) ) {
									echo '<div class="gfm-options-group" style="display:flex; gap:15px; flex-wrap:wrap; margin-top:5px;">';
									foreach ( $genform_field['options'] as $genform_opt ) {
										$genform_type       = $genform_field['type'];
										$genform_input_name = ( 'checkbox' === $genform_type ) ? "{$genform_name}[]" : $genform_name;
										printf(
											'<label class="gfm-option-label" style="display:flex; align-items:center; gap:8px; font-size:14px; cursor:pointer; %6$s"><input type="%1$s" name="%2$s" value="%3$s" %4$s> %5$s</label>',
											esc_attr( $genform_type ),
											esc_attr( $genform_input_name ),
											esc_attr( $genform_opt['value'] ),
											esc_attr( $genform_required ),
											esc_html( $genform_opt['label'] ),
											esc_attr( $genform_style )
										);
									}
									echo '</div>';
								}
								break;
							default:
								printf(
									'<input type="%1$s" name="%2$s" class="gfm-input" placeholder="%3$s" value="%4$s" %5$s style="%6$s">',
									esc_attr( $genform_field['type'] ),
									esc_attr( $genform_name ),
									esc_attr( $genform_placeholder ),
									esc_attr( $genform_default ),
									esc_attr( $genform_required ),
									esc_attr( $genform_style )
								);
								break;
						}
						?>
					</div>
				</div>
				<?php
			endforeach;
		endif;
		?>

		<div class="gfm-submit-wrap">
			<button type="submit" class="gfm-submit">
				<?php echo esc_html( $settings['submit_text'] ?? esc_html__( 'Submit', 'genform' ) ); ?>
			</button>
		</div>

		<div class="gfm-message" style="display:none;"></div>
	</form>
</div>