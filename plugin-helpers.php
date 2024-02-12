<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-10-07 12:31:56
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
 * Get the name of database table used to track user consents.
 *
 * @param  boolean $prefix Should the table name be prefixed with $wpdb->prefix
 * @return string          Database table name
 * @since 0.1.0
 */
function get_database_table_name( $prefix = true ) {
  $table_name = 'air_cookie';

  if ( $prefix ) {
    global $wpdb;
    $table_name = $wpdb->prefix . $table_name;
  }

  return $table_name;
} // end get_database_table_name

/**
 * Get the setting name where installed database version is stored.
 *
 * @return string Option name
 * @since 0.1.0
 */
function get_database_version_key() {
  return get_database_table_name( false ) . '_db_version';
}

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
