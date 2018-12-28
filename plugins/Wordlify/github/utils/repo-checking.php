<?php 

function github_getTree($client, $desired_branch = "") {
    if ($desired_branch === "") {
        global $branch;
        $desired_branch = $branch;
    }

    // get desired branch head commit sha
    $head_reference = 
        $client->api('gitData')->references()->show(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            "heads/$desired_branch"
        );

    // use that sha to get 
    $tree = $client->api('gitData')->trees()->show(
            WORDLIFY_GITHUB_OWNER, 
            WORDLIFY_GITHUB_REPO, 
            $head_reference['object']['sha'], 
            true
        );


    return $tree;
}


function github_isFileInRepo($client, $full_filepath, $branch = "") {
    $tree = github_getTree($client, $branch);

    if (!!$tree && $tree['truncated']) {
        jp_notices_add_error(
            "The github API response was truncated. 
                Your changes may not be reflected in your site."
        );
    }

    $file_is_in_tree = in_array(
        $full_filepath, array_column($tree['tree'], 'path')
    );

    return $file_is_in_tree;
}

function github_getFullTreeWithoutFile(
    $client, 
    $full_filepath, 
    $branch = "",
    $remove_edited = true
) {
    $tree = github_getTree($client, $branch);

    $match = '/-e([0-9]+)/';
    
    $normalized_filepath = preg_replace( 
            $match, '', $full_filepath 
        );
    
    $new_tree = [];

    foreach($tree['tree'] as $file) {
        $normalized_tree_filepath = preg_replace( 
            $match, '', $file['path'] 
        );

        if (
            $normalized_tree_filepath !== $normalized_filepath &&
            $file['type'] !== 'tree'
            ) {
            array_push($new_tree, $file);
        }
    }

    return [
        'new_tree' => $new_tree,
        'sha' => $tree['sha']
    ];
}

?>