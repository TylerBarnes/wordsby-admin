<?php 

function createMediaBranch($client, $desiredBranch = "") {
    global $branch;

    if ($desiredBranch === "") {
        global $mediaBranch;
        $desiredBranch = $mediaBranch;
    }

    global $branch;

    $main_branch_reference = 
    $client->api('gitData')->references()->show(
        WORDLIFY_GITHUB_OWNER, 
        WORDLIFY_GITHUB_REPO, 
        "heads/$branch"
    );

    $new_branch_by_reference = $client->api('gitData')->references()->create(
        WORDLIFY_GITHUB_OWNER, 
        WORDLIFY_GITHUB_REPO, 
        [
            'ref' => "refs/heads/$desiredBranch", 
            'sha' => $main_branch_reference['object']['sha']
        ]
    );

}

function desiredBranchExists($client, $desiredBranch = "") {
    if ($desiredBranch === "") {
        global $mediaBranch;
        $desiredBranch = $mediaBranch;
    }

    $owner = WORDLIFY_GITHUB_OWNER;
    $repo = WORDLIFY_GITHUB_REPO;

    $response = $client->getHttpClient()->get("repos/$owner/$repo/branches");
    $branches = Github\HttpClient\Message\
                    ResponseMediator::getContent($response);

    $desiredBranchExists = in_array(
        $desiredBranch, array_column($branches, 'name')
    );

    return $desiredBranchExists;
}

function createMediaBranchIfItDoesntExist($client) {
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