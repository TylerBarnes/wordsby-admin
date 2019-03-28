<?php 
$develop_preview = 
	defined('DANGEROUS__WORDSBY_PUBLIC_PREVIEWS') && DANGEROUS__WORDSBY_PUBLIC_PREVIEWS 
		? DANGEROUS__WORDSBY_PUBLIC_PREVIEWS : false;

global $pagenow;
if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
    add_action( 'admin_head', 'wpse_125800_custom_publish_box' );
    function wpse_125800_custom_publish_box() {
        if (!is_admin()) return;
			
		if (get_field('build_site_url', 'option')) return;

        $style = '';
        $style .= '<style type="text/css">';
        $style .= '#edit-slug-box, #minor-publishing-actions';
        $style .= '{display: none; }';
        $style .= '</style>';

        echo $style;
    }
}

add_action('admin_notices', 'public_previews_reminder');
function public_previews_reminder() {
	global $develop_preview;

	if ($develop_preview) jp_notices_add_error('Previews are public. disable DANGEROUS__WORDSBY_PUBLIC_PREVIEWS in wp-config.php');

	return;
}

function custom_preview_page_link($link) {
	global $develop_preview;

	$id = get_the_ID();
	$user_id = get_current_user_id();
	$nonce = wp_create_nonce( 'wp_rest' );

	$available_templates = get_option('templates-collections');
	if (!$available_templates) return false;
	$default_template = "index";
	
	$post_type = get_post_type($id);
	$obj = get_post_type_object($post_type);

	$is_archive = get_field('is_archive', $id);
	$archive_post_type = get_field('post_type', $id);

	$assigned_template = get_post_meta($id, "_wp_page_template", true);
	
	$rest_base = !empty($obj->rest_base) ? $obj->rest_base : $obj->name;

	if($post_type == 'page'){

		if (array_key_exists($assigned_template, $available_templates)) {
			$available_template = $assigned_template;
		} else {
			$available_template = $default_template;
		}

	}elseif($is_archive){
		$available_template = "archive/$archive_post_type";
	}else{
		$available_template = "single/$post_type";
	}

	if ($develop_preview) {
		$link = "http://localhost:8000/preview/$available_template/?rest_base=$rest_base&preview=$id&nonce=$nonce&localhost=true";
	} else {
		$link = get_home_url() . "/preview/?available_template=" . urlencode($available_template) . "&rest_base=$rest_base&preview=$id&id=$id&nonce=$nonce";
	}

	return $link;
}

add_filter('preview_post_link', 'custom_preview_page_link');
?>
