<?php 

function set_templates() {
    return populate_templates_from_json_file();
}

add_filter('theme_templates', 'set_templates');
?>