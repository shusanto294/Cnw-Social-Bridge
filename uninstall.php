<?php
/**
 * CNW Social Bridge — Uninstall
 *
 * Fired when the plugin is deleted from WordPress.
 * Removes all database tables, options, user meta, and custom roles created by this plugin.
 */

// Exit if not called by WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

/* ------------------------------------------------------------------
 * 1. Drop all custom database tables
 * ------------------------------------------------------------------ */

$tables = array(
    'cnw_social_worker_threads',
    'cnw_social_worker_replies',
    'cnw_social_worker_messages',
    'cnw_social_worker_categories',
    'cnw_social_worker_tags',
    'cnw_social_worker_thread_tags',
    'cnw_social_worker_user_followed_tags',
    'cnw_social_worker_votes',
    'cnw_social_worker_reputation',
    'cnw_social_worker_activity',
    'cnw_social_worker_saved_threads',
    'cnw_social_worker_notifications',
    'cnw_social_worker_reports',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
}

/* ------------------------------------------------------------------
 * 2. Delete all plugin options
 * ------------------------------------------------------------------ */

$options = array(
    'cnw_social_bridge_version',
    'cnw_social_logo_url',
    'cnw_social_mobile_logo_url',
    'cnw_community_guidelines',
    'cnw_community_guidelines_html',
    'cnw_pusher_host',
    'cnw_pusher_port',
    'cnw_pusher_cluster',
    'cnw_pusher_app_id',
    'cnw_pusher_key',
    'cnw_pusher_secret',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

/* ------------------------------------------------------------------
 * 3. Delete all plugin user meta
 * ------------------------------------------------------------------ */

$meta_keys = array(
    'cnw_avatar_url',
    'cnw_avatar_attachment_id',
    'cnw_phone',
    'cnw_anonymous',
    'cnw_reputation_total',
    'cnw_verified_label',
    'cnw_professional_title',
    'cnw_is_online',
);

foreach ( $meta_keys as $key ) {
    $wpdb->delete( $wpdb->usermeta, array( 'meta_key' => $key ) );
}

/* ------------------------------------------------------------------
 * 4. Remove custom roles
 * ------------------------------------------------------------------ */

remove_role( 'cnw_forum_member' );
remove_role( 'cnw_moderator' );
remove_role( 'cnw_forum_admin' );
