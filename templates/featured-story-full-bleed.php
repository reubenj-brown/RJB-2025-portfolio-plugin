<!-- Featured Story Full Bleed -->
<?php
$hero_fallback = home_url('/wp-content/uploads/2025/11/reuben-j-brown-almeria-greenhouses-el-ejido-agriculture.avif');
$hero_video    = home_url('/wp-content/uploads/2025/07/Eviction-of-Cortijo-El-Uno-Almeria-Spain-Greenhouse-Farms-Invernadero-Reuben-J-Brown.mp4');
?>
<section class="featured-story-full-bleed" id="top">
    <div class="full-bleed-background">
        <!-- AVIF still is the LCP element: loaded eagerly at high priority -->
        <img class="fallback-image"
             src="<?php echo esc_url($hero_fallback); ?>"
             alt=""
             width="2560" height="1600"
             fetchpriority="high" decoding="async">
        <!-- Video is deferred: source attached + played by JS after window load -->
        <video class="full-bleed-video" muted loop playsinline preload="none"
               data-src="<?php echo esc_url($hero_video); ?>"></video>
    </div>
    <div class="full-bleed-content">
        <div class="story-text">
            <a href="/stories/the-cost-of-a-miracle/" target="_blank" rel="noopener noreferrer">
                <p class="featured-kicker">Featured story</p>
                <h1>The Cost of a Miracle</h1>
                <h3>In Europe’s driest region, a vast plastic sea covers an agricultural system built on intensity and innovation — but the cracks in Almería’s miracle are growing, too</h3>
                <p class="story-meta">For <i>Panoramic</i> ⋅ May 2025</p>
            </a>
        </div>
    </div>
</section>

<script>
(function() {
    const video = document.querySelector('.featured-story-full-bleed video');
    const background = document.querySelector('.full-bleed-background');
    if (!video || !background) return;

    // The AVIF still is already painted (LCP). Only start the video once the
    // page has finished loading so it never competes with the critical path.
    function loadHeroVideo() {
        // Bail out gracefully on unsupported / save-data / very slow connections.
        if (!video.canPlayType || !video.canPlayType('video/mp4')) return;
        const conn = navigator.connection;
        if (conn && (conn.saveData || /^(slow-2g|2g)$/.test(conn.effectiveType || ''))) return;

        video.src = video.dataset.src;

        video.addEventListener('canplaythrough', function() {
            background.classList.add('video-loaded'); // fades the AVIF out via CSS
        });
        // Keep it looping/playing.
        video.addEventListener('ended', function() { video.currentTime = 0; video.play(); });
        video.addEventListener('pause', function() {
            if (background.classList.contains('video-loaded')) video.play();
        });
        // On any failure just leave the AVIF still showing.
        video.addEventListener('error', function() { background.classList.remove('video-loaded'); });

        video.load();
        const p = video.play();
        if (p && p.catch) p.catch(function() {});
    }

    function schedule() {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(loadHeroVideo, { timeout: 2000 });
        } else {
            setTimeout(loadHeroVideo, 200);
        }
    }

    if (document.readyState === 'complete') schedule();
    else window.addEventListener('load', schedule);
})();
</script>
