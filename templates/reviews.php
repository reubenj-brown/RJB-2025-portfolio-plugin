<!-- Reviews & Architecture Section -->
<section class="content-section">
    <div class="section-container">
        <div class="reviews-content">
            <div class="strategy-intro">
                <div class="strategy-intro-headline">
                    <span class="display-headline">Reviews</span>
                </div>
                <div class="strategy-intro-body">
                    <h3>After graduating from the University of Cambridge I was Digital Editor of <i>The Architectural Review</i>, where I wrote on architecture and politics and worked on cross-platform audience strategy</h3>
                </div>
            </div>
            <?php
            // Manually pinned primary story (by slug). Change this slug to feature a different story.
            $primary_slug = 'a-book-and-exhibition-highlight-the-aesthetic-triumph-of-protest-architecture-but-its-political-record-needs-examining-too';

            // Helper: build the story array used by this template from a post ID
            $build_review_story = function ($story_id) {
                $featured_image = get_story_featured_image($story_id, 'large');
                if (!$featured_image) {
                    return null;
                }
                $metadata = get_story_metadata($story_id);
                return [
                    'id' => $story_id,
                    'title' => get_the_title($story_id),
                    'short_headline' => !empty($metadata['short_headline']) ? $metadata['short_headline'] : get_the_title($story_id),
                    'excerpt' => get_the_excerpt($story_id),
                    'permalink' => get_permalink($story_id),
                    'metadata' => $metadata,
                    'featured_image' => $featured_image
                ];
            };

            // Resolve the pinned primary story from its slug
            $primary_post = get_page_by_path($primary_slug, OBJECT, 'story');
            $primary_story = $primary_post ? $build_review_story($primary_post->ID) : null;

            // Get reviews stories for the secondary column (request one extra in case the primary is among them)
            $reviews_query = get_portfolio_stories('reviews', 5);

            if ($reviews_query->have_posts() || $primary_story) {
                $stories = [];

                // Collect stories into array
                while ($reviews_query->have_posts()) {
                    $reviews_query->the_post();
                    $story_id = get_the_ID();

                    // Skip the pinned primary story so it doesn't also appear as a secondary
                    if ($primary_story && $story_id === $primary_story['id']) {
                        continue;
                    }

                    $story = $build_review_story($story_id);
                    if ($story) {
                        $stories[] = $story;
                    }
                }
                wp_reset_postdata();

                // Fall back to the first auto-pulled story if the pinned slug wasn't found
                if (!$primary_story && !empty($stories)) {
                    $primary_story = array_shift($stories);
                }

                if ($primary_story) {
                    $secondary_stories = array_slice($stories, 0, 3); // Get up to 3 secondary stories
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
                                        <h1><?php echo esc_html($primary_story['short_headline']); ?></h1>
                                        <?php if (!empty($primary_story['excerpt'])) : ?>
                                            <p class="reviews-primary-standfirst"><?php echo esc_html($primary_story['excerpt']); ?></p>
                                        <?php endif; ?>
                                        <p class="story-meta">
                                            <?php if (!empty($primary_story['metadata']['medium'])) : ?>
                                                <?php echo esc_html($primary_story['metadata']['medium']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($primary_story['metadata']['publication'])) : ?>
                                                <?php echo !empty($primary_story['metadata']['medium']) ? ' for ' : 'For '; ?><i><?php echo esc_html($primary_story['metadata']['publication']); ?></i>
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
                                            <h2><?php echo esc_html($story['title']); ?></h2>
                                            <p class="story-meta">
                                                <?php if (!empty($story['metadata']['medium'])) : ?>
                                                    <?php echo esc_html($story['metadata']['medium']); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($story['metadata']['publication'])) : ?>
                                                    <?php echo !empty($story['metadata']['medium']) ? ' for ' : 'For '; ?><i><?php echo esc_html($story['metadata']['publication']); ?></i>
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
                    echo '<p class="no-stories-message">No reviews stories found.</p>';
                }
            } else {
                echo '<p class="no-stories-message">No reviews stories found.</p>';
            }
            ?>

            <!-- Architecture Stories Horizontal Scroller -->
            <?php
            // Get architecture stories (profiles category) - get ALL stories for the scroller
            $architecture_query = get_portfolio_stories('profiles', -1); // -1 gets all posts

            if ($architecture_query->have_posts()) {
                $architecture_stories = [];

                // Collect architecture stories into array
                while ($architecture_query->have_posts()) {
                    $architecture_query->the_post();
                    $architecture_stories[] = [
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'excerpt' => get_the_excerpt(),
                        'image_url' => get_story_featured_image(get_the_ID(), 'large'),
                        'metadata' => get_story_metadata(get_the_ID()),
                        'permalink' => get_permalink()
                    ];
                }
                wp_reset_postdata();

                if (!empty($architecture_stories)) :
            ?>
                    <!-- Architecture Horizontal Scroll Area -->
                    <div class="architecture-scroll">
                        <?php foreach ($architecture_stories as $story) : ?>
                            <article class="architecture-scroll-item">
                                <a href="<?php echo !empty($story['metadata']['external_url']) ? esc_url($story['metadata']['external_url']) : $story['permalink']; ?>" class="story-link"<?php echo !empty($story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                    <?php if ($story['image_url']) : ?>
                                        <div class="story-image">
                                            <img src="<?php echo $story['image_url']; ?>" alt="<?php echo $story['title']; ?>" />
                                        </div>
                                        <?php if (!empty($story['metadata']['photo_credit'])) : ?>
                                            <div class="caption"><?php echo $story['metadata']['photo_credit']; ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div class="story-content">
                                        <h2><?php echo !empty($story['excerpt']) ? $story['excerpt'] : $story['title']; ?></h2>
                                        <p><?php echo $story['title']; ?></p>
                                        <p class="story-meta">
                                            <?php if (!empty($story['metadata']['medium'])) : ?>
                                                <?php echo $story['metadata']['medium']; ?>
                                            <?php endif; ?>
                                            <?php if (!empty($story['metadata']['publication'])) : ?>
                                                <?php echo !empty($story['metadata']['medium']) ? ' for ' : 'For '; ?><i><?php echo $story['metadata']['publication']; ?></i>
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
            <?php
                endif;
            }
            ?>
        </div>
    </div>
</section>
