<?php
/**
 * Plugin Name: Air Cookie
 * Version: 0.1.0
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-20 14:05:06
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

function get_settings() {
  $lang = pll_current_language();

  $settings = [
    'theme_css'     => plugin_base_url() . "/assets/cookieconsent.css",
    'auto_language' => false,
    'current_lang'  => $lang,
    'autorun'       => true,
    'delay'         => '0',
    'gui_options'   => [
      'consent_modal' => [
        'layout'    => 'box',
        'position'  => 'bottom left',
      ]
    ],
  ];

  $settings = apply_filters( 'air_cookie\settings', $settings );

  foreach ( $settings as $key => $setting ) {
    $settings[ $key ] = apply_filters( "air_cookie\strings\{$key}", $setting );
  }

  $strings = get_strings();
  if ( ! is_array( $strings ) ) {
    return false;
  }

  $settings['languages'][ $lang ] = [
    'consent_modal' => [
      'title'         => pll_translate_string( $strings['consent_modal_title'], $lang ),
      'description'   => pll_translate_string( $strings['consent_modal_description'], $lang ),
      'primary_btn'   => [
        'text'  => pll_translate_string( $strings['consent_modal_primary_btn_text'], $lang ),
        'role'  => 'accept_all',
      ],
      'secondary_btn' => [
        'text'  => pll_translate_string( $strings['consent_modal_secondary_btn_text'], $lang ),
        'role'  => 'accept_necessary',
      ],
    ],
    'settings_modal' => [
      'title'             => pll_translate_string( $strings['settings_modal_title'], $lang ),
      'save_settings_btn' => pll_translate_string( $strings['settings_modal_save_settings_btn'], $lang ),
      'accept_all_btn'    => pll_translate_string( $strings['settings_modal_accept_all_btn'], $lang ),
      'blocks'            => wp_parse_args( get_cookie_categories_for_settings( $lang ), [
        [
          'title'       => pll_translate_string( $strings['settings_modal_big_title'], $lang ),
          'description' => pll_translate_string( $strings['settings_modal_description'], $lang ),
        ]
      ] ),
    ]
  ];

  return apply_filters( 'air_cookie\settings_all', $settings );
} // end get_settings

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

function get_cookie_categories() {
  $categories = [
    [
      'key'         => 'necessary',
      'enabled'     => true,
      'readonly'    => true,
      'title'       => 'Välttämättömät',
      'description' => 'Ryhmän kuvaus tässä.',
    ],
    [
      'key'         => 'analytics',
      'enabled'     => false,
      'readonly'    => false,
      'title'       => 'Analytiikka',
      'description' => 'Analytiikka ryhmän kuvaus tässä.',
    ]
  ];

  $categories = apply_filters( 'air_cookie\categories', $categories );

  foreach ( $categories as $key => $category ) {
    $category_key = $category['key'];
    $categories[ $key ] = apply_filters( "air_cookie\categories\{$category_key}", $category );
  }

  return $categories;
} // end get_cookie_categories

function get_cookie_categories_for_settings( $lang ) {
  $strings = get_strings();
  if ( ! is_array( $strings ) ) {
    return;
  }

  $cookie_groups = get_cookie_categories();
  if ( ! is_array( $cookie_groups ) ) {
    return;
  }

  foreach ( $cookie_groups as $group ) {
    $key = $group['key'];

    $return[] = [
      'title'       => pll_translate_string( $group['title'], $lang ),
      'description' => pll_translate_string( $group['description'], $lang ),
      'toggle'      => [
        'value'       => $key,
        'enabled'     => isset( $group['enabled'] ) ? $group['enabled'] : false,
        'readonly'    => isset( $group['readonly'] ) ? $group['readonly'] : false,
      ],
    ];
  }

  return $return;
} // end get_cookie_categories_for_settings

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
