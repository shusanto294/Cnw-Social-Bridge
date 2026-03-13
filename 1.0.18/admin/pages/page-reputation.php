<?php
/**
 * Admin Reputation — CRUD list / add / edit with bulk actions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_reputation';
$action = sanitize_text_field( $_GET['action'] ?? 'list' );
$id     = intval( $_GET['id'] ?? 0 );
$msg    = sanitize_text_field( $_GET['msg'] ?? '' );
$count  = intval( $_GET['count'] ?? 0 );

$item = null;
if ( $action === 'edit' && $id ) {
    $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    if ( ! $item ) { $action = 'list'; }
}

$users = $wpdb->get_results( "SELECT ID, display_name FROM {$wpdb->users} ORDER BY display_name ASC LIMIT 200" );
$action_types = array(
    'thread_created'  => 'Thread Created (+5)',
    'reply_created'   => 'Reply Created (+2)',
    'received_upvote' => 'Received Upvote (+10)',
    'best_answer'     => 'Accepted as Best Answer (+25)',
    'gave_upvote'     => 'Gave Upvote (+1)',
    'manual_adjustment' => 'Manual Adjustment',
);
$action_type_points = array(
    'thread_created'  => 5,
    'reply_created'   => 2,
    'received_upvote' => 10,
    'best_answer'     => 25,
    'gave_upvote'     => 1,
    'manual_adjustment' => 0,
);
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Reputation', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>Reputation entry saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Reputation entry deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> reputation entry(ies) deleted.</p></div><?php endif; ?>

<?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <h2><?php echo $item ? 'Edit Reputation #' . esc_html( $item->id ) : 'Add Reputation Entry'; ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_reputation' ); ?>
        <input type="hidden" name="action" value="cnw_save_reputation">
        <?php if ( $item ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><label for="user_id">User</label></th>
                <td><select id="user_id" name="user_id" required>
                    <option value="">-- Select --</option>
                    <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $item->user_id ?? '', $u->ID ); ?>><?php echo esc_html( $u->display_name . ' (#' . $u->ID . ')' ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="action_type">Action Type</label></th>
                <td><select id="action_type" name="action_type" required>
                    <?php foreach ( $action_types as $at_key => $at_label ) : ?>
                    <option value="<?php echo esc_attr( $at_key ); ?>" data-points="<?php echo esc_attr( $action_type_points[ $at_key ] ); ?>" <?php selected( $item->action_type ?? '', $at_key ); ?>><?php echo esc_html( $at_label ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="points">Points</label></th>
                <td><input type="number" id="points" name="points" value="<?php echo esc_attr( $item->points ?? 0 ); ?>" required></td></tr>
            <tr><th><label for="reference_type">Reference Type</label></th>
                <td><select id="reference_type" name="reference_type">
                    <option value="">None</option>
                    <option value="thread" <?php selected( $item->reference_type ?? '', 'thread' ); ?>>Thread</option>
                    <option value="reply" <?php selected( $item->reference_type ?? '', 'reply' ); ?>>Reply</option>
                    <option value="vote" <?php selected( $item->reference_type ?? '', 'vote' ); ?>>Vote</option>
                </select></td></tr>
            <tr><th><label for="reference_id">Reference ID</label></th>
                <td><input type="number" id="reference_id" name="reference_id" value="<?php echo esc_attr( $item->reference_id ?? '' ); ?>" min="0"></td></tr>
            <tr><th><label for="description">Description</label></th>
                <td><input type="text" id="description" name="description" class="regular-text" value="<?php echo esc_attr( $item->description ?? '' ); ?>"></td></tr>
        </table>

        <script>
        document.getElementById('action_type').addEventListener('change', function() {
            var sel = this.options[this.selectedIndex];
            var pts = sel.getAttribute('data-points');
            if (pts !== null) document.getElementById('points').value = pts;
        });
        <?php if ( ! $item ) : ?>
        (function() {
            var sel = document.getElementById('action_type');
            var pts = sel.options[sel.selectedIndex].getAttribute('data-points');
            if (pts !== null) document.getElementById('points').value = pts;
        })();
        <?php endif; ?>
        </script>

        <?php submit_button( $item ? 'Update Entry' : 'Create Entry' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reputation' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reputation&action=add' ) ); ?>" class="button button-primary">Add Reputation Entry</a></p>

    <?php
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $per_page = 20;
    $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $where = '';
    $params = array();
    if ( $search ) {
        $like   = '%' . $wpdb->esc_like( $search ) . '%';
        $where  = 'WHERE u.display_name LIKE %s OR rp.action_type LIKE %s OR rp.description LIKE %s';
        $params = array( $like, $like, $like );
    }

    $total_query = "SELECT COUNT(*) FROM $table rp LEFT JOIN {$wpdb->users} u ON rp.user_id = u.ID $where";
    $total       = $search ? (int) $wpdb->get_var( $wpdb->prepare( $total_query, ...$params ) ) : (int) $wpdb->get_var( $total_query );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $paged       = min( $paged, $total_pages );

    $query = "SELECT rp.*, u.display_name AS user_name
         FROM $table rp
         LEFT JOIN {$wpdb->users} u ON rp.user_id = u.ID
         $where ORDER BY rp.created_at DESC LIMIT %d OFFSET %d";
    $query_params = $search ? array_merge( $params, array( $per_page, $offset ) ) : array( $per_page, $offset );
    $rows = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );

    cnw_admin_search_box( 'cnw-reputation', $search );
    cnw_admin_pagination( 'cnw-reputation', $paged, $total_pages, $total, $search );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_reputation' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_reputation">

        <div class="cnw-bulk-bar">
            <select name="bulk_action">
                <option value="">Bulk Actions</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="button">Apply</button>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead><tr>
                <th class="cnw-cb-col"><input type="checkbox" class="cnw-select-all"></th>
                <th style="width:40px">ID</th><th style="width:140px">User</th><th style="width:80px">Total Rep</th><th style="width:70px">Points</th><th style="width:140px">Action</th><th style="width:80px">Ref Type</th><th style="width:70px">Ref ID</th><th>Description</th><th style="width:150px">Created</th><th style="width:100px">Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $row ) :
                $user_total = (int) get_user_meta( $row->user_id, 'cnw_reputation_total', true );
            ?>
                <tr>
                    <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></td>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->user_name ); ?></td>
                    <td><strong><?php echo esc_html( $user_total ); ?></strong></td>
                    <td><strong><?php echo $row->points >= 0 ? '+' : ''; ?><?php echo esc_html( $row->points ); ?></strong></td>
                    <td><?php echo esc_html( str_replace( '_', ' ', ucfirst( $row->action_type ) ) ); ?></td>
                    <td><?php echo $row->reference_type ? esc_html( ucfirst( $row->reference_type ) ) : '—'; ?></td>
                    <td><?php echo $row->reference_id ? esc_html( $row->reference_id ) : '—'; ?></td>
                    <td><?php echo esc_html( $row->description ); ?></td>
                    <td><?php echo esc_html( $row->created_at ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-reputation&action=edit&id=' . $row->id ) ); ?>">Edit</a> |
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_reputation&id=' . $row->id ), 'cnw_delete_reputation' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this entry?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="11">No reputation entries found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <?php cnw_admin_pagination( 'cnw-reputation', $paged, $total_pages, $total, $search ); ?>
<?php endif; ?>
</div>
