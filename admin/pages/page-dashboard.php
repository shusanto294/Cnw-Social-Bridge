<?php
/**
 * Admin Dashboard — overview stats for all tables.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$p = $wpdb->prefix . 'cnw_social_worker_';

$counts = array(
    'threads'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}threads" ),
    'replies'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}replies" ),
    'messages'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}messages" ),
    'categories' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}categories" ),
    'votes'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}votes" ),
    'reputation' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}reputation" ),
);

$cards = array(
    array( 'Threads',    'threads',    'cnw-threads',    'dashicons-format-chat' ),
    array( 'Replies',    'replies',    'cnw-replies',    'dashicons-admin-comments' ),
    array( 'Messages',   'messages',   'cnw-messages',   'dashicons-email-alt' ),
    array( 'Categories', 'categories', 'cnw-categories', 'dashicons-category' ),
    array( 'Votes',      'votes',      'cnw-votes',      'dashicons-thumbs-up' ),
    array( 'Reputation', 'reputation', 'cnw-reputation',  'dashicons-awards' ),
);
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Social Bridge Dashboard', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'Overview of all forum data.', 'cnw-social-bridge' ); ?></p>
    </div>

    <div class="cnw-dashboard-grid">
        <?php foreach ( $cards as $c ) : ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $c[2] ) ); ?>" class="cnw-dash-card">
            <span class="dashicons <?php echo esc_attr( $c[3] ); ?> cnw-dash-icon"></span>
            <div>
                <span class="cnw-dash-count"><?php echo number_format( $counts[ $c[1] ] ); ?></span>
                <span class="cnw-dash-label"><?php echo esc_html( $c[0] ); ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="cnw-section" style="margin-top:30px">
        <h2 class="cnw-section-title"><?php esc_html_e( 'Recent Threads', 'cnw-social-bridge' ); ?></h2>
        <?php
        $recent = $wpdb->get_results(
            "SELECT t.*, u.display_name AS author_name
             FROM {$p}threads t LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             ORDER BY t.created_at DESC LIMIT 5"
        );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr>
                <th style="width:40px">ID</th><th>Title</th><th style="width:140px">Author</th><th style="width:90px">Status</th><th style="width:150px">Created</th>
            </tr></thead>
            <tbody>
            <?php if ( $recent ) : foreach ( $recent as $t ) : ?>
                <tr>
                    <td><?php echo esc_html( $t->id ); ?></td>
                    <td><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-threads&action=edit&id=' . $t->id ) ); ?>"><?php echo esc_html( $t->title ); ?></a></td>
                    <td><?php echo esc_html( $t->author_name ); ?></td>
                    <td><span class="cnw-status cnw-status-<?php echo esc_attr( $t->status ); ?>"><?php echo esc_html( ucfirst( $t->status ) ); ?></span></td>
                    <td><?php echo esc_html( $t->created_at ); ?></td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="5"><?php esc_html_e( 'No threads yet.', 'cnw-social-bridge' ); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
