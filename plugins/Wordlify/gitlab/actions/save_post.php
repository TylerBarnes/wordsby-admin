<?php 
// on post save, trash, or untrash, commit the collections endpoint to the gatsby repo.
add_action('acf/save_post', 'commitData');
add_action('trashed_post', 'commitData');
add_action('untrashed_post', 'commitData');

function commitData($id) {
    // dont create commits when saving menus
    if (isset($_POST['nav-menu-data'])) return;

    // dont create commits when saving preview revisions.
    if (
        isset($_POST['wp-preview']) && 
        $_POST['wp-preview'] === 'dopreview'
    ) return $id;
    
    commit(
        createCommitMessage($id),
        [
            [
                'filename' => 'collections.json',
                'base_path' => 'wordsby/data/',
                'content' => getCollectionsJSON(), 
                'encoding' => 'text'
            ],
            [
                'filename' => 'tax-terms.json',
                'base_path' => 'wordsby/data/',
                'content' => getTaxTermsJSON(), 
                'encoding' => 'text'
            ],
            [
                'filename' => 'options.json',
                'base_path' => 'wordsby/data/',
                'content' => getOptionsJSON(), 
                'encoding' => 'text'
            ],
            [
                'filename' => 'site-meta.json',
                'base_path' => 'wordsby/data/',
                'content' => getSiteMetaJSON(), 
                'encoding' => 'text'
            ],
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
