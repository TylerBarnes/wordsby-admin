<?php 

function getTree($client, $base_path, $desired_branch = "") {
    if ($desired_branch === "") {
        global $branch;
        $desired_branch = $branch;
    }

    $tree = $client->api('repositories')->tree(
        WORDSBY_GITLAB_PROJECT_ID, 
        array(
            'path' => $base_path,
            'recursive' => true,
            'ref' => $desired_branch,
            'per_page' => 9999999
        )
    );

    return $tree;
}


function isFileInRepo($client, $base_path, $filename, $branch = "") {
    $tree = getTree($client, $base_path, $branch);

    return in_array($filename, array_column($tree, 'name'));
}

function getAllEditedFileVersionsInRepo(
    $client, 
    $base_path, 
    $filename, 
    $branch = ""
) {
    $tree = getTree($client, $base_path, $branch);

    $match = '/-e([0-9]+)/';
    
    $normalized_filename = preg_replace( 
            $match, '', $filename 
        );
    
    $allEditedVersions = [];

    foreach($tree as $file) {
        $normalized_tree_filename = preg_replace( 
            $match, '', $file['name'] 
        );
        if ($normalized_tree_filename === $normalized_filename) {
            array_push($allEditedVersions, $file);
        }
    }

    return $allEditedVersions;
}


function makeImagesRelative($json) {
    $url = preg_quote(get_site_url(), "/");

    return preg_replace(
        "/$url\/wp-content\//", '../', $json
    );
}

function updateOrCreate($client, $base_path, $filename) {
    return isFileInRepo($client, $base_path, $filename) 
    ? 'update' : 'create';
}

?>