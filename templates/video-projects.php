<!-- Video Projects Section -->
<section class="content-section">
    <div class="section-container">
        <div class="video-projects-content">
            <div class="strategy-intro">
                <div class="strategy-intro-headline">
                    <span class="display-headline">Video</span>
                </div>
                <div class="strategy-intro-body">
                    <h3>Short-form video projects spanning documentary, reportage, and visual storytelling</h3>
                </div>
            </div>

            <div class="video-grid">

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="#">
                        <video src="#" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>Video Project Title</h2>
                        <p class="story-meta">2:34</p>
                    </div>
                </article>

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="#">
                        <video src="#" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>Video Project Title</h2>
                        <p class="story-meta">1:48</p>
                    </div>
                </article>

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="#">
                        <video src="#" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>Video Project Title</h2>
                        <p class="story-meta">3:12</p>
                    </div>
                </article>

                <article class="video-item">
                    <div class="video-thumbnail" data-video-src="#">
                        <video src="#" muted playsinline preload="metadata"></video>
                        <div class="video-play-btn">
                            <svg viewBox="0 0 24 24" fill="none"><polygon points="8,5 19,12 8,19" fill="white"/></svg>
                        </div>
                    </div>
                    <div class="story-content">
                        <h2>Video Project Title</h2>
                        <p class="story-meta">4:05</p>
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
