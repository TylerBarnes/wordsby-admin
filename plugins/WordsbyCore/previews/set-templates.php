<?php 

function set_templates() {
    return populate_templates_from_json();
}

add_filter('theme_templates', 'set_templates');
?>