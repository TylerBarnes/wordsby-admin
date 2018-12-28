<?php 

add_action('delete_attachment', 'deleteMedia');
function deleteMedia($id) {
    $client = getGithubClient();
    if (!$client) return;

    global $mediaBranch;
    $filepath = wp_get_attachment_metadata($id)['file'];
    $filename = basename($filepath);
    $filedirectory = dirname($filepath); 

    $base_path = 'wordsby/uploads';

    $fulldirectory = "$base_path/$filedirectory/";
    $full_filepath = "$fulldirectory$filename";

    $user = getCurrentUser();

    createMediaBranchIfItDoesntExist($client);

    $head_reference = 
    $client->api('gitData')->references()->show(
        WORDLIFY_GITHUB_OWNER, 
        WORDLIFY_GITHUB_REPO, 
        "heads/$mediaBranch"
    );

    $head_commit = 
    $client->api('gitData')->commits()->show(
        WORDLIFY_GITHUB_OWNER, 
        WORDLIFY_GITHUB_REPO, 
        $head_reference['object']['sha']
    );

    $old_tree_minus_deleted_file = github_getFullTreeWithoutFile(
        $client, $full_filepath, $mediaBranch
    );

    $new_tree = 
    $client->api('gitData')->trees()->create(
        WORDLIFY_GITHUB_OWNER, 
        WORDLIFY_GITHUB_REPO,
        [
            'tree' => $old_tree_minus_deleted_file['new_tree']
        ]
    );


    $commit = 
    $client->api('gitData')->commits()->create(
        WORDLIFY_GITHUB_OWNER, 
        WORDLIFY_GITHUB_REPO,
        [
            'message' => createCommitMessage(
                $id, $filename, "Attachment ", "deleted"
            ), 
            'tree' => $new_tree['sha'], 
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
        "heads/$mediaBranch", 
        [
            'sha' => $commit['sha'],
            'force' => true
        ]
    );

    return $id;
}

?>