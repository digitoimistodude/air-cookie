<?php
/**
 * Plugin Name: Air Cookie
 * Version: 0.1.0
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-20 14:30:46
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Get current version of plugin. Version is semver without extra marks, so it can be used as a int.
 *
 * @since 0.1.0
 * @return integer current version of plugin
 */
function get_plugin_version() {
  return 010;
} // end plugin_version

/**
* Require helpers for this plugin.
*
* @since 0.1.0
*/
require 'plugin-helpers.php';

require plugin_base_path() . '/settings.php';
require plugin_base_path(). '/strings.php';

/**
 * # TODO
 * Github updater
 *
 * @since 0.1.0
 */

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts', 1 );
function enqueue_scripts() {
  $env = 'development' === wp_get_environment_type() ? 'dev' : 'prod';
  $settings = get_settings();

  if ( ! is_array( $settings ) ) {
    return;
  }

  wp_enqueue_script( 'air-cookie', plugin_base_url() . "/assets/{$env}/js/air-cookie.js", [], get_plugin_version(), false );
  wp_localize_script( 'air-cookie', 'airCookieSettings', apply_filters( 'air_cookie\settings', $settings ) );
} // end enqueue_scripts

/**
* Plugin activation hook to save current version for reference in what version activation happened.
* Check if deactivation without version option is apparent, then do not save current version for
* maintaining backwards compatibility.
*
* @since 1.6.0
*/
register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activate' );
function plugin_activate() {
  $deactivated_without = get_option( 'air_cookie_deactivated_without_version' );

  if ( 'true' !== $deactivated_without ) {
    update_option( 'air_cookie_activated_at_version', plugin_version() );
  }
} // end plugin_activate

/**
* Maybe add option if activated version is not yet saved.
* Helps to maintain backwards compatibility.
*
* @since 1.6.0
*/
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\plugin_deactivate' );
add_action( 'admin_init', __NAMESPACE__ . '\plugin_deactivate' );
function plugin_deactivate() {
  $activated_version = get_option( 'air_cookie_activated_at_version' );

  if ( ! $activated_version ) {
    update_option( 'air_cookie_deactivated_without_version', 'true', false );
  }
} // end plugin_deactivate
