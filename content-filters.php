<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-09 11:37:10
 * @Last Modified by:   Roni Laukkarinen
 * @Last Modified time: 2024-02-20 16:29:44
 * @package air-cookie
 */

namespace Air_Cookie\Embeds;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Replace iframe embeds with placeholder.
 *
 * @param  string $content Post content
 * @return string          Modified post content
 * @since  0.1.0
 */
function iframe_embeds( $content ) {
  $regex = '/<iframe[^>]* src=("|\').*(facebook\.com|youtu\.be|youtube\.com|youtube-nocookie\.com|player\.vimeo\.com|soundcloud\.com|spotify\.com|slideshare\.net|video\.wordpress\.com|embedly\.com).*[^>].*>.*?<\/iframe>/mi'; // phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText

  preg_match_all( $regex, $content, $matches );
  foreach ( $matches[0] as $x => $match ) {
    $start = strpos( $match, ' src=' ) + 6;
    $end = strpos( $match, $matches[1][ $x ], $start );
    $src = substr( $match, $start, $end - $start );

    // Skip if already has been modified
    if ( strpos( $match, 'data-cc' ) !== false ) {
      continue;
    }

    // Treat Vimeo embeds a bit differently
    $vimeo_video_id = maybe_get_vimeo_video_id( $src );
    if ( $vimeo_video_id ) {

      // Add dnt=1 (do not track) to url if allowed via filter
      if ( apply_filters( 'air_cookie\embeds\vimeo\add_dnt', true, $src ) ) {
        $new_src = add_query_arg( 'dnt', true, $src );
        $content = str_replace( $src, $new_src, $content );
        $src = $new_src;
      }

      // Skip placeholder if src has dnt=1 (do not track) and if allowed via filter
      if ( apply_filters( 'air_cookie\embeds\vimeo\skip_dnt', true ) && false !== strpos( $src, 'dnt=1' ) ) {
        continue;
      }
    }

    // Make unqique key and id from the src
    $service_key = crc32( $src );
    $embed_id = crc32( $src );

    // Register the embed for javascript
    do_action( 'air_cookie\embeds\register_embed', $service_key, $embed_id, $src );

    // Get the placeholder for embed, allow changing it via filter
    $placeholder = get_embed_placeholder( $service_key, $embed_id, $src );
    $placeholder = apply_filters( 'air_cookie\embeds\placeholder', $placeholder, $service_key, $embed_id, $src );

    // Replace the iframe with placeholder
    $content = str_replace( $match, $placeholder, $content );
  }

  return $content;
} // end iframe_embeds

/**
 * Modify script embeds, move src to data-src and add cookiecategory for
 * loading the script when category is accepted.
 *
 * @param  string $content Post content
 * @return string          Modified post content
 * @since  0.1.0
 */
function script_tag_embeds( $content ) {
  $cookie_category = get_embeds_cookie_category_key();

  preg_match_all( '/<script.*(instagram|twitter|issuu|imgur|tiktok\.com)+.*<\/script>/mi',
    $content, $matches );

  foreach ( $matches[0] as $x => $match ) {
    $adjusted = str_replace( ' src=', ' data-cc="' . $cookie_category . '" data-src=', $match );
    $content = str_replace( $match, $adjusted, $content );
  }

  return $content;
} // end script_tag_embeds
