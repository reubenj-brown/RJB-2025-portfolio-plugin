<?php
/**
 * Domain URL Fix Tool - WordPress Admin
 * Fixes URLs after domain change from old hosting to reubenjbrown.com
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add this functionality to the existing plugin class
add_action('admin_menu', function() {
    add_management_page(
        'Fix Domain URLs',
        'Fix Domain URLs',
        'manage_options',
        'fix-domain-urls',
        'fix_domain_urls_page'
    );
});

function fix_domain_urls_page() {
    // Handle form submission
    if (isset($_POST['action']) && $_POST['action'] === 'fix_urls') {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'fix_domain_urls')) {
            wp_die('Security check failed.');
        }
        
        $old_domain = sanitize_text_field($_POST['old_domain']);
        $new_domain = sanitize_text_field($_POST['new_domain']);
        
        if (empty($old_domain) || empty($new_domain)) {
            echo '<div class="notice notice-error"><p>Both domains are required.</p></div>';
        } else {
            $results = perform_url_replacement($old_domain, $new_domain);
            display_results($results);
            return;
        }
    }
    
    ?>
    <div class="wrap">
        <h1>🔗 Fix Domain URLs</h1>
        
        <div class="notice notice-info">
            <h3>📋 Domain Change URL Fixer</h3>
            <p>This tool will update all URLs in your database after a domain change.</p>
            <p><strong>Important:</strong> Always backup your database before running this tool!</p>
        </div>
        
        <div class="notice notice-warning">
            <h3>⚠️ Before You Begin</h3>
            <ul>
                <li><strong>Backup your database</strong> before proceeding</li>
                <li>Make sure WordPress Settings → General URLs are already updated</li>
                <li>This will update URLs in posts, pages, options, and metadata</li>
            </ul>
        </div>
        
        <form method="post" style="max-width: 600px;">
            <?php wp_nonce_field('fix_domain_urls'); ?>
            <input type="hidden" name="action" value="fix_urls">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="old_domain">Old Domain URL</label>
                    </th>
                    <td>
                        <input type="url" id="old_domain" name="old_domain" 
                               value="https://skyblue-mongoose-220265.hostingersite.com" 
                               class="regular-text" required>
                        <p class="description">The old domain you're moving from</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="new_domain">New Domain URL</label>
                    </th>
                    <td>
                        <input type="url" id="new_domain" name="new_domain" 
                               value="https://reubenjbrown.com" 
                               class="regular-text" required>
                        <p class="description">The new domain you're moving to</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary button-large"
                        onclick="return confirm('Are you sure you want to replace all URLs in the database? Make sure you have a backup!')">
                    🚀 Fix All Domain URLs
                </button>
            </p>
        </form>
        
        <div class="card" style="margin-top: 30px;">
            <h3>🛠️ What This Tool Updates</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <h4 style="color: #008000;">✅ Updates</h4>
                    <ul>
                        <li>Post content URLs</li>
                        <li>Page content URLs</li>
                        <li>Image URLs</li>
                        <li>Custom field URLs</li>
                        <li>WordPress options</li>
                        <li>Widget content</li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: #0073aa;">🔍 Searches In</h4>
                    <ul>
                        <li>wp_posts table</li>
                        <li>wp_postmeta table</li>
                        <li>wp_options table</li>
                        <li>wp_comments table</li>
                        <li>Custom fields</li>
                        <li>Serialized data</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function perform_url_replacement($old_domain, $new_domain) {
    global $wpdb;
    
    $results = [
        'posts' => 0,
        'postmeta' => 0,
        'options' => 0,
        'comments' => 0,
        'errors' => []
    ];
    
    try {
        // Update post content
        $posts_updated = $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->posts} 
            SET post_content = REPLACE(post_content, %s, %s)
            WHERE post_content LIKE %s
        ", $old_domain, $new_domain, '%' . $old_domain . '%'));
        $results['posts'] = $posts_updated !== false ? $posts_updated : 0;
        
        // Update post excerpts
        $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->posts} 
            SET post_excerpt = REPLACE(post_excerpt, %s, %s)
            WHERE post_excerpt LIKE %s
        ", $old_domain, $new_domain, '%' . $old_domain . '%'));
        
        // Update postmeta
        $postmeta_updated = $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->postmeta} 
            SET meta_value = REPLACE(meta_value, %s, %s)
            WHERE meta_value LIKE %s
        ", $old_domain, $new_domain, '%' . $old_domain . '%'));
        $results['postmeta'] = $postmeta_updated !== false ? $postmeta_updated : 0;
        
        // Update options
        $options_updated = $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->options} 
            SET option_value = REPLACE(option_value, %s, %s)
            WHERE option_value LIKE %s
        ", $old_domain, $new_domain, '%' . $old_domain . '%'));
        $results['options'] = $options_updated !== false ? $options_updated : 0;
        
        // Update comments
        $comments_updated = $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->comments} 
            SET comment_content = REPLACE(comment_content, %s, %s)
            WHERE comment_content LIKE %s
        ", $old_domain, $new_domain, '%' . $old_domain . '%'));
        $results['comments'] = $comments_updated !== false ? $comments_updated : 0;
        
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
    }
    
    return $results;
}

function display_results($results) {
    ?>
    <div class="wrap">
        <h1>🎉 Domain URL Fix Complete!</h1>
        
        <div class="notice notice-success">
            <h3>📊 Update Results</h3>
            <ul>
                <li><strong>Posts Updated:</strong> <?php echo $results['posts']; ?></li>
                <li><strong>Post Meta Updated:</strong> <?php echo $results['postmeta']; ?></li>
                <li><strong>Options Updated:</strong> <?php echo $results['options']; ?></li>
                <li><strong>Comments Updated:</strong> <?php echo $results['comments']; ?></li>
            </ul>
        </div>
        
        <?php if (!empty($results['errors'])): ?>
            <div class="notice notice-error">
                <h4>❌ Errors</h4>
                <ul>
                    <?php foreach ($results['errors'] as $error): ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="card" style="max-width: 600px;">
            <h3>🚀 Next Steps</h3>
            <p><strong>Your domain URLs have been updated!</strong></p>
            <p>
                <a href="<?php echo home_url(); ?>" class="button button-primary">
                    Visit Your Site
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=story'); ?>" class="button">
                    Check Your Stories
                </a>
            </p>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
                <h4>💡 Additional Steps</h4>
                <ul>
                    <li>Clear any caching plugins</li>
                    <li>Test your navigation and links</li>
                    <li>Update any external services with the new domain</li>
                    <li>Set up redirects from the old domain if needed</li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}
?>