<?php
namespace Shlink;

class Api
{
  
  private $setting;
  private $headers;

  function __construct()
  {
    $this->host = Setting::get_setting( 'api_host' );
    $this->key = Setting::get_setting( 'api_key' );
    $this->version = Setting::get_setting( 'api_version' );
    $this->set_headers();
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
      "customSlug"   => $custom_slug,
      "findIfExists" => true,
      "validateUrl"  => true
    );
    $args = array(
      'headers' => $this->headers,
      'body'    => $body
    );
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
