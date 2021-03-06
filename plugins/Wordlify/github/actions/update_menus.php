<?php 

add_action('wp_update_nav_menu', 'commitMenus');

function commitMenus($id) {
    commit(
        createCommitMessage($id, "menu", "Menu "),
        [
            [
                'path' => 'wordsby/data/menus.json',
                'content' => getMenusJSON(), 
                'encoding' => 'utf-8'
            ],
        ]
    );
}

?>
