<?php
/**
 * Admin Reports — list, view, manage and change status of user reports.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_reports';
$action = sanitize_text_field( $_GET['action'] ?? 'list' );
$id     = intval( $_GET['id'] ?? 0 );
$msg    = sanitize_text_field( $_GET['msg'] ?? '' );
$count  = intval( $_GET['count'] ?? 0 );
$filter_status = sanitize_text_field( $_GET['status'] ?? '' );

$statuses = array(
    'open'        => array( 'label' => 'Open',        'color' => '#d63638' ),
    'in_progress' => array( 'label' => 'In Progress', 'color' => '#dba617' ),
    'resolved'    => array( 'label' => 'Resolved',    'color' => '#00a32a' ),
    'closed'      => array( 'label' => 'Closed',      'color' => '#787c82' ),
);

$type_labels = array(
    'inappropriate_content' => 'Inappropriate Content',
    'harassment'            => 'Harassment or Bullying',
    'spam'                  => 'Spam or Self-Promotion',
    'confidentiality'       => 'Confidentiality Violation',
    'misinformation'        => 'Misinformation',
    'technical'             => 'Technical Issue / Bug',
    'other'                 => 'Other',
);

// Load single report for view/edit
$item = null;
if ( $action === 'view' && $id ) {
    $item = $wpdb->get_row( $wpdb->prepare(
        "SELECT r.*, u.display_name AS reporter_name, u.user_email AS reporter_email,
                um.meta_value AS reporter_reputation
         FROM $table r
         LEFT JOIN {$wpdb->users} u ON u.ID = r.user_id
         LEFT JOIN {$wpdb->usermeta} um ON um.user_id = r.user_id AND um.meta_key = 'cnw_reputation_total'
         WHERE r.id = %d", $id
    ) );
    if ( ! $item ) { $action = 'list'; }

    // Build proper content link (mirrors frontend logic)
    if ( $item ) {
        // Find the page that contains the [cnw_social_bridge] shortcode
        $cnw_page = get_posts( array(
            'post_type'  => 'page',
            'post_status' => 'publish',
            's'          => '[cnw_social_bridge]',
            'numberposts' => 1,
        ) );
        $base_url = ! empty( $cnw_page ) ? get_permalink( $cnw_page[0]->ID ) : home_url( '/' );
        $base_url = trailingslashit( $base_url ) . '#';

        if ( ! empty( $item->content_type ) && $item->content_type === 'reply' && ! empty( $item->content_id ) ) {
            $thread_id = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT thread_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d",
                $item->content_id
            ) );
            if ( $thread_id ) {
                $item->content_link = $base_url . '/thread/' . $thread_id . '?highlight_reply=' . $item->content_id;
            }
        } elseif ( ! empty( $item->content_type ) && $item->content_type === 'thread' && ! empty( $item->content_id ) ) {
            $item->content_link = $base_url . '/thread/' . $item->content_id;
        }

        if ( empty( $item->content_link ) && ! empty( $item->link ) ) {
            $item->content_link = $item->link;
        }
    }
}

// Status counts for tabs
$status_counts = array();
$count_rows = $wpdb->get_results( "SELECT status, COUNT(*) as cnt FROM $table GROUP BY status" );
foreach ( $count_rows as $row ) {
    $status_counts[ $row->status ] = (int) $row->cnt;
}
$total_count = array_sum( $status_counts );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Reports', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'View and manage reports submitted by community members.', 'cnw-social-bridge' ); ?></p>
    </div>

    <?php if ( $msg === 'updated' ) : ?><div class="notice notice-success is-dismissible"><p>Report updated.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Report deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_done' && $count ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html( $count ); ?> report(s) updated.</p></div><?php endif; ?>

<?php if ( $action === 'view' && $item ) : ?>
    <!-- Single report view -->
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reports' ) ); ?>">&larr; Back to Reports</a></p>

    <div class="cnw-report-detail-card">
        <div class="cnw-report-detail-header">
            <h2><?php echo esc_html( $item->subject ); ?></h2>
            <span class="cnw-status-badge" style="background:<?php echo esc_attr( $statuses[ $item->status ]['color'] ?? '#787c82' ); ?>">
                <?php echo esc_html( $statuses[ $item->status ]['label'] ?? $item->status ); ?>
            </span>
        </div>

        <table class="form-table cnw-report-meta-table">
            <tr><th>Report ID</th><td>#<?php echo esc_html( $item->id ); ?></td></tr>
            <tr><th>Reported By</th><td><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-users&action=edit&id=' . $item->user_id ) ); ?>" target="_blank"><?php echo esc_html( $item->reporter_name ?? 'Unknown' ); ?></a> <span style="color:#787c82;">(User #<?php echo esc_html( $item->user_id ); ?>)</span></td></tr>
            <tr><th>Email</th><td><?php echo esc_html( $item->reporter_email ); ?></td></tr>
            <tr><th>Reputation</th><td><strong><?php echo esc_html( (int) $item->reporter_reputation ); ?></strong> points</td></tr>
            <tr><th>Type</th><td><?php echo esc_html( $type_labels[ $item->type ] ?? $item->type ); ?></td></tr>
            <?php if ( ! empty( $item->content_link ) ) : ?>
            <tr><th>Linked Content</th><td><a href="<?php echo esc_url( $item->content_link ); ?>" target="_blank"><?php echo esc_html( $item->content_link ); ?></a></td></tr>
            <?php endif; ?>
            <tr><th>Submitted</th><td><?php echo esc_html( $item->created_at ); ?></td></tr>
            <tr><th>Last Updated</th><td><?php echo esc_html( $item->updated_at ); ?></td></tr>
        </table>

        <h3>Description</h3>
        <div class="cnw-report-description"><?php echo wp_kses_post( nl2br( $item->description ) ); ?></div>

        <hr>

        <h3>Manage Report</h3>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-report-manage-form">
            <?php wp_nonce_field( 'cnw_update_report' ); ?>
            <input type="hidden" name="action" value="cnw_update_report">
            <input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>">

            <table class="form-table">
                <tr>
                    <th><label for="status">Status</label></th>
                    <td>
                        <select name="status" id="status">
                            <?php foreach ( $statuses as $key => $s ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item->status, $key ); ?>><?php echo esc_html( $s['label'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="admin_notes">Admin Notes</label></th>
                    <td>
                        <textarea name="admin_notes" id="admin_notes" rows="4" class="large-text"><?php echo esc_textarea( $item->admin_notes ); ?></textarea>
                        <p class="description">Internal notes about actions taken. Not visible to the reporter.</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button button-primary" value="Update Report">
            </p>
        </form>

        <hr>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('Delete this report permanently?');">
            <?php wp_nonce_field( 'cnw_delete_report' ); ?>
            <input type="hidden" name="action" value="cnw_delete_report">
            <input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>">
            <button type="submit" class="button" style="color:#b32d2e;border-color:#b32d2e;">Delete Report</button>
        </form>
    </div>

<?php else : ?>
    <!-- Report list -->
    <ul class="subsubsub" style="margin-bottom:12px;">
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reports' ) ); ?>" <?php if ( ! $filter_status ) echo 'class="current"'; ?>>All <span class="count">(<?php echo $total_count; ?>)</span></a> |</li>
        <?php foreach ( $statuses as $key => $s ) : $sc = $status_counts[ $key ] ?? 0; ?>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reports&status=' . $key ) ); ?>" <?php if ( $filter_status === $key ) echo 'class="current"'; ?>><?php echo esc_html( $s['label'] ); ?> <span class="count">(<?php echo $sc; ?>)</span></a><?php echo $key !== 'closed' ? ' |' : ''; ?></li>
        <?php endforeach; ?>
    </ul>

    <?php
    $per_page = 20;
    $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $where = '';
    if ( $filter_status && array_key_exists( $filter_status, $statuses ) ) {
        $where = $wpdb->prepare( " WHERE r.status = %s", $filter_status );
    }

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT r.*, u.display_name AS reporter_name, u.user_email AS reporter_email,
                um.meta_value AS reporter_reputation
         FROM $table r
         LEFT JOIN {$wpdb->users} u ON u.ID = r.user_id
         LEFT JOIN {$wpdb->usermeta} um ON um.user_id = r.user_id AND um.meta_key = 'cnw_reputation_total'
         $where
         ORDER BY r.created_at DESC
         LIMIT %d OFFSET %d",
        $per_page, $offset
    ) );

    $total_q = "SELECT COUNT(*) FROM $table r $where";
    $total   = (int) $wpdb->get_var( $total_q );
    $pages   = (int) ceil( $total / $per_page );
    ?>

    <?php if ( empty( $rows ) ) : ?>
        <div class="cnw-empty-state">
            <span class="dashicons dashicons-flag" style="font-size:36px;color:#c3c4c7;"></span>
            <p>No reports found<?php echo $filter_status ? ' with status "' . esc_html( $statuses[ $filter_status ]['label'] ) . '"' : ''; ?>.</p>
        </div>
    <?php else : ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_reports' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_reports">

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="bulk_action">
                    <option value="">Bulk Actions</option>
                    <option value="mark_open">Mark as Open</option>
                    <option value="mark_in_progress">Mark as In Progress</option>
                    <option value="mark_resolved">Mark as Resolved</option>
                    <option value="mark_closed">Mark as Closed</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            <?php if ( $pages > 1 ) : ?>
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo $total; ?> item(s)</span>
                <?php for ( $p = 1; $p <= $pages; $p++ ) : ?>
                    <?php if ( $p === $paged ) : ?>
                        <span class="tablenav-pages-navspan button disabled"><?php echo $p; ?></span>
                    <?php else : ?>
                        <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $p ) ); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column"><input type="checkbox" class="cnw-select-all"></td>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Reporter</th>
                    <th>Reputation</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $rows as $row ) : ?>
                <tr>
                    <th class="check-column"><input type="checkbox" name="ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></th>
                    <td>#<?php echo esc_html( $row->id ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reports&action=view&id=' . $row->id ) ); ?>">
                            <strong><?php echo esc_html( $row->subject ); ?></strong>
                        </a>
                    </td>
                    <td><?php echo esc_html( $type_labels[ $row->type ] ?? $row->type ); ?></td>
                    <td><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-users&action=edit&id=' . $row->user_id ) ); ?>" target="_blank"><?php echo esc_html( $row->reporter_name ?? 'Unknown' ); ?></a> <span style="color:#787c82;">(#<?php echo esc_html( $row->user_id ); ?>)</span></td>
                    <td><?php echo esc_html( (int) $row->reporter_reputation ); ?></td>
                    <td>
                        <span class="cnw-status-badge" style="background:<?php echo esc_attr( $statuses[ $row->status ]['color'] ?? '#787c82' ); ?>">
                            <?php echo esc_html( $statuses[ $row->status ]['label'] ?? $row->status ); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html( date( 'M j, Y', strtotime( $row->created_at ) ) ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reports&action=view&id=' . $row->id ) ); ?>" class="button button-small">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
    <?php endif; ?>

<?php endif; ?>
</div>

<style>
.cnw-status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
}
.cnw-report-detail-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 24px;
    margin-top: 12px;
}
.cnw-report-detail-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.cnw-report-detail-header h2 { margin: 0; }
.cnw-report-description {
    background: #f6f7f7;
    border: 1px solid #dcdcde;
    border-radius: 4px;
    padding: 16px;
    margin: 8px 0 16px;
    line-height: 1.7;
}
.cnw-report-meta-table th { width: 140px; font-weight: 600; }
.cnw-empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #787c82;
}
.cnw-empty-state p { margin-top: 8px; font-size: 14px; }
</style>
