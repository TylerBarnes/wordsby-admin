<?php 
function custom_preview_page_link($link) {
	$id = get_the_ID();
	$user_id = get_current_user_id();
	// wp_set_current_user( $user_id);
	// wp_set_auth_cookie( $user_id );
	$nonce = wp_create_nonce( 'wp_rest' );
	$post_type = get_post_type($id);
	$obj = get_post_type_object($post_type);
	$rest_base = !empty($obj->rest_base) ? $obj->rest_base : $obj->name;
	$link = "/preview/single/$post_type/?rest_base=$rest_base&preview=$id&nonce=$nonce";
	return $link;
}
add_filter('preview_post_link', 'custom_preview_page_link');
?>