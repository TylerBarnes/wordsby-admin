<?php 
function populate_templates_from_json_file($keep_defaults = '') {
    $wproot = get_home_path();
    $templates_json_path = "$wproot/preview/templates.json";
    $templates_json = false;
    $templates = array();

    if (file_exists($templates_json_path)) {
        $templates_json_str = file_get_contents($templates_json_path);
        $templates_json = json_decode($templates_json_str);
        $default_template_match = '/single\/|taxonomy\/|archive\/|index/';

        // remove defaults unless $keep_defaults is set
        if ($keep_defaults === '') {
            foreach($templates_json as $key => $template) {
            $match = preg_match($default_template_match, $template);
            if ($match) {
                unset($templates_json[$key]);
            }
        }
        }

        foreach($templates_json as $template) {
            $templates[$template] = filenameToTitle($template);
        }
    }

    return $templates;
}


// helper functions
function fromCamelCase($camelCaseString) {
    $re = '/(?<=[a-z])(?=[A-Z])/x';
    $a = preg_split($re, $camelCaseString);
    return join($a, " " );
}

function filenameToTitle($string) {
    if (!is_string($string)) {
        return error_log('Hmm, a string shouldve been passed in here..');
    }

    $without_extension = pathinfo($string, PATHINFO_FILENAME);
    $spaced_decamelcased_title = fromCamelCase($without_extension);
    $dashes_to_spaces = str_replace("-"," ", $spaced_decamelcased_title);
    $capitalized_title = ucwords($dashes_to_spaces);
    
    return $capitalized_title;
}
// end helper functions
?>