<?php
/**
 * More Stories Block Template
 * Horizontal scroller with recent stories - same style as architecture scroller
 */

if (!$stories_query->have_posts()) {
    return;
}

$stories = [];

// Collect stories into array
while ($stories_query->have_posts()) {
    $stories_query->the_post();
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

if (!empty($stories)) :
?>
    <!-- More Stories Horizontal Scroll Area -->
    <div class="more-stories-section">
        <div class="more-stories-scroll">
            <?php foreach ($stories as $story) : ?>
                <article class="more-stories-scroll-item">
                    <a href="<?php echo !empty($story['metadata']['external_url']) ? esc_url($story['metadata']['external_url']) : $story['permalink']; ?>" class="story-link"<?php echo !empty($story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                        <?php if ($story['image_url']) : ?>
                            <div class="story-image">
                                <img src="<?php echo $story['image_url']; ?>" alt="<?php echo $story['title']; ?>" />
                                <?php if (!empty($story['metadata']['photo_credit'])) : ?>
                                    <div class="caption"><?php echo $story['metadata']['photo_credit']; ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="story-content">
                            <h2 class="more-stories-headline"><?php echo $story['title']; ?></h2>
                            <p class="more-stories-title"><?php echo !empty($story['excerpt']) ? $story['excerpt'] : $story['title']; ?></p>
                            <p class="more-stories-meta">
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
    </div>
<?php endif; ?>