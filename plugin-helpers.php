<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-08 13:04:10
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
 *  @since  0.1.0
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
 * Get the name of databse table used to track user consents.
 *
 * @param  boolean $prefix Should the table name be prefixed with $wpdb->prefix
 * @return string          Database table name
 * @since 0.1.0
 */
function get_databse_table_name( $prefix = true ) {
  $table_name = 'air_cookie';

  if ( $prefix ) {
    global $wpdb;
    $table_name = $wpdb->prefix . $table_name;
  }

  return $table_name;
} // end get_databse_table_name

/**
 * Get the setting name where installed databse version is stored.
 *
 * @return string Option name
 * @since 0.1.0
 */
function get_databse_version_key() {
  return get_databse_table_name( false ) . '_db_version';
} // end get_databse_version_key

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

function get_indentification_cookie_name() {
  return apply_filters( 'air_cookie\identification_cookie\name', 'air_cookie_visitor' );
} // end get_indentification_cookie_name

/**
 * Set unique visitor ID if not already set. This is used to identify
 * visitors, their cookie consent choices and timestamp of approval.
 * Possibility to identify when visitor has given their consent is
 * required by Finnish law.
 *
 * @return mixed boolean False if ID exists, string if new ID is set
 * @since 0.1.0
 */
function maybe_set_identification_cookie() {
  $cookie_name = get_indentification_cookie_name();

  if ( isset( $_COOKIE[ $cookie_name ] ) ) {
    return false;
  }

  $visitor_uuid = wp_generate_uuid4();
  $expiration = apply_filters( 'air_cookie\identification_cookie\expiration', YEAR_IN_SECONDS * 5 );

  setcookie( $cookie_name, $visitor_uuid, time() + $expiration, '/' );

  return $visitor_uuid;
} // end maybe_set_identification_cookie
