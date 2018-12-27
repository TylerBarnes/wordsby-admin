<?php 

add_action('wp_handle_upload', 'commitMedia');

function commitMedia($upload) {
    global $mediaBranch;

    try {
        $client = getGitHubClient(); 
        if (!$client) return;

        $initial_filepath = explode("uploads/", $upload['file'])[1];
        $filename = basename($initial_filepath);
        $subdir = dirname($initial_filepath);
        
        $base_path = 'wordsby/uploads';
        $file_dir = "$base_path/$subdir";
        $filepath = "$file_dir/$filename";

        commit(
            createCommitMessage(
                null, "upload", "Upload ", $upload['type']
            ),
            [
                [
                    'path' => $filepath,
                    'content' => base64_encode(
                        file_get_contents($upload['file'])
                    ), 
                    'encoding' => 'base64'
                ]
            ]
        );

        // $head_reference = 
        // $client->api('gitData')->references()->show(
        //     WORDLIFY_GITHUB_OWNER, 
        //     WORDLIFY_GITHUB_REPO, 
        //     $branch_path
        // );

        // $head_commit = 
        // $client->api('gitData')->commits()->show(
        //     WORDLIFY_GITHUB_OWNER, 
        //     WORDLIFY_GITHUB_REPO, 
        //     $head_reference['object']['sha']
        // );

        // $media_blob = 
        // $client->api('gitData')->blobs()->create(
        //     WORDLIFY_GITHUB_OWNER, 
        //     WORDLIFY_GITHUB_REPO, 
        //     [
        //         'content' => base64_encode(file_get_contents($upload['file'])), 
        //         'encoding' => 'base64'
        //     ]
        // ); 
        
        // $tree = 
        // $client->api('gitData')->trees()->create(
        //     WORDLIFY_GITHUB_OWNER, 
        //     WORDLIFY_GITHUB_REPO,
        //     [
        //         'base_tree' => $head_commit['tree']['sha'],
        //         'tree' => [
        //             [
        //                 'path' => $filepath,
        //                 'mode' => '100644',
        //                 'type' => 'blob',
        //                 'sha' => $media_blob['sha']
        //             ],
        //         ]
        //     ]
        // ); 

        // $commit = 
        // $client->api('gitData')->commits()->create(
        //     WORDLIFY_GITHUB_OWNER, 
        //     WORDLIFY_GITHUB_REPO,
        //     [
        //         'message' => createCommitMessage(
        //             null, "upload", "Upload ", $upload['type']
        //         ), 
        //         'tree' => $tree['sha'], 
        //         'parents' => [$head_commit['sha']]
        //     ]
        // );

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

        return $upload;

    } catch (Exception $e) {
        write_log($e); 
    } 

    return $upload;
}

?>