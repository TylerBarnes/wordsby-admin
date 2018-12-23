<?php 

add_action('wp_update_nav_menu', 'commitMenus');
function commitMenus($id) {
    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return $id;

    global $branch;

    
    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;
    $menu_object = wp_get_nav_menu_object($id);
    $title = $menu_object->name;
    
    $base_path = "wordsby/data/";
    
    $client = getGitlabClient();

    if (!$client) return;
    
    $menus_action = isFileInRepo($client, $base_path, 'menus.json') 
    ? 'update' : 'create';

    $menus = json_encode(
        wordlify_get_menus(), JSON_UNESCAPED_SLASHES
    );

    $url = preg_quote(get_site_url(), "/");

    $menus_content = preg_replace(
        "/$url/", '', makeImagesRelative($menus)
    );

    $commit = $client->api('repositories')->createCommit(WORDSBY_GITLAB_PROJECT_ID, array(
        'branch' => $branch, 
        'commit_message' => "Menu $menus_action \"$title\" [id:$id] — by $username (from $site_url)",
        'actions' => array(
            array(
                'action' => $menus_action,
                'file_path' => $base_path . "menus.json",
                'content' => $menus_content,
                'encoding' => 'text'
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    ));

    return $commit; 

}

?>