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
        add_shortcode('reuben_writing', [$this, 'writing_section']);
        add_shortcode('reuben_photography', [$this, 'photography_section']);
        add_shortcode('reuben_strategy', [$this, 'strategy_section']);
        add_shortcode('reuben_cv', [$this, 'cv_section']);
    }
    
public function enqueue_styles() {
    // Only load on portfolio page template
    if (is_page_template('page-portfolio.php')) {
        wp_enqueue_style(
            'reuben-portfolio-sections',
            plugin_dir_url(__FILE__) . 'assets/portfolio-sections.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/portfolio-sections.css') // Auto-version based on file modification
        );
    }
}
    
    public function about_section($atts) {
        $template_path = plugin_dir_path(__FILE__) . 'templates/about-section.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        return '<div class="portfolio-section-error">About section template not found.</div>';
    }
    
    public function writing_section($atts) {
        $template_path = plugin_dir_path(__FILE__) . 'templates/writing-section.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        return '<div class="portfolio-section-placeholder">Writing section coming soon...</div>';
    }
    
    public function photography_section($atts) {
        $template_path = plugin_dir_path(__FILE__) . 'templates/photography-section.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        return '<div class="portfolio-section-placeholder">Photography section coming soon...</div>';
    }
    
    public function strategy_section($atts) {
        $template_path = plugin_dir_path(__FILE__) . 'templates/strategy-section.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        return '<div class="portfolio-section-placeholder">Strategy section coming soon...</div>';
    }
    
    public function cv_section($atts) {
        $template_path = plugin_dir_path(__FILE__) . 'templates/cv-section.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        return '<div class="portfolio-section-placeholder">CV section coming soon...</div>';
    }
}

new ReubenPortfolioSections();
