<?php
/**
 * Plugin Name: Yoast to REST API
 * Description: Adds Yoast fields to page and post metadata to WP REST API responses
 * Author: Niels Garve, Pablo Postigo, Tedy Warsitha, Charlie Francis
 * Author URI: https://github.com/niels-garve
 * Version: 1.4.1
 * Plugin URI: https://github.com/niels-garve/yoast-to-rest-api
 */
class Yoast_To_Wordsby {

	protected $keys = array(
		'yoast_wpseo_focuskw',
		'yoast_wpseo_title',
		'yoast_wpseo_metadesc',
		'yoast_wpseo_linkdex',
		'yoast_wpseo_metakeywords',
		'yoast_wpseo_meta-robots-noindex',
		'yoast_wpseo_meta-robots-nofollow',
		'yoast_wpseo_meta-robots-adv',
		'yoast_wpseo_canonical',
		'yoast_wpseo_redirect',
		'yoast_wpseo_opengraph-title',
		'yoast_wpseo_opengraph-description',
		'yoast_wpseo_opengraph-image',
		'yoast_wpseo_twitter-title',
		'yoast_wpseo_twitter-description',
		'yoast_wpseo_twitter-image'
	);

	// function __construct() {
	// 	add_action( 'rest_api_init', array( $this, 'add_yoast_data' ) );
	// }

	function add_yoast_data() {
		// Posts
		register_rest_field( 'post',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'json_encode_yoast' ),
				'update_callback' => array( $this, 'wp_api_update_yoast' ),
				'schema'          => null,
			)
		);

		// Pages
		register_rest_field( 'page',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'json_encode_yoast' ),
				'update_callback' => array( $this, 'wp_api_update_yoast' ),
				'schema'          => null,
			)
		);

		// Category
		register_rest_field( 'category',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'json_encode_yoast_category' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Tag
		register_rest_field( 'tag',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'json_encode_yoast_tag' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Public custom post types
		$types = get_post_types( array(
			'public'   => true,
			'_builtin' => false
		) );

		foreach ( $types as $key => $type ) {
			register_rest_field( $type,
				'yoast_meta',
				array(
					'get_callback'    => array( $this, 'json_encode_yoast' ),
					'update_callback' => array( $this, 'wp_api_update_yoast' ),
					'schema'          => null,
				)
			);
		}
	}

	/**
	 * Updates post meta with values from post/put request.
	 *
	 * @param array $value
	 * @param object $data
	 * @param string $field_name
	 *
	 * @return array
	 */
	function wp_api_update_yoast( $value, $data, $field_name ) {

		foreach ( $value as $k => $v ) {

			if ( in_array( $k, $this->keys ) ) {
				! empty( $k ) ? update_post_meta( $data->ID, '_' . $k, $v ) : null;
			}
		}

		return $this->json_encode_yoast( $data->ID, null, null );
	}

	function json_encode_yoast( $id ) {
		$wpseo_frontend = WPSEO_Frontend_To_Wordsby::get_instance();
		$wpseo_frontend->reset();

		query_posts( array(
			'p'         => $id, // ID of a page, post, or custom type
			'post_type' => 'any'
		) );

		the_post();

		$og_image = get_post_meta(
			$id, '_yoast_wpseo_opengraph-image', true
		);
		$og_image_id = get_post_meta(
			$id, '_yoast_wpseo_opengraph-image-id', true
		);

		$og_default_image_url = WPSEO_Options::get( 'og_default_image', '' );
    $og_default_image_id  = WPSEO_Options::get( 'og_default_image_id', '' );
				
		if ($og_image === "") {
			$og_image = $og_default_image_url ? $og_default_image_url : "";
		}
				
		if ($og_image_id === "") {
			$og_image_id = $og_default_image_id ? $og_default_image_id : "";
		}
				

		$yoast_meta = [
			'seo_title'     => $wpseo_frontend->get_content_title(),
			'seo_metadesc'  => $wpseo_frontend->metadesc( false ),
			'og_image' =>  $og_image,
			'og_image-id' => $og_image_id,
			'og_title' => 
				get_post_meta(
					$id, '_yoast_wpseo_opengraph-title', true
				),
			'og_description' => 
				get_post_meta(
					$id, '_yoast_wpseo_opengraph-description', true
				),
			'content_score' => 
				get_post_meta(
					$id, '_yoast_wpseo_content_score', true
				),
			'canonical_url' => 
				get_post_meta(
					$id, '_yoast_wpseo_canonical', true
				)
		];

		wp_reset_query();

		return (array) $yoast_meta;
	}

	private function wp_api_encode_taxonomy() {
		$wpseo_frontend = WPSEO_Frontend_To_Wordsby::get_instance();
		$wpseo_frontend->reset();

		$yoast_meta = array(
			'yoast_wpseo_title'    => $wpseo_frontend->get_taxonomy_title(),
			'yoast_wpseo_metadesc' => $wpseo_frontend->metadesc( false ),
		);

		return (array) $yoast_meta;
	}

	function json_encode_yoast_category( $category ) {
		query_posts( array(
			'cat' => $category['id'],
		) );

		the_post();

		$res = $this->wp_api_encode_taxonomy();

		wp_reset_query();

		return $res;
	}

	function json_encode_yoast_tag( $tag ) {
		query_posts( array(
			'tag_id' => $tag['id'],
		) );

		the_post();

		$res = $this->wp_api_encode_taxonomy();

		wp_reset_query();

		return $res;
	}
}

if ( class_exists('WPSEO_Frontend') ) {
	include __DIR__ . '/classes/class-wpseo-frontend-to-wordsby.php';
}

function Wordsby_Yoast_init() {
	if ( class_exists( 'WPSEO_Frontend' ) ) {
		return new Yoast_To_Wordsby();
	} else {
		return false;
	}
}
