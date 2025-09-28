<!-- Architecture Section -->
<section class="content-section">
    <div class="section-container">
        <div class="stories-content">
            <div class="strategy-intro">
                <h3 class="serif-font-scaled">Before this, I was Digital Editor at <i>The Architectural Review</i>, where I wrote on architecture and politics and worked on cross-platform audience strategy. I graduated from the University of Cambridge, where I was president of the architecture society, delivering an exhibition of 250 students’ work in London. I was Production Assistant at The World Around, a New York-based design platform where I helped to launch the Young Climate Prize, and I worked on TV shoots for BBC Maestro.</h3>
            </div>
            <?php
            // Get architecture stories using the same function as other sections
            $architecture_query = get_portfolio_stories('profiles', 11);
            
            if ($architecture_query->have_posts()) {
                $story_count = 0;
                $stories = [];
                
                // Collect stories into array
                while ($architecture_query->have_posts()) {
                    $architecture_query->the_post();
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
                    $featured_stories = array_slice($stories, 0, 2); // First two for featured grid
                    $remaining_stories = array_slice($stories, 2);   // Rest for horizontal scroll
            ?>
                    <!-- Top Two Featured Stories Grid -->
                    <?php if (!empty($featured_stories)) : ?>
                        <div class="architecture-featured-grid">
                            <?php foreach ($featured_stories as $story) : ?>
                                <article class="story-item">
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
                                            <h2 class="serif-font-scaled"><?php echo $story['title']; ?></h2>
                                            <?php if (!empty($story['excerpt'])) : ?>
                                                <p><?php echo $story['excerpt']; ?></p>
                                            <?php endif; ?>
                                            <p class="story-meta">
                                                <?php if (!empty($story['metadata']['publication'])) : ?>
                                                    For <i><?php echo $story['metadata']['publication']; ?></i>
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
                    <?php endif; ?>

                    <!-- Horizontal Scroll Area -->
                    <?php if (!empty($remaining_stories)) : ?>
                        <div class="architecture-scroll">
                            <?php foreach ($remaining_stories as $story) : ?>
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
                                            <h2 class="serif-font-scaled"><?php echo !empty($story['excerpt']) ? $story['excerpt'] : $story['title']; ?></h2>
                                            <p><?php echo $story['title']; ?></p>
                                            <p class="story-meta">
                                                <?php if (!empty($story['metadata']['publication'])) : ?>
                                                    For <i><?php echo $story['metadata']['publication']; ?></i>
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
                    <?php endif; ?>
            <?php
                } else {
                    echo '<p class="no-stories-message">No architecture stories found.</p>';
                }
            } else {
                echo '<p class="no-stories-message">No architecture stories found.</p>';
            }
            ?>
        </div>
    </div>
</section>
