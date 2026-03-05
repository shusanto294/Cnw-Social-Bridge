<?php
/**
 * Admin Threads — CRUD list / add / edit with bulk actions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_threads';
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
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Threads', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>Thread saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Thread deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> thread(s) deleted.</p></div><?php endif; ?>

<?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <h2><?php echo $item ? 'Edit Thread #' . esc_html( $item->id ) : 'Add New Thread'; ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_thread' ); ?>
        <input type="hidden" name="action" value="cnw_save_thread">
        <?php if ( $item ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><label for="title">Title</label></th>
                <td><input type="text" id="title" name="title" class="large-text" value="<?php echo esc_attr( $item->title ?? '' ); ?>" required></td></tr>
            <tr><th><label for="content">Content</label></th>
                <td><textarea id="content" name="content" rows="6" class="large-text"><?php echo esc_textarea( $item->content ?? '' ); ?></textarea></td></tr>
            <tr><th><label for="author_id">Author</label></th>
                <td><select id="author_id" name="author_id">
                    <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $item->author_id ?? get_current_user_id(), $u->ID ); ?>><?php echo esc_html( $u->display_name . ' (#' . $u->ID . ')' ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="status">Status</label></th>
                <td><select id="status" name="status">
                    <?php foreach ( array( 'published', 'draft', 'closed' ) as $s ) : ?>
                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $item->status ?? 'published', $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
        </table>

        <?php submit_button( $item ? 'Update Thread' : 'Create Thread' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-threads' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-threads&action=add' ) ); ?>" class="button button-primary">Add New Thread</a></p>

    <?php
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $per_page = 20;
    $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $where = '';
    $params = array();
    if ( $search ) {
        $like   = '%' . $wpdb->esc_like( $search ) . '%';
        $where  = 'WHERE t.title LIKE %s OR t.content LIKE %s OR u.display_name LIKE %s';
        $params = array( $like, $like, $like );
    }

    $total_query = "SELECT COUNT(*) FROM $table t LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID $where";
    $total       = $search ? (int) $wpdb->get_var( $wpdb->prepare( $total_query, ...$params ) ) : (int) $wpdb->get_var( $total_query );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $paged       = min( $paged, $total_pages );

    $query = "SELECT t.*, u.display_name AS author_name
         FROM $table t LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
         $where ORDER BY t.created_at DESC LIMIT %d OFFSET %d";
    $query_params = $search ? array_merge( $params, array( $per_page, $offset ) ) : array( $per_page, $offset );
    $rows = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );

    cnw_admin_search_box( 'cnw-threads', $search );
    cnw_admin_pagination( 'cnw-threads', $paged, $total_pages, $total, $search );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_threads' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_threads">

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
                <th style="width:40px">ID</th><th>Title</th><th style="width:140px">Author</th><th style="width:90px">Status</th><th style="width:70px">Views</th><th style="width:150px">Created</th><th style="width:130px">Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $row ) : ?>
                <tr>
                    <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></td>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->title ); ?></td>
                    <td><?php echo esc_html( $row->author_name ); ?></td>
                    <td><span class="cnw-status cnw-status-<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( ucfirst( $row->status ) ); ?></span></td>
                    <td><?php echo number_format( intval( $row->views ) ); ?></td>
                    <td><?php echo esc_html( $row->created_at ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-threads&action=edit&id=' . $row->id ) ); ?>">Edit</a> |
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_thread&id=' . $row->id ), 'cnw_delete_thread' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this thread and all its replies?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="8">No threads found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <?php cnw_admin_pagination( 'cnw-threads', $paged, $total_pages, $total, $search ); ?>
<?php endif; ?>
</div>
