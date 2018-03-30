<?php
// if uninstall.php is not called by WordPress, die
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

global $wpdb;
global $wp_post_types;

//Plugin is being uninstalled. Clean up their tables by deleted all crm_customer post types then unset the post type
$wpdb->query("DELETE * FROM 'wp_posts' WHERE post_type = 'crm_customer' ");

if ( isset( $wp_post_types[ 'crm_customer' ] ) ) {
    unset( $wp_post_types[ 'crm_customer' ] );
    return true;
}

?>