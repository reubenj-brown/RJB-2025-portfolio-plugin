<?php
/**
 * Plugin Name: Reuben Portfolio Sections
 * Plugin URI: https://github.com/reubenj-brown/RJB-2025-portfolio-plugin
 * Description: Custom shortcodes for portfolio sections with WordPress post integration
 * Version: 1.3
 * Author: Reuben J. Brown
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

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

        // Register meta field for REST API
        add_action('rest_api_init', [$this, 'register_media_source_rest_field']);

        // Load the interactive viz runtime as an ES module
        add_filter('script_loader_tag', [$this, 'viz_module_type'], 10, 2);
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
        add_shortcode('reuben_reporting', [$this, 'reporting_section']);
        add_shortcode('reuben_solar', [$this, 'solar_section']);
        add_shortcode('reuben_video_projects', [$this, 'video_projects_section']);

        // Interactive data-viz islands (Svelte + D3, built under interactive/)
        add_shortcode('reuben_viz', [$this, 'viz_embed']);

        // Dynamic stories shortcode
        add_shortcode('reuben_dynamic_stories', [$this, 'dynamic_stories_section']);
        
        // Story component shortcodes
        add_shortcode('story_list', [$this, 'story_list']);
        add_shortcode('story_grid', [$this, 'story_grid']);
        add_shortcode('story_grid_2x2', [$this, 'story_grid_2x2']);
        add_shortcode('featured_story_text', [$this, 'featured_story_text']);
        add_shortcode('featured_story_full_bleed', [$this, 'featured_story_full_bleed']);
        add_shortcode('vertical_video', [$this, 'vertical_video']);
        add_shortcode('more_stories', [$this, 'more_stories']);

        // Photography page shortcode
        add_shortcode('photo_section', [$this, 'photo_section']);
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
                        'key' => 'field_medium',
                        'label' => 'Medium',
                        'name' => 'medium',
                        'type' => 'text',
                        'instructions' => 'The medium(s) of the story (e.g., Photo and video, Writing, Photography)',
                        'required' => 0,
                        'placeholder' => 'e.g., Photo and video',
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
                        'key' => 'field_homepage_thumbnail',
                        'label' => 'Homepage Thumbnail',
                        'name' => 'homepage_thumbnail',
                        'type' => 'image',
                        'instructions' => 'Image used on homepage/portfolio sections (separate from story featured image)',
                        'required' => 0,
                        'return_format' => 'url',
                        'preview_size' => 'medium',
                        'library' => 'all',
                    ),
                    array(
                        'key' => 'field_photo_gallery_urls',
                        'label' => 'Photo Gallery URLs',
                        'name' => 'photo_gallery_urls',
                        'type' => 'textarea',
                        'instructions' => 'Add image URLs for the photo gallery (one URL per line). Used in photography page horizontal scroller.',
                        'required' => 0,
                        'rows' => 6,
                        'new_lines' => '',
                        'placeholder' => "https://example.com/image1.jpg\nhttps://example.com/image2.jpg\nhttps://example.com/image3.jpg",
                    ),
                    array(
                        'key' => 'field_photo_description',
                        'label' => 'Photo Description',
                        'name' => 'photo_description',
                        'type' => 'textarea',
                        'instructions' => 'Description text shown at the end of the photo scroller on the photography page.',
                        'required' => 0,
                        'rows' => 3,
                        'placeholder' => 'A brief description of this photo set...',
                    ),
                    array(
                        'key' => 'field_photo_read_more_button',
                        'label' => 'Photography page read more button?',
                        'name' => 'photo_read_more_button',
                        'type' => 'true_false',
                        'instructions' => 'Show a "Read more →" button on the photography page that links to the full story.',
                        'required' => 0,
                        'default_value' => 0,
                        'ui' => 1,
                    ),
                    array(
                        'key' => 'field_hero_video_url',
                        'label' => 'Hero Video URL',
                        'name' => 'hero_video_url',
                        'type' => 'url',
                        'instructions' => 'URL to MP4 video for Video Hero Story template (optional). Leave empty to use featured image instead.',
                        'required' => 0,
                        'placeholder' => 'https://example.com/video.mp4',
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

    /**
     * Section CSS files that make up the portfolio/homepage bundle.
     * base-sections.css MUST stay first (others rely on its variables/resets).
     */
    private function portfolio_css_files() {
        return [
            'base-sections.css',
            'about-section.css',
            'features-section.css',
            'stories-section.css',
            'cv-section.css',
            'featured-story-full-bleed.css',
            'photographs-section.css',
            'reviews-section.css',
            'reporting-section.css',
            'solar-section.css',
            'video-projects-section.css',
        ];
    }

    /**
     * filemtime-based cache-busting version for an asset (falls back to plugin version).
     */
    private function asset_ver($file) {
        $path = plugin_dir_path(__FILE__) . 'assets/' . $file;
        return file_exists($path) ? filemtime($path) : '1.3';
    }

    /**
     * Concatenate the portfolio section CSS into one cached file in uploads/.
     * Rebuilt automatically whenever any source file changes. Returns false if
     * the combined file can't be written (caller falls back to individual files).
     *
     * @return array|false ['url' => ..., 'ver' => ...] or false
     */
    private function combined_portfolio_css() {
        $files     = $this->portfolio_css_files();
        $asset_dir = plugin_dir_path(__FILE__) . 'assets/';

        $upload     = wp_upload_dir();
        if (!empty($upload['error'])) {
            return false;
        }
        $cache_dir  = trailingslashit($upload['basedir']) . 'reuben-portfolio';
        $cache_file = $cache_dir . '/portfolio-combined.css';
        $cache_url  = trailingslashit($upload['baseurl']) . 'reuben-portfolio/portfolio-combined.css';

        // Newest source modification time.
        $latest = 0;
        foreach ($files as $f) {
            $path = $asset_dir . $f;
            if (file_exists($path)) {
                $latest = max($latest, (int) filemtime($path));
            }
        }

        // (Re)build when missing or any source is newer than the cached bundle.
        if (!file_exists($cache_file) || filemtime($cache_file) < $latest) {
            if (!file_exists($cache_dir) && !wp_mkdir_p($cache_dir)) {
                return false;
            }
            $combined = '';
            foreach ($files as $f) {
                $path = $asset_dir . $f;
                if (file_exists($path)) {
                    $combined .= "\n/* ===== {$f} ===== */\n" . file_get_contents($path) . "\n";
                }
            }
            if (file_put_contents($cache_file, $combined) === false) {
                return false;
            }
        }

        return ['url' => $cache_url, 'ver' => filemtime($cache_file)];
    }

    /**
     * Fallback: enqueue the section CSS as individual files (used only if the
     * combined bundle can't be generated).
     */
    private function enqueue_portfolio_css_individually() {
        wp_enqueue_style('reuben-base-sections', plugin_dir_url(__FILE__) . 'assets/base-sections.css', [], $this->asset_ver('base-sections.css'));
        foreach ($this->portfolio_css_files() as $f) {
            if ($f === 'base-sections.css') {
                continue;
            }
            $handle = 'reuben-' . basename($f, '.css');
            wp_enqueue_style($handle, plugin_dir_url(__FILE__) . 'assets/' . $f, ['reuben-base-sections'], $this->asset_ver($f));
        }
    }

    public function enqueue_styles() {
        // Load on portfolio pages and story archive
        if (is_page_template('page-portfolio.php') || is_page_template('test-page.php') || is_page() || is_post_type_archive('story') || is_tax('story_category')) {
            // Serve one combined, cached stylesheet instead of ~10 render-blocking
            // requests. Falls back to individual files if it can't be written.
            $combined = $this->combined_portfolio_css();
            if ($combined) {
                wp_enqueue_style('reuben-portfolio-combined', $combined['url'], [], $combined['ver']);
            } else {
                $this->enqueue_portfolio_css_individually();
            }
        }

        // Load only architecture scroller styles on story pages and story archive for more_stories shortcode
        if (is_singular('story') || is_post_type_archive('story') || is_tax('story_category')) {
            wp_enqueue_style(
                'reuben-base-sections',
                plugin_dir_url(__FILE__) . 'assets/base-sections.css',
                [],
                $this->asset_ver('base-sections.css')
            );

            wp_enqueue_style(
                'reuben-stories-section',
                plugin_dir_url(__FILE__) . 'assets/stories-section.css',
                ['reuben-base-sections'],
                $this->asset_ver('stories-section.css')
            );
        }

        // Photography page styles
        if (is_page_template('page-photography.php')) {
            wp_enqueue_style(
                'reuben-base-sections',
                plugin_dir_url(__FILE__) . 'assets/base-sections.css',
                [],
                $this->asset_ver('base-sections.css')
            );

            wp_enqueue_style(
                'reuben-photograph-page',
                plugin_dir_url(__FILE__) . 'assets/photograph-page.css',
                ['reuben-base-sections'],
                $this->asset_ver('photograph-page.css')
            );
        }
    }

    public function enqueue_scripts() {
        // Only load on portfolio pages
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

    /**
     * [reuben_viz id="solar-output" src="https://…/data.json" title="…"]
     *
     * Prints a placeholder div that the interactive runtime (interactive/dist/
     * rjb-viz.js) hydrates with the matching Svelte island. The runtime is
     * enqueued only on pages that actually use a viz. Any extra atts are passed
     * through as data-* attributes and become props on the component.
     */
    public function viz_embed($atts) {
        $atts = shortcode_atts([
            'id'    => '',
            'src'   => '',
            'title' => '',
            'class' => '',
        ], $atts, 'reuben_viz');

        if ($atts['id'] === '') {
            return '';
        }

        $this->enqueue_viz_runtime();

        $data = 'data-viz="' . esc_attr($atts['id']) . '"';
        if ($atts['src'] !== '') {
            $data .= ' data-src="' . esc_url($atts['src']) . '"';
        }
        if ($atts['title'] !== '') {
            $data .= ' data-title="' . esc_attr($atts['title']) . '"';
        }

        $class = trim('rjb-viz ' . $atts['class']);

        return '<div class="' . esc_attr($class) . '" ' . $data . '></div>';
    }

    /**
     * Enqueue the compiled island runtime once per request. filemtime() is the
     * version so a fresh build busts the cache automatically. No-op if dist/
     * hasn't been built yet.
     */
    private function enqueue_viz_runtime() {
        $rel  = 'interactive/dist/rjb-viz.js';
        $path = plugin_dir_path(__FILE__) . $rel;
        if (!file_exists($path)) {
            return;
        }
        wp_enqueue_script(
            'rjb-viz',
            plugin_dir_url(__FILE__) . $rel,
            [],
            filemtime($path),
            true
        );
    }

    /**
     * The runtime and its lazy chunks are ES modules, so the entry <script>
     * needs type="module". WordPress has no native flag for this.
     */
    public function viz_module_type($tag, $handle) {
        if ($handle === 'rjb-viz') {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
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

    public function reporting_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/reporting.php';
        return ob_get_clean();
    }

    public function solar_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/solar.php';
        return ob_get_clean();
    }

    public function video_projects_section($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/video-projects.php';
        return ob_get_clean();
    }

    /**
     * Photo Section Shortcode - renders a photography section
     * Usage: [photo_section type="stories"] or [photo_section type="portraits"]
     *
     * Queries story posts in the corresponding "photo-{type}" category
     * e.g., type="portraits" queries stories in "photo-portraits" category
     */
    public function photo_section($atts) {
        $atts = shortcode_atts([
            'type' => 'stories',
        ], $atts);

        // Map type to section title and category slug
        $section_config = array(
            'stories' => array('title' => 'Stories', 'category' => 'photo-stories'),
            'portraits' => array('title' => 'Portraits', 'category' => 'photo-portraits'),
            'infrastructure' => array('title' => 'Infrastructure', 'category' => 'photo-infrastructure'),
            'politics' => array('title' => 'Politics', 'category' => 'photo-politics'),
            'cities' => array('title' => 'Cities', 'category' => 'photo-cities'),
            'landscapes' => array('title' => 'Landscapes', 'category' => 'photo-landscapes'),
        );

        $type = sanitize_key($atts['type']);

        if (isset($section_config[$type])) {
            $section_type = $section_config[$type]['category'];
            $section_title = $section_config[$type]['title'];
        } else {
            $section_type = 'photo-' . $type;
            $section_title = ucfirst($type);
        }

        ob_start();
        include plugin_dir_path(__FILE__) . 'photography-sections/photo-section.php';
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
            'show_view_all' => 'false',
            'show_numerals' => 'false',
            'order' => 'DESC'
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

    public function more_stories($atts) {
        $atts = shortcode_atts([
            'limit'   => 5,
            'tag'     => '',
            'heading' => 'More stories',
        ], $atts);

        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/more-stories.php';
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
     * Register media source field for REST API access
     */
    public function register_media_source_rest_field() {
        register_rest_field('attachment', 'media_source', array(
            'get_callback' => function($post) {
                return get_post_meta($post['id'], '_media_source', true);
            },
            'update_callback' => function($value, $post) {
                return update_post_meta($post->ID, '_media_source', sanitize_text_field($value));
            },
            'schema' => array(
                'type' => 'string',
                'description' => 'Media source or credit information',
                'context' => array('view', 'edit'),
            ),
        ));
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
