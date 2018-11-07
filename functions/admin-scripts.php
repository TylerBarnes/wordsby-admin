<?php 

add_action( 'admin_enqueue_scripts', 'admin_scripts' );

function admin_scripts() {
    wp_enqueue_script('preview', get_template_directory_uri() . "/js/preview.js");
}

?>