<?php 

function commitActivePlugins( $plugin, $network_activation, $status ) {

    $client = getGitlabClient(); if (!$client) return;
    
    global $branch;

    
    $site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
    $username = $current_user->user_nicename;
    
    $commit_message = "$status plugin $plugin — by $username (from $site_url)";

    $base_path = "wordsby/data/";
    
    $plugins_action = isFileInRepo($client, $base_path, 'active-plugins.json') 
    ? 'update' : 'create';

    $commit = $client->api('repositories')->createCommit(WORDLIFY_GITLAB_PROJECT_ID, array(
        'branch' => $branch, 
        'commit_message' => $commit_message,
        'actions' => array(
            array(
                'action' => $plugins_action,
                'file_path' => $base_path . "active-plugins.json",
                'content' => getActivePluginsJSON($plugin, $status),
                'encoding' => 'text'
            )
        ),
        'author_email' => $username,
        'author_name' => $current_user->user_email
    )); 
}

function deactivatePlugin( $plugin, $network_activation ) {
    commitActivePlugins( $plugin, $network_activation, 'Deactivated' );
}

function activatePlugin( $plugin, $network_activation ) {
    commitActivePlugins( $plugin, $network_activation, 'Activated' );
}

// https://codex.wordpress.org/Plugin_API/Action_Reference/activated_plugin
// https://codex.wordpress.org/Plugin_API/Action_Reference/deactivated_plugin
add_action( 'activated_plugin', 'activatePlugin', 10, 2 );
add_action( 'deactivated_plugin', 'deactivatePlugin', 10, 2 );

?>
