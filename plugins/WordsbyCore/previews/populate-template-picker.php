<?php 
function get_templates_from_gitlab() {
    if (!defined('WORDLIFY_GITLAB_API_TOKEN')) return false;
    if (!defined('WORDLIFY_GITLAB_PROJECT_ID')) return false;

    try {
        $client = getGitlabClient(); if (!$client) return false;
        global $branch;

        $templates = $client->RepositoryFiles()->getRawFile(
            WORDLIFY_GITLAB_PROJECT_ID,
            'wordsby/data/templates.json',
            $branch
        );

        return json_decode($templates); 
    } catch (Exception $e) {
        write_log("Couldn't get templates from gitlab."); 
        write_log($e); 
        return false;
    }
}

function get_templates_from_github() {
    if (!defined('WORDLIFY_GITHUB_API_TOKEN')) return false;
    if (!defined('WORDLIFY_GITHUB_OWNER')) return false;
    if (!defined('WORDLIFY_GITHUB_REPO')) return false;

    $owner = WORDLIFY_GITHUB_OWNER;
    $repo = WORDLIFY_GITHUB_REPO;
    global $branch;

    $client = getGithubClient();
    
    try {
        $response = 
        $client
        ->getHttpClient()
        ->get(
            "https://api.github.com/repos/$owner/$repo/contents/wordsby/data/templates.json?ref=$branch"
        );

        $repo = Github\HttpClient\Message\ResponseMediator::getContent($response);
        
        return json_decode(
                base64_decode(
                    $repo['content']
                )
            );
    } catch(Exception $e) {
        write_log('there was a problem retrieving your templates from github.');
        return false; 
    }
}

function get_templates_from_git() {
    if (!defined('WORDLIFY_GIT_HOST') || !WORDLIFY_GIT_HOST) return false;

    if (WORDLIFY_GIT_HOST === 'github') {
        return get_templates_from_github();
    } elseif (WORDLIFY_GIT_HOST === 'gitlab') {
        return get_templates_from_gitlab();
    } else {
        return false;
    }
}

function populate_templates_from_json($keep_defaults = '') {
    $wproot = get_home_path();
    $templates_json_path = "$wproot/preview/templates.json";
    $templates_json = false;
    $templates = array();
    $git_templates = get_templates_from_git();

    if (!!$git_templates) {
        $templates_json = $git_templates;
    } elseif (file_exists($templates_json_path)) {
        $templates_json_str = file_get_contents($templates_json_path);
        $templates_json = json_decode($templates_json_str);
    } elseif (get_option('templates-collections')) {
        return get_option('templates-collections');
    } else {
        return;
    }

    $default_template_match = '/single\/|taxonomy\/|archive\/|index/';

    // remove defaults unless $keep_defaults is set
    if ($keep_defaults === '') {
        foreach($templates_json as $key => $template) {
            $match = preg_match($default_template_match, $template);
            if ($match) {
                unset($templates_json[$key]);
            }
        }
    }

    foreach($templates_json as $template) {
        $templates[$template] = filenameToTitle($template);
    }

    update_option('templates-collections', $templates);
    update_option('templates-all', $templates_json);

    return $templates;
}


// helper functions
function fromCamelCase($camelCaseString) {
    $re = '/(?<=[a-z])(?=[A-Z])/x';
    $a = preg_split($re, $camelCaseString);
    return join($a, " " );
}

function filenameToTitle($string) {
    if (!is_string($string)) {
        return error_log('Hmm, a string shouldve been passed in here..');
    }

    $without_extension = pathinfo($string, PATHINFO_FILENAME);
    $spaced_decamelcased_title = fromCamelCase($without_extension);
    $dashes_to_spaces = str_replace("-"," ", $spaced_decamelcased_title);
    $capitalized_title = ucwords($dashes_to_spaces);
    
    return $capitalized_title;
}
// end helper functions
?>