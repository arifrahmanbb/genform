<?php
/**
 * Frontend View: Form Template
 *
 * This file is processed for every [genform] shortcode and handles dynamic field generation.
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$genform_bsize   = esc_attr( $settings['base_font_size'] ?? '16' );
$genform_bweight = esc_attr( $settings['base_font_weight'] ?? '400' );
$genform_balign  = esc_attr( $settings['submit_align'] ?? 'left' );
?>

<div class="gfm-form-container" id="gfm-form-<?php echo esc_attr( $form->id ); ?>" data-size="<?php echo esc_attr( $genform_bsize ); ?>" data-weight="<?php echo esc_attr( $genform_bweight ); ?>">
	<form class="gfm-form gfm-form-js" data-id="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_id" value="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_nonce" value="<?php echo esc_attr( $nonce ); ?>">
		<input type="hidden" name="action" value="genform_submit">

		<?php
		if ( ! empty( $data['fields'] ) ) :
			foreach ( $data['fields'] as $genform_f ) :
				$genform_n   = 'gfm_' . sanitize_title( $genform_f['name'] );
				$genform_ph  = esc_attr( $genform_f['placeholder'] ?? '' );
				$genform_req = ! empty( $genform_f['required'] ) ? 'required' : '';
				$genform_w   = esc_attr( $genform_f['width'] ?? '100' );
				?>
				<div class="gfm-form-field gfm-w-<?php echo esc_attr( $genform_w ); ?> <?php echo esc_attr( $genform_f['css_class'] ?? '' ); ?> gfm-type-<?php echo esc_attr( $genform_f['type'] ); ?>">
					<?php if ( $genform_f['type'] !== 'hidden' ) : ?>
						<label class="gfm-label">
							<?php echo esc_html( $genform_f['label'] ); ?>
							<?php if ( $genform_req ) echo '<span class="gfm-required-mark">*</span>'; ?>
						</label>
					<?php endif; ?>

					<div class="gfm-input-control">
						<?php
						switch ( $genform_f['type'] ) {
							case 'textarea':
								printf(
									'<textarea name="%1$s" class="gfm-textarea" rows="4" placeholder="%2$s" %3$s>%4$s</textarea>',
									esc_attr( $genform_n ),
									esc_attr( $genform_ph ),
									esc_attr( $genform_req ),
									esc_textarea( $genform_f['default_value'] ?? '' )
								);
								break;

							case 'select':
								printf( '<select name="%1$s" class="gfm-select" %2$s>', esc_attr( $genform_n ), esc_attr( $genform_req ) );
								if ( $genform_ph ) {
									printf( '<option value="" disabled selected>%s</option>', esc_html( $genform_ph ) );
								}
								if ( ! empty( $genform_f['options'] ) ) {
									foreach ( $genform_f['options'] as $genform_o ) {
										printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $genform_o['value'] ), selected( $genform_f['default_value'] ?? '', $genform_o['value'], false ), esc_html( $genform_o['label'] ) );
									}
								}
								echo '</select>';
								break;

							case 'radio':
							case 'checkbox':
								if ( ! empty( $genform_f['options'] ) ) {
									echo '<div class="gfm-options-group">';
									foreach ( $genform_f['options'] as $genform_o ) {
										printf(
											'<label class="gfm-option-label"><input type="%1$s" name="%2$s" value="%3$s" %4$s> %5$s</label>',
											esc_attr( $genform_f['type'] ),
											( $genform_f['type'] === 'checkbox' ? esc_attr( "{$genform_n}[]" ) : esc_attr( $genform_n ) ),
											esc_attr( $genform_o['value'] ),
											esc_attr( $genform_req ),
											esc_html( $genform_o['label'] )
										);
									}
									echo '</div>';
								}
								break;

							default:
								printf(
									'<input type="%1$s" name="%2$s" class="gfm-input" placeholder="%3$s" value="%4$s" %5$s>',
									esc_attr( $genform_f['type'] ),
									esc_attr( $genform_n ),
									esc_attr( $genform_ph ),
									esc_attr( $genform_f['default_value'] ?? '' ),
									esc_attr( $genform_req )
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

		<div class="gfm-submit-wrap gfm-align-<?php echo esc_attr( $genform_balign ); ?>">
			<button type="submit" class="gfm-submit <?php echo ( $genform_balign === 'full' ? 'gfm-btn-full' : 'gfm-btn-auto' ); ?>">
				<?php echo esc_html( $settings['submit_text'] ?? esc_html__( 'Submit', 'genform' ) ); ?>
			</button>
		</div>

		<div class="gfm-message gfm-hidden"></div>
	</form>
</div>