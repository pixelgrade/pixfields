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
			'desc' => 'Let fields edition in post edit page'
		),
		'disable_pixfields' => array(
			'label'          => __( 'Disable Pix Fields', 'pixfields_txtd' ),
			'default'        => true,
			'type'           => 'switch',
		),
		'display_on_post_types' => array(
			'label'          => __( 'Post Types', 'pixfields_txtd' ),
			'default'        => array('post', 'page'),
			'type'           => 'post_types_checkbox',
			'description' => 'Which post types should have fields'
		),
	)
); # config