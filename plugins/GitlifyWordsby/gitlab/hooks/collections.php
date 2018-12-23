<?php 

add_action('acf/save_post', 'commitData');

function commitData($id) {
    if (isset($_POST['nav-menu-data'])) return;

    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return $id;

    if (
        isset($_POST['wp-preview']) && 
        $_POST['wp-preview'] === 'dopreview'
    ) return $id;

    global $branch;
    
    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;
    $title = get_the_title($id);
    $base_path = "wordsby/data/";
    
    $client = getGitlabClient(); if (!$client) return;

    
    $media_branch_exists = desiredBranchExists($client);

    if ($media_branch_exists) {
        global $mediaBranch;
        $used_branch = $mediaBranch;
    } else {
        $used_branch = $branch;
    }

    $collections_content = makeImagesRelative(
        json_encode(
            posts_formatted_for_gatsby(false), 
            JSON_UNESCAPED_SLASHES
        )
    );
    
    $tax_terms_content = json_encode(
        custom_api_get_all_taxonomies_terms_callback(), 
        JSON_UNESCAPED_SLASHES
    );

    $options_content = makeImagesRelative(
        json_encode(
            custom_api_get_all_options_callback(),
            JSON_UNESCAPED_SLASHES
        )
    );

    $site_meta_content = getOptionsJSON();

    $commit_message = "Post \"$title\" updated [id:$id] 
                       — by $username (from $site_url)";

    $commit = $client->api('repositories')->createCommit(
        WORDSBY_GITLAB_PROJECT_ID, 
        array(
        'branch' => $used_branch, 
        'commit_message' => $commit_message,
        'actions' => array(
            array(
                'action' => updateOrCreate(
                    $client, $base_path, 'collections.json'
                ),
                'file_path' => $base_path . "collections.json",
                'content' => $collections_content,
                'encoding' => 'text'
            ),
            array(
                'action' => updateOrCreate(
                    $client, $base_path, 'tax-terms.json'
                ),
                'file_path' => $base_path . "tax-terms.json",
                'content' => $tax_terms_content,
                'encoding' => 'text'
            ),
            array(
                'action' => updateOrCreate(
                    $client, $base_path, 'options.json'
                ),
                'file_path' => $base_path . "options.json",
                'content' => $options_content,
                'encoding' => 'text'
            ),
            array(
                'action' => updateOrCreate(
                    $client, $base_path, 'site-meta.json'
                ),
                'file_path' => $base_path . "site-meta.json",
                'content' => $site_meta_content,
                'encoding' => 'text'
            ),
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
        )
    );

    if ($media_branch_exists) {
        // create merge request now that we've commited our data 
        $merge_request = $client->api('merge_requests')->create(
            WORDSBY_GITLAB_PROJECT_ID,  // project_id
            $mediaBranch,               // source_branch
            $branch,                    // target_branch
            $commit_message            // title
        );

        // immediately approve merge request
        if (isset($merge_request['iid'])) {
            try {
                $approved_merge_request = $client->api('merge_requests')->merge(
                    WORDSBY_GITLAB_PROJECT_ID,
                    $merge_request['iid'],
                    "$commit_message [MERGE MEDIA]"
                );
    
                // delete media branch
                $deleted_branch = $client->api('repositories')->deleteBranch(
                    WORDSBY_GITLAB_PROJECT_ID,
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