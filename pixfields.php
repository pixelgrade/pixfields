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
Description: WordPress photo gallery proofing plugin.
Version: 0.0.7
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

global $pixfields_plugin;
$pixfields_plugin = PixFieldsPlugin::get_instance();

function display_pixfields() {
	echo get_pixfields_template();
}

function get_pixfields_template() {

	global $pixfields_plugin;
	return $pixfields_plugin::get_template();
}
