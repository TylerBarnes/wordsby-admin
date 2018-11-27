<?php 
function remove_urls(&$item, $key) {
    $item = replace_urls_with_pathnames($item);
}

function replace_urls_with_pathnames($text) {
    $url = preg_quote(get_site_url(), "/");
    $match = "/$url(?!\/wp-content\/|\/wp-admin\/|\/wp-includes\/)/";
    return preg_replace($match, '', $text);
}
?>