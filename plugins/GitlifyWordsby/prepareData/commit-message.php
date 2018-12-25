<?php 

function createCommitMessage($post_id) {
    $site_url = get_site_url();
    $title = get_the_title($post_id);

    return "Post \"$title\" updated [id:$id] 
    — by $username (from $site_url)";
}

?>