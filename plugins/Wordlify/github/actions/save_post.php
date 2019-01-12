<?php 
add_action('acf/save_post', 'commitJSON');

function commitJSON($id) {
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
                'path' => 'wordsby/data/collections.json',
                'content' => getCollectionsJSON(), 
                'encoding' => 'utf-8'
            ],
            [
                'path' => 'wordsby/data/tax-terms.json',
                'content' => getTaxTermsJSON(), 
                'encoding' => 'utf-8'
            ],
            [
                'path' => 'wordsby/data/options.json',
                'content' => getOptionsJSON(), 
                'encoding' => 'utf-8'
            ],
            [
                'path' => 'wordsby/data/site-meta.json',
                'content' => getSiteMetaJSON(), 
                'encoding' => 'utf-8'
            ],
            [
                'path' => 'wordsby/data/menus.json',
                'content' => getMenusJSON(), 
                'encoding' => 'utf-8'
            ],
        ]
    );
}

?>