<?php
/**
 * Admin Settings page — Pusher, shortcode, role info.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$msg = sanitize_text_field( $_GET['msg'] ?? '' );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Settings', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'Platform Settings', 'cnw-social-bridge' ); ?></p>
    </div>

    <?php if ( $msg === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'cnw-social-bridge' ); ?></p></div>
    <?php endif; ?>

    <div class="cnw-section">
        <div class="cnw-section-heading">
            <h2 class="cnw-section-title"><?php esc_html_e( 'Threads', 'cnw-social-bridge' ); ?></h2>
            <p class="cnw-section-desc"><?php esc_html_e( 'Configure default behaviour for new threads.', 'cnw-social-bridge' ); ?></p>
        </div>

        <div class="cnw-settings-card">
            <div class="cnw-settings-card-header">
                <span class="dashicons dashicons-format-chat cnw-card-icon"></span>
                <div>
                    <h3 class="cnw-settings-card-title"><?php esc_html_e( 'Default Thread Status', 'cnw-social-bridge' ); ?></h3>
                    <p class="cnw-settings-card-desc"><?php esc_html_e( 'The status assigned to new threads when a user submits a question from the frontend.', 'cnw-social-bridge' ); ?></p>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-pusher-form">
                <?php wp_nonce_field( 'cnw_save_thread_settings' ); ?>
                <input type="hidden" name="action" value="cnw_save_thread_settings">

                <table class="form-table cnw-form-table">
                    <tr>
                        <th scope="row">
                            <label for="cnw_default_thread_status"><?php esc_html_e( 'Default Status', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <?php $current_status = get_option( 'cnw_default_thread_status', 'pending' ); ?>
                            <select id="cnw_default_thread_status" name="cnw_default_thread_status">
                                <option value="pending" <?php selected( $current_status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'cnw-social-bridge' ); ?></option>
                                <option value="approved" <?php selected( $current_status, 'approved' ); ?>><?php esc_html_e( 'Approved', 'cnw-social-bridge' ); ?></option>
                                <option value="rejected" <?php selected( $current_status, 'rejected' ); ?>><?php esc_html_e( 'Rejected', 'cnw-social-bridge' ); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e( 'Set to "Pending" to require admin approval before threads are visible. Set to "Approved" to publish immediately.', 'cnw-social-bridge' ); ?></p>
                        </td>
                    </tr>
                </table>

                <div class="cnw-form-footer">
                    <?php submit_button( __( 'Save Thread Settings', 'cnw-social-bridge' ), 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>
    </div>

    <div class="cnw-section">
        <div class="cnw-section-heading">
            <h2 class="cnw-section-title"><?php esc_html_e( 'Replies', 'cnw-social-bridge' ); ?></h2>
            <p class="cnw-section-desc"><?php esc_html_e( 'Configure default behaviour for new replies.', 'cnw-social-bridge' ); ?></p>
        </div>

        <div class="cnw-settings-card">
            <div class="cnw-settings-card-header">
                <span class="dashicons dashicons-admin-comments cnw-card-icon"></span>
                <div>
                    <h3 class="cnw-settings-card-title"><?php esc_html_e( 'Default Reply Status', 'cnw-social-bridge' ); ?></h3>
                    <p class="cnw-settings-card-desc"><?php esc_html_e( 'The status assigned to new replies when a user posts a reply from the frontend.', 'cnw-social-bridge' ); ?></p>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-pusher-form">
                <?php wp_nonce_field( 'cnw_save_reply_settings' ); ?>
                <input type="hidden" name="action" value="cnw_save_reply_settings">

                <table class="form-table cnw-form-table">
                    <tr>
                        <th scope="row">
                            <label for="cnw_default_reply_status"><?php esc_html_e( 'Default Status', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <?php $current_reply_status = get_option( 'cnw_default_reply_status', 'approved' ); ?>
                            <select id="cnw_default_reply_status" name="cnw_default_reply_status">
                                <option value="pending" <?php selected( $current_reply_status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'cnw-social-bridge' ); ?></option>
                                <option value="approved" <?php selected( $current_reply_status, 'approved' ); ?>><?php esc_html_e( 'Approved', 'cnw-social-bridge' ); ?></option>
                                <option value="rejected" <?php selected( $current_reply_status, 'rejected' ); ?>><?php esc_html_e( 'Rejected', 'cnw-social-bridge' ); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e( 'Set to "Pending" to require admin approval before replies are visible. Set to "Approved" to publish immediately.', 'cnw-social-bridge' ); ?></p>
                        </td>
                    </tr>
                </table>

                <div class="cnw-form-footer">
                    <?php submit_button( __( 'Save Reply Settings', 'cnw-social-bridge' ), 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>
    </div>

    <div class="cnw-section">
        <div class="cnw-section-heading">
            <h2 class="cnw-section-title"><?php esc_html_e( 'Pusher', 'cnw-social-bridge' ); ?></h2>
            <p class="cnw-section-desc"><?php esc_html_e( 'Configure Pusher credentials for real-time messaging and notifications.', 'cnw-social-bridge' ); ?></p>
        </div>

        <div class="cnw-settings-card">
            <div class="cnw-settings-card-header">
                <span class="dashicons dashicons-controls-repeat cnw-card-icon"></span>
                <div>
                    <h3 class="cnw-settings-card-title"><?php esc_html_e( 'Pusher Credentials', 'cnw-social-bridge' ); ?></h3>
                    <p class="cnw-settings-card-desc"><?php esc_html_e( 'Enter your Pusher app credentials. You can find these in your Pusher dashboard.', 'cnw-social-bridge' ); ?></p>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-pusher-form">
                <?php wp_nonce_field( 'cnw_save_pusher' ); ?>
                <input type="hidden" name="action" value="cnw_save_pusher">

                <table class="form-table cnw-form-table">
                    <tr>
                        <th scope="row">
                            <label for="cnw_pusher_host"><?php esc_html_e( 'Host', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="cnw_pusher_host" name="cnw_pusher_host"
                                   value="<?php echo esc_attr( get_option( 'cnw_pusher_host', '' ) ); ?>"
                                   class="regular-text" placeholder="ws.example.com" autocomplete="off" />
                            <p class="description"><?php esc_html_e( 'For self-hosted (Soketi/Laravel WebSockets). Leave empty for Pusher.com.', 'cnw-social-bridge' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cnw_pusher_port"><?php esc_html_e( 'Port', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="cnw_pusher_port" name="cnw_pusher_port"
                                   value="<?php echo esc_attr( get_option( 'cnw_pusher_port', '443' ) ); ?>"
                                   class="regular-text" placeholder="443" autocomplete="off" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cnw_pusher_cluster"><?php esc_html_e( 'Cluster', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="cnw_pusher_cluster" name="cnw_pusher_cluster"
                                   value="<?php echo esc_attr( get_option( 'cnw_pusher_cluster', 'mt1' ) ); ?>"
                                   class="regular-text" placeholder="mt1" autocomplete="off" />
                            <p class="description"><?php esc_html_e( 'e.g. mt1, us2, eu, ap1, ap2. Used only for Pusher.com.', 'cnw-social-bridge' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cnw_pusher_app_id"><?php esc_html_e( 'App ID', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="cnw_pusher_app_id" name="cnw_pusher_app_id"
                                   value="<?php echo esc_attr( get_option( 'cnw_pusher_app_id', '' ) ); ?>"
                                   class="regular-text" placeholder="123456" autocomplete="off" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cnw_pusher_key"><?php esc_html_e( 'App Key', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="cnw_pusher_key" name="cnw_pusher_key"
                                   value="<?php echo esc_attr( get_option( 'cnw_pusher_key', '' ) ); ?>"
                                   class="regular-text" placeholder="a1b2c3d4e5f6g7h8i9j0" autocomplete="off" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cnw_pusher_secret"><?php esc_html_e( 'App Secret', 'cnw-social-bridge' ); ?></label>
                        </th>
                        <td>
                            <input type="password" id="cnw_pusher_secret" name="cnw_pusher_secret"
                                   value="<?php echo esc_attr( get_option( 'cnw_pusher_secret', '' ) ); ?>"
                                   class="regular-text" placeholder="••••••••••••••••" autocomplete="off" />
                        </td>
                    </tr>
                </table>

                <div class="cnw-form-footer">
                    <?php submit_button( __( 'Save Pusher Settings', 'cnw-social-bridge' ), 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>
    </div>

    <div class="cnw-section">
        <div class="cnw-section-heading">
            <h2 class="cnw-section-title"><?php esc_html_e( 'Shortcode', 'cnw-social-bridge' ); ?></h2>
            <p class="cnw-section-desc"><?php esc_html_e( 'Use the shortcode below to display the Social Bridge forum on any page or post.', 'cnw-social-bridge' ); ?></p>
        </div>

        <div class="cnw-settings-card">
            <div class="cnw-settings-card-header">
                <span class="dashicons dashicons-shortcode cnw-card-icon"></span>
                <div>
                    <h3 class="cnw-settings-card-title"><?php esc_html_e( 'Forum Shortcode', 'cnw-social-bridge' ); ?></h3>
                    <p class="cnw-settings-card-desc"><?php esc_html_e( 'Copy and paste this shortcode into any page or post to display the forum dashboard.', 'cnw-social-bridge' ); ?></p>
                </div>
            </div>
            <div class="cnw-shortcode-box">
                <code id="cnw-shortcode-value" class="cnw-shortcode-code">[cnw_social_bridge]</code>
                <button type="button" id="cnw-copy-shortcode" class="button button-secondary cnw-btn-copy">
                    <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy', 'cnw-social-bridge' ); ?>
                </button>
            </div>
        </div>
    </div>

    <div class="cnw-settings-info">
        <h2><?php esc_html_e( 'User Roles', 'cnw-social-bridge' ); ?></h2>
        <p><?php esc_html_e( 'The following custom roles are available:', 'cnw-social-bridge' ); ?></p>
        <ul>
            <li><strong><?php esc_html_e( 'Forum Member', 'cnw-social-bridge' ); ?></strong> — <?php esc_html_e( 'Can create threads, reply, and send messages.', 'cnw-social-bridge' ); ?></li>
            <li><strong><?php esc_html_e( 'Moderator', 'cnw-social-bridge' ); ?></strong> — <?php esc_html_e( 'Can moderate content, close/pin threads, and warn users.', 'cnw-social-bridge' ); ?></li>
            <li><strong><?php esc_html_e( 'Forum Admin', 'cnw-social-bridge' ); ?></strong> — <?php esc_html_e( 'Full forum management including bans and settings.', 'cnw-social-bridge' ); ?></li>
        </ul>
    </div>
</div>
