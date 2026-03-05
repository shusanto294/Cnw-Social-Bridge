<?php
/**
 * Admin Forum Users page.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$action = sanitize_text_field( $_GET['action'] ?? 'list' );
$id     = intval( $_GET['id'] ?? 0 );
$msg    = sanitize_text_field( $_GET['msg'] ?? '' );
$count  = intval( $_GET['count'] ?? 0 );

$item = null;
if ( $action === 'edit' && $id ) {
    $item = get_userdata( $id );
    if ( ! $item ) { $action = 'list'; }
}
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Forum Users', 'cnw-social-bridge' ); ?></h1>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>User saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>User deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> user(s) deleted.</p></div><?php endif; ?>

<?php if ( $action === 'edit' && $item ) : ?>
    <h2>Edit User #<?php echo esc_html( $item->ID ); ?></h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_save_user' ); ?>
        <input type="hidden" name="action" value="cnw_save_user">
        <input type="hidden" name="id" value="<?php echo esc_attr( $item->ID ); ?>">

        <table class="form-table">
            <tr><th><label for="display_name">Display Name</label></th>
                <td><input type="text" id="display_name" name="display_name" class="regular-text" value="<?php echo esc_attr( $item->display_name ); ?>" required></td></tr>
            <tr><th><label for="user_email">Email</label></th>
                <td><input type="email" id="user_email" name="user_email" class="regular-text" value="<?php echo esc_attr( $item->user_email ); ?>" required></td></tr>
            <tr><th><label for="first_name">First Name</label></th>
                <td><input type="text" id="first_name" name="first_name" class="regular-text" value="<?php echo esc_attr( $item->first_name ); ?>"></td></tr>
            <tr><th><label for="last_name">Last Name</label></th>
                <td><input type="text" id="last_name" name="last_name" class="regular-text" value="<?php echo esc_attr( $item->last_name ); ?>"></td></tr>
            <tr><th><label for="role">Role</label></th>
                <td><select id="role" name="role">
                    <?php
                    $roles = wp_roles()->get_names();
                    $current_role = ! empty( $item->roles ) ? $item->roles[0] : '';
                    foreach ( $roles as $role_key => $role_name ) : ?>
                    <option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $current_role, $role_key ); ?>><?php echo esc_html( $role_name ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th>Username</th>
                <td><code><?php echo esc_html( $item->user_login ); ?></code> <small>(cannot be changed)</small></td></tr>
            <tr><th>Registered</th>
                <td><?php echo esc_html( $item->user_registered ); ?></td></tr>
            <tr><th>Reputation</th>
                <td><strong><?php echo number_format( (int) get_user_meta( $item->ID, 'cnw_reputation_total', true ) ); ?></strong></td></tr>
        </table>

        <?php submit_button( 'Update User' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-users' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php else : ?>

    <?php
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $per_page = 20;
    $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $where = '';
    $params = array();
    if ( $search ) {
        $like   = '%' . $wpdb->esc_like( $search ) . '%';
        $where  = 'WHERE u.display_name LIKE %s OR u.user_email LIKE %s OR u.user_login LIKE %s';
        $params = array( $like, $like, $like );
    }

    $total_query = "SELECT COUNT(*) FROM {$wpdb->users} u $where";
    $total       = $search ? (int) $wpdb->get_var( $wpdb->prepare( $total_query, ...$params ) ) : (int) $wpdb->get_var( $total_query );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $paged       = min( $paged, $total_pages );

    $query = "SELECT u.ID, u.display_name, u.user_email, u.user_login, u.user_registered,
            (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE author_id = u.ID) AS threads_count,
            (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies  WHERE author_id = u.ID) AS replies_count
        FROM {$wpdb->users} u
        $where
        ORDER BY u.user_registered DESC
        LIMIT %d OFFSET %d";
    $query_params = $search ? array_merge( $params, array( $per_page, $offset ) ) : array( $per_page, $offset );
    $users = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );

    cnw_admin_search_box( 'cnw-users', $search );
    cnw_admin_pagination( 'cnw-users', $paged, $total_pages, $total, $search );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-bulk-form">
        <?php wp_nonce_field( 'cnw_bulk_users' ); ?>
        <input type="hidden" name="action" value="cnw_bulk_users">

        <div class="cnw-bulk-bar">
            <select name="bulk_action">
                <option value="">Bulk Actions</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="button">Apply</button>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="cnw-cb-col"><input type="checkbox" class="cnw-select-all"></th>
                    <th><?php esc_html_e( 'User', 'cnw-social-bridge' ); ?></th>
                    <th style="width:80px"><?php esc_html_e( 'Threads', 'cnw-social-bridge' ); ?></th>
                    <th style="width:80px"><?php esc_html_e( 'Replies', 'cnw-social-bridge' ); ?></th>
                    <th style="width:100px"><?php esc_html_e( 'Reputation', 'cnw-social-bridge' ); ?></th>
                    <th style="width:160px"><?php esc_html_e( 'Registered', 'cnw-social-bridge' ); ?></th>
                    <th style="width:130px"><?php esc_html_e( 'Actions', 'cnw-social-bridge' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $users ) : ?>
                    <?php foreach ( $users as $user ) : ?>
                    <tr>
                        <td class="cnw-cb-col"><input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $user->ID ); ?>" class="cnw-bulk-cb" <?php if ( $user->ID === get_current_user_id() ) echo 'disabled'; ?>></td>
                        <td>
                            <strong><?php echo esc_html( $user->display_name ); ?></strong><br>
                            <small><?php echo esc_html( $user->user_email ); ?></small>
                        </td>
                        <td><?php echo number_format( intval( $user->threads_count ) ); ?></td>
                        <td><?php echo number_format( intval( $user->replies_count ) ); ?></td>
                        <td><strong><?php echo number_format( (int) get_user_meta( $user->ID, 'cnw_reputation_total', true ) ); ?></strong></td>
                        <td><?php echo esc_html( $user->user_registered ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-users&action=edit&id=' . $user->ID ) ); ?>">Edit</a> |
                            <?php if ( $user->ID !== get_current_user_id() ) : ?>
                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cnw_delete_user&id=' . $user->ID ), 'cnw_delete_user' ) ); ?>" class="cnw-delete-link" onclick="return confirm('Delete this user and all their forum data? This cannot be undone.')">Delete</a>
                            <?php else : ?>
                                <span style="color:#999">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="7"><?php esc_html_e( 'No users found.', 'cnw-social-bridge' ); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>

    <?php cnw_admin_pagination( 'cnw-users', $paged, $total_pages, $total, $search ); ?>
<?php endif; ?>
</div>
