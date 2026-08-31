/**
 * Admin UI for the Photography Draft page's "Floating Photo Cards" meta box.
 * Hand-built (no ACF Repeater — that's a PRO-only field type): media pickers
 * use the standard wp.media() library frame, reordering uses jQuery UI
 * Sortable (bundled with wp-admin) — same building blocks any core meta box
 * uses, no new dependency.
 */
(function($) {
    'use strict';

    function openMediaPicker(title, mimeType, onSelect) {
        var frame = wp.media({
            title: title,
            button: { text: 'Use this' },
            library: mimeType ? { type: mimeType } : {},
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            onSelect(attachment);
        });
        frame.open();
    }

    function thumbHtml(attachment) {
        var src = (attachment.sizes && attachment.sizes.thumbnail)
            ? attachment.sizes.thumbnail.url
            : attachment.url;
        return '<img src="' + src + '" alt="">';
    }

    function initRow($row) {
        // Media type radio toggles which fields are visible.
        $row.find('input[name$="[media_type]"]').on('change', function() {
            var type = $row.find('input[name$="[media_type]"]:checked').val();
            $row.toggleClass('media-type-video', type === 'video');
            $row.toggleClass('media-type-image', type !== 'video');
        });

        // "Show see more link?" toggles the URL field.
        $row.find('.pfc-show-see-more').on('change', function() {
            $row.toggleClass('show-see-more', $(this).is(':checked'));
        });

        $row.find('.pfc-choose-image').on('click', function(e) {
            e.preventDefault();
            openMediaPicker('Choose Image', 'image', function(attachment) {
                $row.find('.pfc-image-id').val(attachment.id);
                $row.find('.pfc-image-preview').html(thumbHtml(attachment));
            });
        });

        $row.find('.pfc-choose-video').on('click', function(e) {
            e.preventDefault();
            openMediaPicker('Choose Video (MP4)', 'video/mp4', function(attachment) {
                $row.find('.pfc-video-id').val(attachment.id);
                $row.find('.pfc-video-preview').text(attachment.filename || attachment.title || '');
            });
        });

        $row.find('.pfc-choose-poster').on('click', function(e) {
            e.preventDefault();
            openMediaPicker('Choose Poster Image', 'image', function(attachment) {
                $row.find('.pfc-poster-id').val(attachment.id);
                $row.find('.pfc-poster-preview').html(thumbHtml(attachment));
            });
        });

        $row.find('.pfc-remove-row').on('click', function(e) {
            e.preventDefault();
            $row.remove();
        });
    }

    $(function() {
        var $wrap = $('#pfc-wrap');
        if (!$wrap.length) {
            return;
        }

        var $rows = $wrap.find('.pfc-rows');
        var rowTemplate = document.getElementById('pfc-row-template').innerHTML;
        var nextIndex = $rows.find('.pfc-row').length;

        $rows.find('.pfc-row').each(function() {
            initRow($(this));
        });

        $rows.sortable({
            handle: '.pfc-row-handle',
            axis: 'y',
            placeholder: 'pfc-row-placeholder'
        });

        $('#pfc-add-row').on('click', function(e) {
            e.preventDefault();
            var html = rowTemplate.replace(/__INDEX__/g, nextIndex++);
            var $row = $(html).appendTo($rows);
            initRow($row);
        });
    });
})(jQuery);
