<?php
/**
 * Admin Threads page.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$threads = $wpdb->get_results( "
    SELECT t.*, u.display_name AS author_name
    FROM {$wpdb->prefix}cnw_social_worker_threads t
    LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
    ORDER BY t.created_at DESC
    LIMIT 50
" );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Threads', 'cnw-social-bridge' ); ?></h1>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:40px"><?php esc_html_e( 'ID', 'cnw-social-bridge' ); ?></th>
                <th><?php esc_html_e( 'Title', 'cnw-social-bridge' ); ?></th>
                <th style="width:160px"><?php esc_html_e( 'Author', 'cnw-social-bridge' ); ?></th>
                <th style="width:90px"><?php esc_html_e( 'Status', 'cnw-social-bridge' ); ?></th>
                <th style="width:70px"><?php esc_html_e( 'Views', 'cnw-social-bridge' ); ?></th>
                <th style="width:160px"><?php esc_html_e( 'Created', 'cnw-social-bridge' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $threads ) : ?>
                <?php foreach ( $threads as $thread ) : ?>
                <tr>
                    <td><?php echo esc_html( $thread->id ); ?></td>
                    <td><?php echo esc_html( $thread->title ); ?></td>
                    <td><?php echo esc_html( $thread->author_name ); ?></td>
                    <td>
                        <span class="cnw-status cnw-status-<?php echo esc_attr( $thread->status ); ?>">
                            <?php echo esc_html( ucfirst( $thread->status ) ); ?>
                        </span>
                    </td>
                    <td><?php echo number_format( intval( $thread->views ) ); ?></td>
                    <td><?php echo esc_html( $thread->created_at ); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6"><?php esc_html_e( 'No threads found.', 'cnw-social-bridge' ); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
