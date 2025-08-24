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
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
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
        // No scripts needed currently
        // Photographs section no longer uses carousel JavaScript
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
     * Add admin menu for importing portfolio stories
     */
    public function add_admin_menu() {
        // Debug: Check if this function is being called
        error_log('ReubenPortfolioSections: add_admin_menu called');
        
        // Try both top-level menu and Tools submenu
        $top_page = add_menu_page(
            'Portfolio Import',
            'Portfolio Import',
            'manage_options',
            'portfolio-import',
            [$this, 'admin_import_page'],
            'dashicons-upload',
            30
        );
        
        $tools_page = add_management_page(
            'Import Portfolio Stories',
            'Import Portfolio Stories', 
            'manage_options',
            'import-portfolio-stories',
            [$this, 'admin_import_page']
        );
        
        // Add migration submenu to Tools
        $migration_page = add_management_page(
            'Migrate Posts to Stories',
            'Migrate Posts to Stories',
            'manage_options',
            'migrate-posts-to-stories',
            [$this, 'admin_migration_page']
        );
        
        // Debug: Log the page results
        error_log('ReubenPortfolioSections: top menu added - ' . ($top_page ? 'success' : 'failed'));
        error_log('ReubenPortfolioSections: tools menu added - ' . ($tools_page ? 'success' : 'failed'));
        error_log('ReubenPortfolioSections: migration menu added - ' . ($migration_page ? 'success' : 'failed'));
    }
    
    /**
     * Admin page for importing portfolio stories
     */
    public function admin_import_page() {
        if (isset($_POST['import_stories']) && wp_verify_nonce($_POST['_wpnonce'], 'import_portfolio_stories')) {
            $this->create_portfolio_posts();
        }
        
        ?>
        <div class="wrap">
            <h1>Import Portfolio Stories</h1>
            <p>This will create draft posts for all existing portfolio content (20+ stories) from your current templates.</p>
            
            <div class="notice notice-info">
                <p><strong>What this will create:</strong></p>
                <ul>
                    <li>6 story categories (Features, Reviews, Architecture, Interviews, Photographs, Featured)</li>
                    <li>20+ draft posts with titles, content, and metadata</li>
                    <li>Custom fields for publication info, dates, and photo credits</li>
                    <li>Category assignments for each story</li>
                </ul>
            </div>
            
            <form method="post">
                <?php wp_nonce_field('import_portfolio_stories'); ?>
                <p>
                    <input type="submit" name="import_stories" class="button button-primary" value="Import All Stories" 
                           onclick="return confirm('This will create 20+ draft posts. Are you sure you want to proceed?');">
                </p>
            </form>
            
            <h3>After Import:</h3>
            <ol>
                <li>Go to <strong>Stories</strong> in your admin menu to review all draft posts</li>
                <li>Upload featured images manually and assign them to posts</li>
                <li>Edit content and add more detailed text where needed</li>
                <li>Publish posts when ready</li>
                <li>Replace hardcoded shortcodes with <code>[reuben_dynamic_stories]</code></li>
            </ol>
        </div>
        <?php
    }
    
    /**
     * Create all portfolio posts from existing template data
     */
    private function create_portfolio_posts() {
        // Portfolio stories data
        $portfolio_stories = [
            // FEATURES SECTION
            [
                'title' => 'The Desert Grows',
                'content' => 'A British startup hopes to generate 8% of the U.K.\'s electricity from a London-sized renewables development in Southern Morocco. What about the people who live there?',
                'excerpt' => 'A British startup hopes to generate 8% of the U.K.\'s electricity from a London-sized renewables development in Southern Morocco. What about the people who live there?',
                'category' => 'features',
                'publication' => 'Re—Compose',
                'publish_date' => 'May 2023',
                'image_url' => '/wp-content/uploads/2025/07/reuben-j-brown-solar-morocco-ouarzazate-noor.avif',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'Big Wall',
                'content' => 'At Storror\'s "Big Wall Open", parkour lands in new, physical territory',
                'excerpt' => 'At Storror\'s "Big Wall Open", parkour lands in new, physical territory',
                'category' => 'features',
                'publication' => 'Re—Compose',
                'publish_date' => 'December 2022',
                'image_url' => '/wp-content/uploads/2025/06/Reuben-j-brown-multimedia-journalist-homepage-images-draft.webp',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'Community cannot be owned',
                'content' => 'And why crypto won\'t save the internet',
                'excerpt' => 'And why crypto won\'t save the internet',
                'category' => 'features',
                'publication' => 'Re—Compose',
                'publish_date' => 'April 2022',
                'image_url' => '/wp-content/uploads/2025/07/MusicPoetryoftheKesh.jpeg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            
            // REVIEWS SECTION
            [
                'title' => 'A book and exhibition highlight the aesthetic triumph of \'Protest Architecture\' – but its political record needs examining, too',
                'content' => 'A comprehensive review of the recent exhibition and publication exploring protest architecture and its political implications.',
                'excerpt' => 'A comprehensive review of the recent exhibition and publication exploring protest architecture.',
                'category' => 'reviews',
                'publication' => 'The Architectural Review',
                'publish_date' => 'June 2024',
                'image_url' => 'https://cdn.ca.emap.com/wp-content/uploads/sites/12/2024/06/Protest-Architecture-Exhibition-MAK-Vienna-Architectural-Review-Hambi-1.jpg',
                'photo_credit' => 'Tim Wagner / MAK',
                'external_url' => 'https://www.architectural-review.com/essays/timber-pallets-climbing-ropes-and-a-bottle-of-glitter-protest-architecture-under-review'
            ],
            [
                'title' => 'The 2023 Sharjah Architecture Triennial builds coalitions through its contradictions',
                'content' => 'An in-depth review of the Sharjah Architecture Triennial 2023 and its exploration of architectural contradictions.',
                'excerpt' => 'The 2023 Sharjah Architecture Triennial builds coalitions through its contradictions',
                'category' => 'reviews',
                'publication' => 'The Architectural Review',
                'publish_date' => 'December 2023',
                'image_url' => 'https://cdn.ca.emap.com/wp-content/uploads/sites/12/2023/12/Sharjah-Architecture-Triennial-2023-The-Architectural-Review-Reuben-J-Brown-02.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => 'https://www.architectural-review.com/essays/exhibitions/building-coalition-through-contradictions-the-sharjah-architecture-triennial-2023'
            ],
            [
                'title' => 'In "Drive My Car", we follow the winding path',
                'content' => 'A review of the film "Drive My Car" and its exploration of grief, art, and human connection.',
                'excerpt' => 'In "Drive My Car", we follow the winding path',
                'category' => 'reviews',
                'publication' => 'Re—Compose',
                'publish_date' => 'March 2022',
                'image_url' => '2af947f4-5d59-42d4-9c6b-9792f4019171_976x549.jpeg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'In the music of Merzbow, the search for a sound so ugly it causes physical pain',
                'content' => 'An exploration of Merzbow\'s experimental noise music and its confrontational aesthetic.',
                'excerpt' => 'In the music of Merzbow, the search for a sound so ugly it causes physical pain',
                'category' => 'reviews',
                'publication' => 'Scuff',
                'publish_date' => 'July 2022',
                'image_url' => 'placeholder-merzbow.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            
            // PROFILES SECTION
            [
                'title' => 'Material Cultures',
                'content' => 'The London-based practice combines architecture with materials research and educational programmes to engender a transformation in architectural production',
                'excerpt' => 'The London-based practice combines architecture with materials research and educational programmes',
                'category' => 'profiles',
                'publication' => 'The Architectural Review',
                'publish_date' => 'November 2024',
                'image_url' => 'https://cdn.ca.emap.com/wp-content/uploads/sites/12/2024/11/Wolves_Lane_The_Architectural_Review_©_Henry_Woide__High-Res_001_architectural_review_ar_emerging_2024-2.jpg',
                'photo_credit' => 'Henry Woide',
                'external_url' => 'https://www.architectural-review.com/awards/ar-emerging/material-cultures-united-kingdom'
            ],
            [
                'title' => 'A Threshold',
                'content' => 'The Bangalore-based practice imagines a public life for a private guest house, finding community benefit in a commercial brief',
                'excerpt' => 'The Bangalore-based practice imagines a public life for a private guest house',
                'category' => 'profiles',
                'publication' => 'The Architectural Review',
                'publish_date' => 'November 2024',
                'image_url' => 'https://cdn.ca.emap.com/wp-content/uploads/sites/12/2024/11/ABPAT_SR-4684_architectural_review_ar_emerging-1584x1200.jpg',
                'photo_credit' => 'Atik Bheda',
                'external_url' => 'https://www.architectural-review.com/awards/ar-emerging/a-threshold-india'
            ],
            [
                'title' => 'NWLND Rogiers Vandeputte',
                'content' => 'In a workshop for a secondary school in Ostend, the Belgian duo use simple strategic moves to solve complex spatial problems',
                'excerpt' => 'In a workshop for a secondary school in Ostend, the Belgian duo use simple strategic moves',
                'category' => 'profiles',
                'publication' => 'The Architectural Review',
                'publish_date' => 'November 2024',
                'image_url' => 'https://cdn.ca.emap.com/wp-content/uploads/sites/12/2024/11/NWLNDPPW3OOSTENDE001HR-1500x1200.jpg',
                'photo_credit' => 'Johnny Umans',
                'external_url' => 'https://www.architectural-review.com/awards/ar-emerging/nwlnd-rogiers-vendeputte-belgium'
            ],
            
            // INTERVIEWS SECTION
            [
                'title' => 'The Feminist City with Leslie Kern',
                'content' => 'A conversation with Leslie Kern about feminist urban planning and the gendered experience of cities.',
                'excerpt' => 'A conversation with Leslie Kern about feminist urban planning',
                'category' => 'interviews',
                'publication' => 'Talking Volumes',
                'publish_date' => 'July 2021',
                'image_url' => 'placeholder-feminist-city.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'Sofia Karim\'s Architecture at the \'Hinterlands of Human Experience\'',
                'content' => 'An interview with architect Sofia Karim about her work exploring the boundaries of human experience through architecture.',
                'excerpt' => 'An interview with architect Sofia Karim about her work exploring architectural boundaries',
                'category' => 'interviews',
                'publication' => 'Panoramic',
                'publish_date' => 'May 2023',
                'image_url' => 'placeholder-sofia-karim.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'All the sonnets of Shakespeare with Paul Edmondson Sir Stanley Wells',
                'content' => 'A discussion about Shakespeare\'s complete sonnets with leading Shakespeare scholars.',
                'excerpt' => 'A discussion about Shakespeare\'s complete sonnets with leading scholars',
                'category' => 'interviews',
                'publication' => 'University of Cambridge Festival Podcast',
                'publish_date' => 'May 2022',
                'image_url' => 'placeholder-shakespeare-sonnets.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            
            // PHOTOGRAPHS SECTION
            [
                'title' => 'The Death of Queen Elizabeth II',
                'content' => 'Among the mourners at the Albert Memorial, at least some were surprised at the depth of their own grief. A photographic essay documenting public mourning and private grief during the Queen\'s funeral.',
                'excerpt' => 'Among the mourners at the Albert Memorial, at least some were surprised at the depth of their own grief',
                'category' => 'photographs',
                'publication' => '',
                'publish_date' => 'September 2022',
                'image_url' => '/wp-content/uploads/2025/06/Reuben-j-brown-multimedia-journalist-homepage-images-draft5.webp',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'The Nazi Monuments Vienna Can\'t Take Down',
                'content' => 'A photographic exploration of Vienna\'s Flak Towers and the complex relationship between memory, architecture, and historical reckoning.',
                'excerpt' => 'A photographic exploration of Vienna\'s Flak Towers and historical memory',
                'category' => 'photographs',
                'publication' => '',
                'publish_date' => 'September 2024',
                'image_url' => '/wp-content/uploads/2025/07/Reuben_J_Brown_Flak_Towers_Vienna_Augarten-29.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'Anti-racist protestors quell the violence in Walthamstow',
                'content' => 'Following a wave of racially motivated riots sweeping the UK, counter-protestors in Walthamstow, East London, massed to quash the violence – for now. A photographic document of community solidarity in action.',
                'excerpt' => 'Following a wave of racially motivated riots sweeping the UK, counter-protestors in Walthamstow massed to quash the violence',
                'category' => 'photographs',
                'publication' => 'openDemocracy',
                'publish_date' => 'August 2024',
                'image_url' => '/wp-content/uploads/2025/07/Walthamstow-anti-racist-rally-march-august-7-2024-Reuben-J-Brown-photojournalism-11-e1729971732596-2048x1365-1.webp',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            
            // FEATURED STORIES SECTION
            [
                'title' => 'The Cost of a Miracle',
                'content' => 'In Europe\'s driest region, a vast plastic sea covers a flourishing agricultural system built on intensity and innovation. But the cracks in Almeria\'s "miracle" are growing, too. An investigation into industrial agriculture, environmental costs, and the human communities caught in between.',
                'excerpt' => 'In Europe\'s driest region, a vast plastic sea covers a flourishing agricultural system built on intensity and innovation. But the cracks in Almeria\'s "miracle" are growing, too',
                'category' => 'featured',
                'publication' => 'Panoramic',
                'publish_date' => 'May 2025',
                'image_url' => '/wp-content/uploads/2025/06/Reuben-j-brown-multimedia-journalist-homepage-images-draft13.webp',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'Flak Towers',
                'content' => 'In Vienna\'s Augarten park, towering concrete monuments to Nazi air defence remain as contested symbols of history, memory, and urban planning. An exploration of how cities deal with difficult architectural legacies.',
                'excerpt' => 'In Vienna\'s Augarten park, towering concrete monuments to Nazi air defence remain as contested symbols',
                'category' => 'featured',
                'publication' => 'The Architectural Review',
                'publish_date' => 'September 2024',
                'image_url' => '/wp-content/uploads/2025/07/Reuben_J_Brown_Flak_Towers_Vienna_Augarten-29.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'Acquedotto Nottolini',
                'content' => 'Built in the 19th century, this remarkable aqueduct system transformed Lucca\'s landscape and remains a testament to engineering ambition in the Italian countryside. A journey through historical infrastructure and its contemporary relevance.',
                'excerpt' => 'Built in the 19th century, this remarkable aqueduct system transformed Lucca\'s landscape',
                'category' => 'featured',
                'publication' => 'The Architectural Review',
                'publish_date' => 'June 2024',
                'image_url' => '/wp-content/uploads/2025/07/Acquedotto-Nottolini-Reuben-J-Brown-26.jpg',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ],
            [
                'title' => 'Riots and Resistance in Walthamstow',
                'content' => 'When far-right violence erupted across England, Walthamstow\'s rapid mobilisation of anti-racist counter-protests demonstrated the power of community solidarity. A report from the front lines of community organizing.',
                'excerpt' => 'When far-right violence erupted across England, Walthamstow\'s rapid mobilisation of anti-racist counter-protests demonstrated the power of community solidarity',
                'category' => 'featured',
                'publication' => 'openDemocracy',
                'publish_date' => 'August 2024',
                'image_url' => '/wp-content/uploads/2025/07/Walthamstow-anti-racist-rally-march-august-7-2024-Reuben-J-Brown-photojournalism-11-e1729971732596-2048x1365-1.webp',
                'photo_credit' => 'Reuben J. Brown',
                'external_url' => ''
            ]
        ];

        // Create categories first
        $categories_to_create = [
            'features' => 'Features',
            'reviews' => 'Reviews',
            'profiles' => 'Architecture',
            'interviews' => 'Interviews',
            'photographs' => 'Photographs',
            'featured' => 'Featured'
        ];

        $created_categories = [];
        $results = [];

        foreach ($categories_to_create as $slug => $name) {
            $term = wp_insert_term($name, 'story_category', array(
                'slug' => $slug
            ));
            
            if (!is_wp_error($term)) {
                $created_categories[$slug] = $term['term_id'];
                $results[] = "✓ Created category: {$name}";
            } else {
                // Category might already exist
                $existing_term = get_term_by('slug', $slug, 'story_category');
                if ($existing_term) {
                    $created_categories[$slug] = $existing_term->term_id;
                    $results[] = "✓ Found existing category: {$name}";
                } else {
                    $results[] = "✗ Failed to create category: {$name}";
                }
            }
        }

        $created_posts = 0;
        $failed_posts = 0;

        // Create posts
        foreach ($portfolio_stories as $story) {
            // Create the post
            $post_data = array(
                'post_title'    => wp_strip_all_tags($story['title']),
                'post_content'  => $story['content'],
                'post_excerpt'  => $story['excerpt'],
                'post_status'   => 'draft',
                'post_type'     => 'story',
                'post_author'   => get_current_user_id(),
                'meta_input'    => array(
                    'publication' => $story['publication'],
                    'publish_date' => $story['publish_date'],
                    'photo_credit' => $story['photo_credit'],
                    'external_url' => $story['external_url']
                )
            );

            $post_id = wp_insert_post($post_data);

            if ($post_id && !is_wp_error($post_id)) {
                // Assign to category
                if (isset($created_categories[$story['category']])) {
                    wp_set_post_terms($post_id, [$created_categories[$story['category']]], 'story_category');
                }

                // Store original image URL for manual featured image assignment
                if (!empty($story['image_url'])) {
                    update_post_meta($post_id, 'original_image_url', $story['image_url']);
                }

                $created_posts++;
                $results[] = "✓ Created draft post: " . substr($story['title'], 0, 50) . "...";
            } else {
                $failed_posts++;
                $results[] = "✗ Failed to create post: " . substr($story['title'], 0, 50) . "...";
            }
        }

        // Display results
        echo '<div class="notice notice-success"><p><strong>Import Complete!</strong></p>';
        echo '<p>Categories created: ' . count($created_categories) . '<br>';
        echo 'Posts created: ' . $created_posts . '<br>';
        echo 'Posts failed: ' . $failed_posts . '</p>';
        echo '<p><a href="' . admin_url('edit.php?post_type=story') . '" class="button button-primary">View All Stories</a></p>';
        echo '</div>';
        
        if (!empty($results)) {
            echo '<div class="notice notice-info"><p><strong>Detailed Results:</strong></p>';
            echo '<div style="max-height: 300px; overflow-y: scroll; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;">';
            foreach ($results as $result) {
                echo $result . '<br>';
            }
            echo '</div></div>';
        }
    }
    
    /**
     * Admin page for migrating posts to stories
     */
    public function admin_migration_page() {
        // Handle form submission
        if (isset($_POST['action'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'migrate_posts_to_stories')) {
                wp_die('Security check failed.');
            }
            
            if ($_POST['action'] === 'migrate') {
                $this->perform_migration();
                return;
            }
        }
        
        // Get posts to migrate
        $posts_to_migrate = get_posts([
            'post_type' => 'post',
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => '_migrated_to_story',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);
        
        ?>
        <div class="wrap">
            <h1>🔄 Migrate Posts to Stories</h1>
            
            <div class="notice notice-info">
                <h3>📋 Migration Overview</h3>
                <p>This tool will safely migrate all your WordPress posts to the custom Stories post type.</p>
                <ul>
                    <li><strong>Found:</strong> <?php echo count($posts_to_migrate); ?> posts to migrate</li>
                    <li><strong>Preserves:</strong> Content, metadata, featured images, categories, and publication dates</li>
                    <li><strong>Safe:</strong> Adds migration flags to prevent duplicate migrations</li>
                </ul>
            </div>
            
            <?php if (count($posts_to_migrate) == 0): ?>
                <div class="notice notice-success">
                    <h3>✅ No Posts to Migrate</h3>
                    <p>All posts have already been migrated to Stories, or you have no posts to migrate.</p>
                    <p><a href="<?php echo admin_url('edit.php?post_type=story'); ?>" class="button button-primary">View Your Stories</a></p>
                </div>
            <?php else: ?>
                
                <div class="notice notice-warning">
                    <h3>⚠️ Before You Begin</h3>
                    <ul>
                        <li><strong>Backup your database</strong> before proceeding</li>
                        <li>URLs will change from <code>/posts/</code> to <code>/stories/</code></li>
                        <li>Consider setting up redirects after migration</li>
                    </ul>
                </div>
                
                <div class="card" style="max-width: 800px;">
                    <h3>📝 Posts Ready for Migration</h3>
                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                        <?php foreach ($posts_to_migrate as $post): ?>
                            <div style="margin-bottom: 10px; padding: 8px; background: white; border-left: 4px solid #0073aa;">
                                <strong><?php echo esc_html($post->post_title); ?></strong>
                                <br>
                                <small>
                                    Status: <?php echo $post->post_status; ?> | 
                                    Date: <?php echo get_the_date('Y-m-d', $post); ?> |
                                    ID: <?php echo $post->ID; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <form method="post" style="margin-top: 20px;">
                    <?php wp_nonce_field('migrate_posts_to_stories'); ?>
                    <input type="hidden" name="action" value="migrate">
                    
                    <p>
                        <button type="submit" class="button button-primary button-large" 
                                onclick="return confirm('Are you sure you want to migrate all posts to Stories? This will modify your database. Make sure you have a backup!')">
                            🚀 Migrate <?php echo count($posts_to_migrate); ?> Posts to Stories
                        </button>
                    </p>
                </form>
                
            <?php endif; ?>
            
            <div class="card" style="margin-top: 30px; max-width: 800px;">
                <h3>🛠️ What This Migration Does</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="color: #008000;">✅ Preserves</h4>
                        <ul>
                            <li>Post title and content</li>
                            <li>Featured images</li>
                            <li>Custom fields & metadata</li>
                            <li>Publication dates</li>
                            <li>Post status (draft, published, etc.)</li>
                            <li>Categories (converted to story categories)</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: #0073aa;">🔄 Changes</h4>
                        <ul>
                            <li>Post type: post → story</li>
                            <li>URLs: /posts/ → /stories/</li>
                            <li>Admin location: Posts → Stories</li>
                            <li>Adds migration tracking</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Perform the actual migration
     */
    private function perform_migration() {
        $results = [
            'total' => 0,
            'migrated' => 0,
            'errors' => []
        ];
        
        // Get posts to migrate
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => '_migrated_to_story',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);
        
        $results['total'] = count($posts);
        
        foreach ($posts as $post) {
            try {
                // Update post type
                $updated = wp_update_post([
                    'ID' => $post->ID,
                    'post_type' => 'story'
                ], true);
                
                if (is_wp_error($updated)) {
                    throw new Exception($updated->get_error_message());
                }
                
                // Add migration tracking
                update_post_meta($post->ID, '_migrated_to_story', current_time('mysql'));
                update_post_meta($post->ID, '_original_post_type', 'post');
                
                // Migrate categories to story categories if the taxonomy exists
                if (taxonomy_exists('story_category')) {
                    $categories = wp_get_post_categories($post->ID);
                    if (!empty($categories)) {
                        $story_categories = [];
                        foreach ($categories as $cat_id) {
                            $category = get_category($cat_id);
                            if ($category) {
                                // Create story category if it doesn't exist
                                $story_cat = get_term_by('slug', $category->slug, 'story_category');
                                if (!$story_cat) {
                                    $story_cat = wp_insert_term($category->name, 'story_category', [
                                        'description' => $category->description,
                                        'slug' => $category->slug
                                    ]);
                                    if (!is_wp_error($story_cat)) {
                                        $story_categories[] = $story_cat['term_id'];
                                    }
                                } else {
                                    $story_categories[] = $story_cat->term_id;
                                }
                            }
                        }
                        if (!empty($story_categories)) {
                            wp_set_post_terms($post->ID, $story_categories, 'story_category');
                        }
                    }
                }
                
                $results['migrated']++;
                
            } catch (Exception $e) {
                $results['errors'][] = "Failed to migrate '{$post->post_title}' (ID: {$post->ID}): " . $e->getMessage();
            }
        }
        
        // Display results
        ?>
        <div class="wrap">
            <h1>🎉 Migration Complete!</h1>
            
            <div class="notice notice-success">
                <h3>📊 Migration Results</h3>
                <ul>
                    <li><strong>Total Posts:</strong> <?php echo $results['total']; ?></li>
                    <li><strong>Successfully Migrated:</strong> <?php echo $results['migrated']; ?></li>
                    <li><strong>Errors:</strong> <?php echo count($results['errors']); ?></li>
                </ul>
            </div>
            
            <?php if (!empty($results['errors'])): ?>
                <div class="notice notice-error">
                    <h4>❌ Migration Errors</h4>
                    <ul>
                        <?php foreach ($results['errors'] as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card" style="max-width: 600px;">
                <h3>🚀 Next Steps</h3>
                <p><strong>Your posts have been successfully migrated to Stories!</strong></p>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=story'); ?>" class="button button-primary">
                        View Your Stories
                    </a>
                    <a href="<?php echo admin_url('tools.php?page=migrate-posts-to-stories'); ?>" class="button">
                        Back to Migration Tool
                    </a>
                </p>
                
                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
                    <h4>⚠️ Important: URL Changes</h4>
                    <p>Your post URLs have changed from <code>/posts/</code> to <code>/stories/</code>. 
                    Consider setting up redirects to maintain SEO and prevent broken links.</p>
                </div>
            </div>
        </div>
        <?php
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

// Also add a direct admin_menu hook as backup
add_action('admin_menu', function() {
    error_log('Direct admin_menu hook fired');
    
    add_menu_page(
        'Portfolio Import (Direct)',
        'Portfolio Import',
        'manage_options',
        'portfolio-import-direct',
        function() {
            echo '<div class="wrap"><h1>Portfolio Import</h1>';
            echo '<p>Direct menu hook working! Plugin should be functioning.</p>';
            echo '<p><a href="' . admin_url('admin.php?page=portfolio-import') . '">Try main import page</a></p>';
            echo '</div>';
        },
        'dashicons-upload',
        29
    );
});
