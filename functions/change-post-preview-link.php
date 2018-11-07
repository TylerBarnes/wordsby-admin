<?php 
// add_filter( 'preview_post_link', 'the_preview_fix' );

// function the_preview_fix() {
//     $preview_id = get_the_ID();
//     return "/preview/?custom_preview_id=$preview_id";
// }

function custom_preview_page_link($link) {
	$id = get_the_ID();
	$nonce = wp_create_nonce( 'wp_rest' );
	$post_type = get_post_type($id);
	$obj = get_post_type_object($post_type);
	$rest_base = !empty($obj->rest_base) ? $obj->rest_base : $obj->name;
	// $link = "/wp-json/wp/v2/$rest_base/$id/preview/?_wpnonce=$nonce";
	$link = "/preview/?rest_base=$rest_base&preview=$id&_wpnonce=$nonce";
	return $link;
}
add_filter('preview_post_link', 'custom_preview_page_link');
?>