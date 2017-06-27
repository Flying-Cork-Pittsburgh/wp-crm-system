<?php
// if uninstall.php is not called by WordPress, die
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

$option_name = '';

delete_option( $option_name );

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wbdb->prefix}tablename");

?>