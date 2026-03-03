<?php
/**
 * Admin Forum Users page.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$users = $wpdb->get_results( "
    SELECT u.ID, u.display_name, u.user_email, u.user_login, u.user_registered,
        (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE author_id = u.ID) AS threads_count,
        (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies  WHERE author_id = u.ID) AS replies_count
    FROM {$wpdb->users} u
    ORDER BY u.user_registered DESC
    LIMIT 50
" );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Forum Users', 'cnw-social-bridge' ); ?></h1>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'User', 'cnw-social-bridge' ); ?></th>
                <th style="width:80px"><?php esc_html_e( 'Threads', 'cnw-social-bridge' ); ?></th>
                <th style="width:80px"><?php esc_html_e( 'Replies', 'cnw-social-bridge' ); ?></th>
                <th style="width:160px"><?php esc_html_e( 'Registered', 'cnw-social-bridge' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $users ) : ?>
                <?php foreach ( $users as $user ) : ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html( $user->display_name ); ?></strong><br>
                        <small><?php echo esc_html( $user->user_email ); ?></small>
                    </td>
                    <td><?php echo number_format( intval( $user->threads_count ) ); ?></td>
                    <td><?php echo number_format( intval( $user->replies_count ) ); ?></td>
                    <td><?php echo esc_html( $user->user_registered ); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="4"><?php esc_html_e( 'No users found.', 'cnw-social-bridge' ); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
