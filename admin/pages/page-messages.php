<?php
/**
 * Admin Messages — CRUD list / add / edit with bulk actions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_messages';
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
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Messages', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>Message saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Message deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> message(s) deleted.</p></div><?php endif; ?>

<?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <h2><?php echo $item ? 'Edit Message #' . esc_html( $item->id ) : 'Add New Message'; ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_message' ); ?>
        <input type="hidden" name="action" value="cnw_save_message">
        <?php if ( $item ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><label for="sender_id">Sender</label></th>
                <td><select id="sender_id" name="sender_id" required>
                    <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $item->sender_id ?? get_current_user_id(), $u->ID ); ?>><?php echo esc_html( $u->display_name . ' (#' . $u->ID . ')' ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="recipient_id">Recipient</label></th>
                <td><select id="recipient_id" name="recipient_id" required>
                    <option value="">-- Select --</option>
                    <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $item->recipient_id ?? '', $u->ID ); ?>><?php echo esc_html( $u->display_name . ' (#' . $u->ID . ')' ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="subject">Subject</label></th>
                <td><input type="text" id="subject" name="subject" class="regular-text" value="<?php echo esc_attr( $item->subject ?? '' ); ?>"></td></tr>
            <tr><th><label for="content">Content</label></th>
                <td><textarea id="content" name="content" rows="5" class="large-text" required><?php echo esc_textarea( $item->content ?? '' ); ?></textarea></td></tr>
            <tr><th><label for="is_read">Read?</label></th>
                <td><select id="is_read" name="is_read">
                    <option value="0" <?php selected( $item->is_read ?? 0, 0 ); ?>>Unread</option>
                    <option value="1" <?php selected( $item->is_read ?? 0, 1 ); ?>>Read</option>
                </select></td></tr>
            <tr><th><label for="parent_id">Parent Message ID</label></th>
                <td><input type="number" id="parent_id" name="parent_id" value="<?php echo esc_attr( $item->parent_id ?? '' ); ?>" min="0"><br><small>Leave 0 or empty for new conversation.</small></td></tr>
        </table>

        <?php submit_button( $item ? 'Update Message' : 'Create Message' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-messages' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-messages&action=add' ) ); ?>" class="button button-primary">Add New Message</a></p>

    <?php
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $per_page = 20;
    $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $where = '';
    $params = array();
    if ( $search ) {
        $like   = '%' . $wpdb->esc_like( $search ) . '%';
        $where  = 'WHERE m.subject LIKE %s OR m.content LIKE %s OR s.display_name LIKE %s OR r.display_name LIKE %s';
        $params = array( $like, $like, $like, $like );
    }

    $total_query = "SELECT COUNT(*) FROM $table m LEFT JOIN {$wpdb->users} s ON m.sender_id = s.ID LEFT JOIN {$wpdb->users} r ON m.recipient_id = r.ID $where";
    $total       = $search ? (int) $wpdb->get_var( $wpdb->prepare( $total_query, ...$params ) ) : (int) $wpdb->get_var( $total_query );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $paged       = min( $paged, $total_pages );

    $query = "SELECT m.*, s.display_name AS sender_name, r.display_name AS recipient_name
         FROM $table m
         LEFT JOIN {$wpdb->users} s ON m.sender_id = s.ID
         LEFT JOIN {$wpdb->users} r ON m.recipient_id = r.ID
         $where ORDER BY m.id DESC LIMIT %d OFFSET %d";
    $query_params = $search ? array_merge( $params, array( $per_page, $offset ) ) : array( $per_page, $offset );
    $rows = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );

    cnw_admin_search_box( 'cnw-messages', $search );
    cnw_admin_pagination( 'cnw-messages', $paged, $total_pages, $total, $search );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_messages' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_messages">

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
                <th style="width:40px">ID</th><th style="width:160px">From</th><th style="width:160px">To</th><th style="width:30%">Message</th><th style="width:60px">Read</th><th style="width:150px">Created</th><th style="width:130px">Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $row ) : ?>
                <tr>
                    <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></td>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->sender_name . ' (#' . $row->sender_id . ')' ); ?></td>
                    <td><?php echo esc_html( $row->recipient_name . ' (#' . $row->recipient_id . ')' ); ?></td>
                    <td><?php echo esc_html( $row->content ); ?></td>
                    <td><?php echo $row->is_read ? '<span style="color:green">Yes</span>' : '<span style="color:#999">No</span>'; ?></td>
                    <td><?php echo esc_html( $row->created_at ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-messages&action=edit&id=' . $row->id ) ); ?>">Edit</a> |
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_message&id=' . $row->id ), 'cnw_delete_message' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this message?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="8">No messages found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <?php cnw_admin_pagination( 'cnw-messages', $paged, $total_pages, $total, $search ); ?>
<?php endif; ?>
</div>
