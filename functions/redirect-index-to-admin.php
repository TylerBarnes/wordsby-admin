<?php 
add_filter( 'template_include', 'redirect_index_to_admin', 99 );

function redirect_index_to_admin( $template ) {

      if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['gatsbypress_previews']) || isset($_POST['gatsbypress_preview_keycheck']))) {
            return;
        }

    if (isset($_GET['rest_base']) || isset($_GET['nonce']) || isset($_GET['_wpnonce'])) return;

    $template_filename = str_replace(get_template_directory(). "/", '', $template);

    if ($template_filename === 'index.php') {
        wp_redirect(get_admin_url());
        return;
    } else {
        return $template;
    }
	
}
?>