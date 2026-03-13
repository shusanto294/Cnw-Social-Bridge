/* global wp */
(function ($) {
    'use strict';

    // --- Desktop Logo ---
    var mediaFrame;

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

    $('#cnw-remove-logo').on('click', function (e) {
        e.preventDefault();
        $('#cnw-logo-url').val('');
        $('#cnw-logo-preview').html(
            '<div class="cnw-logo-empty-state">No logo selected</div>'
        );
        $('#cnw-select-logo').text('Select Logo');
        $(this).hide();
    });

    // --- Mobile Logo ---
    var mobileMediaFrame;

    $('#cnw-select-mobile-logo').on('click', function (e) {
        e.preventDefault();

        if (mobileMediaFrame) {
            mobileMediaFrame.open();
            return;
        }

        mobileMediaFrame = wp.media({
            title: 'Select Mobile Logo',
            button: { text: 'Use this image' },
            multiple: false,
            library: { type: 'image' },
        });

        mobileMediaFrame.on('select', function () {
            var attachment = mobileMediaFrame.state().get('selection').first().toJSON();
            var url = attachment.sizes && attachment.sizes.thumbnail
                ? attachment.sizes.thumbnail.url
                : attachment.url;

            $('#cnw-mobile-logo-url').val(url);
            $('#cnw-mobile-logo-preview').html(
                '<img src="' + url + '" alt="Mobile logo preview" class="cnw-logo-preview-img" />'
            );
            $('#cnw-select-mobile-logo').text('Change Mobile Logo');
            $('#cnw-remove-mobile-logo').show();
        });

        mobileMediaFrame.open();
    });

    $('#cnw-remove-mobile-logo').on('click', function (e) {
        e.preventDefault();
        $('#cnw-mobile-logo-url').val('');
        $('#cnw-mobile-logo-preview').html(
            '<div class="cnw-logo-empty-state">No mobile logo selected</div>'
        );
        $('#cnw-select-mobile-logo').text('Select Mobile Logo');
        $(this).hide();
    });
})(jQuery);
