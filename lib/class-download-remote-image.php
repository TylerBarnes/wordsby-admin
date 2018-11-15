<?php 

/**
 * This class handles downloading a remote image file and inserting it
 * into the WP Media Library.
 *
 * Usage:
 * $download_remote_image = new KM_Download_Remote_Image( $url );
 * $attachment_id         = $download_remote_image->download();
 *
 */
class KM_Download_Remote_Image {

	/**
	 * Remote image URL.
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * The attachment data, in this format:
	 *
	 * array(
	 *    $title       = '',
	 *    $caption     = '',
	 *    $alt_text    = '',
	 *    $description = '',
	 * );
	 *
	 * @var array
	 */
	private $attachment_data = array();

	/**
	 * The attachment ID or false if none.
	 *
	 * @var int|bool
	 */
	private $attachment_id = false;

	/**
	 * Constructor.
	 *
	 * @param string $url             The URL for the remote image.
	 *
	 * @param array $attachment_data {
	 *     Optional. Data to be used for the attachment.
	 *
	 *     @type string $title       The title. Also used to create the filename.
	 *     @type string $caption     The caption.
	 *     @type string $alt_text    The alt text.
	 *     @type string $description The description.
	 * }
	 */
	public function __construct( $url, $attachment_data = array() ) {
		$this->url = $this->format_url( $url );

		if ( is_array( $attachment_data ) && $attachment_data ) {
			$this->attachment_data = array_map( 'sanitize_text_field', $attachment_data );
		}
	}

	/**
	 * Add a scheme, if missing, to a URL.
	 *
	 * Warning: This method defaults to using 'http' when adding a scheme to
	 * protocol-relative URLs and would need to be modified for remote images
	 * only available at 'https' URLs.
	 *
	 * @param  string $url The URL.
	 *
	 * @return string The URL, with a scheme possibly prepended.
	 */
	private function format_url( $url ) {

		if ( $this->has_valid_scheme( $url ) ) {
			return $url;
		}

		if ( $this->does_string_start_with_substring( $url, '//' ) ) {
			return "http:{$url}";
		}

		return "http://{$url}";
	}

	/**
	 * Does this URL have a valid scheme?
	 *
	 * @param  string $url The URL.
	 *
	 * @return bool
	 */
	private function has_valid_scheme( $url ) {
		return $this->does_string_start_with_substring( $url, 'https://' ) || $this->does_string_start_with_substring( $url, 'http://' );
	}

	/**
	 * Does this string start with this substring?
	 *
	 * @param string $string    The string.
	 * @param string $substring The substring.
	 *
	 * @return bool
	 */
	private function does_string_start_with_substring( $string, $substring ) {
		return 0 === strpos( $string, $substring );
	}

	/**
	 * Download a remote image and insert it into the WordPress Media Library as an attachment.
	 *
	 * @return bool|int The attachment ID, or false on failure.
	 */
	public function download() {

		if ( ! $this->is_url_valid() ) {
			return false;
		}

		// Download remote file and sideload it into the uploads directory.
		$file_attributes = $this->sideload();

		if ( ! $file_attributes ) {
			return false;
		}

		// Insert the image as a new attachment.
		$this->insert_attachment( $file_attributes['file'], $file_attributes['type'] );

		if ( ! $this->attachment_id ) {
			return false;
		}

		$this->update_metadata();
		$this->update_post_data();
		$this->update_alt_text();

		return $this->attachment_id;
	}

	/**
	 * Is this URL valid?
	 *
	 * @return bool
	 */
	private function is_url_valid() {

		$parsed_url = wp_parse_url( $this->url );

		return $this->has_valid_scheme( $this->url ) && $parsed_url && isset( $parsed_url['host'] );
	}

	/**
	 * Sideload the remote image into the uploads directory.
	 *
	 * @return array|bool Associative array of file attributes, or false on failure.
	 */
	private function sideload() {

		// Gives us access to the download_url() and wp_handle_sideload() functions.
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Download file to temp dir.
		$temp_file = download_url( $this->url, 10 );

		if ( is_wp_error( $temp_file ) ) {
			return false;
		}

        $mime_type = mime_content_type( $temp_file );
    

		if ( ! $this->is_supported_image_type( $mime_type ) ) {
			return false;
		}

		// An array similar to that of a PHP `$_FILES` POST array
		$file = array(
			'name'     => $this->get_filename( $mime_type ),
			'type'     => $mime_type,
			'tmp_name' => $temp_file,
			'error'    => 0,
			'size'     => filesize( $temp_file ),
		);

		$overrides = array(
			// This tells WordPress to not look for the POST form
			// fields that would normally be present. Default is true.
			// Since the file is being downloaded from a remote server,
			// there will be no form fields.
			'test_form'   => false,

			// Setting this to false lets WordPress allow empty files – not recommended.
			'test_size'   => true,

			// A properly uploaded file will pass this test.
			// There should be no reason to override this one.
			'test_upload' => true,
		);

		// Move the temporary file into the uploads directory.
        $file_attributes = wp_handle_sideload( $file, $overrides );

		if ( $this->did_a_sideloading_error_occur( $file_attributes ) ) {
			return false;
		}

		return $file_attributes;
	}

