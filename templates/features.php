<!-- Features Section -->
<section class="content-section features-section">
    <?php
    // Get features stories
    $features_query = get_portfolio_stories('features', 6);

    if ($features_query->have_posts()) {
        $story_count = 0;
        $stories = [];

        // Collect stories into array
        while ($features_query->have_posts()) {
            $features_query->the_post();
            $stories[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'image_url' => get_story_featured_image(get_the_ID(), 'large'),
                'metadata' => get_story_metadata(get_the_ID()),
                'permalink' => get_permalink()
            ];
        }
        wp_reset_postdata();

        if (!empty($stories)) {
            $first_story = $stories[0];
            $remaining_stories = array_slice($stories, 1);
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
                            <p><?php echo $first_story['excerpt']; ?></p>
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
                    <div class="story-image">
                        <img src="<?php echo $first_story['image_url']; ?>" alt="<?php echo $first_story['title']; ?>">
                        <?php if (!empty($first_story['metadata']['photo_credit'])) : ?>
                            <div class="caption">photograph: <?php echo $first_story['metadata']['photo_credit']; ?></div>
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
        }
    } else {
        echo '<p class="no-stories-message">No features stories found.</p>';
    }
    ?>
</section>
