<?php 
// made with the help of 
// https://www.levibotelho.com/development/commit-a-file-with-the-github-api/

add_action('acf/save_post', 'commitData');

function commitData() {
    // dont create commits when saving menus
    if (isset($_POST['nav-menu-data'])) return;

    // dont create commits when saving preview revisions.
    if (
        isset($_POST['wp-preview']) && 
        $_POST['wp-preview'] === 'dopreview'
    ) return $id;

    $branch = "heads/" . WORDLIFY_BRANCH;
    
    try{
        $client = getGitHubClient(); 
        if (!$client) return;

        $head_reference = 
        $client->api('gitData')->references()->show(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            $branch
        );

        $head_commit = 
        $client->api('gitData')->commits()->show(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            $head_reference['object']['sha']
        );

        $collections_blob = 
        $client->api('gitData')->blobs()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            [
                'content' => getCollectionsJSON(), 
                'encoding' => 'utf-8'
            ]
        );

        $tax_terms_blob = 
        $client->api('gitData')->blobs()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            [
                'content' => getTaxTermsJSON(), 
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
                        'path' => 'wordsby/data/collections.json',
                        'mode' => '100644',
                        'type' => 'blob',
                        'sha' => $collections_blob['sha']
                    ],
                    [
                        'path' => 'wordsby/data/tax-terms.json',
                        'mode' => '100644',
                        'type' => 'blob',
                        'sha' => $tax_terms_blob['sha']
                    ],
                ]
            ]
        );

        $commit = 
        $client->api('gitData')->commits()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            [
                'message' => 'testing wordlify git3', 
                'tree' => $tree['sha'], 
                'parents' => [$head_commit['sha']]
            ]
        );

        $update_head_to_new_commit = 
        $client->api('gitData')->references()->update(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            $branch, 
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