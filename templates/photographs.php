<!-- Photographs Section - Updated Classes -->
<section class="content-section">
    <div class="section-container">
        <div class="photographs-content">
            <div class="strategy-intro">
                <h3 class="serif-font-scaled">Photographs are an integrated part of my practice — and I’ve assisted leading editorial and architectural photographers across shoots for magazines and TV</h3>
            </div> 
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

                    // Build image array - prefer Original Image URL, fallback to featured image
                    $images = [];

                    // TEMPORARY WORKAROUND: Hard-coded high-res URLs by story title
                    $story_title = get_the_title();
                    $high_res_urls = [
                        'Anti-racist protestors quell the violence in Walthamstow' => 'https://reubenjbrown.com/wp-content/uploads/2025/08/Walthamstow-anti-racist-rally-march-august-7-2024-Reuben-J-Brown-photojournalism-1.webp',
                        // Add more stories here as needed
                    ];

                    if (isset($high_res_urls[$story_title])) {
                        // Use hard-coded high-res URL
                        $images[] = [
                            'url' => $high_res_urls[$story_title],
                            'alt' => get_the_title()
                        ];
                    } elseif (!empty($metadata['original_image_url'])) {
                        // Use Original Image URL from metadata if available
                        $images[] = [
                            'url' => $metadata['original_image_url'],
                            'alt' => get_the_title()
                        ];
                    } else {
                        // Fallback to featured image if Original Image URL not set
                        $featured_image = get_story_featured_image($story_id, 'large');
                        if ($featured_image) {
                            $images[] = [
                                'url' => $featured_image,
                                'alt' => get_the_title()
                            ];
                        }
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

                    // PHOTO PRIMARY DEBUG - Single focused debug message
                    $debug_msg = "🖼️ PHOTO PRIMARY: " . $first_story['title'] . " | ";
                    $debug_msg .= "Using URL: " . $first_story['images'][0]['url'] . " | ";
                    $debug_msg .= "Original URL " . (!empty($first_story['metadata']['original_image_url']) ?
                        "EXISTS: " . $first_story['metadata']['original_image_url'] : "NOT SET");
                    error_log($debug_msg);
            ?>
                    <!-- Primary Full-Width Story -->
                    <div class="photo-primary-container">
                        <article class="photo-primary">
                            <a href="<?php echo !empty($first_story['metadata']['external_url']) ? esc_url($first_story['metadata']['external_url']) : esc_url($first_story['permalink']); ?>" class="photo-primary-link"<?php echo !empty($first_story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                <div class="photo-primary-image">
                                    <img src="<?php echo esc_url($first_story['images'][0]['url']); ?>" alt="<?php echo esc_attr($first_story['images'][0]['alt']); ?>" />
                                    <div class="photo-primary-overlay">
                                        <h2 class="photo-primary-headline"><?php echo esc_html($first_story['short_headline']); ?></h2>
                                        <?php if (!empty($first_story['excerpt'])) : ?>
                                            <p class="photo-primary-standfirst"><?php echo esc_html($first_story['excerpt']); ?></p>
                                        <?php endif; ?>
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
                                    <div class="features-story-main">
                                        <div class="story-content">
                                            <h2 class="serif-font-scaled">
                                                <a href="<?php echo !empty($story['metadata']['external_url']) ? esc_url($story['metadata']['external_url']) : esc_url($story['permalink']); ?>"<?php echo !empty($story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                                    <?php echo esc_html($story['short_headline']); ?>
                                                </a>
                                            </h2>
                                            <?php if (!empty($story['excerpt'])) : ?>
                                                <p><?php echo esc_html($story['excerpt']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="story-image">
                                            <img src="<?php echo esc_url($story['images'][0]['url']); ?>" alt="<?php echo esc_attr($story['images'][0]['alt']); ?>" />
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
            <?php
                } else {
                    echo '<p class="no-stories-message">No photograph stories found.</p>';
                }
            } else {
                echo '<p class="no-stories-message">No photograph stories found.</p>';
            }
            ?>
        </div>
    </div>
</section>
