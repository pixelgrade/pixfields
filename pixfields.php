<?php
/*
* @package   PixFields
* @author    PixelGrade <contact@pixelgrade.com>
* @license   GPL-2.0+
* @link      http://pixelgrade.com
* @copyright 2014 PixelGrade
*
* @wordpress-plugin
Plugin Name: PixFields
Plugin URI:  http://pixelgrade.com
Description: WordPress easy custom fields management plugin.
Version: 0.6.2
Author: PixelGrade
Author URI: http://pixelgrade.com
Author Email: contact@pixelgrade.com
Text Domain: proof
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Domain Path: /lang
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// ensure EXT is defined
if ( ! defined( 'EXT' ) ) {
	define( 'EXT', '.php' );
}

require 'core/bootstrap' . EXT;

$config = include 'plugin-config' . EXT;

// set textdomain
pixfields::settextdomain( $config['textdomain'] );

// Ensure Test Data
// ----------------

$defaults = include 'plugin-defaults' . EXT;

$current_data = get_option( $config['settings-key'] );

if ( $current_data === false ) {
	add_option( $config['settings-key'], $defaults );
} else if ( count( array_diff_key( $defaults, $current_data ) ) != 0 ) {
	$plugindata = array_merge( $defaults, $current_data );
	update_option( $config['settings-key'], $plugindata );
}
# else: data is available; do nothing

// Load Callbacks
// --------------

$basepath     = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$callbackpath = $basepath . 'callbacks' . DIRECTORY_SEPARATOR;
pixfields::require_all( $callbackpath );

require_once( plugin_dir_path( __FILE__ ) . 'class-pixfields.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'PixFieldsPlugin', 'activate' ) );
//register_deactivation_hook( __FILE__, array( 'PixTypesPlugin', 'deactivate' ) );

if ( ! function_exists( 'init_pixfields_plugin' ) ) {
	function init_pixfields_plugin () {
		global $pixfields_plugin;
		$pixfields_plugin = PixFieldsPlugin::get_instance();
	}
}

add_action('init', 'init_pixfields_plugin');

function display_pixfields() {
	echo get_pixfields_template();
}

function get_pixfields_template() {
	global $pixfields_plugin;
	return $pixfields_plugin->get_template();
}

function get_pixfield( $key , $post_id = null ) {

	if ( $post_id  == null ) {
		global $post;
		$post_id = $post->ID;
	}

	return get_post_meta( $post_id, 'pixfield_' . $key, true );
}

function get_pixfields( $post_id = null ) {
	global $pixfields_plugin;
	if ( $post_id == null ) {
		global $post;
	} else {
		$post = get_post($post_id);
	}

	$post_id = $post->ID;
	$post_type = $post->post_type;

	return $pixfields_plugin->get_post_pixfields($post_type, $post_id);
}

/**
 * Get all the filtrable keys
 * @param $post_type
 *
 * @return array as $key => $label
 */
function pixfields_get_filterable_metakeys( $post_type ) {

	global $pixfields_plugin;
	$fields_list = $pixfields_plugin->get_pixfields_list();
	$return = array();
	if ( isset( $fields_list[$post_type]  ) ) {
		foreach ( $fields_list[$post_type] as $key => $fields ) {
			if ( isset( $fields['filter'] ) ) {
				$return[$fields['meta_key']] = $fields['label'];
			}
		}
	}

	return $return;
}

/**
 * Add the shortcode button only on selected post types
 */
add_filter('filter_shortcodes','filter_pixfields_shortcode_button_by_post_type', 2, 2);
function filter_pixfields_shortcode_button_by_post_type( $shortcodes, $post ) {
	global $pixfields_plugin;
	$plug_settings = $pixfields_plugin->get_plugin_settings();
	$post_types = array_keys( $plug_settings['display_on_post_types'] );

	if ( ! empty($post_types) && ! in_array( $post->post_type, $post_types ) ) {
		unset( $shortcodes['WpGradeShortcode_PixFields'] );
	}

	return $shortcodes;
}
