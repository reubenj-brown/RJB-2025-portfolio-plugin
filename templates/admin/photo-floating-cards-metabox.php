<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders one card row. $index is either a real array index (existing rows)
 * or the string '__INDEX__' (the hidden template row cloned by JS on "Add
 * card" — JS replaces that placeholder with a fresh unique index).
 */
if (!function_exists('pfc_render_row')) {
    function pfc_render_row($index, $card) {
        $media_type    = !empty($card['media_type']) && $card['media_type'] === 'video' ? 'video' : 'image';
        $image_id      = !empty($card['image_id']) ? (int) $card['image_id'] : 0;
        $video_id      = !empty($card['video_id']) ? (int) $card['video_id'] : 0;
        $poster_id     = !empty($card['poster_id']) ? (int) $card['poster_id'] : 0;
        $back_text     = isset($card['back_text']) ? $card['back_text'] : '';
        $show_see_more = !empty($card['show_see_more']);
        $see_more_url  = isset($card['see_more_url']) ? $card['see_more_url'] : '';

        $row_classes = ['pfc-row', 'media-type-' . $media_type];
        if ($show_see_more) {
            $row_classes[] = 'show-see-more';
        }
        $name = 'photo_floating_cards[' . esc_attr($index) . ']';
        ?>
        <div class="<?php echo esc_attr(implode(' ', $row_classes)); ?>" data-index="<?php echo esc_attr($index); ?>">
            <div class="pfc-row-handle" title="Drag to reorder">☰</div>
            <div class="pfc-row-body">
                <div class="pfc-field pfc-media-type">
                    <label>
                        <input type="radio" name="<?php echo $name; ?>[media_type]" value="image" <?php checked($media_type, 'image'); ?>>
                        Image
                    </label>
                    <label>
                        <input type="radio" name="<?php echo $name; ?>[media_type]" value="video" <?php checked($media_type, 'video'); ?>>
                        Video
                    </label>
                </div>

                <div class="pfc-field pfc-field-image">
                    <button type="button" class="button pfc-choose pfc-choose-image">Choose Image</button>
                    <input type="hidden" class="pfc-media-id pfc-image-id" name="<?php echo $name; ?>[image_id]" value="<?php echo esc_attr($image_id); ?>">
                    <span class="pfc-preview pfc-image-preview"><?php if ($image_id) echo wp_get_attachment_image($image_id, 'thumbnail'); ?></span>
                </div>

                <div class="pfc-field pfc-field-video">
                    <button type="button" class="button pfc-choose pfc-choose-video">Choose Video (MP4)</button>
                    <input type="hidden" class="pfc-media-id pfc-video-id" name="<?php echo $name; ?>[video_id]" value="<?php echo esc_attr($video_id); ?>">
                    <span class="pfc-preview pfc-video-preview"><?php if ($video_id) echo esc_html(basename(get_attached_file($video_id))); ?></span>

                    <button type="button" class="button pfc-choose pfc-choose-poster">Choose Poster Image</button>
                    <input type="hidden" class="pfc-media-id pfc-poster-id" name="<?php echo $name; ?>[poster_id]" value="<?php echo esc_attr($poster_id); ?>">
                    <span class="pfc-preview pfc-poster-preview"><?php if ($poster_id) echo wp_get_attachment_image($poster_id, 'thumbnail'); ?></span>
                </div>

                <label class="pfc-field pfc-back-text">
                    Back text
                    <textarea name="<?php echo $name; ?>[back_text]" rows="3"><?php echo esc_textarea($back_text); ?></textarea>
                </label>

                <label class="pfc-field pfc-show-see-more-toggle">
                    <input type="checkbox" class="pfc-show-see-more" name="<?php echo $name; ?>[show_see_more]" value="1" <?php checked($show_see_more); ?>>
                    Show "see more" link?
                </label>

                <label class="pfc-field pfc-field-see-more">
                    See more URL
                    <input type="url" name="<?php echo $name; ?>[see_more_url]" value="<?php echo esc_attr($see_more_url); ?>">
                </label>
            </div>
            <button type="button" class="button-link pfc-remove-row" aria-label="Remove card">×</button>
        </div>
        <?php
    }
}
?>
<div id="pfc-wrap">
    <div class="pfc-rows">
        <?php foreach ($cards as $i => $card): ?>
            <?php pfc_render_row($i, $card); ?>
        <?php endforeach; ?>
    </div>

    <p>
        <button type="button" class="button button-primary" id="pfc-add-row">Add card</button>
    </p>

    <script type="text/html" id="pfc-row-template">
        <?php pfc_render_row('__INDEX__', []); ?>
    </script>
</div>
