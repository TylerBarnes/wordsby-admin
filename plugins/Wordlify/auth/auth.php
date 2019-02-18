<?php 

add_action( 'admin_post_update_github_settings', 'github_handle_save' );


function github_handle_save() {

   // Get the options that were sent
   $org = (!empty($_POST["gh_org"])) ? $_POST["gh_org"] : NULL;
   $repo = (!empty($_POST["gh_repo"])) ? $_POST["gh_repo"] : NULL;

   // Validation would go here

   // Update the values
   update_option( "gh_repo", $repo, TRUE );
   update_option("gh_org", $org, TRUE);

   // Redirect back to settings page
   // The ?page=github corresponds to the "slug" 
   // set in the fourth parameter of add_submenu_page() above.
   $redirect_url = get_bloginfo("url") . "/wp-admin/options-general.php?page=wordsby_options&status=success";
   header("Location: ".$redirect_url);
   exit;
}


// Register the menu.
add_action( "admin_menu", "gh_plugin_menu_func" );
function gh_plugin_menu_func() {
   add_submenu_page( "options-general.php",  // Which menu parent
                  "Wordsby Options",            // Page title
                  "Wordsby",            // Menu title
                  "manage_options",       // Minimum capability (manage_options is an easy way to target administrators)
                  "wordsby_options",            // Menu slug
                  "wordsby_plugin_options"     // Callback that prints the markup
               );
}

// Print the markup for the page
function wordsby_plugin_options() {
if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	wpghpl_handle_authentication_redirection();

	$token = get_option('wpghpl_token');

	echo '<div class="wrap">';

	echo '<h2>GitHub Settings</h2>';

	if ( isset($_GET['status']) && $_GET['status']=='success') { 
	?>
<div
	id="message"
	class="updated notice is-dismissible"
>
	<p>Settings updated!</p><button
		type="button"
		class="notice-dismiss"
	><span class="screen-reader-text">Dismiss this notice.</span></button>
</div>
<?php
	}

	?>
<form
	method="post"
	action="/wp-admin/admin-post.php"
>

	<input
		type="hidden"
		name="action"
		value="update_wpghpl_settings"
	/>

	<h3>GitHub Repository Info</h3>
	<p>
		<label>GitHub Organization:</label>
		<input
			class=""
			type="text"
			name="wpghpl_gh_org"
			value="<?php echo get_option('wpghpl_gh_org'); ?>"
		/>
	</p>

	<p>
		<label>GitHub repository (slug):</label>
		<input
			class=""
			type="text"
			name="wpghpl_gh_repo"
			value="<?php echo get_option('wpghpl_gh_repo'); ?>"
		/>
	</p>

	<?php $client_id = get_option('wpghpl_client_id'); ?>

	<?php if (get_option('wpghpl_auth_single_user') || TRUE) : ?>
	<!-- fields for credentials -->
	<h3>GitHub Application Credentials</h3>

	<p>NOTE: If you're repository is public you can skip this step</p>

	<p><a href="https://github.com/settings/applications/new">Register a new
			gitHub application...</a></p>

	<p><strong>IMPORTANT:</strong> Enter the homepage of your site in the field
		labeled: "Authorization callback URL".</p>

	Enter the credentials provided by GitHub for your registered application.
	<p>
		<label>GitHub Application Client ID:</label>
		<input
			class=""
			type="text"
			name="wpghpl_client_id"
			value="<?php echo $client_id; ?>"
		/>
	</p>
	<p>
		<label>GitHub Application Client Secret:</label>
		<input
			class=""
			type="password"
			name="wpghpl_client_secret"
			value="<?php echo get_option('wpghpl_client_secret'); ?>"
		/>
	</p>
	<?php endif; ?>

	<input
		class="button button-primary"
		type="submit"
		value="Save"
	/>
</form>

<?php if ( get_option('wpghpl_client_id') && get_option('wpghpl_client_secret') ) : ?>

<?php
		$redirect_uri = admin_url('options-general.php?page=wpghpl');
		$state = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
		update_option('wpghpl_auth_state', $state);
		$auth_url = "https://github.com/login/oauth/authorize?state={$state}&client_id={$client_id}&scope=repo&redirect_uri={$redirect_uri}";
		?>

<p>
	<?php if (!$token) : ?>
	<a
		class="button button-primary"
		href="<?php echo $auth_url; ?>"
	>Authorize Pipeline to talk to GitHub</a>
	<?php else : ?>
	<span>Pipeline is authorized! You're ready to go.</span>
	<?php endif; ?>
</p>
<?php
	endif;

	echo '</div>';

	?>
<!-- https://developer.github.com/apps/building-github-apps/creating-github-apps-from-a-manifest/#implementing-the-github-app-manifest-flow -->
<form
	action="https://github.com/settings/apps/new"
	method="post"
>
	Create a GitHub App Manifest: <input
		type="textarea"
		name="manifest"
		id="manifest"
	><br>
	<input
		type="submit"
		value="Submit"
	>
</form>

<script>
input = document.getElementById("manifest")
input.value = JSON.stringify({
	"name": "Octoapp",
	"url": "http://wordsby.code",
	"redirect_url": "http://wordsby.code/wp-admin/admin.php?page=wordsby_options&return_from_github=",
	"public": true,
	"hook_attributes": {
		"url": "http://wordsby.code/github/events",
	},
	"default_permissions": {
		"pull_requests": "write",
		"contents": "read",
		"contents": "write",
	}
})
const fromGH = getURLParameter('return_from_github');
if (fromGH) {
	const return_token = fromGH.replace('?code=', '');
	getGithubApp(return_token);
}
async function getGithubApp(token) {
	const response = await fetch('https://api.github.com/app-manifests/' +
		token +
		'/conversions', {
			method: 'post',
			headers: new Headers({
				'Accept': 'application/vnd.github.fury-preview+json'
			})
		})
	const responseJSON = await response.json();
	console.log(responseJSON);
}

function getURLParameter(name) {
	return decodeURIComponent((new RegExp('[?|&]' + name + '=' +
		'([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(
		/\+/g, '%20')) || null;
}
</script>

<?php

}

/** 
 * Check for whether code and/or state params are being passed back from GitHub after 
 * user authorizes the regsitered app. If so, exchange for token and save.  
 */
function wpghpl_handle_authentication_redirection() {

	#check if we're receiving the GitHub temporary code
	$code = ( !empty($_GET['code']) ) ? $_GET['code'] : FALSE; 
	$state = ( !empty($_GET['state']) ) ? $_GET['state'] : FALSE; 

	if (!$code)
		return;

	$saved_state = get_option('wpghpl_auth_state');

	if ($state != $saved_state)
		return; //TODO: This should throw an error!

	update_option('wpghpl_auth_code', $code);

	//TODO: The php-githup-api library can probably do this easier
	//TODO: This should handle non-success scenarios, like user NOT granting access
	$guzzle = new \Guzzle\Http\Client('https://github.com');
	$guzzle->setDefaultOption('headers', array('Accept' => 'application/json'));
	$body = array
	(
		'client_id' => get_option('wpghpl_client_id'),
		'client_secret' =>get_option('wpghpl_client_secret'),
		'code' => $code,
		'redirect_uri' => admin_url('options-general.php?page=wpghpl'),
		'state' => $state
	);
	$request = $guzzle->post('https://github.com/login/oauth/access_token', null, $body );
	$response = $request->send();

	$data = $response->json();

	if (!empty($data['access_token']))
		update_option('wpghpl_token', $data['access_token']);

}

?>
