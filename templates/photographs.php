<!-- Photographs Section -->
<section class="content-section">
    <div class="section-container">
        <div class="photographs-content">
            <?php
            // Get photograph stories using the same function as other sections
            $photos_query = get_portfolio_stories('photographs', 6);
            
            if ($photos_query->have_posts()) {
                $story_count = 0;
                $stories = [];
                
                // Collect stories into array
                while ($photos_query->have_posts()) {
                    $photos_query->the_post();
                    $story_id = get_the_ID();
                    $metadata = get_story_metadata($story_id);
                    $featured_image = get_story_featured_image($story_id, 'large');
                    
                    // Get photo gallery if available (now a repeater field)
                    $photo_gallery = !empty($metadata['photo_gallery']) ? $metadata['photo_gallery'] : [];
                    
                    // Build image array - use gallery if available, otherwise use featured image or fallback
                    $images = [];
                    if (!empty($photo_gallery) && is_array($photo_gallery)) {
                        foreach ($photo_gallery as $gallery_row) {
                            if (!empty($gallery_row['image'])) {
                                $gallery_image = $gallery_row['image'];
                                $images[] = [
                                    'url' => $gallery_image['sizes']['large'] ?? $gallery_image['url'],
                                    'alt' => $gallery_image['alt'] ?? get_the_title()
                                ];
                            }
                        }
                    } else if ($featured_image) {
                        $images[] = [
                            'url' => $featured_image,
                            'alt' => get_the_title()
                        ];
                    }
                    
                    if (!empty($images)) {
                        $stories[] = [
                            'id' => $story_id,
                            'title' => get_the_title(),
                            'short_headline' => !empty($metadata['short_headline']) ? $metadata['short_headline'] : get_the_title(),
                            'excerpt' => get_the_excerpt(),
                            'permalink' => get_permalink(),
                            'metadata' => $metadata,
                            'images' => $images
                        ];
                    }
                }
                wp_reset_postdata();
                
                if (!empty($stories)) {
                    $first_story = $stories[0];
                    $remaining_stories = array_slice($stories, 1);
            ?>
                    <!-- Primary Full-Width Story -->
                    <div class="photo-primary-container">
                        <article class="photo-primary">
                            <a href="<?php echo esc_url($first_story['permalink']); ?>" class="photo-primary-link">
                                <div class="photo-primary-image">
                                    <img src="<?php echo esc_url($first_story['images'][0]['url']); ?>" alt="<?php echo esc_attr($first_story['images'][0]['alt']); ?>" />
                                    <div class="photo-primary-overlay">
                                        <h2 class="photo-primary-headline"><?php echo esc_html($first_story['short_headline']); ?></h2>
                                        <?php if (!empty($first_story['excerpt'])) : ?>
                                            <p class="photo-primary-standfirst"><?php echo esc_html($first_story['excerpt']); ?></p>
                                        <?php endif; ?>
                                        <p class="photo-primary-meta">
                                            <?php if (!empty($first_story['metadata']['publication'])) : ?>
                                                For <i><?php echo esc_html($first_story['metadata']['publication']); ?></i>
                                            <?php endif; ?>
                                            <?php if (!empty($first_story['metadata']['publish_date'])) : ?>
                                                <?php echo !empty($first_story['metadata']['publication']) ? ' in ' : ''; ?>
                                                <?php echo date('F Y', strtotime($first_story['metadata']['publish_date'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>

                    <!-- Secondary Stories Grid -->
                    <?php if (!empty($remaining_stories)) : ?>
                        <div class="photographs-grid">
                            <?php foreach ($remaining_stories as $story) : ?>
                                <article class="photo-item">
                                    <a href="<?php echo esc_url($story['permalink']); ?>" class="photo-link">
                                        <div class="photo-carousel">
                                            <?php foreach ($story['images'] as $index => $image) : ?>
                                                <div class="photo-slide<?php echo $index === 0 ? ' active' : ''; ?>">
                                                    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="photo-overlay">
                                                <h2 class="photo-headline"><?php echo esc_html($story['short_headline']); ?></h2>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
            <?php
                } else {
                    echo '<p style="text-align: center; color: #808080; padding: 4rem;">No photograph stories found.</p>';
                }
            } else {
                echo '<p style="text-align: center; color: #808080; padding: 4rem;">No photograph stories found.</p>';
            }
            ?>
        </div>
    </div>
</section>
