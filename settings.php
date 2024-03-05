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

    'language' => [
      'default' => $lang,
       'translations'   => [
        $lang   => [
          
        ]
       ]
        ],

    'categories' => [
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

    // Loop categories to transfrom markup. For: settings->categories
    $cookie_categories = get_cookie_categories();
    if ( is_array( $cookie_categories ) ) {
  
      foreach ( $cookie_categories as $group ) {
        $key = $group['key'];
        $enabled = $group['enabled'];
        $readOnly = $group['readonly'];
  
      // autoClear key is for detecting cookie table
      if ( array_key_exists( 'autoClear', $group ) ) {
        // Add text strings for the modals.
        $settings['categories'][ $key ] = [
          'enabled' => $group['enabled'],
          'readOnly' => $group['readonly'],
          'autoClear' => $group['autoClear']
        ];
      }
      else {
        $settings['categories'][ $key ] = [
          'enabled' => $group['enabled'],
          'readOnly' => $group['readonly']
        ];
      }
  
      }
    }

  // Add text strings for the modals.
  $settings['language']['translations'][ $lang ] = [
    'consentModal'       => [
      'title'             => maybe_get_polylang_translation( 'consent_modal_title' ),
      'description'       => maybe_get_polylang_translation( 'consent_modal_description' ),
      'acceptAllBtn'      => maybe_get_polylang_translation( 'consent_modal_primary_btn_text' ),
      'acceptNecessaryBtn'  => maybe_get_polylang_translation( 'consent_modal_secondary_btn_text' ),
      'showPreferencesBtn'  => maybe_get_polylang_translation( 'settings_modal_title' ),
],
    'preferencesModal'      => [
      'title'             => maybe_get_polylang_translation( 'settings_modal_title' ),
      'savePreferencesBtn' => maybe_get_polylang_translation( 'settings_modal_save_settings_btn' ),
      'acceptAllBtn'    => maybe_get_polylang_translation( 'settings_modal_accept_all_btn' ),
      'closeIconLabel'      => maybe_get_polylang_translation( 'settings_close_button_label' ), // Aria label for modal
      'sections'            => wp_parse_args( get_cookie_categories_for_sections( $lang ),
        [
          [
            'title'         => maybe_get_polylang_translation( 'settings_modal_big_title' ),
            'description'   => maybe_get_polylang_translation( 'settings_modal_description' ),
          ],
        ]
      ),
    ],
  ];

  // Allow filtering the whole settings array with text strings included.
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
  
  // get_cookie_categories_for_sections( $lang );
  return $categories;
} // end get_cookie_categories

/**
 * Modify the cookie categories in shape that our javascript wants them.
 *
 * @return array  Cookie group constructed in JS format.
 * @since  0.1.0
 */
function get_cookie_categories_for_sections( $lang ) { // phpcs:ignore
  // Get cookie categories, bail if no.
  $cookie_categories = get_cookie_categories();
  if ( ! is_array( $cookie_categories ) ) {
    return;
  }

  // Loop categories to transfrom the markup. For: preferencesModal->sections
  foreach ( $cookie_categories as $group ) {
    $key = $group['key'];

    $enabled = $group['enabled'];
    $readOnly = $group['readonly'];

     // Add text strings for the modals.
    $return[] = [
      'title'       => $group['title'],
      'description' => $group['description'],
      'linkedCategory'      => $key,
    ];
  }

  return $return;
} // end get_cookie_categories_for_sections

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
