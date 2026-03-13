<?php
/**
 * Admin Categories — CRUD list / add / edit with bulk actions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table  = $wpdb->prefix . 'cnw_social_worker_categories';
$action = sanitize_text_field( $_GET['action'] ?? 'list' );
$id     = intval( $_GET['id'] ?? 0 );
$msg    = sanitize_text_field( $_GET['msg'] ?? '' );
$count  = intval( $_GET['count'] ?? 0 );

$item = null;
if ( $action === 'edit' && $id ) {
    $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    if ( ! $item ) { $action = 'list'; }
}

$parent_cats = $wpdb->get_results( "SELECT id, name FROM $table ORDER BY name ASC" );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Categories', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>Category saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>Category deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> category(ies) deleted.</p></div><?php endif; ?>

<?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <h2><?php echo $item ? 'Edit Category #' . esc_html( $item->id ) : 'Add New Category'; ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_category' ); ?>
        <input type="hidden" name="action" value="cnw_save_category">
        <?php if ( $item ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $item->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><label for="name">Name</label></th>
                <td><input type="text" id="name" name="name" class="regular-text" value="<?php echo esc_attr( $item->name ?? '' ); ?>" required></td></tr>
            <tr><th><label for="slug">Slug</label></th>
                <td><input type="text" id="slug" name="slug" class="regular-text" value="<?php echo esc_attr( $item->slug ?? '' ); ?>"><br><small>Auto-generated from name if empty.</small></td></tr>
            <tr><th><label for="description">Description</label></th>
                <td><textarea id="description" name="description" rows="3" class="large-text"><?php echo esc_textarea( $item->description ?? '' ); ?></textarea></td></tr>
            <tr><th><label for="parent_id">Parent Category</label></th>
                <td><select id="parent_id" name="parent_id">
                    <option value="0">None (top-level)</option>
                    <?php foreach ( $parent_cats as $pc ) : if ( $item && $pc->id == $item->id ) continue; ?>
                    <option value="<?php echo esc_attr( $pc->id ); ?>" <?php selected( $item->parent_id ?? 0, $pc->id ); ?>><?php echo esc_html( $pc->name ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="icon">Icon</label></th>
                <td><input type="text" id="icon" name="icon" class="regular-text" value="<?php echo esc_attr( $item->icon ?? '' ); ?>"><br><small>Dashicon class e.g. dashicons-admin-home</small></td></tr>
            <tr><th><label for="color">Color</label></th>
                <td><input type="color" id="color" name="color" value="<?php echo esc_attr( $item->color ?? '#3bbdd4' ); ?>"></td></tr>
            <tr><th><label for="sort_order">Sort Order</label></th>
                <td><input type="number" id="sort_order" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>" min="0"></td></tr>
            <tr><th><label for="is_active">Active</label></th>
                <td><select id="is_active" name="is_active">
                    <option value="1" <?php selected( $item->is_active ?? 1, 1 ); ?>>Yes</option>
                    <option value="0" <?php selected( $item->is_active ?? 1, 0 ); ?>>No</option>
                </select></td></tr>
        </table>

        <?php submit_button( $item ? 'Update Category' : 'Create Category' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-categories' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-categories&action=add' ) ); ?>" class="button button-primary">Add New Category</a></p>

    <?php
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $per_page = 20;
    $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $where = '';
    $params = array();
    if ( $search ) {
        $like   = '%' . $wpdb->esc_like( $search ) . '%';
        $where  = 'WHERE c.name LIKE %s OR c.slug LIKE %s OR c.description LIKE %s';
        $params = array( $like, $like, $like );
    }

    $total_query = "SELECT COUNT(*) FROM $table c $where";
    $total       = $search ? (int) $wpdb->get_var( $wpdb->prepare( $total_query, ...$params ) ) : (int) $wpdb->get_var( $total_query );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $paged       = min( $paged, $total_pages );

    $query = "SELECT c.*, p.name AS parent_name FROM $table c LEFT JOIN $table p ON c.parent_id = p.id $where ORDER BY c.sort_order ASC, c.name ASC LIMIT %d OFFSET %d";
    $query_params = $search ? array_merge( $params, array( $per_page, $offset ) ) : array( $per_page, $offset );
    $rows = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );

    cnw_admin_search_box( 'cnw-categories', $search );
    cnw_admin_pagination( 'cnw-categories', $paged, $total_pages, $total, $search );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_categories' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_categories">

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
                <th style="width:40px">ID</th><th>Name</th><th style="width:120px">Slug</th><th style="width:120px">Parent</th><th style="width:60px">Order</th><th style="width:60px">Active</th><th style="width:60px">Color</th><th style="width:130px">Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $row ) : ?>
                <tr>
                    <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="cnw-bulk-cb"></td>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><strong><?php echo esc_html( $row->name ); ?></strong></td>
                    <td><?php echo esc_html( $row->slug ); ?></td>
                    <td><?php echo $row->parent_name ? esc_html( $row->parent_name ) : '—'; ?></td>
                    <td><?php echo esc_html( $row->sort_order ); ?></td>
                    <td><?php echo $row->is_active ? '<span style="color:green">Yes</span>' : '<span style="color:#999">No</span>'; ?></td>
                    <td><?php if ( $row->color ) : ?><span style="display:inline-block;width:20px;height:20px;border-radius:3px;background:<?php echo esc_attr( $row->color ); ?>"></span><?php else : ?>—<?php endif; ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-categories&action=edit&id=' . $row->id ) ); ?>">Edit</a> |
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_category&id=' . $row->id ), 'cnw_delete_category' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this category?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="9">No categories found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>

    <?php cnw_admin_pagination( 'cnw-categories', $paged, $total_pages, $total, $search ); ?>
<?php endif; ?>
</div>
