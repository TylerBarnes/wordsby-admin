<?php 
add_action("admin_init", "alwaysOneschema_builderPost", 2, 0);

function alwaysOneschema_builderPost() {
	if( !class_exists('acf') ) return;

	if (!post_type_exists('schema_builder')) {
		error_log('schema_builder post type doesnt exist');
		return;
	};

	$count = wp_count_posts('schema_builder');
	$publish_count = property_exists($count, 'publish') ? $count->publish : false;

    if (!$publish_count && $publish_count === 0) {
        // if (!term_exists('schema_builder', 'category')) wp_insert_term('schema_builder', 'category');
        
        // if (!term_exists('schema_builder', 'term')) wp_insert_term('schema_builder', 'term');

        $post = wp_insert_post([
            'post_type' => 'schema_builder', 
            'post_title' => 'schema_builder', 
            'post_status' => 'publish',
			]);
            // 'tags_input' => ['schema_builder']

        $schema_builder_term_id = get_term_by('slug', 'uncategorized', 'category')->term_taxonomy_id;

		wp_set_post_terms($post, [$schema_builder_term_id], 'category', true);
		
		update_field('is_archive', 1, $post);
		update_field('posts_per_page', 1, $post);
		update_field('post_type', 'schema_builder', $post);
    }
}

function cptui_register_my_cpts_schema_builder() {

	/**
	 * Post Type: schema_builder.
	 */

	$labels = array(
		"name" => __( "Schema Builder", "" ),
		"singular_name" => __( "Schema", "" ),
	);

	$args = array(
		"label" => __( "Schema Builder", "" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => false,
		"show_ui" => true,
		"delete_with_user" => false,
		"show_in_rest" => false,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
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

	register_post_type( "schema_builder", $args );
}

add_action( 'init', 'cptui_register_my_cpts_schema_builder', 1, 0 );

?>