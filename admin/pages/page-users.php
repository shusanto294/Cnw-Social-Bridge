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

$forum_roles = array(
    'cnw_forum_member' => 'Forum Member',
    'cnw_moderator'    => 'Moderator',
    'cnw_forum_admin'  => 'Forum Admin',
);
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Forum Users', 'cnw-social-bridge' ); ?></h1>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-users&action=add' ) ); ?>" class="page-title-action">Add New User</a>
    </div>

    <?php if ( $msg === 'saved' ) : ?><div class="notice notice-success is-dismissible"><p>User saved.</p></div><?php endif; ?>
    <?php if ( $msg === 'password_updated' ) : ?><div class="notice notice-success is-dismissible"><p>Password updated successfully.</p></div><?php endif; ?>
    <?php if ( $msg === 'created' ) : ?><div class="notice notice-success is-dismissible"><p>New user created successfully.</p></div><?php endif; ?>
    <?php if ( $msg === 'deleted' ) : ?><div class="notice notice-warning is-dismissible"><p>User deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'bulk_deleted' && $count ) : ?><div class="notice notice-warning is-dismissible"><p><?php echo esc_html( $count ); ?> user(s) deleted.</p></div><?php endif; ?>
    <?php if ( $msg === 'error_create' ) : ?><div class="notice notice-error is-dismissible"><p>Error creating user. The username or email may already exist.</p></div><?php endif; ?>
    <?php if ( $msg === 'error_password' ) : ?><div class="notice notice-error is-dismissible"><p>Passwords do not match.</p></div><?php endif; ?>

