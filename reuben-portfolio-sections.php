<?php
/**
 * Plugin Name: Reuben Portfolio Sections
 * Description: Custom shortcodes for portfolio sections
 * Version: 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ReubenPortfolioSections {
    
    public function __construct() {
        add_action('init', [$this, 'register_shortcodes']);
        add_action('init', [$this, 'register_custom_post_types']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
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
                '1.0.0'
            );
        }
    }
    
    public function enqueue_scripts() {
        // Only load on portfolio page
        if (is_page_template('page-portfolio.php') || is_page_template('test-page.php') || is_page()) {
            wp_enqueue_script(
                'reuben-photographs-carousel',
                plugin_dir_url(__FILE__) . 'assets/photographs-carousel.js',
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
        include plugin_dir_path(__FILE__) . 'templates/profiles.php';
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
            'show_meta' => 'true'
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
}

new ReubenPortfolioSections();
