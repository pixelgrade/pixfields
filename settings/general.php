<?php
//not used yet - moved them to a per gallery option
return array(
	'type'    => 'postbox',
	'label'   => 'General Settings',
	'options' => array(
		'allow_edit_on_post_page' => array(
			'label'          => __( 'Allow Edit Fields', 'pixfields_txtd' ),
			'default'        => true,
			'type'           => 'switch',
			'desc' => 'When editing any selected post_type'
		),
		'display_on_post_types' => array(
			'label'          => __( 'Post Types', 'pixfields_txtd' ),
			'default'        => array('post' => 'on', 'page' => 'on'),
			'type'           => 'post_types_checkbox',
			'description' => 'Which post types should have fields'
		),
		'display_place' => array(
			'label'          => __( 'Post Types', 'pixfields_txtd' ),
			'default'        => array('template_function'),
			'type'           => 'select',
			'description' => 'Where pixfields should be displayed?',
			'options' => array(
				'template_function' => __('Template function', 'pixfields_txtd'),
				'after_content' => __('After Content', 'pixfields_txtd'),
				'before_content' => __('Before Content', 'pixfields_txtd'),
			)
		)
	)
); # config