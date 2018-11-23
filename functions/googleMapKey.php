<?php

function my_acf_google_map_api($api) 
{
    $google_map_api_key = 'AIzaSyCGmjGFijvdRnJh7K0NBGkVKileCd6Al8Q';
    $api['key'] = $google_map_api_key;

    return $api;
}

add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

?>