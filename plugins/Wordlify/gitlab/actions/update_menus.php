<?php 

add_action('wp_update_nav_menu', 'commitMenus');
function commitMenus($id) {
    commit(
        createCommitMessage($id, "menu", "Menu "),
        [
            [
                'filename' => 'menus.json',
                'base_path' => 'wordsby/data/',
                'content' => getMenusJSON(), 
                'encoding' => 'text'
            ],
        ]
    );
}

?>
