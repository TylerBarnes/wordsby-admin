<?php 

add_action( 'admin_enqueue_scripts', 'admin_scripts' );

function admin_scripts() {
    wp_register_script('preview', get_template_directory_uri() . "/js/preview.js");

    wp_enqueue_script( 'preview' );

    $available_templates = populate_templates_from_json_file(true);

    wp_localize_script( 'preview', 'availableTemplates', $available_templates );
}

?>