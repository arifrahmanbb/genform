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

$bsize   = esc_attr( $settings['base_font_size'] ?? '16' );
$bweight = esc_attr( $settings['base_font_weight'] ?? '400' );
$balign  = esc_attr( $settings['submit_align'] ?? 'left' );
?>

<div class="gfm-form-container" id="gfm-form-<?php echo esc_attr( $form->id ); ?>" data-size="<?php echo esc_attr( $bsize ); ?>" data-weight="<?php echo esc_attr( $bweight ); ?>">
	<form class="gfm-form gfm-form-js" data-id="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_id" value="<?php echo esc_attr( $form->id ); ?>">
		<input type="hidden" name="genform_nonce" value="<?php echo esc_attr( $nonce ); ?>">
		<input type="hidden" name="action" value="genform_submit">

		<?php
		if ( ! empty( $data['fields'] ) ) :
			foreach ( $data['fields'] as $f ) :
				$n   = 'gfm_' . sanitize_title( $f['name'] );
				$ph  = esc_attr( $f['placeholder'] ?? '' );
				$req = ! empty( $f['required'] ) ? 'required' : '';
				$w   = esc_attr( $f['width'] ?? '100' );
				?>
				<div class="gfm-form-field gfm-w-<?php echo esc_attr( $w ); ?> <?php echo esc_attr( $f['css_class'] ?? '' ); ?> gfm-type-<?php echo esc_attr( $f['type'] ); ?>">
					<?php if ( $f['type'] !== 'hidden' ) : ?>
						<label class="gfm-label">
							<?php echo esc_html( $f['label'] ); ?>
							<?php if ( $req ) echo '<span class="gfm-required-mark">*</span>'; ?>
						</label>
					<?php endif; ?>

					<div class="gfm-input-control">
						<?php
						switch ( $f['type'] ) {
							case 'textarea':
								printf(
									'<textarea name="%1$s" class="gfm-textarea" rows="4" placeholder="%2$s" %3$s>%4$s</textarea>',
									esc_attr( $n ),
									$ph,
									$req,
									esc_textarea( $f['default_value'] ?? '' )
								);
								break;

							case 'select':
								printf( '<select name="%1$s" class="gfm-select" %2$s>', esc_attr( $n ), $req );
								if ( $ph ) {
									printf( '<option value="" disabled selected>%s</option>', esc_html( $ph ) );
								}
								if ( ! empty( $f['options'] ) ) {
									foreach ( $f['options'] as $o ) {
										printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $o['value'] ), selected( $f['default_value'] ?? '', $o['value'], false ), esc_html( $o['label'] ) );
									}
								}
								echo '</select>';
								break;

							case 'radio':
							case 'checkbox':
								if ( ! empty( $f['options'] ) ) {
									echo '<div class="gfm-options-group">';
									foreach ( $f['options'] as $o ) {
										printf(
											'<label class="gfm-option-label"><input type="%1$s" name="%2$s" value="%3$s" %4$s> %5$s</label>',
											esc_attr( $f['type'] ),
											( $f['type'] === 'checkbox' ? esc_attr( "{$n}[]" ) : esc_attr( $n ) ),
											esc_attr( $o['value'] ),
											$req,
											esc_html( $o['label'] )
										);
									}
									echo '</div>';
								}
								break;

							default:
								printf(
									'<input type="%1$s" name="%2$s" class="gfm-input" placeholder="%3$s" value="%4$s" %5$s>',
									esc_attr( $f['type'] ),
									esc_attr( $n ),
									$ph,
									esc_attr( $f['default_value'] ?? '' ),
									$req
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

		<div class="gfm-submit-wrap gfm-align-<?php echo esc_attr( $balign ); ?>">
			<button type="submit" class="gfm-submit <?php echo ( $balign === 'full' ? 'gfm-btn-full' : 'gfm-btn-auto' ); ?>">
				<?php echo esc_html( $settings['submit_text'] ?? esc_html__( 'Submit', 'genform' ) ); ?>
			</button>
		</div>

		<div class="gfm-message gfm-hidden"></div>
	</form>
</div>