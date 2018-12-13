<?php 
/**
 * Register Post Type POST Psychic Window
 *
 * @return void
 **/
function psychic_cpt_psychic_window() {
    $labels = array(
        'name'               => 'Psychic Window',
        'singular_name'      => 'Psychic Window',
        'add_new'            => 'Add New Psychic Window',
        'add_new_item'       => 'Add New Psychic Window',
        'edit_item'          => 'Edit Psychic Window',
        'new_item'           => 'New Psychic Window',
        'view_item'          => 'View Psychic Window',
        'search_items'       => 'Search Psychic Window',
        'not_found'          => 'Not found',
        'not_found_in_trash' => 'Not found in trash',
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'query_var'          => true,
        'rewrite'            => array(
            'slug'       => 'psychic-window',
            'with_front' => false,
        ),
        'has_archive'        => false,
        'capability_type'    => 'page',
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-marker', // https://developer.wordpress.org/resource/dashicons/.
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
    );
    register_post_type( 'psychic_window', $args );
}
add_action( 'init', 'psychic_cpt_psychic_window' );
?>