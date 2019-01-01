<?php 
function custom_preview_page_link($link) {
	$id = get_the_ID();
	$user_id = get_current_user_id();
	$nonce = wp_create_nonce( 'wp_rest' );

	$available_templates = get_option('templates-all');
	if (!$available_templates) return false;
	$default_template = "index";
	
	$post_type = get_post_type($id);
	$obj = get_post_type_object($post_type);

	$is_archive = get_field('is_archive', $id);
	$archive_post_type = get_field('post_type', $id);

	$assigned_template = get_post_meta($id, "_wp_page_template", true);
	
	if ($is_archive) {
		$assigned_template = "archive/$archive_post_type";
	} elseif ($assigned_template === "") {
		$assigned_template = "single/$post_type";
	}

	$rest_base = !empty($obj->rest_base) ? $obj->rest_base : $obj->name;
	
	if ($assigned_template === 'default' && !$is_archive) {
		$desired_template = "single/$post_type";
	} else {
		$desired_template = $assigned_template;
	}

	if (array_key_exists($desired_template, $available_templates)) {
		$available_template = $desired_template;
	} else {
		$available_template = $default_template;
	}
	
	$link = get_home_url() . "/preview/$available_template/?rest_base=$rest_base&preview=$id&nonce=$nonce";

	return $link;
}
add_filter('preview_post_link', 'custom_preview_page_link');
?>