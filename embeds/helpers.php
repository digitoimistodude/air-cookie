<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-09 11:39:37
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-09 13:46:07
 * @package air-cookie
 */

namespace Air_Cookie\Embeds;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Get the key for embeds cookie category.
 *
 * @return string Key for cookie category
 * @since  0.1.0
 */
function get_embeds_cookie_category_key() {
  return apply_filters( 'air_cookie\embeds\cookie_category_key', 'embeds' );
} // end get_embeds_cookie_category_key

/**
 * Get the placeholder to show in place of iframe embed.
 *
 * @param  string $service  Key for the service fromn where embed comes from
 * @param  string $embed_id Unique ID for the embed
 * @param  string $src      URI of the embed
 * @return string           Placeholder markup
 * @since 0.1.0
 */
function get_embed_placeholder( $service, $embed_id, $src ) {
  // Get title for placeholder.
  $title = \Air_Cookie\maybe_get_polylang_translation( 'embeds_title' );

  // Should the placeholder autoscale / be responsive (fill parent width + scale proportionally)
  $autoscale = apply_filters( 'air_cookie\embeds\placeholder\autoscale', true, $service, $embed_id, $src );
  $autoscale = $autoscale ? ' data-autoscale' : '';

  $inner_div = '<div data-service="' . $service . '" data-id="' . $embed_id . '" data-title="' . $title . '"' . $autoscale . '></div>';

  $placeholder = '<div class="air-cookie-embed-wrapper">' . $inner_div . '</div>';

  return $placeholder;
} // end get_embed_placeholder


/**
 * Test if URI is Youtube video and return video ID if is.
 *
 * @param  string $src URI to test against
 * @return mixed       Video ID string or boolean false
 */
function maybe_get_youtube_video_id( $src ) {
  preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $src, $matches );

  if ( ! isset( $matches[1] ) ) {
    return false;
  }

  return $matches[1];
} // end maybe_get_youtube_video_id

/**
 * Test if URI is Vimeo video and return video ID if is.
 *
 * @param  string $src URI to test against
 * @return mixed       Video ID string or boolean false
 */
function maybe_get_vimeo_video_id( $src ) {
  preg_match( '#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $src, $matches );

  if ( ! isset( $matches[1] ) ) {
    return false;
  }

  return $matches[1];
} // end maybe_get_vimeo_video_id
