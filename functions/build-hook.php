<?php 
add_action( 'save_post', 'build_hook' );
function build_hook($post_id)
{
	if(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
		return;
	}
	$buildHook = get_field('build_webhook', 'options');
	error_log($buildHook);
   	if ($buildHook) {
		   $response = Requests::post( $buildHook );
	}
}
 ?>