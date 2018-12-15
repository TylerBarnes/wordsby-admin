<?php 

$branch = defined('WORDSBY_GITLAB_BRANCH') ? WORDSBY_GITLAB_BRANCH : 'master';

// This file is generated by Composer
require_once __DIR__ . '/vendor/autoload.php';

function getGitlabToken() {
    // https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html
    $gitlab_token = defined('WORDSBY_GITLAB_API_TOKEN') ? WORDSBY_GITLAB_API_TOKEN : false;
    
    if (!$gitlab_token) return;
    return $gitlab_token;
}

function getGitlabClient () {
    return \Gitlab\Client::create('https://gitlab.com/api/v4/')
        ->authenticate(getGitlabToken(), \Gitlab\Client::AUTH_URL_TOKEN);
}

function getTree($client, $base_path) {
    global $branch;
    $tree = $client->api('repositories')->tree(WORDSBY_GITLAB_PROJECT_ID, array(
        'path' => $base_path,
        'recursive' => true,
        'ref' => $branch
    ));

    return $tree;
}

function isFileInRepo($client, $base_path, $filename) {
    $tree = getTree($client, $base_path);

    return in_array($filename, array_column($tree, 'name'));
}


add_action('save_post', 'commitCollections');

function commitCollections($id) {
    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return $id;

    global $branch;
    
    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;
    $title = get_the_title($id);
    $base_path = "wordsby/data/";
    
    $client = getGitlabClient();

    $collections_exists = isFileInRepo($client, $base_path, 'collections.json');
    $tax_terms_exists = isFileInRepo($client, $base_path, 'tax-terms.json');
    
    $collections_action = $collections_exists ? 'update' : 'create';
    $tax_terms_action = $tax_terms_exists ? 'update' : 'create';

    $collections_content = json_encode(posts_formatted_for_gatsby($id));
    $tax_terms_content = json_encode(custom_api_get_all_taxonomies_terms_callback());


    $commit = $client->api('repositories')->createCommit(WORDSBY_GITLAB_PROJECT_ID, array(
        'branch' => $branch, 
        'commit_message' => "\"$title\" [id:$id] — by $username (from $site_url)",
        'actions' => array(
            array(
                'action' => $collections_action,
                'file_path' => $base_path . "collections.json",
                'content' => $collections_content,
                'encoding' => 'text'
            ),
            array(
                'action' => $tax_terms_action,
                'file_path' => $base_path . "tax-terms.json",
                'content' => $tax_terms_content,
                'encoding' => 'text'
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    ));

    write_log($commit); 

    return $commit; 

}

add_action('delete_attachment', 'deleteMedia');
function deleteMedia($id) {
    if (!defined('WORDSBY_GITLAB_PROJECT_ID')) return $id;

    global $branch;

    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;

    $filepath = wp_get_attachment_metadata($id)['file'];
    $filename = basename($filepath);
    $filedirectory = dirname($filepath); 

    $base_path = 'wordsby/uploads';

    $fulldirectory = "$base_path/$filedirectory/";
    $full_filepath = "$fulldirectory$filename";

    $client = getGitlabClient();

    $media_exists = isFileInRepo($client, $fulldirectory, $filename);

    if (!$media_exists) return;

    $commit = $client->api('repositories')->createCommit(WORDSBY_GITLAB_PROJECT_ID, array(
        'branch' => $branch, 
        'commit_message' => "\"$filename\" deleted — by $username (from $site_url)",
        'actions' => array(
            array(
                'action' => 'delete',
                'file_path' => $full_filepath
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    ));
}

add_action('wp_handle_upload', 'commitMedia');

function commitMedia($upload) {
    if (!defined("WORDSBY_GITLAB_PROJECT_ID")) return $upload;

    global $branch;

    $initial_filepath = explode("uploads/",$upload['file'])[1];
    $filename = basename($initial_filepath);
    $subdir = dirname($initial_filepath);
    
    $base_path = 'wordsby/uploads';
    $file_dir = "$base_path/$subdir";
    $filepath = "$file_dir/$filename";

    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;

    $client = getGitlabClient();

    $media_exists = isFileInRepo($client, $file_dir, $filename);

    $action = $media_exists ? 'update' : 'create';

    $commit = $client->api('repositories')->createCommit(WORDSBY_GITLAB_PROJECT_ID, array(
        'branch' => $branch, 
        'commit_message' => "\"$filename\" — by $username (from $site_url)",
        'actions' => array(
            array(
                'action' => $action,
                'file_path' => $filepath,
                'content' => base64_encode(file_get_contents($upload['file'])),
                'encoding' => 'base64'
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    ));

    return $upload;
}

?>