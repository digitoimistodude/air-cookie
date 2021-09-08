<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-07 16:56:00
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-08 11:02:13
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

function register_rest_endpoint() {
  register_rest_route( 'air-cookie/v1', '/consent', [
    'methods'             => 'post',
    'callback'            => __NAMESPACE__ . '\record_consent',
    'permission_callback' => __NAMESPACE__ . '\record_consent_permission_callback',
  ] );
} // end register_rest_endpoint

/**
 * Record the given consent to database. Possibility to identify when
 * visitor has given their consent is required by Finnish law.
 *
 * @since 0.1.0
 */
function record_consent( $request ) {
  global $wpdb;

  // Get database table name.
  $table_name = get_databse_table_name();

  // Get cookie consent settings.
  $settings = get_settings();
  $visitor_cookie_name = get_indentification_cookie_name();

  // Get cookie value from visitors browser.
  $cookie_value = wp_kses_stripslashes( $_COOKIE[ $settings['cookie_name'] ] );

  // Try to get visitor identifier, set a one if does not exist.
  $visitor_uuid = null;
  if ( isset( $_COOKIE[ $visitor_cookie_name ] ) ) {
    $visitor_uuid = $_COOKIE[ $visitor_cookie_name ];
  } else {
    $visitor_uuid = maybe_set_identification_cookie();
  }

  // Record the consent.
  $inserted = $wpdb->insert(
    $table_name,
    [
      'visitor_id'      => $visitor_uuid,
      'cookie_revision' => json_decode( $cookie_value )->revision,
      'cookie_value'    => $cookie_value,
      'timestamp'       => wp_date( 'Y-m-d H:i:s' ),
      'expiry'          => wp_date( 'Y-m-d H:i:s', strtotime( "+{$settings['cookie_expiration']} days" ) ),
    ],
    [
      '%s',
      '%s',
      '%s',
      '%s',
      '%s',
    ]
  );

  if ( ! $inserted ) {
    return false;
  }

  return true;
} // end record_consent

/**
 * Check the nonce is set to prevent spamming the endpoint from elsewhere.
 *
 * @since 0.1.0
 */
function record_consent_permission_callback( $request ) {
  $nonce = $request->get_header( 'X-WP-Nonce' );
  if ( $nonce && wp_verify_nonce( $nonce, 'wp_rest' ) ) {
    return true;
  }

  return false;
} // end record_consent_permission_callback
