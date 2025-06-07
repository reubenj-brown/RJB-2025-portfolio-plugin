<?php
/**
 * Plugin Name: Reuben Portfolio Sections
 * Description: Custom shortcodes for portfolio sections
 * Version: 1.0.1
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
        add_shortcode('reuben_writing', [$this, 'writing_section']);
        add_shortcode('reuben_photography', [$this, 'photography_section']);
        add_shortcode('reuben_strategy', [$this, 'strategy_section']);
        add_shortcode('reuben_cv', [$this, 'cv_section']);
    }
    
    public function enqueue_styles() {
        // Only load on portfolio page
        if (is_page_template('page-portfolio.php')) {
            wp_enqueue_style(
                'reuben-portfolio-sections',
                plugin_dir_url(__FILE__) . 'assets/portfolio-sections.css',
                [],
                '1.0.0'
            );
        }
    }
    
    public function about_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/about-section.php';
        return ob_get_clean();
    }
    
    public function writing_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/writing-section.php';
        return ob_get_clean();
    }
    
    public function photography_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/photography-section.php';
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
}

new ReubenPortfolioSections();