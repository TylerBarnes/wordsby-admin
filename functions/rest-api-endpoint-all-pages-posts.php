<?php 
add_action( 'rest_api_init', 'custom_api_get_all_posts' );   

function custom_api_get_all_posts() {
    register_rest_route( 'wp/v1', '/collections', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_posts_callback'
    ));

    register_rest_route( 'wp/v1', '/collections/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_posts_callback',
        'args' => [
            'id' => [
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param);
                }
            ]
        ]
    ));
}

function custom_api_get_all_posts_callback( $data ) {
    $id_param = $data->get_param('id');

    // Initialize the array that will receive the posts' data. 
    $posts_data = array();

    // Get the posts using the 'post' and 'news' post types
    $posts = get_posts( array(
            'post_type' => 'any',
            'posts_per_page' => -1, 
            'p' => $id_param           
        )
    ); 
    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach( $posts as $post ) {
        $id = $post->ID; 
        $post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;
        $permalink = get_permalink($id);
        $post_type = $post->post_type;
        $template_slug = get_page_template_slug($id);
        $template_slug_no_ext = strstr($template_slug, '.', true);
        $template = $template_slug_no_ext ? $template_slug_no_ext : "default/$post_type";

        $post->pathname = str_replace(home_url(), '', $permalink); 
        $post->permalink = $permalink;
        $post->featured_img = $post_thumbnail;
        $post->acf = get_fields($id);
        $post->template_slug = $template;

        $posts_data[] = $post;
    }                  
    return $posts_data;                   
} 
?>