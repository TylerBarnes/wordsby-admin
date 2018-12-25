<?php 

add_action('delete_attachment', 'deleteMedia');
function deleteMedia($id) {
    if (!defined('WORDLIFY_GITLAB_PROJECT_ID')) return $id;

    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;

    $filepath = wp_get_attachment_metadata($id)['file'];
    $filename = basename($filepath);
    $filedirectory = dirname($filepath); 

    $base_path = 'wordsby/uploads';

    $fulldirectory = "$base_path/$filedirectory/";
    $full_filepath = "$fulldirectory$filename";

    $client = getGitlabClient();

    if (!$client) return;

    createMediaBranchIfItDoesntExist($client);
    
    global $mediaBranch;

    $media_exists = isFileInRepo(
        $client, $fulldirectory, $filename, $mediaBranch
    );

    if (!$media_exists) return;

    $actions = array();

    $edited_file_versions = getAllEditedFileVersionsInRepo(
        $client, $fulldirectory, $filename, $mediaBranch
    );

    if (count($edited_file_versions) > 0) {
        foreach($edited_file_versions as $file) {
            array_push($actions, [
                'action' => 'delete',
                'file_path' => $file['path']
            ]);
        }
    } else {
        array_push($actions, array(
            'action' => 'delete',
            'file_path' => $full_filepath
        ));
    }

    $commit = $client->api('repositories')->createCommit(
        WORDLIFY_GITLAB_PROJECT_ID, 
        array(
            'branch' => $mediaBranch, 
            'commit_message' => "
                        \"$filename\" deleted 
                        — by $username (from $site_url)
            ",
            'actions' => $actions,
            'author_email' => $username,
            'author_name' => $current_user->user_email
        )
    );
}

?>