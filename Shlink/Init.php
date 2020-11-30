<?php
namespace Shlink;

class Init
{
  function __construct()
  {
    $this->setting = new Setting();
    $this->api     = new Api();
    $this->actions();
    $this->filters();
  }

  public function actions()
  {
  }

  public function filters()
  {
    add_filter( 'get_shortlink', array( $this, 'create_short_url' ), 10, 4 );
  }

  public function create_short_url( $shortlink, $id, $context, $allow_slugs )
  {
    $post_id = 0;
    if ( 'query' === $context && is_singular() ) {
      $post_id = get_queried_object_id();
      $post    = get_post( $post_id );
    } elseif ( 'post' === $context ) {
      $post = get_post( $id );
      if ( ! empty( $post->ID ) ) {
        $post_id = $post->ID;
      }
    }

    if ( ! empty( $post_id ) ) {
      $post_type = get_post_type_object( $post->post_type );

      if ( 'page' === $post->post_type && get_option( 'page_on_front' ) == $post->ID && 'page' === get_option( 'show_on_front' ) ) {
        $shortlink = home_url( '/' );
      } elseif ( $post_type->public ) {
        $url = get_permalink( $post_id );
        $response = $this->api->create( $url );
        $code = wp_remote_retrieve_response_code( $response );
        if( 200 === $code ){
          $result = json_decode( wp_remote_retrieve_body( $response ) );
          $shortlink = $result->shortUrl;
          update_post_meta( $post_id, '_shlink_shorturl', $result->shortUrl );
          update_post_meta( $post_id, '_shlink_shortcode', $result->shortCode );
        }
      }
    }
    return $shortlink;
  }

}

?>