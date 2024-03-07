<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-08 14:51:36
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-12-08 15:47:45
 * @package air-cookie
 */

namespace Air_Cookie\Embeds;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Global variable to hold the embed services.
 * Needed for passing the services from the_content
 * filters to our custom javascript builder.
 *
 * @since 0.1.0
 */
$air_cookie_embeds = [];

/**
 * Get current version of included IframeManager script version.
 *
 * @since 0.1.0
 */
function get_script_version() {
  return '1.2.5';
} // end get_script_version

/**
* Helper functions.
*
* @since 0.1.0
*/
require \Air_Cookie\plugin_base_path() . '/embeds/helpers.php';

/**
* The actual script and javascript related things.
*
* @since 0.1.0
*/
require \Air_Cookie\plugin_base_path() . '/embeds/script-injection.php';
add_action( 'wp_footer', __NAMESPACE__ . '\inject_js', 10 );
add_action( 'air_cookie_js_' . get_embeds_cookie_category_key(), __NAMESPACE__ . '\load_embeds_on_cookie_accept' );
add_action( 'air_cookie\embeds\register_embed', __NAMESPACE__ . '\register_embed_for_js', 10, 3 );

/**
 * Register new strings needed for embeds.
 *
 * @since 0.1.0
 */
require \Air_Cookie\plugin_base_path() . '/embeds/strings.php';
add_filter( 'air_cookie\strings', __NAMESPACE__ . '\register_strings' );
add_filter( 'air_cookie\categories', __NAMESPACE__ . '\register_embeds_cookie_category' );

/**
 * Filters to find embeds and block thise.
 *
 * @since 0.1.0
 */
require \Air_Cookie\plugin_base_path() . '/embeds/content-filters.php';
add_filter( 'the_content', __NAMESPACE__ . '\iframe_embeds', 1000 );
add_filter( 'the_content', __NAMESPACE__ . '\script_tag_embeds', 1000 );

/**
 * Register new strings needed for embeds.
 *
 * @since 0.1.0
 */
require \Air_Cookie\plugin_base_path() . '/embeds/thumbnails.php';
add_filter( 'air_cookie\embeds\thumbnail', __NAMESPACE__ . '\maybe_get_youtube_thumbnail_url', 10, 2 );
add_filter( 'air_cookie\embeds\thumbnail', __NAMESPACE__ . '\maybe_get_vimeo_thumbnail_url', 10, 2 );
