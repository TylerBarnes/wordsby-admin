<?php 

add_action('wp_handle_upload', 'commitMedia');

function commitMedia($upload) {
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

    return $upload;
}

?>