<?php
namespace Shlink;

class Api
{
  
  private $setting;
  private $headers;

  function __construct()
  {
    $settings = Setting::get_settings();
    $this->host    = $settings->api_host;
    $this->key     = $settings->api_key;
    $this->version = $settings->api_version;
    $this->timeout = $settings->api_timeout;
    $this->set_headers();
    $this->starttime = time();
    $this->debug = false;
    $this->info  = false;
    $this->log_file = SHLINK_PATH . '/logs.log';
    $this->pid = random_int(0, 9999999);
    $this->info( __DIR__."\n" );
    $this->info( __CLASS__."\n" );
    $this->info( sprintf( "Start time: %s\n", date( 'c', $this->starttime ) ) );
  }

  public function __destruct()
  {
    $endtime = time();
    $this->info( sprintf( "End time: %s\n", date( 'c', $endtime ) ) );
    $this->info( sprintf( "Run time: %s\n", $endtime - $this->starttime ) );
  }

  public function log( $message )
  {
    if( $this->debug ) echo $message;
    $message = sprintf("[%s] [%s] %s", $this->pid, date('c'), $message );
    file_put_contents($this->log_file, $message, FILE_APPEND);
  }

  public function info( $message )
  {
    if( $this->info ) echo $message;
    $message = sprintf("[%s] [%s] %s", $this->pid, date('c'), $message );
    file_put_contents($this->log_file, $message, FILE_APPEND); 
  }

  /**
   * Build API URL
   */
  public function get_url( $enpoint )
  {
    if( !$this->is_ok() ) return;
    return sprintf('%s/rest/v%s/%s', $this->host, $this->version, $enpoint );
  }

  /**
   * Create short URL
   */
  public function create( $long_url, $custom_slug = '' )
  {
    if( !$this->is_ok() ) return;
    $url = $this->get_url( 'short-urls' );
    $body = array(
      "longUrl"      => $long_url,
      "findIfExists" => true,
      "validateUrl"  => true
    );
    if( !empty( $custom_slug ) ) $body['customSlug'] = $custom_slug;
    $args = array(
      'headers' => $this->headers,
      'timeout' => $this->timeout,
      'body'    => $body
    );
    $this->info( sprintf("%s - %s\n", $url, serialize($args) ) );
    $response = wp_remote_post( $url, $args );
    $code     = wp_remote_retrieve_response_code( $response );
    $body     = json_decode( wp_remote_retrieve_body( $response ) );
    return ( 200 === $code ) ? $body : new \WP_Error( $code, __( $body->title, SHLINK_TEXT_DOMAIN ), $body );
  }

  public function get_short_url( $response )
  {
    return isset( $response->shortUrl ) ? $response->shortUrl : null;
  }

  /**
   * PRIVATE
   */
  private function is_ok()
  {
    return ( !empty( $this->host ) && !empty( $this->key ) && !empty( $this->version ) );
  }

  private function set_headers()
  {
    if( !$this->is_ok() ) return;
    $this->headers = array(
      'X-Api-Key' => $this->key
    );
  }

}
