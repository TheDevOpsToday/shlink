<?php
namespace Shlink;

class Init
{
  public static function create_short_url( $shortlink, $id, $context, $allow_slugs )
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
        $shortlink = get_post_meta( $post_id, '_shlink_shorturl', true );
        if( empty( $shortlink ) ){
          $api = new Api();
          $url = get_permalink( $post_id );
          $result = $api->create( $url );
          if( !is_wp_error( $result ) ){
            $shortlink = true;
            update_post_meta( $post_id, '_shlink_shorturl', $result->shortUrl );
            update_post_meta( $post_id, '_shlink_shortcode', $result->shortCode );
          }
        }
      }
    }
    return $shortlink;
  }

  public function get_short_url( $shortlink, $id, $context, $allow_slugs )
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
      $shortlink = get_post_meta( $post_id, '_shlink_shorturl', true );
    }
    return $shortlink;
  }

}

?>