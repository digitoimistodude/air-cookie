<?php
/**
 * Plugin Name: Air Cookie
 * Version: 0.1.0
 *
 * @Author: Timi Wahalahti
 * @Date:   2021-08-10 10:49:07
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-17 11:48:21
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

  wp_enqueue_script( 'air-cookie', plugin_base_url() . "/assets/{$env}/js/air-cookie.js", [], get_plugin_version(), false );

  $settings = get_settings();
  $settings['languages'] = get_strings();

  wp_localize_script( 'air-cookie', 'airCookieSettings', apply_filters( 'air_cookie\settings', $settings ) );
} // end enqueue_scripts

function get_settings() {
  $settings = [
    'theme_css'     => plugin_base_url() . "/assets/cookieconsent.css",
    'current_lang'  => pll_current_language(),
    'autorun'       => true,
    'delay'         => '0',
    'gui_options'   => [
      'consent_modal' => [
        'layout'    => 'box',
        'position'  => 'bottom left',
      ]
    ],
  ];

  foreach ( $settings as $key => $value ) {
    $settings[ $key ] = apply_filters( "air_cookie\settings\{$key}", $value );
  }

  return $settings;
} // end get_settings

function get_strings() {
  $all_strings = [
    'fi'  => [
      'consent_modal' => [
        'title'       => 'Käytämme verkkosivuillamme evästeitä',
        'description' => 'Käytämme yhteistyökumppaneidemme kanssa evästeitä mm. sivuston toiminnallisuuteen, mainonnan ja sosiaalisen median liitännäisten toteuttamiseen sekä sivuston käytön analysointiin. Kävijätietoja voidaan jakaa sosiaalisen median palveluja, verkkomainontaa tai analytiikkapalveluja tarjoavien kumppaneiden kanssa. <button type="button" data-cc="c-settings" class="cc-link">Let me choose</button>',
        'primary_btn' => [
          'text'  => 'Hyväksy kaikki evästeet',
          'role'  => 'accept_all',
        ],
        'secondary_btn' => [
          'text'  => 'Hyväksy vain välttämättömät',
          'role'  => 'accept_necessary',
        ],
      ],
      'settings_modal' => [
        'title'             => 'Evästeasetukset',
        'save_settings_btn' => "Tallenna asetukset",
        'accept_all_btn'    => "Hyväksy kaikki",
        'blocks'            => array_values( get_cookie_groups( 'fi' ) ),
      ],
    ],
  ];

  foreach ( $all_strings as $lang => $strings_group ) {
    // Allow filtering by whole language
    $strings_group = apply_filters( "air_cookie\translations\{$lang}", $strings_group );

    foreach ( $strings_group as $strings_group_key => $strings ) {
      // Allow filtering by language specific group
      $strings = apply_filters( "air_cookie\translations\{$lang}\{$strings_group_key}", $strings );

      foreach ( $strings as $key => $value ) {
        // Allow filtering by individual strings
        $all_strings[ $lang ][ $strings_group_key ][ $key ] = apply_filters( "air_cookie\translations\{$lang}\{$strings_group_key}\{$key}", $value );
      }
    }
  }

  return $all_strings;
} // end get_strings

function get_cookie_groups( $lang ) {
  $groups = [
    'fi'  => [
      'description' => [
        'title'       => 'Evästeiden käyttö',
        'description' => 'Käytämme yhteistyökumppaneidemme kanssa evästeitä mm. sivuston toiminnallisuuteen, mainonnan ja sosiaalisen median liitännäisten toteuttamiseen sekä sivuston käytön analysointiin. Kävijätietoja voidaan jakaa sosiaalisen median palveluja, verkkomainontaa tai analytiikkapalveluja tarjoavien kumppaneiden kanssa.'
      ],
      'necessary' => [
        'title'       => 'Välttämättömät',
        'description' => 'Käytämme yhteistyökumppaneidemme kanssa evästeitä mm. sivuston toiminnallisuuteen, mainonnan ja sosiaalisen median liitännäisten toteuttamiseen sekä sivuston käytön analysointiin. Kävijätietoja voidaan jakaa sosiaalisen median palveluja, verkkomainontaa tai analytiikkapalveluja tarjoavien kumppaneiden kanssa.',
        'toggle'  => [
          'value'     => 'necessary',
          'enabled'   => true,
          'readonly'  => true,
        ],
      ],
      'analytics' => [
        'title'       => 'Analytiikka',
        'description' => 'Käytämme yhteistyökumppaneidemme kanssa evästeitä mm. sivuston toiminnallisuuteen, mainonnan ja sosiaalisen median liitännäisten toteuttamiseen sekä sivuston käytön analysointiin. Kävijätietoja voidaan jakaa sosiaalisen median palveluja, verkkomainontaa tai analytiikkapalveluja tarjoavien kumppaneiden kanssa.',
        'toggle' => [
          'value'     => 'analytics',
          'enabled'   => false,
          'readonly'  => false,
        ],
      ],
    ],
  ];

  $groups = apply_filters( "air_cookie\cookie_groups", $groups );

  foreach ( $groups as $group_lang => $group ) {
    $groups[ $group_lang ] = apply_filters( "air_cookie\cookie_groups\{$group_lang}", $group );
  }

  if ( ! isset( $groups[ $lang ] ) ) {
    return [];
  }

  return $groups[ $lang ];
} // end get_cookie_groups

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