	/**
	 * Is this image MIME type supported by the WordPress Media Libarary?
	 *
	 * @param  string $mime_type The MIME type.
	 *
	 * @return bool
	 */
	private function is_supported_image_type( $mime_type ) {
		return in_array( $mime_type, array( 'image/jpeg', 'image/gif', 'image/png', 'image/x-icon' ), true );
	}

	/**
	 * Get filename for attachment, including extension.
	 *
	 * @param  string $mime_type The MIME type.
	 *
	 * @return string            The filename.
	 */
	private function get_filename( $mime_type ) {

		if ( empty( $this->attachment_data['title'] ) ) {
			return basename( $this->url );
		}

		$filename  = sanitize_title_with_dashes( $this->attachment_data['title'] );
		$extension = $this->get_extension_from_mime_type( $mime_type );

		return $filename . $extension;
	}

	/**
	 * Get a file extension, including the preceding '.' from a file's MIME type.
	 *
	 * @param  string $mime_type The MIME type.
	 *
	 * @return string            The file extension or empty string if not found.
	 */
	private function get_extension_from_mime_type( $mime_type ) {

		$extensions = array(
			'image/jpeg'   => '.jpg',
			'image/gif'    => '.gif',
			'image/png'    => '.png',
			'image/x-icon' => '.ico',
		);

		return isset( $extensions[ $mime_type ] ) ? $extensions[ $mime_type ] : '';
	}

	/**
	 * Did an error occur while sideloading the file?
	 *
	 * @param  array $file_attributes The file attribues, or array containing an 'error' key on failure.
	 *
	 * @return bool
	 */
	private function did_a_sideloading_error_occur( $file_attributes ) {
		return isset( $file_attributes['error'] );
	}

	/**
	 * Insert attachment into the WordPress Media Library.
	 *
	 * @param  string $file_path The path to the media file.
	 * @param  string $mime_type The MIME type of the media file.
	 */
	private function insert_attachment( $file_path, $mime_type ) {

		// Get the path to the uploads directory.
		$wp_upload_dir = wp_upload_dir();

		// Prepare an array of post data for the attachment.
		$attachment_data = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $file_path ),
			'post_mime_type' => $mime_type,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment_data, $file_path );

		if ( ! $attachment_id ) {
			return;
		}

		$this->attachment_id = $attachment_id;
	}

	/**
	 * Update attachment metadata.
	 */
	private function update_metadata() {

		$file_path = get_attached_file( $this->attachment_id );

		if ( ! $file_path ) {
			return;
		}

		// Gives us access to the wp_generate_attachment_metadata() function.
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Generate metadata for the attachment and update the database record.
		$attach_data = wp_generate_attachment_metadata( $this->attachment_id, $file_path );
		wp_update_attachment_metadata( $this->attachment_id, $attach_data );
	}

	/**
	 * Update attachment title, caption and description.
	 */
	private function update_post_data() {

		if ( empty( $this->attachment_data['title'] )
		     && empty( $this->attachment_data['caption'] )
		     && empty( $this->attachment_data['description'] )
		) {
			return;
		}

		$data = array(
			'ID' => $this->attachment_id,
		);

		// Set image title (post title)
		if ( ! empty( $this->attachment_data['title'] ) ) {
			$data['post_title'] = $this->attachment_data['title'];
		}

		// Set image caption (post excerpt)
		if ( ! empty( $this->attachment_data['caption'] ) ) {
			$data['post_excerpt'] = $this->attachment_data['caption'];
		}

		// Set image description (post content)
		if ( ! empty( $this->attachment_data['description'] ) ) {
			$data['post_content'] = $this->attachment_data['description'];
		}

		wp_update_post( $data );
	}

	/**
	 * Update attachment alt text.
	 */
	private function update_alt_text() {

		if ( empty( $this->attachment_data['alt_text'] ) && empty( $this->attachment_data['title'] ) ) {
			return;
		}

		// Use the alt text string provided, or the title as a fallback.
		$alt_text = ! empty( $this->attachment_data['alt_text'] ) ? $this->attachment_data['alt_text'] : $this->attachment_data['title'];

		update_post_meta( $this->attachment_id, '_wp_attachment_image_alt', $alt_text );
	}
}

?>