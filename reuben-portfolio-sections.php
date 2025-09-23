<?php
/**
 * Plugin Name: Reuben Portfolio Sections
 * Plugin URI: https://github.com/reubenj-brown/RJB-2025-portfolio-plugin
 * Description: Custom shortcodes for portfolio sections with WordPress post integration
 * Version: 1.2
 * Author: Reuben J. Brown
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Debug: Log when plugin file is loaded
error_log('ReubenPortfolioSections: Plugin file loaded at ' . date('Y-m-d H:i:s'));

class ReubenPortfolioSections {
    
    public function __construct() {
        add_action('init', [$this, 'register_shortcodes']);
        add_action('init', [$this, 'register_custom_post_types']);
        add_action('acf/init', [$this, 'register_acf_fields']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // Media library custom fields
        add_filter('attachment_fields_to_edit', [$this, 'add_media_source_field'], 10, 2);
        add_filter('attachment_fields_to_save', [$this, 'save_media_source_field'], 10, 2);
        
        // Debug: Log that plugin is loaded
        error_log('ReubenPortfolioSections: Plugin constructor loaded');
    }
    
    public function register_shortcodes() {
        // Main section shortcodes
        add_shortcode('reuben_about', [$this, 'about_section']);
        add_shortcode('reuben_stories', [$this, 'stories_section']);
        add_shortcode('reuben_features', [$this, 'features_section']);
        add_shortcode('reuben_reviews', [$this, 'reviews_section']);
        add_shortcode('reuben_profiles', [$this, 'profiles_section']);
        add_shortcode('reuben_interviews', [$this, 'interviews_section']);
        add_shortcode('reuben_photographs', [$this, 'photographs_section']);
        add_shortcode('reuben_strategy', [$this, 'strategy_section']);
        add_shortcode('reuben_cv', [$this, 'cv_section']);
        
        // Dynamic stories shortcode
        add_shortcode('reuben_dynamic_stories', [$this, 'dynamic_stories_section']);
        
        // Story component shortcodes
        add_shortcode('story_list', [$this, 'story_list']);
        add_shortcode('story_grid', [$this, 'story_grid']);
        add_shortcode('story_grid_2x2', [$this, 'story_grid_2x2']);
        add_shortcode('featured_story_text', [$this, 'featured_story_text']);
        add_shortcode('featured_story_full_bleed', [$this, 'featured_story_full_bleed']);
        add_shortcode('vertical_video', [$this, 'vertical_video']);
    }
    
    /**
     * Register ACF field groups for stories
     */
    public function register_acf_fields() {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(array(
                'key' => 'group_story_metadata',
                'title' => 'Story Metadata',
                'fields' => array(
                    array(
                        'key' => 'field_publication',
                        'label' => 'Publication',
                        'name' => 'publication',
                        'type' => 'text',
                        'instructions' => 'The publication where this story was published (e.g., The Architectural Review)',
                        'required' => 0,
                        'placeholder' => 'e.g., The Architectural Review',
                    ),
                    array(
                        'key' => 'field_publish_date',
                        'label' => 'Publication Date',
                        'name' => 'publish_date',
                        'type' => 'text',
                        'instructions' => 'When this story was published (e.g., June 2024)',
                        'required' => 0,
                        'placeholder' => 'e.g., June 2024',
                    ),
                    array(
                        'key' => 'field_external_url',
                        'label' => 'External URL',
                        'name' => 'external_url',
                        'type' => 'url',
                        'instructions' => 'Link to the published story on external website',
                        'required' => 0,
                        'placeholder' => 'https://www.example.com/story',
                    ),
                    array(
                        'key' => 'field_short_headline',
                        'label' => 'Short Headline',
                        'name' => 'short_headline',
                        'type' => 'text',
                        'instructions' => 'Alternative shorter or more allegorical version of the headline for use in different layouts',
                        'required' => 0,
                        'placeholder' => 'e.g., A shorter, more poetic version',
                    ),
                    array(
                        'key' => 'field_photo_credit',
                        'label' => 'Photo Credit',
                        'name' => 'photo_credit',
                        'type' => 'text',
                        'instructions' => 'Photo credit for the featured image',
                        'required' => 0,
                        'placeholder' => 'e.g., Reuben J. Brown',
                    ),
                    array(
                        'key' => 'field_original_image_url_v2',
                        'label' => 'Original Image URL',
                        'name' => 'original_image_url',
                        'type' => 'text',
                        'instructions' => 'URL of the original image (used when no featured image is set). Can be full URL or relative path like /wp-content/uploads/...',
                        'required' => 0,
                        'readonly' => 0,
                    ),
                    array(
                        'key' => 'field_original_image_picker',
                        'label' => 'Original Image (Media Library)',
                        'name' => 'original_image_picker',
                        'type' => 'image',
                        'instructions' => 'Alternative to URL field above - select image from WordPress media library',
                        'required' => 0,
                        'return_format' => 'url',
                        'preview_size' => 'medium',
                        'library' => 'all',
                    ),
                    array(
                        'key' => 'field_photo_gallery_repeater',
                        'label' => 'Photo Gallery',
                        'name' => 'photo_gallery',
                        'type' => 'repeater',
                        'instructions' => 'Add multiple images for photograph stories (used in carousel display). Click "Add Row" then select images.',
                        'required' => 0,
                        'collapsed' => '',
                        'min' => 0,
                        'max' => 5,
                        'layout' => 'table',
                        'button_label' => 'Add Image',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_gallery_image',
                                'label' => 'Image',
                                'name' => 'image',
                                'type' => 'image',
                                'instructions' => '',
                                'required' => 1,
                                'conditional_logic' => 0,
                                'return_format' => 'array',
                                'preview_size' => 'medium',
                                'library' => 'all',
                            ),
                        ),
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'story',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
            ));
        }
    }
    
    public function register_custom_post_types() {
        // Register Stories custom post type
        register_post_type('story', [
            'label' => 'Stories',
            'labels' => [
                'name' => 'Stories',
                'singular_name' => 'Story',
                'add_new' => 'Add New Story',
                'add_new_item' => 'Add New Story',
                'edit_item' => 'Edit Story',
                'new_item' => 'New Story',
                'view_item' => 'View Story',
                'search_items' => 'Search Stories',
                'not_found' => 'No stories found',
                'not_found_in_trash' => 'No stories found in trash',
                'all_items' => 'All Stories',
                'menu_name' => 'Stories'
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'stories'],
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'menu_icon' => 'dashicons-format-aside',
            'show_in_rest' => true, // For Gutenberg editor
        ]);
        
        // Register taxonomies for story categories
        register_taxonomy('story_category', 'story', [
            'label' => 'Story Categories',
            'labels' => [
                'name' => 'Story Categories',
                'singular_name' => 'Story Category',
                'add_new_item' => 'Add New Category',
                'new_item_name' => 'New Category Name',
                'edit_item' => 'Edit Category',
                'update_item' => 'Update Category',
                'search_items' => 'Search Categories',
                'all_items' => 'All Categories'
            ],
            'hierarchical' => true,
            'public' => true,
            'rewrite' => ['slug' => 'story-category'],
            'show_in_rest' => true
        ]);
    }
    
 public function enqueue_styles() {
        // Only load on portfolio page
        if (is_page_template('page-portfolio.php') || is_page_template('test-page.php') || is_page()) {
            // Base styles for all sections
            wp_enqueue_style(
                'reuben-base-sections',
                plugin_dir_url(__FILE__) . 'assets/base-sections.css',
                [],
                '1.0.0'
            );
            
            // Individual section styles
            wp_enqueue_style(
                'reuben-about-section',
                plugin_dir_url(__FILE__) . 'assets/about-section.css',
                ['reuben-base-sections'],
                '1.0.0'
            );
            
            wp_enqueue_style(
                'reuben-stories-section',
                plugin_dir_url(__FILE__) . 'assets/stories-section.css',
                ['reuben-base-sections'],
                '1.0.0'
            );
            
            wp_enqueue_style(
                'reuben-strategy-section',
                plugin_dir_url(__FILE__) . 'assets/strategy-section.css',
                ['reuben-base-sections'],
                '1.0.0'
            );
            
            wp_enqueue_style(
                'reuben-cv-section',
                plugin_dir_url(__FILE__) . 'assets/cv-section.css',
                ['reuben-base-sections'],
                '1.0.0'
            );
            
            wp_enqueue_style(
                'reuben-featured-story-full-bleed',
                plugin_dir_url(__FILE__) . 'assets/featured-story-full-bleed.css',
                ['reuben-base-sections'],
                '1.0.0'
            );
            
            wp_enqueue_style(
                'reuben-photographs-section',
                plugin_dir_url(__FILE__) . 'assets/photographs-section.css',
                ['reuben-base-sections'],
                '3.3.0'
            );
            
            wp_enqueue_style(
                'reuben-reviews-section',
                plugin_dir_url(__FILE__) . 'assets/reviews-section.css',
                ['reuben-base-sections'],
                '1.0.0'
            );
        }
    }
    
    public function enqueue_scripts() {
        // Only load on portfolio page
        if (is_page_template('page-portfolio.php') || is_page_template('test-page.php') || is_page()) {
            wp_enqueue_script(
                'reuben-cv-dropdown',
                plugin_dir_url(__FILE__) . 'assets/cv-dropdown.js',
                [],
                '1.0.0',
                true
            );
        }
    }
    
    public function about_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/about-section.php';
        return ob_get_clean();
    }
    
    public function stories_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/stories-section.php';
        return ob_get_clean();
    }
    
    public function features_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/features.php';
        return ob_get_clean();
    }
    
    public function reviews_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/reviews.php';
        return ob_get_clean();
    }
    
    public function profiles_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/architecture.php';
        return ob_get_clean();
    }
    
    public function interviews_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/interviews.php';
        return ob_get_clean();
    }
    
    public function photographs_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/photographs.php';
        return ob_get_clean();
    }
    
    public function strategy_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/strategy-section.php';
        return ob_get_clean();
    }
    
    public function cv_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/cv-section.php';
        return ob_get_clean();
    }
    
    /**
     * Dynamic Stories Section - pulls from WordPress posts
     */
    public function dynamic_stories_section($atts) {
        $atts = shortcode_atts([
            'category' => '',
            'limit' => 6,
            'layout' => 'grid', // grid, list, featured
            'show_excerpt' => 'true',
            'show_meta' => 'true',
            'show_view_all' => 'false'
        ], $atts);
        
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/dynamic-stories-section.php';
        return ob_get_clean();
    }
    
    // Story component shortcode methods
    public function story_list($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/story-list.php';
        return ob_get_clean();
    }
    
    public function story_grid($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/story-grid.php';
        return ob_get_clean();
    }
    
    public function story_grid_2x2($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/story-grid-2x2.php';
        return ob_get_clean();
    }
    
    public function featured_story_text($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/featured-story-text.php';
        return ob_get_clean();
    }
    
    public function featured_story_full_bleed($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/featured-story-full-bleed.php';
        return ob_get_clean();
    }
    
    public function vertical_video($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/vertical-video.php';
        return ob_get_clean();
    }
    
    
    
    
    
    /**
     * Add custom "source" field to media library attachment edit screen
     */
    public function add_media_source_field($form_fields, $post) {
        $source = get_post_meta($post->ID, '_media_source', true);
        
        $form_fields['media_source'] = array(
            'label' => 'Source',
            'input' => 'text',
            'value' => $source,
            'helps' => 'Enter the source or credit for this image (e.g., photographer name, agency, etc.)'
        );
        
        return $form_fields;
    }
    
    /**
     * Save custom "source" field from media library
     */
    public function save_media_source_field($post, $attachment) {
        if (isset($attachment['media_source'])) {
            update_post_meta($post['ID'], '_media_source', sanitize_text_field($attachment['media_source']));
        }
        
        return $post;
    }
    
    /**
     * Helper function to get media source/credit
     */
    public static function get_media_source($attachment_id) {
        return get_post_meta($attachment_id, '_media_source', true);
    }
}

// Initialize plugin
$reuben_portfolio = new ReubenPortfolioSections();

// Include domain URL fix tool
require_once plugin_dir_path(__FILE__) . 'fix-domain-urls.php';
