<!-- Video Projects Section -->
<section class="content-section">
    <div class="section-container">
        <div class="video-projects-content">
            <div class="strategy-intro">
                <div class="strategy-intro-headline">
                    <span class="display-headline">Video</span>
                </div>
            </div>

            <div class="video-grid">

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/Detention_Centers_Arizona_Surprise_Reuben_J_Brown_1080p.mp4')); ?>">
                        <video src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/Detention_Centers_Arizona_Surprise_Reuben_J_Brown_1080p.mp4')); ?>" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>ICE Is Turning Warehouses Into Detention Centers</h2>
                        <p class="story-meta">2:27</p>
                    </div>
                </article>

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/San_Rafael_Valley_Border_Wall_Reuben_J_Brown.mp4')); ?>">
                        <video src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/San_Rafael_Valley_Border_Wall_Reuben_J_Brown.mp4')); ?>" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>New Border Wall Construction in the San Rafael Valley</h2>
                        <p class="story-meta">2:19</p>
                    </div>
                </article>

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/Reynolds_Center_Short-Form-Vertical-Video-Publisher-Revenue_Reuben_J_Brown.mp4')); ?>">
                        <video src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/Reynolds_Center_Short-Form-Vertical-Video-Publisher-Revenue_Reuben_J_Brown.mp4')); ?>" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>Why News Publishers Are Making Videos Like This One</h2>
                        <p class="story-meta">2:29</p>
                    </div>
                </article>

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="<?php echo esc_url(home_url('/wp-content/uploads/2026/02/CRIT_Cronkite_News_RIVER_REUBEN_J_BROWN_Jan15.mp4')); ?>">
                        <video src="#" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>The Colorado River Becomes a Legal Person</h2>
                        <p class="story-meta">2:43</p>
                    </div>
                </article>

            </div>
        </div>
    </div>
</section>

<!-- Video Lightbox -->
<div class="video-lightbox" id="videoProjectsLightbox">
    <button class="video-lightbox-close" aria-label="Close video">&times;</button>
    <video class="video-lightbox-player" controls playsinline></video>
</div>

<script>
(function() {
    var lightbox = document.getElementById('videoProjectsLightbox');
    var player = lightbox.querySelector('.video-lightbox-player');
    var closeBtn = lightbox.querySelector('.video-lightbox-close');

    document.querySelectorAll('.video-thumbnail').forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            var src = this.getAttribute('data-video-src');
            if (!src || src === '#') return;
            player.src = src;
            lightbox.classList.add('active');
            player.play();
        });
    });

    function closeLightbox() {
        player.pause();
        player.removeAttribute('src');
        player.load();
        lightbox.classList.remove('active');
    }

    closeBtn.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) closeLightbox();
    });
})();
</script>
