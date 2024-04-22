<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-09-07 16:56:00
 * @Modified by:   Roni Laukkarinen
 * @Modified time: 2024-03-14 13:38:00
 * @Last Modified by:   Jesse Raitapuro (Digiaargh)
 * @Last Modified time: 2024-03-07 12:00:00
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

function register_rest_endpoint() {
  register_rest_route( 'air-cookie/v1', '/consent', [
    'methods'             => 'POST',
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

  // Get request data
  $data = json_decode( $request->get_body() );

  // Get database table name
  $table_name = get_database_table_name();

  // Get cookie consent settings
  // This get_settings() NOT the deprecated WordPress function, but air-cookie's own function
  $settings = get_settings(); // phpcs:ignore WordPress.WP.DeprecatedFunctions.get_settingsFound

  // Serialize the cookie levels for storage
  $cookie_value = maybe_serialize( $data->level );

  // Check if cookie_revision and cookie_value has content
  if ( empty( $table_name ) || empty( $data->visitorid ) || empty( $data->revision ) ) {
    return;
  }

  // Check if the index exists
  $index_exists = $wpdb->get_row( "SHOW INDEX FROM {$table_name} WHERE Key_name = 'idx_id_revision_value'" ); // phpcs:ignore

  // CREATE INDEX idx_id_revision_value ON wp_air_cookie (visitor_id, cookie_revision, cookie_value);
  if ( null === $index_exists ) {
    $wpdb->query( "CREATE INDEX idx_id_revision_value ON {$table_name} (visitor_id, cookie_revision, cookie_value);" ); // phpcs:ignore
  }

  // Check if the user consent for this revision and levels has been already recorded
  $exists = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE visitor_id = %s AND cookie_revision = %s AND cookie_value = %s", $data->visitorid, $data->revision, $cookie_value ) ); // phpcs:ignore

  // Bail if the consent has been already recorded
  if ( null !== $exists ) {
    return;
  }

  // Record the consent.
  $inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $table_name,
    [
      'visitor_id'      => $data->visitorid,
      'cookie_revision' => $data->revision,
      'cookie_value'    => $cookie_value,
      'timestamp'       => wp_date( 'Y-m-d H:i:s' ),
      'expiry'          => wp_date( 'Y-m-d H:i:s', strtotime( "+{$settings['cookie']['expiresAfterDays']} days" ) ),
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
