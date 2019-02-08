<?php 

function commitActivePlugins($plugin, $network_activation, $status) {
		$site_url = get_site_url();
    $current_user = wp_get_current_user()->data;
		$username = $current_user->user_nicename;
		
    commit(
        "$status plugin $plugin — by $username (from $site_url)",
        [
            [
                'path' => 'wordsby/data/active-plugins.json',
                'content' => getActivePluginsJSON($plugin, $status), 
                'encoding' => 'utf-8'
            ],
        ]
    );
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
