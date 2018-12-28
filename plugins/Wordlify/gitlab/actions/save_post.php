<?php 

add_action('acf/save_post', 'commitData');

function commitData($id) {
    // dont create commits when saving menus
    if (isset($_POST['nav-menu-data'])) return;

    // dont create commits when saving preview revisions.
    if (
        isset($_POST['wp-preview']) && 
        $_POST['wp-preview'] === 'dopreview'
    ) return $id;
    
    $client = getGitlabClient(); if (!$client) return;

    global $branch;

    // if the media branch exists we're going to commit there
    $media_branch_exists = desiredBranchExists($client);
    if ($media_branch_exists) {
        global $mediaBranch;
        $used_branch = $mediaBranch;
    } else {
        // otherwise just commit to the main branch
        $used_branch = $branch;
    }

    $base_path = "wordsby/data/";
    $user = getCurrentUser();
    $commit_message = createCommitMessage($id);

    $commit = $client->api('repositories')->createCommit(
        WORDLIFY_GITLAB_PROJECT_ID, 
        [
            'branch' => $used_branch, 
            'commit_message' => $commit_message,
            'actions' => [
                [
                    'action' => updateOrCreate(
                        $client, $base_path, 'collections.json'
                    ),
                    'file_path' => $base_path . "collections.json",
                    'content' => getCollectionsJSON(),
                    'encoding' => 'text'
                ],
                [
                    'action' => updateOrCreate(
                        $client, $base_path, 'tax-terms.json'
                    ),
                    'file_path' => $base_path . "tax-terms.json",
                    'content' => getTaxTermsJSON(),
                    'encoding' => 'text'
                ],
                [
                    'action' => updateOrCreate(
                        $client, $base_path, 'options.json'
                    ),
                    'file_path' => $base_path . "options.json",
                    'content' => getOptionsJSON(),
                    'encoding' => 'text'
                ],
                [
                    'action' => updateOrCreate(
                        $client, $base_path, 'site-meta.json'
                    ),
                    'file_path' => $base_path . "site-meta.json",
                    'content' => getSiteMetaJSON(),
                    'encoding' => 'text'
                ],
            ],
            'author_email' => $user['name'],
            'author_name' => $user['email']
        ]
    );

    if ($media_branch_exists) {
        // create merge request now that we've commited our data 
        $merge_request = $client->api('merge_requests')->create(
            WORDLIFY_GITLAB_PROJECT_ID,  // project_id
            $mediaBranch,               // source_branch
            $branch,                    // target_branch
            $commit_message            // title
        );

        // immediately approve merge request
        if (isset($merge_request['iid'])) {
            try {
                $approved_merge_request = $client->api('merge_requests')->merge(
                    WORDLIFY_GITLAB_PROJECT_ID,
                    $merge_request['iid'],
                    "$commit_message [MERGE MEDIA]"
                );
    
                // delete media branch
                $deleted_branch = $client->api('repositories')->deleteBranch(
                    WORDLIFY_GITLAB_PROJECT_ID,
                    $mediaBranch   
                );
            } catch (Exception $e) {
                write_log($e);
            }
        }
    }

    return $commit; 

}


?>