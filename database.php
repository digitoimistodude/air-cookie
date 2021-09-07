<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-24 13:26:51
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-07 16:22:19
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

function get_databse_table_name( $prefix = true ) {
  $table_name = 'air_cookie';

  if ( $prefix ) {
    global $wpdb;
    $table_name = $wpdb->prefix . $table_name;
  }

  return $table_name;
} // end get_databse_table_name

function get_databse_version_key() {
  return get_databse_table_name( false ) . '_db_version';
} // end get_databse_version_key

function maybe_init_database() {
  $installed_version = get_option( get_databse_version_key() );
  if ( absint( $installed_version ) === get_databse_version() ) {
    return;
  }

  global $wpdb;
  $table_name = get_databse_table_name();
  $charset_collate = $wpdb->get_charset_collate();

  $sql_table = "CREATE TABLE {$table_name} (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    visitor_id varchar(255),
    user_id bigint(20) DEFAULT '0',
    cookie_value varchar(255),
    timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    expiry datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY (id)
  ) {$charset_collate};";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql_table );

  add_site_option( get_databse_version_key(), get_databse_version() );
} // end maybe_init_database

function save_to_databse( $user_id, $visitor_id, $cookie_value ) {
  global $wpdb;
  $table_name = get_databse_table_name();

  $inserted = $wpdb->insert(
    $table_name,
    [
      'user_id'         => $user_id,
      'visitor_id'      => $visitor_id,
      'cookie_value'    => $cookie_value,
      'timestamp'       => wp_date( 'Y-m-d H:i:s' ),
      'expiry'          => wp_date( 'Y-m-d H:i:s', strtotime( '+182 days' ) ),
    ],
    [
      '%d',
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
} // end save_to_databse

function register_rest_endpoint() {
  register_rest_route( 'air-cookie/v1', '/consent', [
    'methods'             => 'post',
    'callback'            => __NAMESPACE__ . '\register_consent',
    'permission_callback' => __NAMESPACE__ . '\register_consent_permission_callback',
  ] );
} // end

function register_consent( $request ) {
  if ( empty( $request->get_param( 'visitor' ) ) ) {
    return false;
  }

  if ( empty( $request->get_param( 'cookie' ) ) ) {
    return false;
  }

  $user_id = is_user_logged_in() ? get_current_user_id() : 0;

  $db_row_id = save_to_databse( $user_id, $request->get_param( 'visitor' ), $request->get_param( 'cookie' )  );
  if ( $db_row_id ) {
    return true;
  }

  return false;
} // end

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
} // end
