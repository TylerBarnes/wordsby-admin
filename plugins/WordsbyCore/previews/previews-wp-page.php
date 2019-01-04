<?php 

function add_preview_post_type() {
    $args = array(
      'public' => true,
      'label'  => 'preview',
      'show_ui' => false,
      'show_in_nav_menus' => false,
      'show_in_menu' => false,
      'show_in_admin_bar' => false,
      'hierarchical' => true,
      'has_archive' => true,
      'rewrite' => [
          'with_front' => false,
          'slug' => 'preview'
      ],
      'show_in_rest' => false,
    );
    register_post_type( 'preview', $args );
}
add_action( 'init', 'add_preview_post_type' );

add_filter('archive_template', 'add_archive_template');

function add_archive_template($template) {
    global $wp_query;
    if (is_post_type_archive('preview')) {
        $template = __DIR__ . '/archive-preview.php';
    }
    return $template;
}

?>