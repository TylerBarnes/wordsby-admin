<?php 
	
function return_unsplash_image_url() {
	$url = "https://source.unsplash.com/random/300x300/?nature";

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

function upload_local_file_to_media_gallery($file = '') {
	if ($file === '') return;

	$filename = basename($file);
	$upload_file = wp_upload_bits($filename, null, file_get_contents($file));
	if (!$upload_file['error']) {
		$wp_filetype = wp_check_filetype($filename, null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_parent' => $parent_post_id,
			'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
		if (!is_wp_error($attachment_id)) {
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
			return wp_update_attachment_metadata( $attachment_id,  $attachment_data );
		} else {
			return $attachment_id;
		}
	}
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
				<label for="regenerate_avatar">Regenerate avatar from unsplash.com?</label>
			</th>
			<td>
				<input type="checkbox" name="regenerate_avatar" id="user_location" class="regular-text" /> yes
			</td>
		</tr>
		<tr>
			<th>
				<label for="upload_avatar">Upload your own avatar</label>
			</th>
			<td>
				<!-- <div class='image-preview-wrapper'>
					<img id='image-preview' src='' width='100' height='100' style='max-height: 100px; width: 100px;'>
				</div> -->
				<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
				<input type='hidden' name='image_attachment_id' id='image_attachment_id' value=''>
			</td>
		</tr>
	</table>

	<?php wp_enqueue_media(); ?>
	<?php $my_saved_attachment_post_id = get_option( 'media_selector_attachment_id', 0 ); ?>

	<script type='text/javascript'>
		jQuery( document ).ready( function( $ ) {
			// Uploading files
			var file_frame;
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var set_to_post_id = <?php echo $my_saved_attachment_post_id; ?>; // Set this
			jQuery('#upload_image_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					// Set the post ID to what we want
					file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					// Open frame
					file_frame.open();
					return;
				} else {
					// Set the wp.media post id so the uploader grabs the ID we want when initialised
					wp.media.model.settings.post.id = set_to_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
					// We set multiple to false so only get one image from the uploader
					attachment = file_frame.state().get('selection').first().toJSON();
					// Do something with attachment.id and/or attachment.url here
					$( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
					$( '#image_attachment_id' ).val( attachment.id );
					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});
					// Finally, open the modal
					file_frame.open();
			});
			// Restore the main ID when the add media button is pressed
			jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
			});
		});
	</script>

	<!-- <script>
		jQuery(document).ready(function() {
			jQuery("form#your-profile").attr('enctype', 'multipart/form-data');
		});
	</script> -->
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

	if (isset($_POST['regenerate_avatar'])) {
		return add_unsplash_image_to_usermeta($user_id);
	};
	
	if (isset($_POST['image_attachment_id'])) {
		$image_id = $_POST['image_attachment_id'];
		write_log($image_id);
		return update_user_meta($user_id, 'local_unsplash_avatar', wp_get_attachment_url($image_id));
	};

}
?>