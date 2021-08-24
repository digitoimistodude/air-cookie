<?php
/**
 * Plugin Name: Air Cookie
 * Version: 0.1.0
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-24 11:34:06
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

add_action( 'wp_head', __NAMESPACE__ . '\inject_js' );

function inject_js() {
  wp_enqueue_script( 'cookieconsent', plugin_base_url() . "/assets/cookieconsent.js", [], '2.4.7', false );

  $settings = get_settings();

  if ( ! is_array( $settings ) ) {
    return;
  }

  $cookie_categories = get_cookie_categories();
  ?>

  <script type="text/javascript">
    window.addEventListener( 'load', function () {
      var cc = initCookieConsent();

      airCookieSettings = <?php echo json_encode( apply_filters( 'air_cookie\settings', $settings ) ); ?>

      <?php if ( ! empty( $cookie_categories ) && is_array( $cookie_categories ) ) : ?>
        airCookieSettings.onAccept = function() {
          <?php foreach ( $cookie_categories as $cookie_category ) {
            echo do_category_js( $cookie_category );
          } ?>
        }
      <?php endif; ?>

      cc.run( airCookieSettings );
    });
  </script>
<?php } // end inject_js

function do_category_js( $category ) {
  $category_key = $category['key'];
  $event_key = "air_cookie_{$category_key}";

  ob_start(); ?>

  if ( cc.allowedCategory( '<?php echo $category_key; ?>' ) ) {
    const <?php echo $event_key ?> = new CustomEvent( '<?php echo $event_key ?>' );
    const air_cookie = new CustomEvent( 'air_cookie', {
      'category': '<?php echo $category_key; ?>'
    } );

    document.dispatchEvent( <?php echo $event_key ?> );
    document.dispatchEvent( air_cookie );

    <?php do_action( 'air_cookie_js_' . $category_key, $category ); ?>
  }

  <?php return ob_get_clean();
} // end do_category_js

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

add_action( 'air_cookie_js_necessary', function() {
  ob_start(); ?>
    console.log( 'necessary' );
  <?php echo ob_get_clean();
} );

add_action( 'air_cookie_js_analytics', function() {
  ob_start(); ?>
    console.log( 'analytics' );
  <?php echo ob_get_clean();
} );

add_action( 'wp_head', function() { ?>
  <script type="text/javascript">
    document.addEventListener( 'air_cookie', function( event ) {
      console.log( 'global event  ' + event.category );
    } );

    document.addEventListener( 'air_cookie_necessary', function( event ) {
      console.log( 'category event necessary' );
    } );
  </script>
<?php } );
