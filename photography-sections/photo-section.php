<?php
/**
 * Photo Section Template
 * Renders a photography section based on ACF repeater field
 *
 * Expected variables:
 * - $section_type: string (stories, portraits, infrastructure, events, cities, landscapes)
 * - $section_title: string (display title for the section)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the repeater field name based on section type
$repeater_field = $section_type . '_photo_sets';
$photo_sets = get_field($repeater_field);

// Skip if no photo sets
if (empty($photo_sets)) {
    return;
}
?>

<section class="photo-type" id="<?php echo esc_attr($section_type); ?>">
    <div class="photo-type-headline">
        <h2><?php echo esc_html($section_title); ?></h2>
    </div>

    <?php foreach ($photo_sets as $set) : ?>
        <div class="photo-set">
            <div class="photo-title">
                <?php if (!empty($set['set_title'])) : ?>
                    <h3><?php echo esc_html($set['set_title']); ?></h3>
                <?php endif; ?>

                <?php if (!empty($set['set_meta'])) : ?>
                    <p class="story-meta"><?php echo wp_kses_post($set['set_meta']); ?></p>
                <?php endif; ?>
            </div>

            <div class="photo-scroll">
                <?php
                // Get images from gallery field
                $images = $set['images'];
                if (!empty($images)) :
                    foreach ($images as $image) :
                        // Handle both URL string and array format
                        $img_url = is_array($image) ? $image['url'] : $image;
                        $img_alt = is_array($image) ? $image['alt'] : '';
                ?>
                    <div class="photo-picture">
                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>" loading="lazy">
                    </div>
                <?php
                    endforeach;
                endif;
                ?>

                <?php if (!empty($set['description'])) : ?>
                    <div class="photo-description">
                        <p><?php echo wp_kses_post($set['description']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>
