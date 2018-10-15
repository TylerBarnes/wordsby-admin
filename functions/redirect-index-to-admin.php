<?php 
add_filter( 'template_include', 'redirect_index_to_admin', 99 );

function redirect_index_to_admin( $template ) {

    $template_filename = str_replace(get_template_directory(). "/", '', $template);

    if ($template_filename === 'index.php') {
        wp_redirect(get_admin_url());
        return;
    } else {
        return $template;
    }
	
}
?>