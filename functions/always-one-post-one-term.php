<?php 
add_action("init", "alwaysOneDummyPost");

function alwaysOneDummyPost() {
    $count = wp_count_posts('dummy')->publish;

    if (!$count && $count === 0) {
        if (!term_exists('dummy', 'category')) wp_insert_term('dummy', 'category');
        
        if (!term_exists('dummy', 'term')) wp_insert_term('dummy', 'term');

        $post = wp_insert_post([
            'post_type' => 'dummy', 
            'post_title' => 'dummy', 
            'post_status' => 'publish',
            'tags_input' => ['dummy']
            ]);

        $dummy_term_id = get_term_by('slug', 'dummy', 'category')->term_taxonomy_id;

        wp_set_post_terms($post, [$dummy_term_id], 'category', true);
    }
}

function cptui_register_my_cpts_dummy() {

	/**
	 * Post Type: dummy.
	 */

	$labels = array(
		"name" => __( "dummy", "" ),
		"singular_name" => __( "dummy", "" ),
	);

	$args = array(
		"label" => __( "dummy", "" ),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => false,
		"delete_with_user" => false,
		"show_in_rest" => false,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => false,
		"show_in_nav_menus" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => true,
		"query_var" => false,
		"supports" => array( "title", "editor", "thumbnail" ),
		"taxonomies" => array( "category", "post_tag" ),
	);

	register_post_type( "dummy", $args );
}

add_action( 'init', 'cptui_register_my_cpts_dummy' );

?>