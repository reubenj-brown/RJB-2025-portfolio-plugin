<!-- Features Section -->
<section class="content-section features-section">
    <?php
    // Build the data array for a single story by ID.
    if (!function_exists('rjb_build_feature_story')) {
        function rjb_build_feature_story($id) {
            return [
                'id' => $id,
                'title' => get_the_title($id),
                'excerpt' => get_the_excerpt($id),
                'image_url' => get_story_featured_image($id, 'large'),
                'metadata' => get_story_metadata($id),
                'permalink' => get_permalink($id)
            ];
        }
    }

    // Find the manually flagged lead story (checkbox in the story editor).
    // If several are flagged, the most recent one wins (default date DESC order).
    $lead_query = new WP_Query([
        'post_type' => 'story',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'tax_query' => [[
            'taxonomy' => 'story_category',
            'field' => 'slug',
            'terms' => 'features'
        ]],
        'meta_query' => [[
            'key' => 'story_lead_feature',
            'value' => '1'
        ]]
    ]);
    $lead_id = !empty($lead_query->posts) ? $lead_query->posts[0] : 0;
    wp_reset_postdata();

    // Pool of features stories, newest first (one spare beyond the 3 on the right).
    $features_query = get_portfolio_stories('features', 5);
    $pool_ids = !empty($features_query->posts) ? wp_list_pluck($features_query->posts, 'ID') : [];
    wp_reset_postdata();

    if ($lead_id || !empty($pool_ids)) {
        if ($lead_id) {
            // Manual lead: pin it left, fill the right with the rest.
            $first_story = rjb_build_feature_story($lead_id);
            $remaining_ids = array_values(array_diff($pool_ids, [$lead_id]));
        } else {
            // No manual lead: newest story is the lead.
            $first_story = rjb_build_feature_story($pool_ids[0]);
            $remaining_ids = array_slice($pool_ids, 1);
        }

        // Cap the right-hand column at three stories.
        $remaining_ids = array_slice($remaining_ids, 0, 3);
        $remaining_stories = array_map('rjb_build_feature_story', $remaining_ids);
    ?>
            <!-- Left half - Main featured story -->
            <div class="features-left" #stories>
                <div class="features-story-main">
                    <div class="story-content">
                        <h1>
                            <a href="<?php echo !empty($first_story['metadata']['external_url']) ? esc_url($first_story['metadata']['external_url']) : $first_story['permalink']; ?>"<?php echo !empty($first_story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                <?php echo !empty($first_story['metadata']['short_headline']) ? $first_story['metadata']['short_headline'] : $first_story['title']; ?>
                            </a>
                        </h1>
                        <?php if (!empty($first_story['excerpt'])) : ?>
                            <h2><?php echo $first_story['excerpt']; ?></h2>
                        <?php endif; ?>
                        <p class="story-meta">
                            <?php if (!empty($first_story['metadata']['medium'])) : ?>
                                <?php echo $first_story['metadata']['medium']; ?>
                            <?php endif; ?>
                            <?php if (!empty($first_story['metadata']['publication'])) : ?>
                                <?php echo !empty($first_story['metadata']['medium']) ? ' for ' : 'For '; ?><i><?php echo $first_story['metadata']['publication']; ?></i>
                            <?php endif; ?>
                            <?php if (!empty($first_story['metadata']['publish_date'])) : ?>
                                <?php echo !empty($first_story['metadata']['publication']) ? ' in ' : ''; ?>
                                <?php echo date('F Y', strtotime($first_story['metadata']['publish_date'])); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="features-story-media">
                        <div class="story-image">
                            <img src="<?php echo $first_story['image_url']; ?>" alt="<?php echo $first_story['title']; ?>">
                        </div>
                        <?php if (!empty($first_story['metadata']['photo_credit'])) : ?>
                            <div class="caption"><?php echo $first_story['metadata']['photo_credit']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right half - Secondary stories -->
            <div class="features-right">
                <?php foreach ($remaining_stories as $story) : ?>
                    <div class="features-story-small">
                        <div class="story-content">
                            <h2>
                                <a href="<?php echo !empty($story['metadata']['external_url']) ? esc_url($story['metadata']['external_url']) : $story['permalink']; ?>"<?php echo !empty($story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                    <?php echo $story['title']; ?>
                                </a>
                            </h2>
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
                        <div class="story-image">
                            <img src="<?php echo $story['image_url']; ?>" alt="<?php echo $story['title']; ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
    <?php
    } else {
        echo '<p class="no-stories-message">No features stories found.</p>';
    }
    ?>
</section>
