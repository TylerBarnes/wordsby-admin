<?php 

// On media edited. Can't use the proper hook because the image file isn't committed until after the hook returns.. that means we have to check if it's an image edit when intermediate sizes get created instead.
add_filter('image_make_intermediate_size', 'commitEditedMedia');
function commitEditedMedia($full_filepath) {
    // bail out if this is an upload
    if (
        isset($_POST) && 
        isset($_POST['action']) && 
        $_POST['action'] !== 'image-editor'
        ) return $full_filepath;

    // bail out if this is an intermediate image size. 
    // gatsby creates our image sizes so we only commit full size images to the repo.
    if (
        !preg_match(
            '/^(?!.*-\d{2,4}x\d{2,4}).*\.(jpg|png|bmp|gif|ico)$/', $full_filepath
            )
        ) return $full_filepath;

    // bail if the file doesn't exist. It should, but just in case.
    if (!file_exists($full_filepath)) {
        jp_notices_add_error("There was an error saving your image. Please try again.");
        return $full_filepath;
    };

	$dirname = pathinfo( $full_filepath, PATHINFO_DIRNAME );
	$ext = pathinfo( $full_filepath, PATHINFO_EXTENSION );
    $filename = pathinfo( $full_filepath, PATHINFO_FILENAME );

    $repo_full_filepath = "wordsby/" . substr(
        $full_filepath, 
        strpos($full_filepath, "/uploads/") + 1
    );  

    $repo_filename = pathinfo( $repo_full_filepath, PATHINFO_BASENAME );
	$repo_dirname  = pathinfo( $repo_full_filepath, PATHINFO_DIRNAME );


    $client = getGithubClient();
    if (!$client) return null; 

    createMediaBranchIfItDoesntExist($client);
    
    global $mediaBranch;


    // $site_url = get_site_url();
    // $current_user = wp_get_current_user()->data;
    // $username = $current_user->user_nicename;

    // $commit_message = "
    //         \"$filename\" edited (\"$repo_filename\") 
    //         — by $username (from $site_url)
    //     ";

    // $actions = array(
    //     array(
    //         'action' => $action,
    //         'file_path' => $repo_full_filepath,
    //         'content' => base64_encode(file_get_contents($full_filepath)),
    //         'encoding' => 'base64'
    //     )
    // );

    // $original_filename = preg_replace( 
    //     '/-e([0-9]+)$/', '', $filename 
    //     ) . ".$ext";

    // $repo_original_filepath = "$repo_dirname/$original_filename";
    
    // $original_media_exists = isFileInRepo(
    //     $client, $repo_dirname, $original_filename, $mediaBranch
    // );
    
    // // delete the original media file from the repo if 
    // // IMAGE_EDIT_OVERWRITE is true.
    // if (
    //     defined( 'IMAGE_EDIT_OVERWRITE' ) && 
    //     IMAGE_EDIT_OVERWRITE &&
    //     $original_media_exists
    //     ) {
    //     array_push($actions, array(
    //         'action' => 'delete',
    //         'file_path' => $repo_original_filepath,
    //     ));
    // }

    // $commit = $client->api('repositories')->createCommit(
    //     WORDLIFY_GITLAB_PROJECT_ID, 
    //     array(
    //         'branch' => $mediaBranch, 
    //         'commit_message' => $commit_message,
    //         'actions' => $actions,
    //         'author_email' => $username,
    //         'author_name' => $current_user->user_email
    //     )
    // );

    return $full_filepath;
}

?>
