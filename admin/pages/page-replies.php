<?php
/**
 * Admin Replies — CRUD list / add / edit with bulk actions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_replies';
$action = sanitize_text_field( $_GET['action'] ?? 'list' );
$id     = intval( $_GET['id'] ?? 0 );
$msg    = sanitize_text_field( $_GET['msg'] ?? '' );
$count  = intval( $_GET['count'] ?? 0 );

$item = null;
if ( $action === 'edit' && $id ) {
    $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    if ( ! $item ) { $action = 'list'; }
}

$users   = $wpdb->get_results( "SELECT ID, display_name FROM {$wpdb->users} ORDER BY display_name ASC LIMIT 200" );
$threads = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}cnw_social_worker_threads ORDER BY created_at DESC LIMIT 200" );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Replies', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>Reply saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Reply deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> reply(ies) deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_updated' && $count ) : ?><div class="notice notice-success is-dismissible"><p><?php echo esc_html( $count ); ?> reply(ies) updated.</p></div><?php endif; ?>
    <?php if ( $msg === 'status_updated' ) : ?><div class="notice notice-success is-dismissible"><p>Reply status updated.</p></div><?php endif; ?>

<?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <h2><?php echo $item ? 'Edit Reply #' . esc_html( $item->id ) : 'Add New Reply'; ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_reply' ); ?>
        <input type="hidden" name="action" value="cnw_save_reply">
        <?php if ( $item ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><label for="thread_id">Thread</label></th>
                <td><select id="thread_id" name="thread_id" required>
                    <option value="">-- Select Thread --</option>
                    <?php foreach ( $threads as $t ) : ?>
                    <option value="<?php echo esc_attr( $t->id ); ?>" <?php selected( $item->thread_id ?? '', $t->id ); ?>>#<?php echo esc_html( $t->id . ' — ' . $t->title ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="author_id">Author</label></th>
                <td><select id="author_id" name="author_id">
                    <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $item->author_id ?? get_current_user_id(), $u->ID ); ?>><?php echo esc_html( $u->display_name . ' (#' . $u->ID . ')' ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="parent_id">Parent Reply ID</label></th>
                <td><input type="number" id="parent_id" name="parent_id" value="<?php echo esc_attr( $item->parent_id ?? '' ); ?>" min="0"><br><small>Leave 0 or empty for top-level reply.</small></td></tr>
            <tr><th><label for="content">Content</label></th>
                <td><textarea id="content" name="content" rows="5" class="large-text" required><?php echo esc_textarea( $item->content ?? '' ); ?></textarea></td></tr>
            <tr><th><label for="status">Status</label></th>
                <td><select id="status" name="status">
                    <?php foreach ( array( 'pending' => 'Pending', 'approved' => 'Approve', 'rejected' => 'Reject', 'spam' => 'Spam' ) as $s => $label ) : ?>
                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $item->status ?? 'approved', $s ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
        </table>

        <?php submit_button( $item ? 'Update Reply' : 'Create Reply' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-replies' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-replies&action=add' ) ); ?>" class="button button-primary">Add New Reply</a></p>

    <?php
    $search        = sanitize_text_field( $_GET['s'] ?? '' );
    $filter_status = sanitize_text_field( $_GET['status'] ?? '' );
    $per_page      = 20;
    $paged         = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset        = ( $paged - 1 ) * $per_page;

    $where  = array();
    $params = array();
    if ( $search ) {
        $like     = '%' . $wpdb->esc_like( $search ) . '%';
        $where[]  = '(r.content LIKE %s OR u.display_name LIKE %s OR t.title LIKE %s)';
        $params   = array_merge( $params, array( $like, $like, $like ) );
    }
    if ( $filter_status ) {
        $where[]  = 'r.status = %s';
        $params[] = $filter_status;
    }
    $where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

    $total_query = "SELECT COUNT(*) FROM $table r LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID LEFT JOIN {$wpdb->prefix}cnw_social_worker_threads t ON r.thread_id = t.id $where_sql";
    $total       = $params ? (int) $wpdb->get_var( $wpdb->prepare( $total_query, ...$params ) ) : (int) $wpdb->get_var( $total_query );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $paged       = min( $paged, $total_pages );

    $query = "SELECT r.*, u.display_name AS author_name, t.title AS thread_title
         FROM $table r
         LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
         LEFT JOIN {$wpdb->prefix}cnw_social_worker_threads t ON r.thread_id = t.id
         $where_sql ORDER BY r.created_at DESC LIMIT %d OFFSET %d";
    $query_params = array_merge( $params, array( $per_page, $offset ) );
    $rows = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );

    $status_counts = array(
        'all'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" ),
        'pending'  => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'pending' ) ),
        'approved' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'approved' ) ),
        'rejected' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'rejected' ) ),
        'spam'     => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", 'spam' ) ),
    );

    cnw_admin_search_box( 'cnw-replies', $search );
    ?>

    <!-- Status filter tabs -->
    <ul class="subsubsub" style="margin:8px 0;">
        <?php
        $base_url = admin_url( 'admin.php?page=cnw-replies' );
        if ( $search ) $base_url = add_query_arg( 's', $search, $base_url );
        $filters = array(
            ''         => array( 'All', $status_counts['all'] ),
            'pending'  => array( 'Pending', $status_counts['pending'] ),
            'approved' => array( 'Approved', $status_counts['approved'] ),
            'rejected' => array( 'Rejected', $status_counts['rejected'] ),
            'spam'     => array( 'Spam', $status_counts['spam'] ),
        );
        $links = array();
        foreach ( $filters as $fval => $fdata ) {
            $url   = $fval ? add_query_arg( 'status', $fval, $base_url ) : $base_url;
            $cls   = ( $filter_status === $fval ) ? ' class="current"' : '';
            $links[] = sprintf( '<li><a href="%s"%s>%s <span class="count">(%s)</span></a></li>', esc_url( $url ), $cls, esc_html( $fdata[0] ), number_format( $fdata[1] ) );
        }
        echo implode( ' | ', $links );
        ?>
    </ul>
    <div style="clear:both;"></div>

    <?php cnw_admin_pagination( 'cnw-replies', $paged, $total_pages, $total, $search ); ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_replies' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_replies">

        <div class="cnw-bulk-bar">
            <select name="bulk_action">
                <option value="">Bulk Actions</option>
                <option value="approve">Approve</option>
                <option value="reject">Reject</option>
                <option value="pending">Set Pending</option>
                <option value="spam">Mark as Spam</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="button">Apply</button>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead><tr>
                <th class="cnw-cb-col"><input type="checkbox" class="cnw-select-all"></th>
                <th style="width:40px">ID</th><th style="width:180px">Thread</th><th>Reply</th><th style="width:140px">Author</th><th style="width:60px">Parent</th><th style="width:90px">Status</th><th style="width:150px">Created</th><th style="width:220px">Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $row ) : ?>
                <tr>
                    <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></td>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->thread_title ); ?></td>
                    <td><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $row->content ), 12, '...' ) ); ?></td>
                    <td><?php echo esc_html( $row->author_name ); ?></td>
                    <td><?php echo $row->parent_id ? esc_html( $row->parent_id ) : '—'; ?></td>
                    <td><span class="cnw-status cnw-status-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( ucfirst( $row->status ) ); ?></span></td>
                    <td><?php echo esc_html( $row->created_at ); ?></td>
                    <td>
                        <?php if ( $row->status !== 'approved' ) : ?>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_quick_status_reply&id=' . $row->id . '&status=approved' ), 'cnw_quick_status_reply' ) ); ?>" style="color:#22a55b;font-weight:600;">Approve</a> |
                        <?php endif; ?>
                        <?php if ( ! in_array( $row->status, array( 'rejected', 'spam' ), true ) ) : ?>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_quick_status_reply&id=' . $row->id . '&status=rejected' ), 'cnw_quick_status_reply' ) ); ?>" style="color:#d63638;font-weight:600;">Reject</a> |
                        <?php endif; ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-replies&action=edit&id=' . $row->id ) ); ?>">Edit</a> |
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_reply&id=' . $row->id ), 'cnw_delete_reply' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this reply?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="9">No replies found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <?php cnw_admin_pagination( 'cnw-replies', $paged, $total_pages, $total, $search ); ?>
<?php endif; ?>
</div>
