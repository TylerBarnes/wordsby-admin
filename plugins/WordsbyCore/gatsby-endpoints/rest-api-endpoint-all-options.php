<?php 
add_action( 'rest_api_init', 'custom_api_get_all_options' );   

function custom_api_get_all_options() {
    register_rest_route( 'wp/v1', '/all-options', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_options_callback'
    ));
}

function custom_api_get_all_options_callback() {
    $options_data = get_fields('options');
    array_walk_recursive($options_data, 'remove_urls');

    foreach($options_data as &$option) {
        if (is_null($option)) {
            $option = "null";
        }
    }

    $options_data['id'] = 'options';

    return $options_data;    
} 
?>