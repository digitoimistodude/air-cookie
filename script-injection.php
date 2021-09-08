<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-07 17:00:04
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-08 12:20:47
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Build the cookie consent javascript and add it to site header.
 *
 * @since 0.1.0
 */
function inject_js() {
  // Get our settings, bail if no settings.
  $settings = get_settings();
  if ( ! is_array( $settings ) ) {
    return;
  }

  // Cookie Consent javascript base.
  wp_enqueue_script( 'cookieconsent', plugin_base_url() . '/assets/cookieconsent.js', [], get_script_version(), false );

  // Get cookie categories
  $cookie_categories = get_cookie_categories();

  // Build our javascript to run the Cookie Consent.
  ?>
  <script type="text/javascript">
    window.addEventListener( 'load', function () {
      var cc = initCookieConsent();

      <?php // Settings ?>
      airCookieSettings = <?php echo json_encode( apply_filters( 'air_cookie\settings', $settings ) ); ?>

      <?php // Allow adding categiry specific javascript to be runned when the category is accepted.
      if ( ! empty( $cookie_categories ) && is_array( $cookie_categories ) ) : ?>
        airCookieSettings.onAccept = function() {
          <?php // REST API request to record when user accepts any cookies. ?>
          var xhr = new XMLHttpRequest();
          xhr.open( 'POST', '<?php echo esc_url( rest_url( 'air-cookie/v1/consent' ) ) ?>', true );
          xhr.setRequestHeader( 'X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>');
          xhr.send();

          <?php foreach ( $cookie_categories as $cookie_category ) {
            echo do_category_js( $cookie_category );
          } ?>
        }
      <?php endif; ?>

      <?php // Run the Cookie Consent at last. ?>
      cc.run( airCookieSettings );
    });
  </script>
<?php } // end inject_js

/**
 * Build javascript to be runned for each category when it is accepted.
 *
 * @param  string $category Category key.
 * @return string           Custom javascript for the category onAccept
 */
function do_category_js( $category ) {
  $category_key = $category['key'];
  $event_key = "air_cookie_{$category_key}";

  ob_start(); ?>

  if ( cc.allowedCategory( '<?php echo $category_key; ?>' ) ) {
    <?php // Do category specific JS action. ?>
    const <?php echo $event_key ?> = new CustomEvent( '<?php echo $event_key ?>' );
    document.dispatchEvent( <?php echo $event_key ?> );

    <?php // Do global JS action with category property. ?>
    const air_cookie = new CustomEvent( 'air_cookie', {
      detail: {
        category: '<?php echo $category_key; ?>'
      }
    } );
    document.dispatchEvent( air_cookie );

    <?php // Allow adding custom JS straight to onAccept.
    do_action( 'air_cookie_js_' . $category_key, $category ); ?>
  }

  <?php return ob_get_clean();
} // end do_category_js
