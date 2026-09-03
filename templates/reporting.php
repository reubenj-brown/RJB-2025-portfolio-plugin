<!-- Reporting Section -->
<div class="reporting-section">
    <!-- Intro headline/body (from Cronkite section) -->
    <div class="strategy-intro">
        <div class="strategy-intro-headline">
            <span class="display-headline">Reporting</span>
        </div>
        <div class="strategy-intro-body">
            <h3>I’m currently pursuing an M.A. in Investigative Journalism at the Walter Cronkite School in Phoenix, where I am currently a reporter at the <a href="https://howardcenter.asu.edu">Howard Center for Investigative Journalism</a> and was a previosuly a <a href="https://cronkite.asu.edu/specializations/business-journalism-fellowship-graduate-fellowship/">Steele Fellow</a> in Investigative Business Journalism</h3>
        </div>
    </div>

    <!-- Features grid -->
    <?php echo do_shortcode('[reuben_features]'); ?>

    <!-- Reporting scroller -->
    <section class="content-section">
        <div class="section-container">
            <div class="stories-content">
                <?php
                // Get reporting stories (same set the grid used)
                $reporting_query = get_portfolio_stories('reporting', 12);

                if ($reporting_query->have_posts()) {
                    $stories = [];

                    // Collect stories into array
                    while ($reporting_query->have_posts()) {
                        $reporting_query->the_post();
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
                ?>
                        <!-- Horizontal Scroll Area -->
                        <div class="architecture-scroll">
                            <?php foreach ($stories as $story) : ?>
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
                                            <h2><?php echo $story['title']; ?></h2>
                                            <?php if (!empty($story['excerpt'])) : ?>
                                                <p><?php echo $story['excerpt']; ?></p>
                                            <?php endif; ?>
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
                    } else {
                        echo '<p class="no-stories-message">No reporting stories found.</p>';
                    }
                } else {
                    echo '<p class="no-stories-message">No reporting stories found.</p>';
                }
                ?>
            </div>
        </div>
    </section>
</div>
