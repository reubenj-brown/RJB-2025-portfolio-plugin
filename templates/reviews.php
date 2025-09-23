<!-- Reviews Section -->
<section class="content-section">
    <div class="section-container">
        <div class="reviews-content">
                        <div class="strategy-intro">
                <h3 class="serif-font-scaled">I have a background in design. My undergraduate degree was in architecture at the University of Cambridge, where I was president of ARCSOC, the student architecture society. My first journalism job was as an editor and critic at <i>The Architectural Review</i>.</h3>
            </div>
            <?php
            // Get reviews stories using the same function as other sections
            $reviews_query = get_portfolio_stories('reviews', 4);
            
            if ($reviews_query->have_posts()) {
                $stories = [];
                
                // Collect stories into array
                while ($reviews_query->have_posts()) {
                    $reviews_query->the_post();
                    $story_id = get_the_ID();
                    $metadata = get_story_metadata($story_id);
                    $featured_image = get_story_featured_image($story_id, 'large');
                    
                    if ($featured_image) {
                        $stories[] = [
                            'id' => $story_id,
                            'title' => get_the_title(),
                            'short_headline' => !empty($metadata['short_headline']) ? $metadata['short_headline'] : get_the_title(),
                            'excerpt' => get_the_excerpt(),
                            'permalink' => get_permalink(),
                            'metadata' => $metadata,
                            'featured_image' => $featured_image
                        ];
                    }
                }
                wp_reset_postdata();
                
                if (!empty($stories)) {
                    $primary_story = $stories[0];
                    $secondary_stories = array_slice($stories, 1, 3); // Get up to 3 secondary stories
            ?>
                    <div class="reviews-layout">
                        <!-- Primary Story - Left Column -->
                        <div class="reviews-primary">
                            <article class="reviews-primary-story">
                                <div class="reviews-primary-image">
                                    <a href="<?php echo !empty($primary_story['metadata']['external_url']) ? esc_url($primary_story['metadata']['external_url']) : esc_url($primary_story['permalink']); ?>" class="reviews-primary-image-link"<?php echo !empty($primary_story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                        <img src="<?php echo esc_url($primary_story['featured_image']); ?>" alt="<?php echo esc_attr($primary_story['title']); ?>" />
                                    </a>
                                </div>
                                <div class="reviews-primary-content">
                                    <a href="<?php echo !empty($primary_story['metadata']['external_url']) ? esc_url($primary_story['metadata']['external_url']) : esc_url($primary_story['permalink']); ?>" class="reviews-primary-text-link"<?php echo !empty($primary_story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                        <h2 class="reviews-primary-headline"><?php echo esc_html($primary_story['short_headline']); ?></h2>
                                        <?php if (!empty($primary_story['excerpt'])) : ?>
                                            <p class="reviews-primary-standfirst"><?php echo esc_html($primary_story['excerpt']); ?></p>
                                        <?php endif; ?>
                                        <p class="reviews-primary-meta">
                                            <?php if (!empty($primary_story['metadata']['publication'])) : ?>
                                                For <i><?php echo esc_html($primary_story['metadata']['publication']); ?></i>
                                            <?php endif; ?>
                                            <?php if (!empty($primary_story['metadata']['publish_date'])) : ?>
                                                <?php echo !empty($primary_story['metadata']['publication']) ? ' in ' : ''; ?>
                                                <?php echo date('F Y', strtotime($primary_story['metadata']['publish_date'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </a>
                                </div>
                            </article>
                        </div>

                        <!-- Secondary Stories - Right Column -->
                        <div class="reviews-secondary">
                            <?php foreach ($secondary_stories as $story) : ?>
                                <article class="reviews-secondary-story">
                                    <a href="<?php echo !empty($story['metadata']['external_url']) ? esc_url($story['metadata']['external_url']) : esc_url($story['permalink']); ?>" class="reviews-secondary-link"<?php echo !empty($story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                        <div class="reviews-secondary-image">
                                            <img src="<?php echo esc_url($story['featured_image']); ?>" alt="<?php echo esc_attr($story['title']); ?>" />
                                        </div>
                                        <div class="reviews-secondary-content">
                                            <h3 class="reviews-secondary-headline"><?php echo esc_html($story['title']); ?></h3>
                                            <p class="reviews-secondary-meta">
                                                <?php if (!empty($story['metadata']['publication'])) : ?>
                                                    For <i><?php echo esc_html($story['metadata']['publication']); ?></i>
                                                <?php endif; ?>
                                                <?php if (!empty($story['metadata']['publish_date'])) : ?>
                                                    <?php echo !empty($story['metadata']['publication']) ? ' in ' : ''; ?>
                                                    <?php echo date('F Y', strtotime($story['metadata']['publish_date'])); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
            <?php
                } else {
                    echo '<p style="text-align: center; color: #808080; padding: 4rem;">No reviews stories found.</p>';
                }
            } else {
                echo '<p style="text-align: center; color: #808080; padding: 4rem;">No reviews stories found.</p>';
            }
            ?>
        </div>
    </div>
</section>
