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
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }
    
    public function register_shortcodes() {
        add_shortcode('reuben_about', [$this, 'about_section']);
        add_shortcode('reuben_stories', [$this, 'stories_section']);
        add_shortcode('reuben_strategy', [$this, 'strategy_section']);
        add_shortcode('reuben_cv', [$this, 'cv_section']);
        
        // Story component shortcodes
        add_shortcode('story_list', [$this, 'story_list']);
        add_shortcode('story_grid', [$this, 'story_grid']);
        add_shortcode('featured_story_text', [$this, 'featured_story_text']);
        add_shortcode('featured_story_full_bleed', [$this, 'featured_story_full_bleed']);
        add_shortcode('vertical_video', [$this, 'vertical_video']);
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
