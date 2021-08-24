<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-20 14:17:57
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-24 12:01:10
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Get the full array of setting for CookieConsent script.
 *
 * @return array/boolean Array of all settings, boolean false if something is not defined
 * @since  0.1.0
 */
function get_settings() {
  $lang = get_current_language();

  // Default settings.
  $settings = [
    'theme_css'     => plugin_base_url() . '/assets/cookieconsent.css',
    'auto_language' => false,
    'current_lang'  => $lang,
    'autorun'       => true,
    'page_scripts'  => true,
    'delay'         => '0',
    'gui_options'   => [
      'consent_modal' => [
        'layout'    => 'box',
        'position'  => 'bottom left',
      ]
    ],
  ];

  // Allow filtering all the settings.
  $settings = apply_filters( 'air_cookie\settings', $settings );

  // Allow filtering individual settings.
  foreach ( $settings as $key => $setting ) {
    $settings[ $key ] = apply_filters( "air_cookie\strings\{$key}", $setting );
  }

  // Get text strings, bail of none.
  $strings = get_strings();
  if ( ! is_array( $strings ) ) {
    return false;
  }

  // Add text strings for the modals.
  $settings['languages'][ $lang ] = [
    'consent_modal' => [
      'title'         => maybe_get_polylang_translation( 'consent_modal_title' ),
      'description'   => maybe_get_polylang_translation( 'consent_modal_description' ),
      'primary_btn'   => [
        'text'  => maybe_get_polylang_translation( 'consent_modal_primary_btn_text' ),
        'role'  => 'accept_all',
      ],
      'secondary_btn' => [
        'text'  => maybe_get_polylang_translation( 'consent_modal_secondary_btn_text' ),
        'role'  => 'accept_necessary',
      ],
    ],
    'settings_modal' => [
      'title'             => maybe_get_polylang_translation( 'settings_modal_title' ),
      'save_settings_btn' => maybe_get_polylang_translation( 'settings_modal_save_settings_btn' ),
      'accept_all_btn'    => maybe_get_polylang_translation( 'settings_modal_accept_all_btn' ),
      'blocks'            => wp_parse_args( get_cookie_categories_for_settings( $lang ), [
        [
          'title'       => maybe_get_polylang_translation( 'settings_modal_big_title' ),
          'description' => maybe_get_polylang_translation( 'settings_modal_description' ),
        ]
      ] ),
    ]
  ];

  // Allow filtering the whole settings aray with text strings included.
  return apply_filters( 'air_cookie\settings_all', $settings );
} // end get_settings

/**
 * Get different categories where cookies can belong to and their respective settings.
 * Has default categories necessary and analytics. It is possible to modify the category
 * settings, but for all oher categories than necessary its recommended to have enabled
 * false by default and not forcing the category. That's the way Finnish cookie directions
 * spesify the functionality of different categories.
 *
 * You may add categories with "air_cookie\categories" filter and modify individual
 * categories with "air_cookie\categories\category_key" filters.
 *
 * @return array Cookie categories registered.
 * @since  0.1.0
 */
function get_cookie_categories() {
  // Default categories
  $categories = [
    [
      'key'         => 'necessary',
      'enabled'     => true, // necessary should always be enabled by default
      'readonly'    => true, // necessary should always be readonly
      'title'       => maybe_get_polylang_translation( 'category_necessary_title' ),
      'description' => maybe_get_polylang_translation( 'category_necessary_description' ),
    ],
    [
      'key'         => 'analytics',
      'enabled'     => false, // it is advised to have other categories disabled by default
      'readonly'    => false, // user should have always control over other categories
      'title'       => maybe_get_polylang_translation( 'category_analytics_title' ),
      'description' => maybe_get_polylang_translation( 'category_analytics_description' ),
    ]
  ];

  // Filter all categories and allow adding new ones.
  $categories = apply_filters( 'air_cookie\categories', $categories );

  // Loop individual categories to allow filtering those.
  foreach ( $categories as $key => $category ) {
    $category_key = $category['key'];
    $categories[ $key ] = apply_filters( "air_cookie\categories\{$category_key}", $category );
  }

  return $categories;
} // end get_cookie_categories

/**
 * Modify the cookie categories in shape that our javascript wants them.
 *
 * @return array  Cookie group constructed in JS format.
 * @since  0.1.0
 */
function get_cookie_categories_for_settings( $lang ) {
  // Get cookie categories, bail if no.
  $cookie_categories = get_cookie_categories();
  if ( ! is_array( $cookie_categories ) ) {
    return;
  }

  // Loop categories to transfrom the markup.
  foreach ( $cookie_categories as $group ) {
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
