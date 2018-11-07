<?php 
$post_id = $_GET['preview_id'];

// $revisions = wp_get_post_revisions( 
//     $post_id, 
//     array( 
//         // 'check_enabled' => false,
//         'post_status' => 'any'
//     ) 
// );

$revisions2 = get_posts( array(
              'post_status' => 'any',
            'post_parent' => intval($post_id),
            'post_type' => 'revision',
            'sort_column' => 'ID',
            'sort_order' => 'desc',
            'posts_per_page' => 1         
        )
    ); 
?>
<pre><?php print_r($revisions2); ?></pre>