<?php 
add_action( 'save_post', 'build_hook' );
function build_hook($post_id)
{
	if(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
		return;
	}
	$buildHook = get_field('build_webhook', 'options');
	error_log($buildHook);
   	if ($buildHook) {
		   $response = Requests::post( $buildHook );
	}
}

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_5bbd6668c1ee0',
	'title' => 'Gatsby Options',
	'fields' => array(
		array(
			'key' => 'field_5bbd666ed62e4',
			'label' => 'Build Webhook',
			'name' => 'build_webhook',
			'type' => 'url',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'acf-options-gatsby',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

endif;
 ?>