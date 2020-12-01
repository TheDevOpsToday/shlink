<?php
/*
Plugin Name: Shlink
Plugin URI: https://thedevops.today/plugins/shlink
Description: Wordpress plugin for shlink.io. Allow you to use the custom shortlink ganarator right from your website.
Version: 1.0
Author: Salim Ali
Author URI: https://thedevops.today/author/salimali
Text Domain: shlink
*/

define( "SHLINK_PATH", plugin_dir_path( __FILE__ ) );
define( "SHLINK_TEXT_DOMAIN", "shlink" );

// autoload the class from the plugin
spl_autoload_register(function($className){
  $className = str_replace('\\', '/', $className);
  $filePath = SHLINK_PATH.$className.'.php';
  if(file_exists($filePath)) require_once $filePath;
});

add_action( 'admin_menu', array( 'Shlink\Setting', 'admin_menu' ) );
add_action( 'admin_init', array( 'Shlink\Setting', 'admin_init' ) );
// Add setting link in the plugins list page
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array( 'Shlink\Setting', 'action_settings_link' ) );
add_filter( 'pre_get_shortlink', array( 'Shlink\Init', 'create_short_url' ), 20, 2 );

// Create short url
function shlink_short_url( $long_url, $custom_slug = '' ){
	$api = new \Shlink\Api();
	$response = $api->create( $long_url, $custom_slug );
	return is_wp_error( $response ) ? $response : $api->get_short_url( $response );
}
