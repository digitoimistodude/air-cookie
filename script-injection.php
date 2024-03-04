<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-07 17:00:04
 * @Last Modified by:   Jesse Raitapuro (Digiaargh)
 * @Last Modified time: 2024-03-01 17:26:00
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Add our stylesheet.
 * CSS is small, let it load every time.
 *
 * @since 0.1.0
 */
function enqueue_stylesheet() {
  wp_enqueue_style( 'cookieconsent', plugin_base_url() . '/assets/cookieconsent.css', [], get_script_version(), 'all' );
} // end enqueue_stylesheet

/**
 * Build the cookie consent javascript and add it to site header.
 *
 * @since 0.1.0
 */
function inject_js() {
  // Get our settings, bail if no settings.
  // This is our own function, not WordPress deprecated core function.
  $settings = get_settings(); // phpcs:ignore WordPress.WP.DeprecatedFunctions.get_settingsFound
  if ( ! is_array( $settings ) ) {
		return;
  }

  // Cookie Consent javascript base.
  wp_enqueue_script( 'cookieconsent', plugin_base_url() . '/assets/cookieconsent.js', [], get_script_version(),
  array(
    'in_footer' => true,
    'strategy'  => 'defer',
  )
);

  // Get cookie categories
  $cookie_categories = get_cookie_categories();

  // Build our javascript to run the Cookie Consent.
  ob_start();
  ?>
    <?php // Settings ?>
    airCookieSettings = <?php echo json_encode( apply_filters( 'air_cookie\settings', $settings ) ); // phpcs:ignore ?>

    <?php // Allow adding category specific javascript to be runned when the category is accepted.
    if ( ! empty( $cookie_categories ) && is_array( $cookie_categories ) ) : ?>
      function ccOnAccept() {
        airCookierecordConsent();

        <?php foreach ( $cookie_categories as $cookie_category ) {
          echo do_category_js( $cookie_category ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } ?>
      }

      function ccOnChange() {
        airCookierecordConsent();
        <?php foreach ( $cookie_categories as $cookie_category ) {
          echo do_category_js( $cookie_category ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } ?>
      }

      <?php // Function to detect and change regex cookies ?>
      function checkRegexCookies(categories) {
          for (let categoryName in categories) {
              let category = categories[categoryName];
              <?php // Check if the category has cookies specified ?>
              if (category.autoClear) {
                  category.autoClear.cookies.forEach(function(cookie) {
                      <?php // Regex pattern for ^(*) ?>
                      let cookie_regex_check = /\^\(.*\)/;
                      if (cookie_regex_check.test(cookie.name) === true ) {
                          <?php // Replace regular string with regex object (https://cookieconsent.orestbida.com/reference/configuration-reference.html#category-autoclear) ?>
                          cookie.name = new RegExp(cookie.name);
                      }
                  });
              }
          }
      }

    <?php endif; ?>

      <?php // Add functions to handle changes ?>
      const ccOnChanges = {
        onFirstConsent: () => {
          ccOnAccept();
        },
        onChange: () => {
          ccOnChange();

          <?php // Fixes: Embeds were allowed even when you removed embeds from consent. ?>
          if ( ! CookieConsent.getCookie( 'categories' ).includes('embeds') ) {
            if ( 'undefined' !== typeof manager ) {
              manager.rejectService('all');
            }
          }
        }
      }
      airCookieSettings = Object.assign(airCookieSettings, ccOnChanges);
      <?php // end add functions to handle changes ?>

      <?php // Check categories for regex cookies ?>
      checkRegexCookies(airCookieSettings['categories']);
          

      <?php // Run the Cookie Consent at last. ?>
      CookieConsent.run( airCookieSettings );

      const preferences = CookieConsent.getUserPreferences();
    <?php if ( apply_filters( 'air_cookie\styles\set_max_width', true ) ) : ?>
      var cookieconsent_element = document.querySelector('div#cc_div div#cm');
      if( typeof( cookieconsent_element ) != 'undefined' && cookieconsent_element != null ) {
        cookieconsent_element.style = 'max-width: 30em;';
      }
    <?php endif; ?>

    <?php // Function to set the visitor id if not already and send consent record request. ?>
    function airCookierecordConsent() {
      <?php // Set visitor identification if not set already. ?>
      if ( null === CookieConsent.getCookie( 'data' ) || ! ( "visitorid" in CookieConsent.getCookie( 'data' ) ) ) {
        CookieConsent.setCookieData({ value: {
          visitorid: '<?php echo wp_generate_uuid4(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>', mode: 'update'}
        });
      }

      <?php // REST API request to record user consent. ?>
      var xhr = new XMLHttpRequest();
      xhr.open( 'POST', '<?php echo esc_url( rest_url( 'air-cookie/v1/consent' ) ); ?>', true );
      xhr.setRequestHeader( 'X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>');
      xhr.send( JSON.stringify( {
        visitorid: CookieConsent.getCookie( 'data' ).visitorid,
        revision: CookieConsent.getCookie( 'revision' ),
        level: CookieConsent.getCookie( 'categories' ),
      } ) );
    }

    <?php // Add event listener for custom elements that can be used to accept cookie categories. ?>
    var elements = document.querySelectorAll('[data-aircookie-accept]');
    for (var i = 0; i < elements.length; i++) {
      elements[i].addEventListener('click', function(e) {
        e.preventDefault();

        var accepted = e.target.getAttribute('data-aircookie-accept');

        if ( 'all' === accepted ) {
          CookieConsent.acceptCategory('all')
        } else {
          <?php // Get previously accepted categories and fallback to necessary if not accepted previously. ?>
          var accepted_prev = CookieConsent.getCookie('level');
          if ( 'undefined' === typeof accepted_prev ) {
            accepted_prev = [ 'necessary' ];
            CookieConsent.hide();
          }

          accepted_prev.push( accepted );
          CookieConsent.acceptCategory( accepted_prev );
        }

        <?php // Remove all elements that have accept-category action specified. ?>
        var elements = document.querySelectorAll('[data-aircookie-remove-on="accept-' + accepted + '"]');
        for (var i = 0; i < elements.length; i++) {
          elements[i].remove();
        }
      });
    }
  <?php $script = ob_get_clean();

  $script = apply_filters( 'air_cookie_inline_js', $script );

  // Add our javascript to the site
  wp_add_inline_script( 'cookieconsent', $script, 'after' );
} // end inject_js

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
  if ( CookieConsent.getCookie( 'categories' ).includes( '<?php echo $category_key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>' ) ) {
    <?php // Remove all elements that have accept-category action specified. ?>
    var elements = document.querySelectorAll('[data-aircookie-remove-on="accept-<?php echo $category_key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"]');
    for (var i = 0; i < elements.length; i++) {
      elements[i].remove();
    }

    <?php // Do category specific JS action. ?>
    const <?php echo $event_key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> = new CustomEvent( '<?php echo $event_key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>' );
    document.dispatchEvent( <?php echo $event_key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> );

    <?php // Do global JS action with category property. ?>
    const air_cookie = new CustomEvent( 'air_cookie', {
      detail: {
        category: '<?php echo $category_key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
      }
    } );
    document.dispatchEvent( air_cookie );

    <?php // Allow adding custom JS straight to onAccept.
    do_action( 'air_cookie_js_' . $category_key, $category ); ?>
  }

  <?php return ob_get_clean();
} // end do_category_js
