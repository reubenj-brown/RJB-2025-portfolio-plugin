<!-- More Stories Section -->
<?php
// Get the limit from shortcode attributes
$limit = isset($atts['limit']) ? intval($atts['limit']) : 5;

// Query most recent stories
$stories_query = new WP_Query([
    'post_type' => 'story',
    'posts_per_page' => $limit,
    'orderby' => 'date',
    'order' => 'DESC'
]);

$stories = [];

if ($stories_query->have_posts()) {
    while ($stories_query->have_posts()) {
        $stories_query->the_post();

        // Get story metadata
        $publication = get_field('publication');
        $publish_date = get_field('publish_date');
        $external_url = get_field('external_url');
        $photo_credit = get_field('photo_credit');

        // Get featured image
        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');

        $stories[] = [
            'title' => get_the_title(),
            'excerpt' => get_the_excerpt(),
            'permalink' => get_permalink(),
            'image_url' => $image_url,
            'metadata' => [
                'publication' => $publication,
                'publish_date' => $publish_date,
                'external_url' => $external_url,
                'photo_credit' => $photo_credit
            ]
        ];
    }
    wp_reset_postdata();
}
?>

<section class="content-section">
    <div class="section-container">
        <?php if (!empty($stories)) : ?>
            <div class="architecture-scroll">
                <?php foreach ($stories as $story) : ?>
                    <article class="architecture-scroll-item">
                        <?php if ($story['image_url']) : ?>
                            <a href="<?php echo !empty($story['metadata']['external_url']) ? esc_url($story['metadata']['external_url']) : $story['permalink']; ?>" class="story-link"<?php echo !empty($story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                <div class="story-image">
                                    <img src="<?php echo $story['image_url']; ?>" alt="<?php echo $story['title']; ?>" />
                                </div>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo !empty($story['metadata']['external_url']) ? esc_url($story['metadata']['external_url']) : $story['permalink']; ?>" class="story-link"<?php echo !empty($story['metadata']['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                            <div class="story-content">
                                <h2 class="serif-font-scaled"><?php echo $story['title']; ?></h2>
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
    </div>
</section>
