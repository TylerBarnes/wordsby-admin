<?php 

add_action('wp_update_nav_menu', 'commitMenus');

function commitMenus($id) {
    try {
        $client = getGitHubClient(); 
        if (!$client) return;

        global $branch;
        $branch_path = "heads/$branch";

        $head_reference = 
        $client->api('gitData')->references()->show(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            $branch_path
        );

        $head_commit = 
        $client->api('gitData')->commits()->show(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            $head_reference['object']['sha']
        );

        $menus_blob = 
        $client->api('gitData')->blobs()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            [
                'content' => getMenusJSON(), 
                'encoding' => 'utf-8'
            ]
        ); 
        
        $tree = 
        $client->api('gitData')->trees()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            [
                'base_tree' => $head_commit['tree']['sha'],
                'tree' => [
                    [
                        'path' => 'wordsby/data/menus.json',
                        'mode' => '100644',
                        'type' => 'blob',
                        'sha' => $menus_blob['sha']
                    ],
                ]
            ]
        );

        $commit = 
        $client->api('gitData')->commits()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            [
                'message' => createCommitMessage($id, "menu", "Menu "), 
                'tree' => $tree['sha'], 
                'parents' => [$head_commit['sha']]
            ]
        );

        $update_head_to_new_commit = 
        $client->api('gitData')->references()->update(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            $branch_path, 
            [
                'sha' => $commit['sha'],
                'force' => false
            ]
        );

    } catch (Exception $e) {
        write_log($e); 
    }  

}

?>