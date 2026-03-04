<?php
/**
 * Admin Tags — CRUD list / add / edit with bulk actions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_tags';
$action = sanitize_text_field( $_GET['action'] ?? 'list' );
$id     = intval( $_GET['id'] ?? 0 );
$msg    = sanitize_text_field( $_GET['msg'] ?? '' );
$count  = intval( $_GET['count'] ?? 0 );

$item = null;
if ( $action === 'edit' && $id ) {
    $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    if ( ! $item ) { $action = 'list'; }
}
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Tags', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>Tag saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Tag deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> tag(s) deleted.</p></div><?php endif; ?>

<?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <h2><?php echo $item ? 'Edit Tag #' . esc_html( $item->id ) : 'Add New Tag'; ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_tag' ); ?>
        <input type="hidden" name="action" value="cnw_save_tag">
        <?php if ( $item ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><label for="name">Name</label></th>
                <td><input type="text" id="name" name="name" class="regular-text" value="<?php echo esc_attr( $item->name ?? '' ); ?>" required></td></tr>
            <tr><th><label for="slug">Slug</label></th>
                <td><input type="text" id="slug" name="slug" class="regular-text" value="<?php echo esc_attr( $item->slug ?? '' ); ?>"><br><small>Auto-generated from name if empty.</small></td></tr>
            <tr><th><label for="description">Description</label></th>
                <td><textarea id="description" name="description" class="large-text" rows="4"><?php echo esc_textarea( $item->description ?? '' ); ?></textarea><br><small>Optional. Brief description of what this tag covers.</small></td></tr>
        </table>

        <?php submit_button( $item ? 'Update Tag' : 'Create Tag' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-tags' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-tags&action=add' ) ); ?>" class="button button-primary">Add New Tag</a></p>

    <?php
    $thread_tags_table = $wpdb->prefix . 'cnw_social_worker_thread_tags';
    $followed_table    = $wpdb->prefix . 'cnw_social_worker_user_followed_tags';

    $rows = $wpdb->get_results(
        "SELECT t.*,
            (SELECT COUNT(*) FROM $thread_tags_table tt WHERE tt.tag_id = t.id) AS thread_count,
            (SELECT COUNT(*) FROM $followed_table uf WHERE uf.tag_id = t.id) AS follower_count
         FROM $table t ORDER BY t.name ASC"
    );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_tags' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_tags">

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
                <th style="width:40px">ID</th><th>Name</th><th style="width:150px">Slug</th><th>Description</th><th style="width:80px">Threads</th><th style="width:80px">Followers</th><th style="width:140px">Created</th><th style="width:130px">Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $row ) : ?>
                <tr>
                    <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></td>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><strong><?php echo esc_html( $row->name ); ?></strong></td>
                    <td><?php echo esc_html( $row->slug ); ?></td>
                    <td><?php echo esc_html( $row->description ? wp_trim_words( $row->description, 12, '…' ) : '—' ); ?></td>
                    <td><?php echo esc_html( $row->thread_count ); ?></td>
                    <td><?php echo esc_html( $row->follower_count ); ?></td>
                    <td><?php echo esc_html( $row->created_at ? date( 'M j, Y', strtotime( $row->created_at ) ) : '—' ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-tags&action=edit&id=' . $row->id ) ); ?>">Edit</a> |
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_tag&id=' . $row->id ), 'cnw_delete_tag' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this tag?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="9">No tags found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>
<?php endif; ?>
</div>
