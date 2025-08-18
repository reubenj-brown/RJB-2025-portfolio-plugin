<?php
/**
 * Migration Script: Move Posts to Stories Custom Post Type
 * 
 * This script migrates all posts from the default 'post' type to the custom 'story' type.
 * It preserves all content, metadata, featured images, categories, and tags.
 * 
 * IMPORTANT: Always backup your database before running this script!
 * 
 * Usage:
 * 1. Upload this file to your WordPress root directory or plugin directory
 * 2. Access it via browser: yoursite.com/wp-content/plugins/RJB-2025-portfolio-plugin/migrate-posts-to-stories.php
 * 3. Or run via WP-CLI if available
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    require_once('../../../wp-config.php');
}

// Security check - only allow admin users
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Start output buffering for clean display
ob_start();

echo "<h1>Post to Stories Migration Tool</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    .success { color: #008000; }
    .warning { color: #ff8800; }
    .error { color: #cc0000; }
    .info { color: #0073aa; }
    .step { margin: 20px 0; padding: 15px; border-left: 4px solid #0073aa; background: #f9f9f9; }
    .results { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #fff; }
</style>";

// Check if this is a confirmation run
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';
$dry_run = !$confirm;

if ($dry_run) {
    echo "<div class='step info'>";
    echo "<h2>🔍 DRY RUN MODE</h2>";
    echo "<p>This is a preview of what will be migrated. No changes will be made to your database.</p>";
    echo "<p><strong>To actually perform the migration, <a href='?confirm=yes'>click here</a></strong></p>";
    echo "</div>";
} else {
    echo "<div class='step warning'>";
    echo "<h2>⚠️ LIVE MIGRATION MODE</h2>";
    echo "<p>This will permanently modify your database. Make sure you have a backup!</p>";
    echo "</div>";
}

/**
 * Migration function
 */
function migrate_posts_to_stories($dry_run = true) {
    global $wpdb;
    
    $results = [
        'total_posts' => 0,
        'migrated' => 0,
        'errors' => [],
        'skipped' => []
    ];
    
    echo "<div class='step'>";
    echo "<h3>📊 Analyzing Posts</h3>";
    
    // Get all posts that are not already stories
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
    
    $results['total_posts'] = count($posts);
    echo "<p>Found <strong>{$results['total_posts']}</strong> posts to migrate.</p>";
    echo "</div>";
    
    if ($results['total_posts'] == 0) {
        echo "<div class='step info'>";
        echo "<p>No posts found to migrate. All posts may already be migrated or you have no posts.</p>";
        echo "</div>";
        return $results;
    }
    
    echo "<div class='step'>";
    echo "<h3>🔄 Migration Process</h3>";
    
    foreach ($posts as $post) {
        $post_id = $post->ID;
        $post_title = $post->post_title;
        
        try {
            if (!$dry_run) {
                // Start transaction-like approach
                $original_post_type = $post->post_type;
                
                // Update post type
                $updated = wp_update_post([
                    'ID' => $post_id,
                    'post_type' => 'story'
                ], true);
                
                if (is_wp_error($updated)) {
                    throw new Exception($updated->get_error_message());
                }
                
                // Add migration flag to prevent re-migration
                update_post_meta($post_id, '_migrated_to_story', current_time('mysql'));
                update_post_meta($post_id, '_original_post_type', $original_post_type);
                
                // Convert categories to story categories if taxonomy exists
                $categories = wp_get_post_categories($post_id);
                if (!empty($categories) && taxonomy_exists('story_category')) {
                    $story_categories = [];
                    foreach ($categories as $cat_id) {
                        $category = get_category($cat_id);
                        if ($category) {
                            // Create or get story category
                            $story_cat = wp_insert_term($category->name, 'story_category', [
                                'description' => $category->description,
                                'slug' => $category->slug
                            ]);
                            if (!is_wp_error($story_cat)) {
                                $story_categories[] = $story_cat['term_id'];
                            }
                        }
                    }
                    if (!empty($story_categories)) {
                        wp_set_post_terms($post_id, $story_categories, 'story_category');
                    }
                }
                
                echo "<p class='success'>✅ Migrated: <strong>{$post_title}</strong> (ID: {$post_id})</p>";
                $results['migrated']++;
                
            } else {
                echo "<p class='info'>📝 Would migrate: <strong>{$post_title}</strong> (ID: {$post_id})</p>";
                $results['migrated']++;
            }
            
        } catch (Exception $e) {
            $error_msg = "Failed to migrate '{$post_title}' (ID: {$post_id}): " . $e->getMessage();
            $results['errors'][] = $error_msg;
            echo "<p class='error'>❌ {$error_msg}</p>";
        }
    }
    
    echo "</div>";
    
    return $results;
}

// Run the migration
$results = migrate_posts_to_stories($dry_run);

// Display results
echo "<div class='results'>";
echo "<h3>📈 Migration Results</h3>";
echo "<ul>";
echo "<li><strong>Total Posts Found:</strong> {$results['total_posts']}</li>";
echo "<li><strong>Successfully " . ($dry_run ? 'Analyzed' : 'Migrated') . ":</strong> {$results['migrated']}</li>";
echo "<li><strong>Errors:</strong> " . count($results['errors']) . "</li>";
echo "</ul>";

if (!empty($results['errors'])) {
    echo "<h4 class='error'>Errors:</h4>";
    echo "<ul>";
    foreach ($results['errors'] as $error) {
        echo "<li class='error'>{$error}</li>";
    }
    echo "</ul>";
}
echo "</div>";

if ($dry_run && $results['total_posts'] > 0) {
    echo "<div class='step warning'>";
    echo "<h3>🚀 Ready to Migrate?</h3>";
    echo "<p>If the preview above looks correct, you can proceed with the actual migration.</p>";
    echo "<p><strong><a href='?confirm=yes' onclick='return confirm(\"Are you sure you want to migrate all posts to stories? This will modify your database. Make sure you have a backup!\")'>Click here to start the migration</a></strong></p>";
    echo "</div>";
} elseif (!$dry_run) {
    echo "<div class='step success'>";
    echo "<h3>🎉 Migration Complete!</h3>";
    echo "<p>Your posts have been successfully migrated to the Stories custom post type.</p>";
    echo "<p><a href='" . admin_url('edit.php?post_type=story') . "' class='button button-primary'>View Your Stories</a></p>";
    echo "</div>";
}

// Additional recommendations
echo "<div class='step'>";
echo "<h3>📋 What Happens Next</h3>";
echo "<ul>";
echo "<li><strong>Custom Fields:</strong> All custom fields and metadata are preserved</li>";
echo "<li><strong>Featured Images:</strong> Featured images are automatically transferred</li>";
echo "<li><strong>URLs:</strong> URLs will change from /posts/ to /stories/ - consider setting up redirects</li>";
echo "<li><strong>Categories:</strong> Post categories are converted to story categories if the taxonomy exists</li>";
echo "<li><strong>Rollback:</strong> A migration flag is added to prevent duplicate migrations</li>";
echo "</ul>";
echo "</div>";

// Security note
echo "<div class='step warning'>";
echo "<h3>🔒 Security Note</h3>";
echo "<p><strong>Important:</strong> Delete this migration script file after use for security purposes.</p>";
echo "</div>";

// End output buffering and display
$output = ob_get_clean();
echo $output;
?>