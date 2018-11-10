<?php 
	
function return_unsplash_image_url() {
	$url = "https://source.unsplash.com/random/300x300";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$a = curl_exec($ch); // $a will contain all headers

	$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL

	// Uncomment to see all headers
	/*
	echo "<pre>";
	print_r($a);echo"<br>";
	echo "</pre>";
	*/

	return $url . '.jpg'; // Voila	
}

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

function download_unsplash_image_to_media_return_image() {

	$url = return_unsplash_image_url();

	$post_id = 1;
	$desc = null;

	// $image_id = media_sideload_image($url, $post_id, $desc, 'id');
	$attachment_data = array(
		// 'title'       = 'Title for the featured image of ' . $post_title,
		// 'caption'     = 'Caption for the featured image of ' . $post_title,
		// 'alt_text'    = 'Alt text for the featured image of ' . $post_title,
		// 'description' = 'Description for the featured image of ' . $post_title,
	);

	$image = new KM_Download_Remote_Image( $url, $attachment_data );
	$image_id = $image->download();
	
	return $image_id;
}

add_action( 'user_register', 'add_unsplash_image_to_usermeta', 10, 1 );

function add_unsplash_image_to_usermeta( $user_id ) {


	$image_id = download_unsplash_image_to_media_return_image();
	if (is_wp_error($image_id)) return write_log($image_id);
    // if ( isset( $_POST['first_name'] ) )
        update_user_meta($user_id, 'local_unsplash_avatar', wp_get_attachment_url($image_id));

}

add_action('init', 'test');

function test() {
	// write_log(get_user_meta(6, 'local_unsplash_avatar'));
}

function add_avatars_to_all_users() {
	$users = get_users();


	foreach ( $users as $user ):
		$unsplash = get_user_meta($user->ID, 'local_unsplash_avatar');

		if (empty($unsplash)) {
			add_unsplash_image_to_usermeta($user->ID);
		} else {
			// write_log($unsplash);
		}
	endforeach;
}

add_action('init', 'add_avatars_to_all_users');


add_filter( 'get_avatar' , 'my_custom_avatar' , 1 , 5 );

function my_custom_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	$user = false;
	
	// write_log($id_or_email)

    if ( is_numeric( $id_or_email ) ) {

        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );   
    }

    if ( $user && is_object( $user ) ) {

		// write_log($user);

        if ( $user->data->ID ) {
			$avatar = get_user_meta($user->data->ID, 'local_unsplash_avatar')[0];
			// write_log($avatar);
            $avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        }

    }

    return $avatar;
}


/**
 * Show custom user profile fields
 * 
 * @param  object $profileuser A WP_User object
 * @return void
 */
function custom_user_profile_fields( $profileuser ) {
?>
	<table class="form-table">
		<tr>
			<th>
				<label for="regenerate_avatar">Regenerate profile picture?</label>
			</th>
			<td>
				<input type="checkbox" name="regenerate_avatar" id="user_location" class="regular-text" />
			</td>
		</tr>
	</table>
<?php
}
add_action( 'show_user_profile', 'custom_user_profile_fields', 10, 1 );
add_action( 'edit_user_profile', 'custom_user_profile_fields', 10, 1 );



add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

function save_extra_user_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
	}
	
	if (!isset($_POST['regenerate_avatar'])) return;

    add_unsplash_image_to_usermeta($user_id);
}
?>