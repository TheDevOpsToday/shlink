<?php
namespace Shlink;

class Init
{
  public static function create_short_url( $original, $post_id )
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
        $shortlink = self::generate_short_url( $post_id );
      }
    }
    return ($shortlink) ? $shortlink : $original;
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

  public function generate_short_url( $post_id )
  {
    if ( !in_array( get_post_status( $post_id ), array('publish', 'future', 'private') ) ) {
      return false;
    }
    $permalink = get_permalink( $post_id );
    $shortlink = get_post_meta( $post_id, '_shlink_shorturl', true );
    if( empty( $shortlink ) ){
      $api = new Api();
      $api->info( 'create_short_url - '.$permalink."\n" );
      $result = $api->create( $permalink );
      if( !is_wp_error( $result ) ){
        $shortlink = $result->shortUrl;
        update_post_meta( $post_id, '_shlink_shorturl', $result->shortUrl );
        update_post_meta( $post_id, '_shlink_shortcode', $result->shortCode );
      }
    }
    return ($shortlink) ? $shortlink : false;
  }

}

?>