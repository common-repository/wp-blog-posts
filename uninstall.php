<?php
/**
 * If uninstall.php is not called by WordPress, die.
 *
 * @package WPBlogPosts
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;
// Remove options from option table.
delete_option( 'wpbp-switch' );