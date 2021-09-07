<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-07 16:56:00
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-07 16:57:00
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

function register_rest_endpoint() {
  register_rest_route( 'air-cookie/v1', '/consent', [
    'methods'             => 'post',
    'callback'            => __NAMESPACE__ . '\register_consent',
    'permission_callback' => __NAMESPACE__ . '\register_consent_permission_callback',
  ] );
} // end register_rest_endpoint

function register_consent( $request ) {
  global $wpdb;
  $table_name = get_databse_table_name();

  $settings = get_settings();

  $cookie_value = wp_kses_stripslashes( $_COOKIE['air_cookie'] );

  $visitor_uuid = null;
  if ( isset( $_COOKIE['air_cookie_visitor'] ) ) {
    $visitor_uuid = $_COOKIE['air_cookie_visitor'];
  } else {
    $visitor_uuid = maybe_set_identification_cookie();
  }

  $inserted = $wpdb->insert(
    $table_name,
    [
      'visitor_id'      => $visitor_uuid,
      'cookie_version'  => json_decode( $cookie_value )->revision,
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

  return $wpdb->insert_id; // returns new row id
} // end register_consent

/**
 * Check the nonce is set to prevent spamming the endpoint from elsewhere
 *
 * @return boolean Does the user have permission to send data to callback?
 */
function register_consent_permission_callback( $request ) {
  $nonce = $request->get_header( 'X-WP-Nonce' );
  if ( $nonce && wp_verify_nonce( $nonce, 'wp_rest' ) ) {
    return true;
  }

  return false;
} // end register_consent_permission_callback
