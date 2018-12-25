<?php 

function makeImagesRelative($json) {
    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '../', $json
    );
}

?>