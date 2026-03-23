<?php
/**
 * Photo Section Template
 * Renders a photography section by querying story posts in a specific category
 *
 * Expected variables:
 * - $section_type: string (photo-stories, photo-portraits, photo-infrastructure, photo-events, photo-cities, photo-landscapes)
 * - $section_title: string (display title for the section)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Query stories in the photography category
$args = array(
    'post_type' => 'story',
    'posts_per_page' => -1,
    'tax_query' => array(
        array(
            'taxonomy' => 'story_category',
            'field' => 'slug',
            'terms' => $section_type,
        ),
    ),
    'orderby' => 'menu_order date',
    'order' => 'ASC',
);

$photo_query = new WP_Query($args);

// Skip if no posts
if (!$photo_query->have_posts()) {
    wp_reset_postdata();
    return;
}
?>

<section class="photo-type" id="<?php echo esc_attr($section_type); ?>">
    <div class="photo-type-headline">
        <h2><?php echo esc_html($section_title); ?></h2>
    </div>

    <?php while ($photo_query->have_posts()) : $photo_query->the_post();
        $story_id = get_the_ID();
        $metadata = function_exists('get_story_metadata') ? get_story_metadata($story_id) : array();

        // Build meta string
        $meta_parts = array();
        if (!empty($metadata['medium'])) {
            $meta_parts[] = esc_html($metadata['medium']);
        }
        if (!empty($metadata['publication'])) {
            $meta_parts[] = 'for <i>' . esc_html($metadata['publication']) . '</i>';
        }
        if (!empty($metadata['publish_date'])) {
            $meta_parts[] = 'in ' . esc_html($metadata['publish_date']);
        }
        $meta_string = implode(' ', $meta_parts);

        // Get images - try photo_gallery first, then featured image
        $images = array();
        $photo_gallery = get_field('photo_gallery', $story_id);
        if (!empty($photo_gallery)) {
            foreach ($photo_gallery as $gallery_item) {
                if (!empty($gallery_item['image'])) {
                    $images[] = $gallery_item['image'];
                }
            }
        }

        // Fallback to featured image if no gallery
        if (empty($images) && has_post_thumbnail($story_id)) {
            $images[] = array(
                'url' => get_the_post_thumbnail_url($story_id, 'full'),
                'alt' => get_the_title(),
            );
        }

        // Skip if no images
        if (empty($images)) {
            continue;
        }
    ?>
        <div class="photo-set">
            <div class="photo-title">
                <h3><?php echo esc_html(get_the_title()); ?></h3>

                <?php if (!empty($meta_string)) : ?>
                    <p class="story-meta"><?php echo $meta_string; ?></p>
                <?php endif; ?>
            </div>

            <div class="photo-scroll">
                <?php foreach ($images as $image) :
                    $img_url = is_array($image) ? $image['url'] : $image;
                    $img_alt = is_array($image) ? ($image['alt'] ?? get_the_title()) : get_the_title();
                ?>
                    <div class="photo-picture">
                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>" loading="lazy">
                    </div>
                <?php endforeach; ?>

                <?php if (has_excerpt()) : ?>
                    <div class="photo-description">
                        <p><?php echo wp_kses_post(get_the_excerpt()); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>

    <?php wp_reset_postdata(); ?>
</section>
