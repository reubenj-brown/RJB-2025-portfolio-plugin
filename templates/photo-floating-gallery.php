<?php
if (!defined('ABSPATH')) {
    exit;
}

$cards = get_post_meta(get_the_ID(), '_photo_floating_cards', true);
if (empty($cards) || !is_array($cards)) {
    return;
}
?>
<div class="photo-floating-grid" id="photoFloatingGrid">
<?php foreach ($cards as $card):
    $media_type = !empty($card['media_type']) && $card['media_type'] === 'video' ? 'video' : 'image';
    $poster_id  = $media_type === 'video' ? (int) $card['poster_id'] : (int) $card['image_id'];
    $poster_src = $poster_id ? wp_get_attachment_image_src($poster_id, 'large') : false;
    if (!$poster_src) {
        continue;
    }
    $video_url = '';
    if ($media_type === 'video') {
        $video_url = wp_get_attachment_url((int) $card['video_id']);
        if (!$video_url) {
            continue;
        }
    }
    $poster_alt    = get_post_meta($poster_id, '_wp_attachment_image_alt', true);
    $back_text     = !empty($card['back_text']) ? $card['back_text'] : '';
    $show_see_more = !empty($card['show_see_more']) && !empty($card['see_more_url']);
    // Card box matches the media's real aspect ratio, so nothing gets cropped.
    $card_ratio    = (!empty($poster_src[1]) && !empty($poster_src[2])) ? $poster_src[1] . '/' . $poster_src[2] : '4/5';
?>
    <div class="flip-card" data-media="<?php echo esc_attr($media_type); ?>">
        <div class="flip-card-inner" style="--card-ratio: <?php echo esc_attr($card_ratio); ?>;">
            <div class="flip-card-front">
                <img class="flip-card-poster"
                     src="<?php echo esc_url($poster_src[0]); ?>"
                     alt="<?php echo esc_attr($poster_alt); ?>"
                     loading="lazy">
                <?php if ($media_type === 'video'): ?>
                    <video muted loop playsinline preload="none"
                           poster="<?php echo esc_url($poster_src[0]); ?>"
                           data-src="<?php echo esc_url($video_url); ?>"></video>
                <?php endif; ?>
            </div>
            <div class="flip-card-back">
                <?php if ($back_text): ?>
                    <div class="flip-card-back-text"><?php echo nl2br(esc_html($back_text)); ?></div>
                <?php endif; ?>
                <?php if ($show_see_more): ?>
                    <a href="<?php echo esc_url($card['see_more_url']); ?>" class="flip-card-see-more">See more →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
