<?php 
// made with the help of 
// https://www.levibotelho.com/development/commit-a-file-with-the-github-api/

add_action('acf/save_post', 'commitData');

function commitData() {
    $branch = "heads/" . WORDLIFY_BRANCH;
    
    try{
        $client = getGitHubClient(); 

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

        $blob = 
        $client->api('gitData')->blobs()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            [
                'content' => 'Test content3', 
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
                        'path' => 'test.md',
                        'mode' => '100644',
                        'type' => 'blob',
                        'sha' => $blob['sha']
                    ]
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