<?php 

// made with the help of 
// https://www.levibotelho.com/development/commit-a-file-with-the-github-api/
function commit($commit_message, $files) {
    global $branch;
    $branch_path = "heads/$branch";

    global $mediaBranch;
    $media_branch_path = "heads/$mediaBranch";

    try {
        $client = getGithubClient();
        if (!$client) return;

        $user = getCurrentUser();

        // check if the media branch exists.
        $media_branch_exists = desiredBranchExists($client);
    
        // check if there are any text files in the commit.
        $is_media_only_commit = !in_array(
            'utf-8', array_column($files, 'encoding')
        );
    
        if ($is_media_only_commit && !$media_branch_exists) {
            createMediaBranch($client);
        }
    
        // if there are no text files in the commit or the media branch exists then we want to commit to the media branch.
        // This is to trigger the least amount of builds possible on the main branch.
        // Media files are commited to their own branch if the commit doesn't conain any JSON files. 
        // On saving a post, if the media branch exists it will be commited there.
        // Once JSON is commited, the media branch gets merged triggering a site build from our main branch.
        // If we trigger a build for every commit then the build times could be 2 to 3 times longer when someone uploads 1 or even 10 images to a post.
        // this fixes that.
        $should_commit_to_media_branch = 
        $is_media_only_commit || $media_branch_exists;
    
        if ($should_commit_to_media_branch) {
            $commit_branch = $media_branch_path;
        } else {
            $commit_branch = $branch_path;
        }

        $head_reference = 
        $client->api('gitData')->references()->show(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            $commit_branch
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
                'message' => $commit_message, 
                'tree' => $tree['sha'], 
                'parents' => [$head_commit['sha']],
                'author' => [
                    'name' => 'WP user ' . $user['name'],
                    'email' => $user['email']
                ]
            ]
        );

        $update_head_to_new_commit = 
        $client->api('gitData')->references()->update(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO,
            $commit_branch, 
            [
                'sha' => $commit['sha'],
                'force' => false
            ]
        );

        if (!$is_media_only_commit && $media_branch_exists) {
            $owner = WORDLIFY_GITHUB_OWNER;
            $repo = WORDLIFY_GITHUB_REPO;

            // merge the media branch to the main branch.
            $merge_media = $client->getHttpClient()->post(
                "repos/$owner/$repo/merges",
                [],
                json_encode([
                    'base' => $branch,
                    'head' => $mediaBranch,
                    'commit_message' => "$commit_message [MERGE MEDIA]"
                ])
            );

            // remove the media branch
            $delete_branch = $client->getHttpClient()->delete(
                "repos/$owner/$repo/git/refs/heads/$mediaBranch"
            );
        }

    } catch (Exception $e) {
        write_log($e); 
    }
}

?>