<?php 

function getGithubToken() {
    // https://github.com/settings/tokens
    $github_token = defined('WORDLIFY_GITHUB_API_TOKEN') ? WORDLIFY_GITHUB_API_TOKEN : false;
    
    if (!$github_token) return;
    return $github_token;
}

function getGithubClient () {
    $github_headers = @get_headers('https://api.github.com/');
    if(!$github_headers || strpos($github_headers[0], '404')) {
        $error = "Your post couldn't be saved due to a server network error. Try again later.";
        jp_notices_add_error($error);
        write_log($error); 
        return false;
    }

    try {
        $client = new \Github\Client();
        $client->authenticate(
            getGithubToken(), null, Github\Client::AUTH_URL_TOKEN
        );
        return $client;
    } catch (Exception $e) {
        write_log($e); 
        jp_notices_add_error($e);
        return false;
    }

}

?>