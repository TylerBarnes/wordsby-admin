<?php 
// dont compress jpegs
add_filter('jpeg_quality', function() {
    return 100;
});

/**
 * Remove standard image sizes so that these sizes are not
 * created during the Media Upload process
 *
 * Tested with WP 3.2.1
 *
 * Hooked to intermediate_image_sizes_advanced filter
 * See wp_generate_attachment_metadata( $attachment_id, $file ) in wp-admin/includes/image.php
 *
 * @param $sizes, array of default and added image sizes
 * @return $sizes, modified array of image sizes
 * @author Ade Walker http://www.studiograsshopper.ch
 */
function unsetSizes($sizes) {
	unset($sizes['thumbnail']);
	unset($sizes['medium']);
	unset($sizes['medium_large']);
	unset($sizes['large']);
	
	return $sizes;
}

function sgr_filter_image_sizes( $sizes) {
	if (!function_exists('get_field')) {
		// write_log('no get field'); 
		return unsetSizes($sizes);
	} else if (
		function_exists('get_field') && get_field('disable_generate_thumbnails', 'option')
	) {
		// write_log('no get field and dont generate thumbnails'); 
		return unsetSizes($sizes);
	}
	return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'sgr_filter_image_sizes');
?>