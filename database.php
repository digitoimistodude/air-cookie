<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-24 13:26:51
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-08-24 13:43:27
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
    user_id bigint(20) DEFAULT '0',
    visitor_id varchar(255),
    cookie_version varchar(255),
    cookie_value varchar(255),
    timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    expiry datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY (id)
  ) {$charset_collate};";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql_table );

  add_site_option( get_databse_version_key(), get_databse_version() );
} // end maybe_init_database
