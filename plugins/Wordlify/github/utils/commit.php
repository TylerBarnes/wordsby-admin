<?php 

function commit($id, $files, $branch = "", $mediaBranch = "") {
    if ($branch === "") {
        global $branch;
        $branch_path = "heads/$branch";
    }

    $client = getGithubClient();

    // check if the media branch exists
    $media_branch_exists = desiredBranchExists($client);

    // check if this is a media only commit
        // array filter the $files arg to check for base64 vs utf-8

    // if it is then commit to the media branch

    // if it isn't then commit to the media branch if it exists
        // commit to the media branch
        // merge the media branch into the main branch
        // delete the media branch

    // if it isn't a media commit and the media branch doesn't exist then just commit to the main branch.


    try {
        $client = getGitHubClient(); 
        if (!$client) return;

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

        $trees = [];

        foreach ($files as $file) {
            $file_blob = 
            $client->api('gitData')->blobs()->create(
                WORDLIFY_GITHUB_OWNER, 
                WORDLIFY_GITHUB_REPO, 
                [
                    'content' => $file['content'], 
                    'encoding' => $file['encoding']
                ]
            );

            array_push($trees, [
                'path' => $file['path'],
                'mode' => '100644',
                'type' => 'blob',
                'sha' => $file_blob['sha']
            ]);
        }
        
        $tree = 
        $client->api('gitData')->trees()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            [
                'base_tree' => $head_commit['tree']['sha'],
                'tree' => $trees
            ]
        );

        $commit = 
        $client->api('gitData')->commits()->create(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            [
                'message' => createCommitMessage($id), 
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