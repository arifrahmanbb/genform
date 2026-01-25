/**
 * GenForm Frontend JavaScript
 *
 * Manages dynamic typography and handling of form submissions via AJAX.
 */

(function ($) {
	'use strict';

	$(document).ready(function () {

		/**
		 * Initialization: Apply dynamic styles based on form settings.
		 */
		$('.gfm-form-container').each(function () {
			const $container = $(this);
			const size = $container.data('size');
			const weight = $container.data('weight');

			if (size) {
				$container.css('--gfm-base-size', size + 'px');
			}
			if (weight) {
				$container.css('font-weight', weight);
			}
		});

		/**
		 * Handles form submission via AJAX.
		 */
		$(document).on('submit', '.gfm-form-js', function (e) {
			e.preventDefault();

			const $form = $(this);
			const $btn = $form.find('.gfm-submit');
			const $msg = $form.find('.gfm-message');
			const originalText = $btn.text();
			const formData = $form.serialize();

			// Prepare UI for submission state.
			$btn.prop('disabled', true).text('...');
			$msg.addClass('gfm-hidden').removeClass('gfm-success gfm-error');

			$.ajax({
				url: genform.ajax_url,
				type: 'POST',
				data: formData,
				success: (response) => {
					if (response.success) {
						$msg.removeClass('gfm-hidden').addClass('gfm-success').text(response.data.message).hide().fadeIn();
						if (response.data.redirect) {
							window.location.href = response.data.redirect;
						}
						$form[0].reset();
					} else {
						$msg.removeClass('gfm-hidden').addClass('gfm-error').text(response.data.message).hide().fadeIn();
					}
				},
				error: () => {
					$msg.removeClass('gfm-hidden').addClass('gfm-error').text('An unknown error occurred.').hide().fadeIn();
				},
				complete: () => {
					$btn.prop('disabled', false).text(originalText);
				}
			});
		});
	});

})(jQuery);