<?php if ( $action === 'add' ) : ?>
    <h2>Add New User</h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-crud-form">
        <?php wp_nonce_field( 'cnw_create_user' ); ?>
        <input type="hidden" name="action" value="cnw_create_user">

        <table class="form-table">
            <tr><th><label for="display_name">Display Name</label></th>
                <td><input type="text" id="display_name" name="display_name" class="regular-text" required></td></tr>
            <tr><th><label for="user_email">Email</label></th>
                <td><input type="email" id="user_email" name="user_email" class="regular-text" required></td></tr>
            <tr><th><label for="first_name">First Name</label></th>
                <td><input type="text" id="first_name" name="first_name" class="regular-text"></td></tr>
            <tr><th><label for="last_name">Last Name</label></th>
                <td><input type="text" id="last_name" name="last_name" class="regular-text"></td></tr>
            <tr><th><label for="cnw_phone">Phone</label></th>
                <td><input type="tel" id="cnw_phone" name="cnw_phone" class="regular-text"></td></tr>
            <tr><th><label for="cnw_verified_label">Verified Label</label></th>
                <td><input type="text" id="cnw_verified_label" name="cnw_verified_label" class="regular-text" value="Verified Social Worker">
                <p class="description">Text shown next to the verified badge (e.g. "Verified Social Worker").</p></td></tr>
            <tr><th><label for="cnw_professional_title">Professional Title</label></th>
                <td><input type="text" id="cnw_professional_title" name="cnw_professional_title" class="regular-text" value="Licensed Clinical Social Worker">
                <p class="description">Professional title shown on the profile (e.g. "Licensed Clinical Social Worker").</p></td></tr>
            <tr><th><label>Profile Photo</label></th>
                <td>
                    <div style="margin-bottom:10px;">
                        <img id="cnw-avatar-preview" src="<?php echo esc_url( CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR ); ?>" style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                    </div>
                    <input type="hidden" id="cnw_avatar_url" name="cnw_avatar_url" value="">
                    <button type="button" id="cnw-avatar-upload" class="button">Choose Photo</button>
                    <button type="button" id="cnw-avatar-remove" class="button" style="margin-left:5px;display:none;">Remove Photo</button>
                    <p class="description">Upload a custom profile photo or use the default Gravatar.</p>
                </td></tr>
            <tr><th><label for="role">Role</label></th>
                <td><select id="role" name="role">
                    <?php
                    $roles = wp_roles()->get_names();
                    foreach ( $roles as $role_key => $role_name ) : ?>
                    <option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( 'cnw_forum_member', $role_key ); ?>><?php echo esc_html( $role_name ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><label for="user_login">Username</label></th>
                <td><input type="text" id="user_login" name="user_login" class="regular-text" required></td></tr>
            <tr><th><label for="user_pass">Password</label></th>
                <td><input type="password" id="user_pass" name="user_pass" class="regular-text" required autocomplete="new-password">
                <p class="description">Set the initial password for this user.</p></td></tr>
            <tr><th><label for="user_pass_confirm">Confirm Password</label></th>
                <td><input type="password" id="user_pass_confirm" name="user_pass_confirm" class="regular-text" required autocomplete="new-password"></td></tr>
        </table>

        <?php submit_button( 'Create User' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=cnw-users' ) ); ?>" class="button">&larr; Back to list</a>
    </form>

<?php elseif ( $action === 'edit' && $item ) : ?>
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
            <?php $phone = get_user_meta( $item->ID, 'cnw_phone', true ); ?>
            <tr><th><label for="cnw_phone">Phone</label></th>
                <td><input type="tel" id="cnw_phone" name="cnw_phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>"></td></tr>
            <?php $verified_label = get_user_meta( $item->ID, 'cnw_verified_label', true ); ?>
            <tr><th><label for="cnw_verified_label">Verified Label</label></th>
                <td><input type="text" id="cnw_verified_label" name="cnw_verified_label" class="regular-text" value="<?php echo esc_attr( $verified_label ?: 'Verified Social Worker' ); ?>">
                <p class="description">Text shown next to the verified badge (e.g. "Verified Social Worker").</p></td></tr>
            <?php $professional_title = get_user_meta( $item->ID, 'cnw_professional_title', true ); ?>
            <tr><th><label for="cnw_professional_title">Professional Title</label></th>
                <td><input type="text" id="cnw_professional_title" name="cnw_professional_title" class="regular-text" value="<?php echo esc_attr( $professional_title ?: 'Licensed Clinical Social Worker' ); ?>">
                <p class="description">Professional title shown on the profile (e.g. "Licensed Clinical Social Worker").</p></td></tr>
            <?php $avatar_url = get_user_meta( $item->ID, 'cnw_avatar_url', true ); ?>
            <tr><th><label>Profile Photo</label></th>
                <td>
                    <div style="margin-bottom:10px;">
                        <img id="cnw-avatar-preview" src="<?php echo esc_url( $avatar_url ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR ); ?>" style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                    </div>
                    <input type="hidden" id="cnw_avatar_url" name="cnw_avatar_url" value="<?php echo esc_attr( $avatar_url ); ?>">
                    <button type="button" id="cnw-avatar-upload" class="button">Choose Photo</button>
                    <?php if ( $avatar_url ) : ?>
                        <button type="button" id="cnw-avatar-remove" class="button" style="margin-left:5px;">Remove Photo</button>
                    <?php else : ?>
                        <button type="button" id="cnw-avatar-remove" class="button" style="margin-left:5px;display:none;">Remove Photo</button>
                    <?php endif; ?>
                    <p class="description">Upload a custom profile photo or use the default Gravatar.</p>
                </td></tr>
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

        <h3>Set New Password</h3>
        <p class="description" style="margin-bottom:10px;">Leave blank to keep the current password.</p>
        <table class="form-table">
            <tr><th><label for="new_password">New Password</label></th>
                <td><input type="password" id="new_password" name="new_password" class="regular-text" autocomplete="new-password"></td></tr>
            <tr><th><label for="new_password_confirm">Confirm New Password</label></th>
                <td><input type="password" id="new_password_confirm" name="new_password_confirm" class="regular-text" autocomplete="new-password"></td></tr>
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
