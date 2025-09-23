<!-- Featured Story Full Bleed -->
<section class="featured-story-full-bleed" id="top">
    <div class="full-bleed-background"
         data-fallback-image="<?php echo esc_url(home_url('/wp-content/uploads/2025/06/Reuben-j-brown-multimedia-journalist-homepage-images-draft13.webp')); ?>">
        <video autoplay muted loop playsinline>
            <source src="<?php echo esc_url(home_url('/wp-content/uploads/2025/07/Eviction-of-Cortijo-El-Uno-Almeria-Spain-Greenhouse-Farms-Invernadero-Reuben-J-Brown.mp4')); ?>" type="video/mp4">
        </video>
        <div class="fallback-image" style="background-image: url('<?php echo esc_url(home_url('/wp-content/uploads/2025/06/Reuben-j-brown-multimedia-journalist-homepage-images-draft13.webp')); ?>')"></div>
    </div>
    <div class="full-bleed-content">
        <div class="story-text">
            <a href="https://www.panoramicthemagazine.com/post/the-cost-of-a-miracle" target="_blank" rel="noopener noreferrer">
                <h2 class="serif-font-scaled"><i>The Cost of a Miracle</i></h2>
                <h3>In Europe’s driest region, a vast plastic sea covers an agricultural system built on intensity and innovation. But the cracks in Almeria's miracle are growing, too</h3>
                <p class="story-meta">For <i>Panoramic</i> in May 2025</p>
            </a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('.featured-story-full-bleed video');
    const background = document.querySelector('.full-bleed-background');

    if (video && background) {
        // Handle video load errors
        video.addEventListener('error', function() {
            console.log('Video failed to load, showing fallback image');
            background.classList.add('video-error');
        });

        // Handle video stall/timeout (slow connection) - but only if video hasn't loaded yet
        video.addEventListener('stalled', function() {
            if (video.readyState < 3) { // Only show fallback if video hasn't loaded enough data
                console.log('Video stalled during loading, showing fallback image');
                background.classList.add('video-error');
            }
        });

        // Timeout fallback for very slow connections
        const loadTimeout = setTimeout(function() {
            if (video.readyState < 3) { // HAVE_FUTURE_DATA
                console.log('Video load timeout, showing fallback image');
                background.classList.add('video-error');
            }
        }, 3000); // 3 second timeout

        // Clear timeout if video loads successfully
        video.addEventListener('canplaythrough', function() {
            clearTimeout(loadTimeout);
            background.classList.remove('video-error');
        });

        // Ensure video keeps looping and playing
        video.addEventListener('ended', function() {
            video.currentTime = 0;
            video.play();
        });

        // Handle video pause (ensure it restarts)
        video.addEventListener('pause', function() {
            if (!background.classList.contains('video-error')) {
                video.play();
            }
        });

        // Also handle if video source fails to load
        video.addEventListener('loadstart', function() {
            const source = video.querySelector('source');
            if (source) {
                source.addEventListener('error', function() {
                    console.log('Video source failed to load, showing fallback image');
                    background.classList.add('video-error');
                });
            }
        });
    }
});
</script>
