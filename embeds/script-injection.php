<?php
/**
 * Script injection
 *
 * Inject scripts.
 *
 * @Author: Roni Laukkarinen
 * @Date: 2024-02-12 08:27:08
 * @Last Modified by:   Roni Laukkarinen
 * @Last Modified time: 2024-02-20 16:22:58
 *
 * @package air-cookie
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */

namespace Air_Cookie\Embeds;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

function inject_js() {
  // IframeManager base.
  wp_enqueue_script( 'iframemanager', \Air_Cookie\plugin_base_url() . '/assets/iframemanager.js', [], get_script_version(), false );
  wp_enqueue_style( 'iframemanager', \Air_Cookie\plugin_base_url() . '/assets/iframemanager.css', [], false, 'all' ); // phpcs:ignore

  global $air_cookie_embeds;

  // Build our javascript to run the IframeManager.
  ob_start();
  ?>
    var manager = iframemanager();

    manager.run({
      currLang: 'en',
      services: <?php echo json_encode( $air_cookie_embeds ); // phpcs:ignore ?>
    });

    <?php // Remove secondary button, as we don't use it ?>
    document.querySelectorAll('button.c-la-b').forEach( element => {
      element.remove();
    } );
  <?php $script = ob_get_clean();

  $script = apply_filters( 'air_cookie_iframemanager_inline_js', $script );

  // Add our javascript to the site
  wp_add_inline_script( 'iframemanager', $script, 'after' );
} // end inject_js

/**
 * Allow all embeds when CookieConsent cookie group is accepted.
 *
 * @since 0.1.0
 */
function load_embeds_on_cookie_accept() {
  ob_start(); ?>
  manager.acceptService( 'all' );
  <?php echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // end load_embeds_on_cookie_accept

/**
 * Register the embed to be used on our javascript.
 *
 * @param  string $service_key Key for the service fromn where embed comes from
 * @param  string $embed_id    Unique ID for the embed
 * @param  string $src         URI of the embed
 * @since  0.1.0
 */
function register_embed_for_js( $service_key, $embed_id, $src ) {
  global $air_cookie_embeds;

  // Get the language for strings
  $lang = \Air_Cookie\get_current_language();

  // Build embed base
  $service = [
    'embedUrl'  => $src,
    'cookie'    => [
      'name'  => 'air_cookie_embeds', // By default, use same cookie for all embeds
    ],
    'languages' => [
      $lang => [
        'notice'  => \Air_Cookie\maybe_get_polylang_translation( 'embeds_description' ),
        'loadBtn' => \Air_Cookie\maybe_get_polylang_translation( 'embeds_load_button' ),
      ],
    ],
  ];

  // Try to get thumbnail, add if one exists
  $thumbnail_url = apply_filters( 'air_cookie\embeds\thumbnail', null, $src );
  if ( ! empty( $thumbnail_url ) ) {
    $service['thumbnailUrl'] = $thumbnail_url;
  }

  // Add embed to our services array for javascript
  $air_cookie_embeds[ $service_key ] = $service;
} // end register_embed_for_js
