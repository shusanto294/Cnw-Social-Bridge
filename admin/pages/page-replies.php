<?php
/**
 * Admin Replies page.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$replies = $wpdb->get_results( "
    SELECT r.*, u.display_name AS author_name, t.title AS thread_title
    FROM {$wpdb->prefix}cnw_social_worker_replies r
    LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
    LEFT JOIN {$wpdb->prefix}cnw_social_worker_threads t ON r.thread_id = t.id
    ORDER BY r.created_at DESC
    LIMIT 50
" );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Replies', 'cnw-social-bridge' ); ?></h1>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:40px"><?php esc_html_e( 'ID', 'cnw-social-bridge' ); ?></th>
                <th><?php esc_html_e( 'Thread', 'cnw-social-bridge' ); ?></th>
                <th style="width:160px"><?php esc_html_e( 'Author', 'cnw-social-bridge' ); ?></th>
                <th style="width:90px"><?php esc_html_e( 'Status', 'cnw-social-bridge' ); ?></th>
                <th style="width:160px"><?php esc_html_e( 'Created', 'cnw-social-bridge' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $replies ) : ?>
                <?php foreach ( $replies as $reply ) : ?>
                <tr>
                    <td><?php echo esc_html( $reply->id ); ?></td>
                    <td><?php echo esc_html( $reply->thread_title ); ?></td>
                    <td><?php echo esc_html( $reply->author_name ); ?></td>
                    <td>
                        <span class="cnw-status cnw-status-<?php echo esc_attr( $reply->status ); ?>">
                            <?php echo esc_html( ucfirst( $reply->status ) ); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html( $reply->created_at ); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="5"><?php esc_html_e( 'No replies found.', 'cnw-social-bridge' ); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
