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

    $filename = pathinfo( $full_filepath, PATHINFO_FILENAME );

    $repo_full_filepath = "wordsby/" . substr(
        $full_filepath, 
        strpos($full_filepath, "/uploads/") + 1
    );  

    $client = getGithubClient();
    if (!$client) return null; 

    createMediaBranchIfItDoesntExist($client);
    
    // delete the original media file from the repo if 
    // IMAGE_EDIT_OVERWRITE is true.
    // if (
    //     defined( 'IMAGE_EDIT_OVERWRITE' ) && 
    //     IMAGE_EDIT_OVERWRITE &&
    //     $original_media_exists
    //     ) {
       
    // }
    // to do the above, we'll need to create a deleteCommittedFile function
    // or equivalent .
    // the commit function only adds or modifies files. It doesn't remove them.
    // alternatively we could update the commit file to delete as well..
    // check delete_attachment.php in the github folder
    // also check image_make_intermediate_sizes.php in the gitlab folder
    // as the function there respects IMAGE_EDIT_OVERWRITE

    commit(
        createCommitMessage(
            null, $filename, "Edited "
        ),
        [
            [
                'path' => $repo_full_filepath,
                'content' => base64_encode(
                    file_get_contents($full_filepath)
                ), 
                'encoding' => 'base64'
            ]
        ]
    );

    return $full_filepath;
}

?>
