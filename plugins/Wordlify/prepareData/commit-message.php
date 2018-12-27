<?php 

function createCommitMessage(
    $post_id, 
    $title = "", 
    $post_type = "Post", 
    $action = "updated"
    ) {

    $site_url = get_site_url();

    if (!$post_id) {
        $id_message = "";
    } else {
        $id_message = "[id:$post_id]";
    }
    
    if ($title === "") {
        $title = get_the_title($post_id);
    } elseif ($title === "menu") {
        $title = wp_get_nav_menu_object($post_id)->name;
    }

    $username = getCurrentUser()['name'];

    return "
            $post_type \"$title\" $action $id_message 
            — by $username (from $site_url)
    ";
}

?>