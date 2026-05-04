<?php
/**
 * Uninstall LMS Courses Table
 * Runs when the plugin is deleted from WordPress admin.
 */

// Security: only run if WordPress initiated the uninstall
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin settings
delete_option( 'abct_settings' );

// Delete all transient cache
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_abct_courses_%' OR option_name LIKE '_transient_timeout_abct_courses_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
