<?php
// disable the git pull webhook field
function disable_acf_load_field( $field ) {
	$field['disabled'] = 1;
	return $field;
}

add_filter('acf/load_field/name=git_pull_webhook', 'disable_acf_load_field');

if( function_exists('acf_add_options_page') ) {

	acf_add_options_page([
		'page_title' => 'Gatsby',
		'icon_url' => '/wp-content/themes/gatsby-wordpress-admin-theme/assets/icons/gatsby.svg'		
    ]);
	
}

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_5bbd6668c1ee0',
	'title' => 'Settings',
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
		array(
			'key' => 'field_5bbd7b3a93827',
			'label' => 'How should we populate the template dropdown?',
			'name' => 'how_should_we_populate_the_template_dropdown',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'none' => 'Don\'t populate',
				'gatsby_repo' => 'Gatsby git repo',
				'gatsby' => 'Gatsby Filesystem',
				'repeater' => 'Values from this page',
			),
			'default_value' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5bbd6cbdb8620',
			'label' => 'Templates',
			'name' => 'templates',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_5bbd7b3a93827',
						'operator' => '==',
						'value' => 'repeater',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => '',
			'min' => 0,
			'max' => 0,
			'layout' => 'block',
			'button_label' => '',
			'sub_fields' => array(
				array(
					'key' => 'field_5bbd7461b8621',
					'label' => 'Template Name',
					'name' => 'template_name',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => 'Example Template',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array(
					'key' => 'field_5bbd766b42cbc',
					'label' => 'Template Filename',
					'name' => 'template_filename',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '50',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => 'ExampleTemplate.js',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
			),
		),
		array(
			'key' => 'field_5bbd7beb93828',
			'label' => 'Gatsby templates path',
			'name' => 'gatsby_templates_path',
			'type' => 'text',
			'instructions' => 'Enter the path to your templates folder from the theme root.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_5bbd7b3a93827',
						'operator' => '==',
						'value' => 'gatsby',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'gatsby/sitename/src/templates',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5bbd8a152931a',
			'label' => 'Gatsby git repo',
			'name' => 'gatsby_git_repo',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_5bbd7b3a93827',
						'operator' => '==',
						'value' => 'gatsby_repo',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		),
		array(
			'key' => 'field_5bbd8a922931b',
			'label' => 'Git pull webhook',
			'name' => 'git_pull_webhook',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => get_stylesheet_directory_uri() . "/webhooks/git_pull.php",
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'disabled' => 1,
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