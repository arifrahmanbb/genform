/**
 * GenForm Frontend JavaScript
 * 
 * @package GenForm
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Handle form submission via AJAX
        $('.genform-form').on('submit', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('.genform-submit-btn');
            var $message = $form.find('.genform-message');
            var formData = new FormData(this);

            // Disable submit button
            $submitBtn.prop('disabled', true).text('Submitting...');
            $message.hide().removeClass('success error');

            // AJAX request
            $.ajax({
                url: genformFrontend.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        $message
                            .addClass('success')
                            .html(response.data.message)
                            .show();

                        // Reset form
                        $form[0].reset();

                        // Redirect if URL is set
                        if (response.data.redirect_url) {
                            setTimeout(function () {
                                window.location.href = response.data.redirect_url;
                            }, 2000);
                        }
                    } else {
                        $message
                            .addClass('error')
                            .html(response.data.message)
                            .show();
                    }
                },
                error: function () {
                    $message
                        .addClass('error')
                        .html('An error occurred. Please try again.')
                        .show();
                },
                complete: function () {
                    $submitBtn.prop('disabled', false).text('Submit');
                }
            });
        });

    });

})(jQuery);
