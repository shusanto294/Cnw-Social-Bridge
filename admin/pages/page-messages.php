<?php
/**
 * Admin Messages page.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$messages = $wpdb->get_results( "
    SELECT m.*,
        sender.display_name    AS sender_name,
        recipient.display_name AS recipient_name
    FROM {$wpdb->prefix}cnw_social_worker_messages m
    LEFT JOIN {$wpdb->users} sender    ON m.sender_id    = sender.ID
    LEFT JOIN {$wpdb->users} recipient ON m.recipient_id = recipient.ID
    ORDER BY m.created_at DESC
    LIMIT 50
" );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Messages', 'cnw-social-bridge' ); ?></h1>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:40px"><?php esc_html_e( 'ID', 'cnw-social-bridge' ); ?></th>
                <th style="width:140px"><?php esc_html_e( 'From', 'cnw-social-bridge' ); ?></th>
                <th style="width:140px"><?php esc_html_e( 'To', 'cnw-social-bridge' ); ?></th>
                <th><?php esc_html_e( 'Subject', 'cnw-social-bridge' ); ?></th>
                <th style="width:60px"><?php esc_html_e( 'Read', 'cnw-social-bridge' ); ?></th>
                <th style="width:160px"><?php esc_html_e( 'Created', 'cnw-social-bridge' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $messages ) : ?>
                <?php foreach ( $messages as $msg ) : ?>
                <tr>
                    <td><?php echo esc_html( $msg->id ); ?></td>
                    <td><?php echo esc_html( $msg->sender_name ); ?></td>
                    <td><?php echo esc_html( $msg->recipient_name ); ?></td>
                    <td><?php echo esc_html( $msg->subject ); ?></td>
                    <td><?php echo $msg->is_read ? '✓' : '—'; ?></td>
                    <td><?php echo esc_html( $msg->created_at ); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6"><?php esc_html_e( 'No messages found.', 'cnw-social-bridge' ); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
