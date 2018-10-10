<?php 
function set_templates() {
    $acf_templates = get_field('templates', 'option');
    
    $gatsby_templates = [];

    foreach($acf_templates as $template) {
        $template_name = $template['template_name'];
        $gatsby_templates["$template_name.js"] = $template_name;
    }

    return $gatsby_templates;
}

add_filter('theme_templates', 'set_templates');
?>