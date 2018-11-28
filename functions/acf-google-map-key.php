<?php
function my_acf_google_map_api($api) {
    $api['key'] = get_field('google_maps_api_key', 'option');
    return $api;
}

add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');
?>