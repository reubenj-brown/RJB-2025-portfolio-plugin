<?php
if (!defined('ABSPATH')) {
    exit;
}

$cards = get_field('photo_floating_cards');
if (empty($cards)) {
    return;
}
?>
<div class="photo-floating-grid" id="photoFloatingGrid">
<?php foreach ($cards as $card):
    $media_type = !empty($card['card_media_type']) ? $card['card_media_type'] : 'image';
    $poster     = $media_type === 'video' ? $card['card_video_poster'] : $card['card_image'];
    if (empty($poster) || empty($poster['url'])) {
        continue;
    }
    if ($media_type === 'video' && empty($card['card_video'])) {
        continue;
    }
    $back_text    = !empty($card['card_back_text']) ? $card['card_back_text'] : '';
    $show_see_more = !empty($card['card_show_see_more']) && !empty($card['card_see_more_url']);
?>
    <div class="flip-card" data-media="<?php echo esc_attr($media_type); ?>">
        <div class="flip-card-inner">
            <div class="flip-card-front">
                <img class="flip-card-poster"
                     src="<?php echo esc_url($poster['url']); ?>"
                     alt="<?php echo esc_attr($poster['alt']); ?>"
                     loading="lazy">
                <?php if ($media_type === 'video'): ?>
                    <video muted loop playsinline preload="none"
                           poster="<?php echo esc_url($poster['url']); ?>"
                           data-src="<?php echo esc_url($card['card_video']['url']); ?>"></video>
                <?php endif; ?>
            </div>
            <div class="flip-card-back">
                <?php if ($back_text): ?>
                    <div class="flip-card-back-text"><?php echo nl2br(esc_html($back_text)); ?></div>
                <?php endif; ?>
                <?php if ($show_see_more): ?>
                    <a href="<?php echo esc_url($card['card_see_more_url']); ?>" class="flip-card-see-more">See more →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
