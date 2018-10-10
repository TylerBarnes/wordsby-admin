<?php 

function set_templates($templates) {
    $template_population_method = get_field('how_should_we_populate_the_template_dropdown', 'options');

    if ($template_population_method === 'gatsby') {
        return populate_templates_from_gatsby_filesystem(
            get_field('gatsby_templates_path', 'option')
        );
    } elseif ($template_population_method === 'repeater') {
        return populate_templates_from_acf_options(
            get_field('templates', 'option')
        );
    } elseif(
        $template_population_method === 'gatsby_repo' 
        && get_field('gatsby_repo_templates_path', 'option')
        ) {
        return populate_templates_from_gatsby_repo(
            get_field('gatsby_repo_templates_path', 'option')
        );
    } else {
        return $templates;
    }
    
}

add_filter('theme_templates', 'set_templates');

function populate_templates_from_gatsby_repo($template_path_from_gatsby_root) {
    if (file_exists(get_template_directory() . "/gatsby/")) {
        return populate_templates_from_gatsby_filesystem("gatsby/$template_path_from_gatsby_root");
    }
};

function populate_templates_from_gatsby_filesystem($gatsby_templates_path) {
    $gatsby_templates = [];
    $full_gatsby_templates_path = get_template_directory() . "/" . $gatsby_templates_path;

    if (!file_exists($full_gatsby_templates_path)) return write_log("The specified templates directory does not exist. Trying at $full_gatsby_templates_path");

    // recursively return folders and templates in an array called $r.
    $ritit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($full_gatsby_templates_path), RecursiveIteratorIterator::CHILD_FIRST); 
    $r = array(); 
    foreach ($ritit as $splFileInfo) { 
        // if it's a hidden file or a . or a .. then just skip it.
        if (preg_match('/^(\.\w+|\.$|\.\.$)/i', $splFileInfo->getFileName())) continue;

        $path = $splFileInfo->isDir() 
                ? array($splFileInfo->getFilename() => array()) 
                : array($splFileInfo->getFilename()); 

        for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) { 
            $path = array($ritit->getSubIterator($depth)->current()->getFilename() => $path); 
        } 
        
        $r = array_merge_recursive($r, $path);
    } 

    // $r is an array of our folders and files. 
    // add our files and paths to the gatsby templates array
    foreach($r as $key => $value) {
        if (is_string($value)) {
            $gatsby_templates[$value] = filenameToTitle($value);
        } elseif (is_array($value)) {
            foreach ($value as $file) {
                if (!is_string($file)) continue;

                $gatsby_templates["$key/$file"] = filenameToTitle($file);
            }
        }
    }

    return $gatsby_templates;
}

function populate_templates_from_acf_options($acf_templates) {
    $gatsby_templates = [];

    foreach($acf_templates as $template) {
        $name = $template['template_name'];
        $filename = $template['template_filename'];
        $gatsby_templates["$filename"] = $name;
    }

    return $gatsby_templates;
}


// helper functions
function fromCamelCase($camelCaseString) {
    $re = '/(?<=[a-z])(?=[A-Z])/x';
    $a = preg_split($re, $camelCaseString);
    return join($a, " " );
}

function filenameToTitle($string) {
    if (!is_string($string)) {
        return write_log('Hmm, a string shouldve been passed in here..');
    }

    // $fileExtension = pathinfo($string, PATHINFO_EXTENSION);
    // $fileExtensionString = (string)$fileExtension;
    // write_log($fileExtensionString);

    // if ($fileExtensionString !== 'js') {
    //     // write_log("$string is not a javascript file");
    //     return false;
    // }

    $without_extension = pathinfo($string, PATHINFO_FILENAME);
    $spaced_decamelcased_title = fromCamelCase($without_extension);
    $capitalized_title = ucfirst($spaced_decamelcased_title);
    
    return $capitalized_title;
}
// end helper functions
?>