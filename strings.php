<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-20 14:19:21
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-20 14:31:40
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

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
  ];

  $strings = apply_filters( 'air_cookie\strings', $strings );

  foreach ( $strings as $key => $string ) {
    $strings[ $key ] = apply_filters( "air_cookie\strings\{$key}", $string );
  }

  return $strings;
} // end get_strings

add_action( 'init', __NAMESPACE__ . '\register_strings' );
function register_strings() {
  $strings = get_strings();
  if ( ! is_array( $strings ) ) {
    return;
  }

  foreach ( $strings as $key => $string ) {
    $multiline = false !== strpos( $key, 'description' ) ? true : false;
    pll_register_string( $key, $string, 'Air Cookie', $multiline );
  }

  $cookie_categories = get_cookie_categories();
  if ( is_array( $cookie_categories ) ) {
    foreach ( $cookie_categories as $cookie_category ) {
      $cookie_category_key = $cookie_category['key'];
      pll_register_string( "cookie_category_{$cookie_category_key}_title", $cookie_category['title'], 'Air Cookie' );
      pll_register_string( "cookie_category_{$cookie_category_key}_description", $cookie_category['description'], 'Air Cookie', true );
    }
  }
} // end register_strings
