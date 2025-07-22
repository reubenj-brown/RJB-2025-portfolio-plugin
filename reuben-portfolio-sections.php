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
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
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
    
    /**
     * Add admin menu for importing portfolio stories
     */
    public function add_admin_menu() {
        // Debug: Check if this function is being called
        error_log('ReubenPortfolioSections: add_admin_menu called');
        
        $page = add_management_page(
            'Import Portfolio Stories',
            'Import Portfolio Stories', 
            'manage_options',
            'import-portfolio-stories',
            [$this, 'admin_import_page']
        );
        
        // Debug: Log the page result
        error_log('ReubenPortfolioSections: admin page added - ' . ($page ? 'success' : 'failed'));
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
                    <li>6 story categories (Features, Reviews, Profiles, Interviews, Photographs, Featured)</li>
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
            'profiles' => 'Profiles',
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
}

new ReubenPortfolioSections();
