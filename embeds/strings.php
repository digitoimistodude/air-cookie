<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-09 11:35:57
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-09 13:46:18
 * @package air-cookie
 */

namespace Air_Cookie\Embeds;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Register new strings needed for embeds.
 *
 * @param  array $strings  Strings from air cookie
 * @return array           Strings with our new ones added
 */
function register_strings( $strings ) {
  $new_strings = [
    'embeds_title'                => 'Tämä upote saattaa käyttää evästeitä',
    'embeds_description'          => 'Salli kaikkien upotteiden näyttäminen hyväksymällä kaikki evästeet tai valitse alta näyttääksesi tämä upote kerran.',
    'embeds_load_button'          => 'Näytä vain tämä upote',
    'embeds_category_title'       => 'Upotteet',
    'embeds_category_description' => 'Salli ja näytä upotteet kolmansien osapuolien palveluista kuten Youtube, Vimeo, Instagram, Facebook ja Twitter.',
  ];

  return array_merge( $strings, $new_strings );
} // end function get_strings

/**
 * Register new cookie category for embeds.
 *
 * @param  array $categories Cookie categories from air cookie
 * @return array             New cookie categories with new ones added
 * @since  0.1.0
 */
function register_embeds_cookie_category( $categories ) {
  $categories[] = [
    'key'         => get_embeds_cookie_category_key(),
    'enabled'     => false,
    'readonly'    => false,
    'title'       => \Air_Cookie\maybe_get_polylang_translation( 'embeds_category_title' ),
    'description' => \Air_Cookie\maybe_get_polylang_translation( 'embeds_category_description' ),
  ];

  return $categories;
} // end register_embeds_cookie_category
