<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-20 14:17:57
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-24 09:34:42
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

function get_settings() {
  $lang = get_current_language();

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

  return apply_filters( 'air_cookie\settings_all', $settings );
} // end get_settings

function get_cookie_categories() {
  $categories = [
    [
      'key'         => 'necessary',
      'enabled'     => true,
      'readonly'    => true,
      'title'       => maybe_get_polylang_translation( 'category_necessary_title' ),
      'description' => maybe_get_polylang_translation( 'category_necessary_description' ),
    ],
    [
      'key'         => 'analytics',
      'enabled'     => false,
      'readonly'    => false,
      'title'       => maybe_get_polylang_translation( 'category_analytics_title' ),
      'description' => maybe_get_polylang_translation( 'category_analytics_description' ),
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
