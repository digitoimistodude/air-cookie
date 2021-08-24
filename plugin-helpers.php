<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-24 09:38:05
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 *  Get the version at where plugin was activated.
 *
 *  @since  1.6.0
 *  @return integer  version where plugin was activated
 */
function plugin_activated_at_version() {
  return absint( apply_filters( 'air_cookie_activated_at_version', get_option( 'air_cookie_activated_at_version' ) ) );
} // end plugin_activated_at_version

/**
 *  Wrapper function to get real base path for this plugin.
 *
 *  @since  0.1.0
 *  @return string  Path to this plugin
 */
function plugin_base_path() {
  return untrailingslashit( plugin_dir_path( __FILE__ ) );
} // end plugin_base_path

/**
 *  Wrapper function to get real url path for this plugin.
 *
 *  @since  0.1.0
 *  @return string  Url to this plugin
 */
function plugin_base_url() {
  return untrailingslashit( plugin_dir_url( __FILE__ ) );
} // end plugin_base_url

function get_current_language() {
  if ( function_exists( 'pll_current_language' ) ) {
    return pll_current_language();
  }

  return get_locale();
} // end get_current_language
