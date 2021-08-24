<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-20 14:19:21
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-24 12:48:55
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Get the default strings for the cookie consent modal and settings.
 *
 * Modify the strings from Polylang if its installed, otherwise you
 * can use filter "air_cookie\strings" to modify all strings or
 * "air_cookie\strings\string_key" to modify singular string.
 *
 * @return array Array of default strings
 * @since 0.1.0
 */
function get_strings() {
  $strings = [
    'consent_modal_title'                 => 'Käytämme verkkosivuillamme evästeitä',
    'consent_modal_description'           => 'Käytämme yhteistyökumppaneidemme kanssa evästeitä mm. sivuston toiminnallisuuteen, mainonnan ja sosiaalisen median liitännäisten toteuttamiseen sekä sivuston käytön analysointiin. Kävijätietoja voidaan jakaa sosiaalisen median palveluja, verkkomainontaa tai analytiikkapalveluja tarjoavien kumppaneiden kanssa. <button type="button" data-cc="c-settings" class="cc-link">Let me choose</button>',
    'consent_modal_primary_btn_text'      => 'Hyväksy kaikki evästeet',
    'consent_modal_secondary_btn_text'    => 'Hyväksy vain välttämättömät',
    'settings_modal_title'                => 'Evästeasetukset',
    'settings_modal_big_title'            => 'Evästeiden käyttö',
    'settings_modal_description'          => 'Hello testing testing kuuluuko?',
    'settings_modal_save_settings_btn'    => 'Tallenna asetukset',
    'settings_modal_accept_all_btn'       => 'Hyväksy kaikki',
    'category_necessary_title'            => 'Välttämättömät',
    'category_necessary_description'      => 'Ryhmän kuvaus tässä.',
    'category_analytics_title'            => 'Analytiikka',
    'category_analytics_description'      => 'Analytiikka ryhmän kuvaus tässä.',
  ];

  // Modify all the strings with one filter.
  $strings = apply_filters( 'air_cookie\strings', $strings );

  // Loop strings to get singular filters for each string.
  foreach ( $strings as $key => $string ) {
    $strings[ $key ] = apply_filters( "air_cookie\strings\{$key}", $string );
  }

  return $strings;
} // end get_strings

/**
 * Register default strings for Polylang to allow translations.
 *
 * @since 0.1.0
 */
add_action( 'init', __NAMESPACE__ . '\register_strings' );
function register_strings() {
  $pll_group = get_polylang_group();

  // Get strings, bail if none (for example failed filter).
  $strings = get_strings();
  if ( ! is_array( $strings ) ) {
    return;
  }

  // Loop strings and register those
  foreach ( $strings as $key => $string ) {
    $multiline = false !== strpos( $key, 'description' ) ? true : false; // Try to determine if edit field should be textarea
    pll_register_string( $key, $string, $pll_group, $multiline );
  }

  // Get cookie categories and register their strings for translation
  $cookie_categories = get_cookie_categories();
  if ( is_array( $cookie_categories ) ) {
    foreach ( $cookie_categories as $cookie_category ) {
      $cookie_category_key = $cookie_category['key'];
      pll_register_string( "cookie_category_{$cookie_category_key}_title", $cookie_category['title'], $pll_group );
      pll_register_string( "cookie_category_{$cookie_category_key}_description", $cookie_category['description'], $pll_group, true );
    }
  }
} // end register_strings

/**
 * Get translation for the default string. If Polylang is not active,
 * return the default string.
 *
 * @param  string $string_key Which string to get
 * @return string/boolean     Translated string if the key exists, otherwise false
 * @since  0.1.0
 */
function maybe_get_polylang_translation( $string_key ) {
  $strings = get_strings();

  // Bail if no string with requested key.
  if ( ! array_key_exists( $string_key, $strings ) ) {
    return false;
  }

  // Return default string if Polylang is not active
  if ( ! function_exists( 'pll_translate_string' ) ) {
    return $strings[ $string_key ];
  }

  return pll_translate_string( $strings[ $string_key ], get_current_language() );
} // end maybe_get_polylang_translation

/**
 * Get group to which register the strings in Polylang.
 *
 * @return string Name of the group
 * @since 0.1.0
 */
function get_polylang_group() {
  return apply_filters( 'air_cookie\pll\group', 'Air Cookie' );
} // end get_polylang_group
