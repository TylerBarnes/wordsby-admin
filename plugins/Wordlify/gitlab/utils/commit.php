<?php 

function commit($commit_message, $files) {
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

    $menus = json_encode(
        wordlify_get_menus(), JSON_UNESCAPED_SLASHES
    );

    $url = preg_quote(get_site_url(), "/");

    $menus_content = preg_replace(
        "/$url/", '', makeImagesRelative($menus)
    );

    $base_path = "wordsby/data/";
		$user = getCurrentUser();
		
		$actions = [];

		foreach($files as $file) {
			array_push($actions, [
				'action' => updateOrCreate(
						$client, $file['base_path'], $file['filename']
				),
				'file_path' => $file['base_path'] . $file['filename'],
				'content' => $file['content'],
				'encoding' => $file['encoding']
			]);
		}

    $commit = $client->api('repositories')->createCommit(
        WORDLIFY_GITLAB_PROJECT_ID, 
        [
            'branch' => $used_branch, 
            'commit_message' => $commit_message,
            'actions' => $actions,
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
