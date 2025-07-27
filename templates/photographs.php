<!-- Photographs Section -->
<section class="content-section">
    <div class="section-container">
        <div class="photographs-content">
            <div class="photographs-grid">
                <?php
                // Get photograph stories using the same function as other sections
                $photos_query = get_portfolio_stories('photographs', 6);
                
                if ($photos_query->have_posts()) {
                    while ($photos_query->have_posts()) {
                        $photos_query->the_post();
                        $story_id = get_the_ID();
                        $metadata = get_story_metadata($story_id);
                        $featured_image = get_story_featured_image($story_id, 'large');
                        
                        // Get photo gallery if available
                        $photo_gallery = !empty($metadata['photo_gallery']) ? $metadata['photo_gallery'] : [];
                        
                        // Build image array - use gallery if available, otherwise use featured image or fallback
                        $images = [];
                        if (!empty($photo_gallery)) {
                            foreach ($photo_gallery as $gallery_image) {
                                $images[] = [
                                    'url' => $gallery_image['sizes']['large'] ?? $gallery_image['url'],
                                    'alt' => $gallery_image['alt'] ?? get_the_title()
                                ];
                            }
                        } else if ($featured_image) {
                            $images[] = [
                                'url' => $featured_image,
                                'alt' => get_the_title()
                            ];
                        }
                        
                        if (empty($images)) continue; // Skip if no images
                        
                        // Use short headline if available, otherwise regular title
                        $headline = !empty($metadata['short_headline']) ? $metadata['short_headline'] : get_the_title();
                        $standfirst = get_the_excerpt();
                        $permalink = get_permalink();
                ?>
                        <article class="photo-item">
                            <a href="<?php echo esc_url($permalink); ?>" class="photo-link">
                                <div class="photo-carousel">
                                    <?php foreach ($images as $index => $image) : ?>
                                        <div class="photo-slide<?php echo $index === 0 ? ' active' : ''; ?>">
                                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="photo-overlay">
                                        <h2 class="photo-headline"><?php echo esc_html($headline); ?></h2>
                                    </div>
                                </div>
                            </a>
                        </article>
                <?php
                    }
                    wp_reset_postdata();
                } else {
                    echo '<p style="text-align: center; color: #808080; padding: 4rem;">No photograph stories found.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</section>
