<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-07 17:03:41
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 *  Get the version at where plugin was activated.
 *
 *  @return integer  version where plugin was activated
 *  @since  1.6.0
 */
function plugin_activated_at_version() {
  return absint( apply_filters( 'air_cookie_activated_at_version', get_option( 'air_cookie_activated_at_version' ) ) );
} // end plugin_activated_at_version

/**
 *  Wrapper function to get real base path for this plugin.
 *
 *  @return string  Path to this plugin
 *  @since  0.1.0
 */
function plugin_base_path() {
  return untrailingslashit( plugin_dir_path( __FILE__ ) );
} // end plugin_base_path

/**
 *  Wrapper function to get real url path for this plugin.
 *
 *  @return string  Url to this plugin
 *  @since  0.1.0
 */
function plugin_base_url() {
  return untrailingslashit( plugin_dir_url( __FILE__ ) );
} // end plugin_base_url

/**
 * Get the current language for the site. If Polylang is not active,
 * return the locale of site.
 *
 * @return string Language.
 * @since 0.1.0
 */
function get_current_language() {
  if ( function_exists( 'pll_current_language' ) ) {
    return pll_current_language();
  }

  return get_locale();
} // end get_current_language

function maybe_set_identification_cookie() {
  if ( isset( $_COOKIE['air_cookie_visitor'] ) ) {
    return false;
  }

  $visitor_uuid = wp_generate_uuid4();
  $expiration = YEAR_IN_SECONDS * 10;

  setcookie( 'air_cookie_visitor', $visitor_uuid, time() + $expiration, '/' );

  return $visitor_uuid;
} // end maybe_set_identification_cookie
