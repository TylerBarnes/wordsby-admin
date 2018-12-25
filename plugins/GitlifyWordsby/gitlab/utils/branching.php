<?php 

function createMediaBranch($client, $desiredBranch = "") {
    global $branch;

    if ($desiredBranch === "") {
        global $mediaBranch;
        $desiredBranch = $mediaBranch;
    }

    return $client->api('repositories')->createBranch(
        WORDLIFY_GITLAB_PROJECT_ID,
        $desiredBranch,
        $branch
    );
}

function desiredBranchExists($client, $desiredBranch = "") {
    if ($desiredBranch === "") {
        global $mediaBranch;
        $desiredBranch = $mediaBranch;
    }

    $branches = $client->api('repositories')->branches(
        WORDLIFY_GITLAB_PROJECT_ID
    );  

    $desiredBranchExists = in_array(
        $desiredBranch, array_column($branches, 'name')
    );

    return $desiredBranchExists;
}

function createMediaBranchIfItDoesntExist($client) {
    if (!defined('WORDLIFY_GITLAB_PROJECT_ID')) return false;

    global $branch;
    global $mediaBranch;
    $desiredBranch = $mediaBranch;

    $response = [
        'branch' => $desiredBranch
    ];

    if (desiredBranchExists($client)) {
        $response['action'] = 'exists';
    } else {
        createMediaBranch($client);
        $response['action'] = 'created';
    }

    return $response;
}

?>