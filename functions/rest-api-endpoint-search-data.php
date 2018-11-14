<?php 
add_action( 'rest_api_init', 'custom_api_get_search_data' );   

function custom_api_get_search_data() {
    register_rest_route( 'wp/v1', '/collections/search', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_search_data_callback'
    ));
}

function custom_api_get_search_data_callback( $data ) {

    // Initialize the array that will receive the posts' data. 
    $posts_data = array();

    // Get the posts using the 'post' and 'news' post types
    $posts = get_posts( array(
            'post_type' => 'any',
            'posts_per_page' => -1
        )
    ); 
    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach( $posts as $post ) {

        $id = $post->ID; 
        $fields = get_fields($id);

        $post->searchData = [];
        array_push($post->searchData, $post->post_content);

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($fields));
        foreach($iterator as $key => $value) {
            if(
                $value !== null  && 
                strlen($value) > 20 &&
                substr($value, 0, 4 ) !== "http"
            ) 
                array_push($post->searchData, $value);
        }

        $posts_data[$id] = [
            'post_title' => $post->post_title,
            'post_type' => $post->post_type,
            'pathname' => str_replace(home_url(), '', get_permalink($id)),
            'search_data' => $post->searchData
        ];
        
    }                  
    return $posts_data;                   
} 
?>