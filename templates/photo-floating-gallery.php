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
<?php
$has_image_cards = false;
foreach ($cards as $card):
    $media_type = !empty($card['media_type']) && $card['media_type'] === 'video' ? 'video' : 'image';
    $poster_id  = $media_type === 'video' ? (int) $card['poster_id'] : (int) $card['image_id'];
    // 'wc-card' caps the long edge at 1920 (registered in the main plugin
    // file). Falls back to the full size automatically on any attachment
    // that hasn't had that size generated yet.
    $poster_src = $poster_id ? wp_get_attachment_image_src($poster_id, 'wc-card') : false;
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
    // This also satisfies the watercolor effect's requirement that each card's
    // dimensions be known before render — no layout shift, no hand-typed ratios.
    $card_ratio    = (!empty($poster_src[1]) && !empty($poster_src[2])) ? $poster_src[1] . '/' . $poster_src[2] : '4/5';
    $aria_label    = $poster_alt
        ? sprintf('Reveal the note about %s', $poster_alt)
        : 'Reveal the note about this photograph';
    // The card itself is capped at 1920 for weight; the lightbox can afford
    // the original, since it's only fetched when someone actually expands.
    $full_src      = wp_get_attachment_image_src($poster_id, 'full');
    $full_url      = $full_src ? $full_src[0] : $poster_src[0];
?>
<?php if ($media_type === 'video'): ?>
    <?php /* Video cards are non-interactive: they just play. No canvas, no text
             layer, no click target — so no tabindex/role either. */ ?>
    <div class="photo-card photo-card-video">
        <div class="wc-stage" style="--card-ratio: <?php echo esc_attr($card_ratio); ?>;">
            <img class="wc-poster-img"
                 src="<?php echo esc_url($poster_src[0]); ?>"
                 alt="<?php echo esc_attr($poster_alt); ?>"
                 loading="lazy">
            <video muted loop playsinline preload="none"
                   poster="<?php echo esc_url($poster_src[0]); ?>"
                   data-src="<?php echo esc_url($video_url); ?>"></video>
        </div>
    </div>
<?php else: ?>
    <?php /* Stacking order is load-bearing: poster behind text, canvas on top.
             Poster in front of text makes the reveal expose the photo again
             instead of the caption. */ ?>
    <?php $has_image_cards = true; ?>
    <div class="photo-card">
        <div class="wc-stage"
             data-src="<?php echo esc_url($poster_src[0]); ?>"
             style="--card-ratio: <?php echo esc_attr($card_ratio); ?>;"
             tabindex="0" role="button"
             aria-label="<?php echo esc_attr($aria_label); ?>">
            <div class="wc-layer wc-poster"></div>
            <div class="wc-layer wc-text">
                <?php if ($back_text): ?>
                    <div class="wc-text-body"><?php echo nl2br(esc_html($back_text)); ?></div>
                <?php endif; ?>
                <div class="wc-card-actions">
                    <button type="button" class="wc-expand"
                            data-card="<?php echo esc_url($poster_src[0]); ?>"
                            data-full="<?php echo esc_url($full_url); ?>"
                            data-alt="<?php echo esc_attr($poster_alt); ?>">Expand ↗</button>
                    <?php if ($show_see_more): ?>
                        <a href="<?php echo esc_url($card['see_more_url']); ?>" class="wc-read-more">Read more →</a>
                    <?php endif; ?>
                </div>
            </div>
            <canvas class="wc-layer wc-gl"></canvas>
        </div>
    </div>
<?php endif; ?>
<?php endforeach; ?>
</div>
<?php if ($has_image_cards): ?>
<?php /* No close button: clicking anywhere dismisses, and Escape closes
         immediately. tabindex allows focus to move into the overlay when it
         opens, so Escape and the arrow keys land somewhere sensible and
         focus isn't left behind on the page underneath. */ ?>
<div class="photo-lightbox" id="photoLightbox" role="dialog" aria-modal="true"
     aria-label="Expanded photograph" tabindex="-1">
    <?php /* The figure is sized to the photo's own aspect ratio inside the
             96vw/96vh box, so the canvas matches the image exactly and the
             shader's cover-fit is a no-op. The <img> is the fallback shown
             when WebGL is unavailable or motion is reduced. */ ?>
    <div class="photo-lightbox-figure">
        <img class="photo-lightbox-image" src="" alt="">
        <canvas class="photo-lightbox-gl"></canvas>
    </div>
</div>
<?php endif; ?>
<script>
/* Shuffles the cards and gives each a randomized scale and horizontal offset.
   Done here rather than in PHP so the full-page cache can't freeze one
   "random" arrangement for every visitor, and inline during parse — after the
   grid, before first paint — so there's no flash and no reflow.

   scale  in [0.66, 1.00] of the column width
   offset in [0, 1 - scale] — so scale + offset never exceeds 1 and a card
   mathematically cannot cross into the next column.

   Shuffling moves the elements themselves rather than using CSS `order`, so
   DOM order still matches visual order and the reading and tab order follow
   what's actually on screen. It also keeps :nth-child() meaningful, which the
   half-drop stagger and the mobile left/right pinning both depend on. The
   order no longer matches the CMS, which is the point.

   This runs before watercolor-reveal.js (deferred, and waiting on
   DOMContentLoaded), so the lightbox's arrow-key navigation picks up the
   shuffled order too. */
(function () {
    var grid = document.getElementById('photoFloatingGrid');
    if (!grid) return;
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.photo-card'));

    // Fisher-Yates
    for (var i = cards.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var swap = cards[i];
        cards[i] = cards[j];
        cards[j] = swap;
    }

    // Re-inserted via a fragment, so the reorder costs one reflow, not one per card.
    var frag = document.createDocumentFragment();
    for (var k = 0; k < cards.length; k++) {
        var s = 0.66 + Math.random() * 0.34;
        cards[k].style.setProperty('--card-scale', s.toFixed(4));
        cards[k].style.setProperty('--card-offset', (Math.random() * (1 - s)).toFixed(4));
        frag.appendChild(cards[k]);
    }
    grid.appendChild(frag);
})();
</script>
