(function ($) {
	'use strict';

	$(document).on('submit', '.gfm-form-js', function (e) {
		e.preventDefault();
		const $form = $(this);
		const $btn = $form.find('.gfm-submit');
		const $msg = $form.find('.gfm-message');
		const formData = $form.serialize();

		$btn.prop('disabled', true).text('Processing...');
		$msg.hide().removeClass('gfm-success gfm-error');

		$.ajax({
			url: genform.ajax_url,
			type: 'POST',
			data: formData,
			success: function (response) {
				if (response.success) {
					$msg.addClass('gfm-success').text(response.data.message).fadeIn();
					if (response.data.redirect) {
						window.location.href = response.data.redirect;
					}
					$form[0].reset();
				} else {
					$msg.addClass('gfm-error').text(response.data.message).fadeIn();
				}
			},
			error: function () {
				$msg.addClass('gfm-error').text('An error occurred. Please try again.').fadeIn();
			},
			complete: function () {
				$btn.prop('disabled', false).text($btn.data('original-text') || 'Submit');
			}
		});
	});

})(jQuery);
