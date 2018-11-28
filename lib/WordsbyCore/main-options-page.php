<?php
// disable the git pull webhook field
function disable_acf_load_field( $field ) {
	$field['disabled'] = 1;
	$field['default_value'] = get_stylesheet_directory_uri() . "/webhooks/git_pull.php";
	return $field;
}

add_filter('acf/load_field/name=git_pull_webhook', 'disable_acf_load_field');

if( function_exists('acf_add_options_page') ) {

	$gatsby_icon_url = get_stylesheet_directory_uri() . "/assets/icons/gatsby.svg";

	acf_add_options_page([
		'page_title' => 'Gatsby',
		'icon_url' => $gatsby_icon_url
    ]);
	
}

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_5bbd6668c1ee0',
	'title' => 'Settings',
	'fields' => array(
		array(
			'key' => 'field_5bbda2ed92b36',
			'label' => 'Gatsby Build',
			'name' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'left',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_5bbd666ed62e4',
			'label' => 'Build Webhook',
			'name' => 'build_webhook',
			'type' => 'url',
			'instructions' => 'This webhook is visited whenever a page or post is updated. You can use this to build your site when you publish or edit content.',
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
		array(
			'key' => 'field_5bbda59d91140',
			'label' => 'Frontend Site URL',
			'name' => 'build_site_url',
			'instructions' => 'This url is used to link to the permalink of your front end site from your backend edit page.',
			'type' => 'url',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		)
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