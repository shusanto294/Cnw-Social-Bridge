<?php
/**
 * Admin Votes — CRUD list / add / edit with bulk actions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_votes';
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
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Votes', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>Vote saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Vote deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> vote(s) deleted.</p></div><?php endif; ?>

<?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <h2><?php echo $item ? 'Edit Vote #' . esc_html( $item->id ) : 'Add New Vote'; ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_vote' ); ?>
        <input type="hidden" name="action" value="cnw_save_vote">
        <?php if ( $item ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><label for="user_id">User</label></th>
                <td><select id="user_id" name="user_id" required>
                    <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $item->user_id ?? get_current_user_id(), $u->ID ); ?>><?php echo esc_html( $u->display_name . ' (#' . $u->ID . ')' ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="target_type">Target Type</label></th>
                <td><select id="target_type" name="target_type" required>
                    <option value="thread" <?php selected( $item->target_type ?? '', 'thread' ); ?>>Thread</option>
                    <option value="reply" <?php selected( $item->target_type ?? '', 'reply' ); ?>>Reply</option>
                </select></td></tr>
            <tr><th><label for="target_id">Target ID</label></th>
                <td><input type="number" id="target_id" name="target_id" value="<?php echo esc_attr( $item->target_id ?? '' ); ?>" min="1" required></td></tr>
            <tr><th><label for="vote_type">Vote Type</label></th>
                <td><select id="vote_type" name="vote_type" required>
                    <option value="1" <?php selected( $item->vote_type ?? 1, 1 ); ?>>Upvote (+1)</option>
                    <option value="-1" <?php selected( $item->vote_type ?? 1, -1 ); ?>>Downvote (-1)</option>
                </select></td></tr>
        </table>

        <?php submit_button( $item ? 'Update Vote' : 'Create Vote' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-votes' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-votes&action=add' ) ); ?>" class="button button-primary">Add New Vote</a></p>

    <?php
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $per_page = 20;
    $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $where = '';
    $params = array();
    if ( $search ) {
        $like   = '%' . $wpdb->esc_like( $search ) . '%';
        $where  = 'WHERE u.display_name LIKE %s OR v.target_type LIKE %s';
        $params = array( $like, $like );
    }

    $total_query = "SELECT COUNT(*) FROM $table v LEFT JOIN {$wpdb->users} u ON v.user_id = u.ID $where";
    $total       = $search ? (int) $wpdb->get_var( $wpdb->prepare( $total_query, ...$params ) ) : (int) $wpdb->get_var( $total_query );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $paged       = min( $paged, $total_pages );

    $query = "SELECT v.*, u.display_name AS user_name
         FROM $table v
         LEFT JOIN {$wpdb->users} u ON v.user_id = u.ID
         $where ORDER BY v.created_at DESC LIMIT %d OFFSET %d";
    $query_params = $search ? array_merge( $params, array( $per_page, $offset ) ) : array( $per_page, $offset );
    $rows = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );

    cnw_admin_search_box( 'cnw-votes', $search );
    cnw_admin_pagination( 'cnw-votes', $paged, $total_pages, $total, $search );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_votes' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_votes">

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
                <th style="width:40px">ID</th><th style="width:140px">User</th><th style="width:100px">Target Type</th><th style="width:80px">Target ID</th><th style="width:100px">Vote</th><th style="width:150px">Created</th><th style="width:100px">Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $row ) : ?>
                <tr>
                    <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></td>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->user_name ); ?></td>
                    <td><?php echo esc_html( ucfirst( $row->target_type ) ); ?></td>
                    <td><?php echo esc_html( $row->target_id ); ?></td>
                    <td><?php echo $row->vote_type == 1 ? '<span style="color:green">Upvote</span>' : '<span style="color:red">Downvote</span>'; ?></td>
                    <td><?php echo esc_html( $row->created_at ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-votes&action=edit&id=' . $row->id ) ); ?>">Edit</a> |
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_vote&id=' . $row->id ), 'cnw_delete_vote' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this vote?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="8">No votes found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <?php cnw_admin_pagination( 'cnw-votes', $paged, $total_pages, $total, $search ); ?>
<?php endif; ?>
</div>
