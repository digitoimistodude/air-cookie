<?php
/**
 * Plugin Name: Air cookie
 * Plugin URI: https://github.com/digitoimistodude/air-cookie
 * Description: Simple cookie banner and management.
 * Version: 1.2.4
 * Author: Digitoimisto Dude Oy
 * Author URI: https://www.dude.fi
 * Requires at least: 5.5
 * Tested up to: 5.8
 * License: GPL-3.0+
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Get current version of plugin.
 * Version is semver without extra marks, so it can be used as a int.
 *
 * @since 0.1.0
 */
function get_plugin_version() {
  return 124;
} // end plugin_version

/**
 * Get current version of database schema.
 * Version is timestamp in YYYYmmdd format, so it can be used as a int.
 *
 * @since 0.1.0
 */
function get_database_version() {
  return 20210907; // date without dashes
}

/**
 * Get current version of included CookieConsent script version.
 *
 * @since 0.1.0
 */
function get_script_version() {
  return '2.9.1';
} // end get_script_version

/**
* Require helpers for this plugin.
*
* @since 0.1.0
*/
require 'plugin-helpers.php';

/**
 * Github updater.
 *
 * @since 0.1.0
 */
require plugin_base_path() . '/plugin-update-checker/plugin-update-checker.php';
$update_checker = \Puc_v4_Factory::buildUpdateChecker( 'https://github.com/digitoimistodude/air-cookie', __FILE__, 'air-cookie' );

/**
 * Compile settings for the script.
 *
 * @since 0.1.0
 */
require plugin_base_path() . '/settings.php';

/**
 * Default strings and translation support for those.
 *
 * @since 0.1.0
 */
require plugin_base_path(). '/strings.php';
add_action( 'init', __NAMESPACE__ . '\register_strings' );

/**
 * The actual script and javascript related things.
 *
 * @since 0.1.0
 */
require plugin_base_path() . '/script-injection.php';
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_stylesheet' );
add_action( 'wp_footer', __NAMESPACE__ . '\inject_js', 15 );

/**
 * Rest api for recording the visitor consents.
 *
 * @since 0.1.0
 */
require plugin_base_path() . '/rest-api.php';
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_endpoint' );

/**
 * Database creation.
 *
 * @since 0.1.0
 */
if ( is_admin() ) {
  require plugin_base_path() . '/database.php';
  add_action( 'admin_init', __NAMESPACE__ . '\maybe_init_database' );
}

/**
 * Embeds control if enabled.
 *
 * @since 0.1.0
 */
if ( apply_filters( 'air_cookie\embeds', true ) ) {
  require plugin_base_path() . '/embeds/embeds.php';
}

/**
* Plugin activation hook to save current version for reference in what version activation happened.
* Check if deactivation without version option is apparent, then do not save current version for
* maintaining backwards compatibility.
*
* @since 0.1.0
*/
register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activate' );
function plugin_activate() {
  $deactivated_without = get_option( 'air_cookie_deactivated_without_version' );

  if ( 'true' !== $deactivated_without ) {
    update_option( 'air_cookie_activated_at_version', get_plugin_version() );
  }
} // end plugin_activate

/**
* Maybe add option if activated version is not yet saved.
* Helps to maintain backwards compatibility.
*
* @since 0.1.0
*/
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\plugin_deactivate' );
add_action( 'admin_init', __NAMESPACE__ . '\plugin_deactivate' );
function plugin_deactivate() {
  $activated_version = get_option( 'air_cookie_activated_at_version' );

  if ( ! $activated_version ) {
    update_option( 'air_cookie_deactivated_without_version', 'true', false );
  }
} // end plugin_deactivate
