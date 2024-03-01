<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-20 14:17:57
 * @Last Modified by:   Jesse Raitapuro (Digiaargh)
 * @Last Modified time: 2024-03-01 17:26:00
 *
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
  $categories_version = get_cookie_categories_revision();
  $lang = get_current_language();

  // Default settings.
  $settings = [
    'revision'          => $categories_version, // use version number to invalidate if categories change
    // 'theme_css'         => plugin_base_url() . '/assets/cookieconsent.css',

    'cookie'                => [
      'name' => 'air_cookie',
      'expiresAfterDays' => 182,// in days, 182 days = 6 months
    ],

    'guiOptions'       => [
      'consentModal' => [
        'layout'    => 'cloud inline',
        'position'  => 'bottom center',
        'equalWeightButtons' => true,
        'flipButtons'        => false,
      ],

      'preferencesModal' => [
        'layout'    => 'box',
        'equalWeightButtons' => true,
        'flipButtons'        => false,
      ],
    ],
    //KESKEN JÄIN TÄHÄN
    'language' => [
      'default' => $lang,
       'translations'   => [
        $lang   => [
          
        ]
       ]
        ],

    'categories' => [
      'necessary' => [
        'enabled' => true,
        'readOnly' => true
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
  $settings['language']['translations'][ $lang ] = [
    'consentModal'       => [
      'title'             => maybe_get_polylang_translation( 'consent_modal_title' ),
      'description'       => maybe_get_polylang_translation( 'consent_modal_description' ),
      'acceptAllBtn'      => maybe_get_polylang_translation( 'consent_modal_primary_btn_text' ),
      'acceptNecessaryBtn'  => maybe_get_polylang_translation( 'consent_modal_secondary_btn_text' ),
      'showPreferencesBtn'  => maybe_get_polylang_translation( 'settings_modal_title' ),
      'footer' => 'test',
],
    'preferencesModal'      => [
      'title'             => maybe_get_polylang_translation( 'settings_modal_title' ),
      'savePreferencesBtn' => maybe_get_polylang_translation( 'settings_modal_save_settings_btn' ),
      'acceptAllBtn'    => maybe_get_polylang_translation( 'settings_modal_accept_all_btn' ),
      'sections'            => wp_parse_args( get_cookie_categories_for_settings( $lang ),
        [
          [
            'title'         => maybe_get_polylang_translation( 'settings_modal_big_title' ),
            'description'   => maybe_get_polylang_translation( 'settings_modal_description' ),
          ],
        ]
      ),
    ],
  ];

  $cookie_categories = get_cookie_categories();

  if ( ! is_array( $cookie_categories ) ) {
    return;
  }

  // Loop categories to transfrom the markup for categories
  foreach ( $cookie_categories as $group ) {
    $key = $group['key'];
    $enabled = $group['enabled'];
    $readOnly = $group['readonly'];
     // Add text strings for the modals.
    $settings['categories'][ $key ] = [
      'enabled' => $group['enabled'],
      'readOnly' => $group['readonly']
    ];
  }

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
      'key'         => 'functional',
      'enabled'     => false, // it is advised to have other categories disabled by default
      'readonly'    => false, // user should have always control over other categories
      'title'       => maybe_get_polylang_translation( 'category_functional_title' ),
      'description' => maybe_get_polylang_translation( 'category_functional_description' ),
    ],
    [
      'key'         => 'analytics',
      'enabled'     => false, // it is advised to have other categories disabled by default
      'readonly'    => false, // user should have always control over other categories
      'title'       => maybe_get_polylang_translation( 'category_analytics_title' ),
      'description' => maybe_get_polylang_translation( 'category_analytics_description' ),
    ],
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
function get_cookie_categories_for_settings( $lang ) { // phpcs:ignore
  // Get cookie categories, bail if no.
  $cookie_categories = get_cookie_categories();
  if ( ! is_array( $cookie_categories ) ) {
    return;
  }

  // Loop categories to transfrom the markup.
  foreach ( $cookie_categories as $group ) {
    $key = $group['key'];

    $enabled = $group['enabled'];
    $readOnly = $group['readonly'];

     // Add text strings for the modals.
    $settings['categories'][ $key ] = [
      'enabled' => $group['enabled'],
      'readOnly' => $group['readonly']
    ];

    $return[] = [
      'title'       => $group['title'],
      'description' => $group['description'],
      'linkedCategory'      => $key,
    ];
  }

  return $return;
} // end get_cookie_categories_for_settings

/**
 * Get version of the current cookie category settings in order to use it
 * in consent cookie and records database.
 *
 * @return string String hash "version" of current cookie categories.
 * @since 0.1.0
 */
function get_cookie_categories_revision() {
  $categories = get_cookie_categories();

  foreach ( $categories as $key => $cat ) {
    unset( $categories[ $key ]['title'] );
    unset( $categories[ $key ]['description'] );
  }

  $hash = crc32( maybe_serialize( $categories ) );

  return apply_filters( 'air_cookie\categories\revision', $hash, $categories );
} // end get_cookie_categories_revision
