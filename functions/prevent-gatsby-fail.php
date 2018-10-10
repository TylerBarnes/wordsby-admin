<?php 
// This function will be used to prevent gatsby from failing when you have 0 posts and 0 pages.

// add_action( 'init', 'ny_page_category' );
// function ny_page_category() {
// 	$labels = array(
// 		'name'              => 'Page Categories',
// 		'singular_name'     => 'Page Category',
// 		'search_items'      => 'Search Page Categories',
// 		'all_items'         => 'All Page Categories',
// 		'parent_item'       => 'Parent Page Category',
// 		'parent_item_colon' => 'Parent Page Category:',
// 		'edit_item'         => 'Edit Page Category',
// 		'update_item'       => 'Update Page Category',
// 		'add_new_item'      => 'Add New Page Category',
// 		'new_item_name'     => 'New Page Category Name',
// 		'menu_name'         => 'Page Categories',
// 	);
// 	$args = array(
// 		'hierarchical'      => true,
// 		'labels'            => $labels,
// 		'show_ui'           => $show_ui,
// 		'query_var'         => true,
// 		'rewrite'           => array( 'slug' => 'page_category' ),
// 	);
// 	register_taxonomy( 'page_category', array( 'page' ), $args );
// }

// add_action( 'pre_get_posts', 'my_hide_system_pages' );
// function my_hide_system_pages( $query ) {
//     $query->set( 'tax_query', array(array(
//         'taxonomy' => 'page_category',
//         'field' => 'slug',
//         'terms' => array( 'system-page' ),
//         'operator' => 'NOT IN'
//     )));
// }


// function the_slug_exists($post_name) {
// 	global $wpdb;
// 	if($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A')) {
// 		return true;
// 	} else {
// 		return false;
// 	}
// }

// // create a page
// if (isset($_GET['activated']) && is_admin()){
//     $page_title = 'Hidden Page';
//     $page_check = get_page_by_title($page_title);
//     $page = array(
// 	    'post_type' => 'page',
// 	    'post_title' => $page_title,
// 	    'post_content' => 'Look away..',
// 	    'post_status' => 'publish',
// 	    'post_author' => 1,
// 	    'post_slug' => 'hidden-page'
//     );
//     if(!isset($page_check->ID) && !the_slug_exists('blog')){
//         $page_id = wp_insert_post($page);
//     }
// }

// // change the Sample page to the home page
// if (isset($_GET['activated']) && is_admin()){
//     $home_page_title = 'Home';
//     $home_page_content = '';
//     $home_page_check = get_page_by_title($home_page_title);
//     $home_page = array(
// 	    'post_type' => 'page',
// 	    'post_title' => $home_page_title,
// 	    'post_content' => $home_page_content,
// 	    'post_status' => 'publish',
// 	    'post_author' => 1,
// 	    'ID' => 2,
// 	    'post_slug' => 'home'
//     );
//     if(!isset($home_page_check->ID) && !the_slug_exists('home')){
//         $home_page_id = wp_insert_post($home_page);
//     }
// }
// if (isset($_GET['activated']) && is_admin()){
// 	// Set the blog page
// 	$blog = get_page_by_title( 'Blog' );
// 	update_option( 'page_for_posts', $blog->ID );

// 	// Use a static front page
// 	$front_page = 2; // this is the default page created by WordPress
// 	update_option( 'page_on_front', $front_page );
// 	update_option( 'show_on_front', 'page' );
// }

?>