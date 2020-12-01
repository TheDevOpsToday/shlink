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
add_filter( 'pre_get_shortlink', 'shlink_create_short_url', 20, 2 );

// Create short url
function shlink_short_url( $long_url, $custom_slug = '' ){
	$api = new \Shlink\Api();
	$response = $api->create( $long_url, $custom_slug );
	return is_wp_error( $response ) ? $response : $api->get_short_url( $response );
}

function shlink_create_short_url( $original, $post_id )
{
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
  if ( wp_is_post_revision( $post_id ) ) return;

  if (0 == $post_id) {
    $post = get_post( $post_id );
    if (is_object( $post ) && !empty( $post->ID ) ) {
      $post_id = $post->ID;
    }
  }
  
  if ( $post_id ) {
    $shortlink = get_post_meta($post_id, '_shlink_shorturl', true);
    if ( empty( $shortlink ) ) {
      $shortlink = shlink_generate_short_url( $post_id );
    }
  }
  return ($shortlink) ? $shortlink : $original;
}

function shlink_generate_short_url( $post_id, $custom_slug = null )
  {
    if ( !in_array( get_post_status( $post_id ), array('publish', 'future', 'private') ) ) {
      return false;
    }
    $permalink = get_permalink( $post_id );
    $shortlink = get_post_meta( $post_id, '_shlink_shorturl', true );
    if( !empty( $shortlink ) ) return $shortlink;
    $settings = Setting::get_settings();
    // check settings
    if( empty( $settings->api_host ) || empty( $settings->api_key ) || empty( $settings->api_version ) ) return false;

    $url = sprintf('%s/rest/v%s/short-urls', $settings->api_host, $settings->api_version );
    $args = array(
      'timeout' => $settings->timeout,
      'headers' => array(
        'X-Api-Key' => $settings->api_key
      ),
      'body'    => array(
        "longUrl"      => $permalink,
        "findIfExists" => true,
        "validateUrl"  => true
      )
    );
    if( !empty( $custom_slug ) ) $args['body']['customSlug'] = $custom_slug;
    $logger = new Shlink\Logger();
    $logger->start();
    $logger->log( sprintf( "calling api > %s :: %s\n", $url, serialize( $args ) ) );
    $response = wp_remote_post( $url, $args );
    $logger->log( sprintf( "Call ended > %s", serialize( $response ) ) );
    $code     = wp_remote_retrieve_response_code( $response );
    $body     = json_decode( wp_remote_retrieve_body( $response ) );
    $logger->log( sprintf( "Call report > %s :: %s", $code, serialize( $body ) ) );
    if( 200 === $code ){
      $shortlink = $body->shortUrl;
      update_post_meta( $post_id, '_shlink_shorturl', $body->shortUrl );
      update_post_meta( $post_id, '_shlink_shortcode', $body->shortCode );
    }
    $logger->end();
    return ($shortlink) ? $shortlink : false;
  }