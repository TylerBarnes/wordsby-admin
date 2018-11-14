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

        $post->searchData = [];
        if($post->post_content > ""){
          array_push($post->searchData, wp_strip_all_tags($post->post_content));
        }


        $fields = get_fields($id);
        if($fields){
          $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($fields));
          foreach($iterator as $key => $value) {
              if(
                  $value !== null  && $value !== ""  &&   // check for empty strings
                  strlen($value) > 50 &&                  //remove small strings
                  substr($value, 0, 4 ) !== "http"        //remove links
              )
              array_push($post->searchData, wp_strip_all_tags($value, true));
          }
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
