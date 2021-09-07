<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-07 17:00:04
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-07 17:03:52
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

function inject_js() {
  wp_enqueue_script( 'cookieconsent', plugin_base_url() . "/assets/cookieconsent.js", [], get_script_version(), false );

  $settings = get_settings();
  if ( ! is_array( $settings ) ) {
    return;
  }

  $categories_version = get_cookie_categories_version();
  $cookie_categories = get_cookie_categories();
  ?>

  <script type="text/javascript">
    window.addEventListener( 'load', function () {
      var cc = initCookieConsent();

      airCookieSettings = <?php echo json_encode( apply_filters( 'air_cookie\settings', $settings ) ); ?>

      <?php if ( ! empty( $cookie_categories ) && is_array( $cookie_categories ) ) : ?>
        airCookieSettings.onAccept = function() {
          var xhr = new XMLHttpRequest();
          xhr.open( 'POST', '<?php echo esc_url( rest_url( 'air-cookie/v1/consent' ) ) ?>', true );
          xhr.setRequestHeader( 'X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>');
          xhr.send();

          <?php foreach ( $cookie_categories as $cookie_category ) {
            echo do_category_js( $cookie_category );
          } ?>
        }
      <?php endif; ?>

      cc.run( airCookieSettings );
    });

    function airCookieReadCookie( name ) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
    }
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
