<?php 
function make_image_paths_relative(&$item, $key) {
    $item = replace_wp_content_with_relative($item);
}

function replace_wp_content_with_relative($input) {
    if (is_string($input)) {
        $url = preg_quote(get_site_url(), "/");
        $match = "/\/wp-content\//";
        return preg_replace($match, '../', $input);
    } else {
        return $input;
    }
}
?>