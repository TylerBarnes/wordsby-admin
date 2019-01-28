<?php 
add_action( 'rest_api_init', 'get_last_publish' );   

function get_last_publish() {
    register_rest_route( 'wordsby/v1', '/last_publish/(?P<id>\\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_last_publish_callback',
        'args' => array(
            'id' => array(
            'validate_callback' => 
                function($param, $request, $key) {
                    return is_numeric( $param );
                }
            ),
        ),
    ));
}

function get_last_publish_callback( $data ) {
    $id_param = $data->get_param('id');

    if (!$id_param) return false;

    return get_the_modified_date('U', $id_param);
} 
?>