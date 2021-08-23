<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-20 14:17:57
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-20 14:47:56
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

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
