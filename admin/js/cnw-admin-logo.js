/* global wp */
(function ($) {
    'use strict';

    var mediaFrame;

    // Open media library frame
    $('#cnw-select-logo').on('click', function (e) {
        e.preventDefault();

        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        mediaFrame = wp.media({
            title: 'Select Platform Logo',
            button: { text: 'Use this image' },
            multiple: false,
            library: { type: 'image' },
        });

        mediaFrame.on('select', function () {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            var url = attachment.sizes && attachment.sizes.medium
                ? attachment.sizes.medium.url
                : attachment.url;

            $('#cnw-logo-url').val(url);
            $('#cnw-logo-preview').html(
                '<img src="' + url + '" alt="Logo preview" class="cnw-logo-preview-img" />'
            );
            $('#cnw-select-logo').text('Change Logo');
            $('#cnw-remove-logo').show();
        });

        mediaFrame.open();
    });

    // Remove logo
    $('#cnw-remove-logo').on('click', function (e) {
        e.preventDefault();
        $('#cnw-logo-url').val('');
        $('#cnw-logo-preview').html(
            '<div class="cnw-logo-empty-state">No logo selected</div>'
        );
        $('#cnw-select-logo').text('Select Logo');
        $(this).hide();
    });
})(jQuery);
