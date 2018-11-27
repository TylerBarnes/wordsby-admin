<?php 
add_action( 'rest_api_init', 'custom_api_get_all_posts' );   

function custom_api_get_all_posts() {
    register_rest_route( 'wp/v1', '/collections', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_posts_callback'
    ));
}

function custom_api_get_all_posts_callback( $data ) {
    $id_param = $data->get_param('id');

    return posts_formatted_for_gatsby($id_param);    
} 

function posts_formatted_for_gatsby($id_param, $revision = "") {
    // Initialize the array that will receive the posts' data. 
    $posts_data = array();

    if ($revision === "") {
        $posts = get_posts( array(
                'post_type' => 'any',
                'posts_per_page' => -1, 
                'p' => $id_param           
            )
        ); 
    } else {
        $posts = get_posts( array(
            'post_type' => 'revision',
            'posts_per_page' => 1, 
            'post_parent' => $id_param,
            'post_status' => 'any'     
        )
    ); 
    }
    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach( $posts as $post ) {
        $id = $post->ID; 
        $post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;
        $permalink = get_permalink($id);
        $post_type = $post->post_type;
        $template_slug = get_page_template_slug($id);

        $template = $template_slug ? $template_slug : "single/$post_type";

        $post_taxonomies = get_post_taxonomies($id);

        $post_taxonomy_terms = array();
        $post_terms = array();

        foreach($post_taxonomies as $taxonomy) {
            $terms = get_the_terms($id, $taxonomy);
            
            if (!$terms) continue;

            foreach($terms as $term) {
                $term->pathname = str_replace(home_url(), "", get_term_link($term));
                array_push($post_terms, $term->slug);
            }
            
            $firstTermPathname = $terms[0]->pathname;
            $firstTermSlug = $terms[0]->slug . "/";
            
            $taxonomy_pathname = str_replace($firstTermSlug, "", $firstTermPathname);

            $taxonomy_object = get_taxonomy($taxonomy);
            
            $post_taxonomy_terms[$taxonomy] = array(
                'labels' => array(
                    'plural' => $taxonomy_object->label,
                    'single' => $taxonomy_object->labels->singular_name
                ),
                'pathname' => $taxonomy_pathname,
                'terms' => $terms
            );
        } 

        $all_acf = get_fields($id);

        if ($all_acf) {
            array_walk_recursive($all_acf, 'remove_urls');
        }

        $post->type = "collection";
        $post->taxonomies = $post_taxonomy_terms;
        $post->term_slugs = $post_terms;
        $post->taxonomy_slugs = $post_taxonomies;
        $post->pathname = str_replace(home_url(), '', $permalink); 
        $post->permalink = $permalink;
        $post->featured_img = $post_thumbnail;
        $post->template_slug = $template;
        $post->acf = $all_acf;
        $post->post_content = replace_urls_with_pathnames(do_shortcode($post->post_content));

        $posts_data[] = $post;
    }                  
    return $posts_data;   
}
?>