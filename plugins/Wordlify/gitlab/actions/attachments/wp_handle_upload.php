<?php 

add_action('wp_handle_upload', 'commitMedia');
function commitMedia($upload) {
    if (!defined("WORDLIFY_GITLAB_PROJECT_ID")) return $upload;

    $initial_filepath = explode("uploads/", $upload['file'])[1];
    $filename = basename($initial_filepath);
    $subdir = dirname($initial_filepath);
    
    $base_path = 'wordsby/uploads';
    $file_dir = "$base_path/$subdir";
    $filepath = "$file_dir/$filename";

    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;

    $client = getGitlabClient();

    if (!$client) return;

    createMediaBranchIfItDoesntExist($client);

    global $mediaBranch;

    $media_exists = isFileInRepo($client, $file_dir, $filename, $mediaBranch);
    $action = $media_exists ? 'update' : 'create';

    $commit = $client->api('repositories')->createCommit(
        WORDLIFY_GITLAB_PROJECT_ID, 
        array(
        'branch' => $mediaBranch, 
        'commit_message' => "
                    \"$filename\" 
                    — by $username (from $site_url)
        ",
        'actions' => array(
            array(
                'action' => $action,
                'file_path' => $filepath,
                'content' => base64_encode(file_get_contents($upload['file'])),
                'encoding' => 'base64'
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    ));

    return $upload;
}

?>