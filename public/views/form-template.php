<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend Form Template
 */
?>
<div class="gfm-form-container gfm-premium-look" id="gfm-form-<?php echo esc_attr( $form->id ); ?>">
	<form class="gfm-form-js" method="post" data-id="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_id" value="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_nonce" value="<?php echo esc_attr( $nonce ); ?>">
		<input type="hidden" name="action" value="genform_submit">

		<div class="gfm-fields-wrapper">
			<?php
			if ( ! empty( $data['fields'] ) ) :
				foreach ( $data['fields'] as $genform_field ) :
					?>
					<?php
					$genform_name        = 'gfm_' . sanitize_title( $genform_field['name'] );
					$genform_placeholder = esc_attr( $genform_field['placeholder'] ?? '' );
					$genform_required    = ! empty( $genform_field['required'] ) ? 'required' : '';
					$genform_css_class   = esc_attr( $genform_field['css_class'] ?? '' );
					$genform_default     = esc_attr( $genform_field['default_value'] ?? '' );
					?>
					<div class="gfm-form-field <?php echo esc_attr( $genform_css_class ); ?> gfm-type-<?php echo esc_attr( $genform_field['type'] ); ?>">
						<?php if ( 'hidden' !== $genform_field['type'] ) : ?>
							<label class="gfm-label">
								<?php echo esc_html( $genform_field['label'] ); ?>
								<?php if ( $genform_required ) : ?>
									<span class="gfm-required-mark">*</span>
								<?php endif; ?>
							</label>
						<?php endif; ?>

						<div class="gfm-input-control">
							<?php
							switch ( $genform_field['type'] ) {
								case 'textarea':
									printf(
										'<textarea name="%1$s" class="gfm-textarea" placeholder="%2$s" %3$s>%4$s</textarea>',
										esc_attr( $genform_name ),
										esc_attr( $genform_placeholder ),
										esc_attr( $genform_required ),
										esc_textarea( $genform_default )
									);
									break;
								case 'select':
									printf(
										'<select name="%1$s" class="gfm-select" %2$s>',
										esc_attr( $genform_name ),
										esc_attr( $genform_required )
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
										echo '<div class="gfm-options-group">';
										foreach ( $genform_field['options'] as $genform_opt ) {
											$genform_type       = $genform_field['type'];
											$genform_input_name = ( 'checkbox' === $genform_type ) ? "{$genform_name}[]" : $genform_name;
											printf(
												'<label class="gfm-option-label"><input type="%1$s" name="%2$s" value="%3$s" %4$s> %5$s</label>',
												esc_attr( $genform_type ),
												esc_attr( $genform_input_name ),
												esc_attr( $genform_opt['value'] ),
												esc_attr( $genform_required ),
												esc_html( $genform_opt['label'] )
											);
										}
										echo '</div>';
									}
									break;
								default:
									printf(
										'<input type="%1$s" name="%2$s" class="gfm-input" placeholder="%3$s" value="%4$s" %5$s>',
										esc_attr( $genform_field['type'] ),
										esc_attr( $genform_name ),
										esc_attr( $genform_placeholder ),
										esc_attr( $genform_default ),
										esc_attr( $genform_required )
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
		</div>

		<div class="gfm-submit-area">
			<button type="submit" class="gfm-submit">
				<?php echo esc_html( $settings['submit_text'] ?? esc_html__( 'Submit', 'genform' ) ); ?>
			</button>
		</div>

		<div class="gfm-message" style="display:none;"></div>
	</form>
</div>