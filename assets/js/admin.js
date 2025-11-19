/**
 * GenForm Admin JavaScript
 * 
 * @package GenForm
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Copy shortcode to clipboard
        $('.genform-copy-shortcode').on('click', function (e) {
            e.preventDefault();
            var shortcode = $(this).data('shortcode');

            // Create temporary input
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(shortcode).select();
            document.execCommand('copy');
            $temp.remove();

            // Show feedback
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.text(genformAdmin.strings.copied || 'Copied!');

            setTimeout(function () {
                $btn.text(originalText);
            }, 2000);
        });

    });

})(jQuery);
