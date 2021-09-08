<?php
/**
 * @Author: Timi Wahalahti
 * @Date:   2021-08-24 13:26:51
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2021-09-08 11:01:54
 * @package air-cookie
 */

namespace Air_Cookie;

if ( ! defined( 'ABSPATH' ) ) {
  exit();
}

/**
 * Create or update the database schema if database version stored and in plugin
 * are different.
 *
 * @since 0.1.0
 */
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
    cookie_revision varchar(255),
    cookie_value varchar(255),
    timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    expiry datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY (id)
  ) {$charset_collate};";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql_table );

  add_site_option( get_databse_version_key(), get_databse_version() );
} // end maybe_init_database
