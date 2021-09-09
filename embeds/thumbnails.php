<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-09 12:03:08
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-09 13:46:12
 * @package air-cookie
 */

namespace Air_Cookie\Embeds;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Get Youtube video preview thumbnail for embed placeholder.
 *
 * @param  mixed  $thumbnail_url Boolean false or null if thumbnail not set already, string url if is set
 * @param  string $src           Url of the embed
 * @return mixed                 Url of the thumbnail if available, boolean false if not.
 * @since  0.1.0
 */
function maybe_get_youtube_thumbnail_url( $thumbnail_url, $src ) {
  // Bail if thumbnail is already set
  if ( ! empty( $thumbnail_url ) ) {
    return $thumbnail_url;
  }

  // Bail if url is not Youtube
  $video_id = maybe_get_youtube_video_id( $src );
  if ( ! $video_id ) {
    return false;
  }

  return "https://i3.ytimg.com/vi/{$video_id}/hqdefault.jpg";
} // end maybe_get_youtube_thumbnail_url

/**
 * Get Vimeo video preview thumbnail for embed placeholder.
 *
 * @param  mixed  $thumbnail_url Boolean false or null if thumbnail not set already, string url if is set
 * @param  string $src           Url of the embed
 * @return mixed                 Url of the thumbnail if available, boolean false if not.
 * @since  0.1.0
 */
function maybe_get_vimeo_thumbnail_url( $thumbnail_url, $src ) {
  // Bail if thumbnail is already set
  if ( ! empty( $thumbnail_url ) ) {
    return $thumbnail_url;
  }

  // Bail if url is not Vimeo
  $video_id = maybe_get_vimeo_video_id( $src );
  if ( ! $video_id ) {
    return false;
  }

  $cache_key = "vimeo_thumbnail|{$video_id}";
  $cache_group = 'air_cookie_embed_thumbnails';

  // Try to load from local cache
  $thumbnail = wp_cache_get( $cache_key, $cache_group );
  if ( ! empty( $thumbnail ) ) {
    return $thumbnail;
  }

  // Call Vimeo API to get the thumbnail uri
  $response = wp_safe_remote_get( "https://vimeo.com/api/v2/video/{$video_id}.json" );
  if ( is_wp_error( $response ) ) {
    return false;
  }

  // Bail if API error
  if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
    return false;
  }

  // Bail if no thumbnail in API response
  $body = wp_remote_retrieve_body( $response );
  $data = json_decode( $body );
  if ( ! isset( $data[0] ) || ! isset( $data[0]->thumbnail_large ) ) {
    return false;
  }

  // Save to local cache
  $thumbnail = $data[0]->thumbnail_large;
  wp_cache_set( $cache_key, $thumbnail, $cache_group, MONTH_IN_SECONDS );

  return $thumbnail;
} // end maybe_get_vimeo_thumbnail_url
