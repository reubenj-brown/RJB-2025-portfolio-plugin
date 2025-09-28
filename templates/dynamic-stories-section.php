<?php
/**
 * Dynamic Stories Section Template
 * Pulls stories from WordPress posts instead of hardcoded content
 */

// Get stories from WordPress using the helper function from theme
$stories_query = function_exists('get_portfolio_stories') 
    ? get_portfolio_stories($atts['category'], intval($atts['limit']))
    : new WP_Query([
        'post_type' => 'story',
        'posts_per_page' => intval($atts['limit']),
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

$show_excerpt = $atts['show_excerpt'] === 'true';
$show_meta = $atts['show_meta'] === 'true';
?>

<section class="content-section">
    <div class="section-container">
        <div class="stories-content">
            
            <?php if ($atts['layout'] === 'featured' && $stories_query->have_posts()) : ?>
                <!-- Featured Story Layout -->
                <?php
                $stories_query->the_post();
                $meta = function_exists('get_story_metadata') ? get_story_metadata(get_the_ID()) : [];
                $featured_image = function_exists('get_story_featured_image') ? get_story_featured_image(get_the_ID(), 'large') : get_the_post_thumbnail_url(get_the_ID(), 'large');
                ?>
                
                <article class="featured-story">
                    <?php if ($featured_image) : ?>
                        <div class="story-image">
                            <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                        </div>
                        <?php if (!empty($meta['photo_credit'])) : ?>
                            <div class="caption">photograph: <?php echo esc_html($meta['photo_credit']); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="story-content">
                        <h2 class="serif-font-scaled">
                            <a href="<?php echo !empty($meta['external_url']) ? esc_url($meta['external_url']) : get_permalink(); ?>"<?php echo !empty($meta['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                <?php the_title(); ?>
                            </a>
                        </h2>
                        
                        <?php if ($show_excerpt && has_excerpt()) : ?>
                            <h3><?php the_excerpt(); ?></h3>
                        <?php endif; ?>
                        
                        <?php if ($show_meta && (!empty($meta['publication']) || !empty($meta['publish_date']))) : ?>
                            <p class="story-meta">
                                <?php if (!empty($meta['publication'])) : ?>
                                    For <i><?php echo esc_html($meta['publication']); ?></i>
                                <?php endif; ?>
                                <?php if (!empty($meta['publish_date'])) : ?>
                                    <?php echo !empty($meta['publication']) ? ' in ' : ''; ?>
                                    <?php echo esc_html($meta['publish_date']); ?>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
                
                <?php wp_reset_postdata(); ?>
                
                <!-- Remaining stories in grid -->
                <?php if ($stories_query->found_posts > 1) : ?>
                    <div class="stories-grid">
                        <?php
                        // Skip the first post since it's featured
                        $stories_query->rewind_posts();
                        $stories_query->the_post(); // Skip first
                        
                        while ($stories_query->have_posts()) : $stories_query->the_post();
                            $meta = function_exists('get_story_metadata') ? get_story_metadata(get_the_ID()) : [];
                            $story_image = function_exists('get_story_featured_image') ? get_story_featured_image(get_the_ID(), 'large') : get_the_post_thumbnail_url(get_the_ID(), 'large');
                        ?>
                            <article class="story-item">
                                <a href="<?php echo !empty($meta['external_url']) ? esc_url($meta['external_url']) : get_permalink(); ?>" class="story-link"<?php echo !empty($meta['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                    <?php if ($story_image) : ?>
                                        <div class="story-image">
                                            <img src="<?php echo esc_url($story_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                                        </div>
                                        <?php if (!empty($meta['photo_credit'])) : ?>
                                            <div class="caption"><?php echo esc_html($meta['photo_credit']); ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <div class="story-content">
                                        <h2 class="serif-font-scaled"><?php the_title(); ?></h2>
                                        
                                        <?php if ($show_excerpt && has_excerpt()) : ?>
                                            <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($show_meta && (!empty($meta['publication']) || !empty($meta['publish_date']))) : ?>
                                            <p class="story-meta">
                                                <?php if (!empty($meta['publication'])) : ?>
                                                    For <i><?php echo esc_html($meta['publication']); ?></i>
                                                <?php endif; ?>
                                                <?php if (!empty($meta['publish_date'])) : ?>
                                                    <?php echo !empty($meta['publication']) ? ' in ' : ''; ?>
                                                    <?php echo esc_html($meta['publish_date']); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($atts['layout'] === 'list') : ?>
                <!-- List Layout -->
                <div class="stories-list">
                    <?php if ($stories_query->have_posts()) : ?>
                        <?php while ($stories_query->have_posts()) : $stories_query->the_post(); 
                            $meta = function_exists('get_story_metadata') ? get_story_metadata(get_the_ID()) : [];
                        ?>
                            <article class="story-list-item">
                                <a href="<?php echo !empty($meta['external_url']) ? esc_url($meta['external_url']) : get_permalink(); ?>" class="story-link"<?php echo !empty($meta['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                    <h2 class="serif-font-scaled"><?php the_title(); ?></h2>
                                    
                                    <?php if ($show_excerpt && has_excerpt()) : ?>
                                        <p class="story-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($show_meta && (!empty($meta['publication']) || !empty($meta['publish_date']))) : ?>
                                        <p class="story-meta">
                                            <?php if (!empty($meta['publication'])) : ?>
                                                For <i><?php echo esc_html($meta['publication']); ?></i>
                                            <?php endif; ?>
                                            <?php if (!empty($meta['publish_date'])) : ?>
                                                <?php echo !empty($meta['publication']) ? ' in ' : ''; ?>
                                                <?php echo esc_html($meta['publish_date']); ?>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                
            <?php else : ?>
                <!-- Grid Layout (default) -->
                <div class="stories-grid">
                    <?php if ($stories_query->have_posts()) : ?>
                        <?php while ($stories_query->have_posts()) : $stories_query->the_post(); 
                            $meta = function_exists('get_story_metadata') ? get_story_metadata(get_the_ID()) : [];
                            $story_image = function_exists('get_story_featured_image') ? get_story_featured_image(get_the_ID(), 'large') : get_the_post_thumbnail_url(get_the_ID(), 'large');
                        ?>
                            <article class="story-item">
                                <a href="<?php echo !empty($meta['external_url']) ? esc_url($meta['external_url']) : get_permalink(); ?>" class="story-link"<?php echo !empty($meta['external_url']) ? ' target="_blank" rel="noopener"' : ''; ?>>
                                    <?php if ($story_image) : ?>
                                        <div class="story-image">
                                            <img src="<?php echo esc_url($story_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                                        </div>
                                        <?php if (!empty($meta['photo_credit'])) : ?>
                                            <div class="caption"><?php echo esc_html($meta['photo_credit']); ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <div class="story-content">
                                        <h2 class="serif-font-scaled"><?php the_title(); ?></h2>
                                        
                                        <?php if ($show_excerpt && has_excerpt()) : ?>
                                            <p><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($show_meta && (!empty($meta['publication']) || !empty($meta['publish_date']))) : ?>
                                            <p class="story-meta">
                                                <?php if (!empty($meta['publication'])) : ?>
                                                    For <i><?php echo esc_html($meta['publication']); ?></i>
                                                <?php endif; ?>
                                                <?php if (!empty($meta['publish_date'])) : ?>
                                                    <?php echo !empty($meta['publication']) ? ' in ' : ''; ?>
                                                    <?php echo esc_html($meta['publish_date']); ?>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; ?>
                        
                        <!-- View All Tile - part of grid -->
                        <?php if (isset($atts['show_view_all']) && $atts['show_view_all'] === 'true') : ?>
                            <article class="story-item view-all-tile">
                                <a href="<?php echo get_post_type_archive_link('story'); ?>" class="story-link">
                                    <div class="story-content view-all-content">
                                        <h2 class="view-all-heading">View all stories →</h2>
                                    </div>
                                </a>
                            </article>
                        <?php endif; ?>
                    <?php else : ?>
                        <p class="no-stories-message">No stories found. <a href="<?php echo admin_url('post-new.php?post_type=story'); ?>">Add your first story</a>.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            
        </div>
    </div>
</section>

<?php wp_reset_postdata(); ?>