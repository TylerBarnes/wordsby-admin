<?php 

function getActivePluginsJSON($plugin, $status) {
	$active_plugins = get_option( 'active_plugins' );

	if (!$active_plugins) $active_plugins = [null];

	$plugin_index = array_search($plugin, $active_plugins);

	if($plugin_index !== FALSE && $status === "Deactivated"){
			unset($active_plugins[$plugin_index]);
	}

	$formatted_plugin_list = [];
	
	foreach ($active_plugins as $plugin) {
			if (is_null($plugin)) continue;
			
			$plugin_data = get_plugin_data( 
					ABSPATH . "wp-content/plugins/" . $plugin, false, false 
			);

			if (!$plugin_data) continue;

			$plugin_data['id'] = $plugin;

			array_push($formatted_plugin_list, $plugin_data);
	}

	$plugins_json = json_encode(
			$formatted_plugin_list, JSON_UNESCAPED_SLASHES
	);

	return $plugins_json;
}

?>
