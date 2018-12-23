<?php 

function getOptionsJSON() {
    return json_encode(array(
        array(
            'key' => 'url',
            'value' => get_bloginfo('url')
        ),
        array(
            'key' => 'name',
            'value' => get_bloginfo('name')
        ),
        array(
            'key' => 'description',
            'value' => get_bloginfo('description')
        ),
    ));
}

?>