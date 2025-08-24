<?php
/**
 * WordPress Draft Posts Creator
 * Run this script once to create draft posts for all existing portfolio content
 * 
 * Instructions:
 * 1. Upload this file to your WordPress plugin directory
 * 2. Activate the Advanced Custom Fields plugin
 * 3. Access: yoursite.com/wp-content/plugins/RJB-2025-portfolio-plugin/create-draft-posts.php
 * 4. Or run from WordPress admin: Tools > Import (if you set it up as an admin page)
 */

// WordPress environment
require_once('../../../wp-config.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Portfolio stories data
$portfolio_stories = [
    // FEATURES SECTION
    [
        'title' => 'The Desert Grows',
        'content' => 'A British startup hopes to generate 8% of the U.K.'s electricity from a London-sized renewables development in Southern Morocco. What about the people who live there?',
        'excerpt' => 'A British startup hopes to generate 8% of the U.K.'s electricity from a London-sized renewables development in Southern Morocco. What about the people who live there?',
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

foreach ($categories_to_create as $slug => $name) {
    $term = wp_insert_term($name, 'story_category', array(
        'slug' => $slug
    ));
    
    if (!is_wp_error($term)) {
        $created_categories[$slug] = $term['term_id'];
        echo "✓ Created category: {$name} (ID: {$term['term_id']})<br>";
    } else {
        // Category might already exist
        $existing_term = get_term_by('slug', $slug, 'story_category');
        if ($existing_term) {
            $created_categories[$slug] = $existing_term->term_id;
            echo "✓ Found existing category: {$name} (ID: {$existing_term->term_id})<br>";
        } else {
            echo "✗ Failed to create category: {$name}<br>";
        }
    }
}

echo "<br><strong>Creating draft posts...</strong><br><br>";

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

        // Handle featured image (you would need to import/upload images manually)
        // For now, we'll just store the URL in a custom field
        if (!empty($story['image_url'])) {
            update_post_meta($post_id, 'original_image_url', $story['image_url']);
        }

        $created_posts++;
        echo "✓ Created draft post: {$story['title']} (ID: {$post_id})<br>";
    } else {
        $failed_posts++;
        echo "✗ Failed to create post: {$story['title']}<br>";
        if (is_wp_error($post_id)) {
            echo "   Error: " . $post_id->get_error_message() . "<br>";
        }
    }
}

echo "<br><strong>Import Complete!</strong><br>";
echo "✓ Categories created: " . count($created_categories) . "<br>";
echo "✓ Posts created: {$created_posts}<br>";
echo "✗ Posts failed: {$failed_posts}<br>";

echo "<br><strong>Next Steps:</strong><br>";
echo "1. Go to WordPress Admin > Stories to review all draft posts<br>";
echo "2. Upload featured images manually and assign them to posts<br>";
echo "3. Edit content and add more detailed text where needed<br>";
echo "4. Publish posts when ready<br>";
echo "5. Delete this script file for security<br>";

?>