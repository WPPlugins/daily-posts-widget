<?php
/**
 * @package SGI\DailyPostsWidget
 */

/*
Plugin Name: Daily Posts Widget
Description: Widget that will display posts per day in a nice tabbely manner
Version: 1.0.1
Author: Sibin Grasic
Author URI: http://sgi.io
*/

/* Prevent Direct access */
if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

/*Define plugin main file*/
if ( !defined('SGI_DPW_FILE') )
	define ( 'SGI_DPW_FILE', __FILE__ );


/* Define BaseName */
if ( !defined('SGI_DPW_BASENAME') )
	define ('SGI_DPW_BASENAME',plugin_basename(SGI_DPW_FILE));

/* Define internal path */
if ( !defined( 'SGI_DPW_PATH' ) )
	define( 'SGI_DPW_PATH', plugin_dir_path( SGI_DPW_FILE ) );


/* Define internal version for possible update changes */
define ('SGI_DPW_VERSION', '1.0');

/* Load Up the text domain */
function sgi_dpw_load_textdomain()
{
	load_plugin_textdomain('sgidpw', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action('wp_loaded','sgi_dpw_load_textdomain');

/* Check if we're running compatible software */
if ( version_compare( PHP_VERSION, '5.2', '<' ) && version_compare(WP_VERSION, '3.8', '<') ) :
	if (is_admin()) :
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( __FILE__ );
		wp_die(__('Daily Posts Widget plugin requires WordPress 3.8 and PHP 5.3 or greater. The plugin has now disabled itself','sgidpw'));
	endif;
endif;

/* Let's load up the plugin */

function sgi_dpw_init_widget()
{
	require_once (SGI_DPW_PATH.'lib/widget-dpw.php');
	register_widget('SGI_DPW_Widget');
}

add_action('widgets_init','sgi_dpw_init_widget',20);