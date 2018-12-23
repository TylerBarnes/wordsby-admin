<?php 

function getGitlabToken() {
    // https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html
    $gitlab_token = defined('WORDSBY_GITLAB_API_TOKEN') ? WORDSBY_GITLAB_API_TOKEN : false;
    
    if (!$gitlab_token) return;
    return $gitlab_token;
}

function getGitlabClient () {
    $gitlab_url = 'https://gitlab.com/api/v4/';
    $test_url = $gitlab_url . 'license';

    $gitlab_headers = @get_headers($test_url);
    if(!$gitlab_headers || strpos($gitlab_headers[0], '404')) {
        $error = "Your post couldn't be saved due to a server network error. Try again later.";
        jp_notices_add_error($error);
        write_log($error); 
        return false;
    }

    try {
        return \Gitlab\Client::create($gitlab_url)
        ->authenticate(getGitlabToken(), \Gitlab\Client::AUTH_URL_TOKEN);
    } catch (Exception $e) {
        write_log($e); 
        jp_notices_add_error($e);
        return false;
    }

}

?>