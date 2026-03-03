<?php
/**
 * Plugin Name: Cnw Social Bridge
 * Description: A social forum plugin with threads, replies, messages, and user roles.
 * Version: 1.0.0
 * Author: CNW
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cnw-social-bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Constants ────────────────────────────────────────────────────────────────
define( 'CNW_SOCIAL_BRIDGE_VERSION',    '1.0.0' );
define( 'CNW_SOCIAL_BRIDGE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CNW_SOCIAL_BRIDGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// ── Includes ─────────────────────────────────────────────────────────────────
require_once CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/class-cnw-admin.php';
require_once CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'includes/class-cnw-rest-api.php';

// ── Core plugin class ────────────────────────────────────────────────────────

class Cnw_Social_Bridge {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Lifecycle hooks
        register_activation_hook( __FILE__,   array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__,  array( $this, 'deactivate' ) );

        // Shortcode
        add_shortcode( 'cnw_social_bridge', array( $this, 'render_shortcode' ) );

        // Sub-modules
        new Cnw_Social_Bridge_Admin();
        new Cnw_Social_Bridge_REST_API();
    }

    /* ------------------------------------------------------------------
     * Activation — create DB tables and user roles
     * ------------------------------------------------------------------ */

    public function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql_threads = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_threads (
            id         bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            author_id  bigint(20) unsigned NOT NULL,
            title      varchar(255)        NOT NULL,
            content    longtext            NOT NULL,
            status     varchar(20)         DEFAULT 'published',
            views      int(11)             DEFAULT 0,
            created_at datetime            DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY author_id  (author_id),
            KEY status     (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        $sql_replies = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_replies (
            id         bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            thread_id  bigint(20) unsigned NOT NULL,
            author_id  bigint(20) unsigned NOT NULL,
            parent_id  bigint(20) unsigned DEFAULT NULL,
            content    longtext            NOT NULL,
            status     varchar(20)         DEFAULT 'approved',
            created_at datetime            DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY thread_id  (thread_id),
            KEY author_id  (author_id),
            KEY parent_id  (parent_id),
            KEY status     (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        $sql_messages = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_messages (
            id           bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            sender_id    bigint(20) unsigned NOT NULL,
            recipient_id bigint(20) unsigned NOT NULL,
            subject      varchar(255)        DEFAULT NULL,
            content      longtext            NOT NULL,
            is_read      tinyint(1)          DEFAULT 0,
            parent_id    bigint(20) unsigned DEFAULT NULL,
            created_at   datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sender_id    (sender_id),
            KEY recipient_id (recipient_id),
            KEY parent_id    (parent_id),
            KEY is_read      (is_read),
            KEY created_at   (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_threads );
        dbDelta( $sql_replies );
        dbDelta( $sql_messages );

        $this->create_user_roles();

        add_option( 'cnw_social_bridge_version', CNW_SOCIAL_BRIDGE_VERSION );
    }

    private function create_user_roles() {
        add_role( 'cnw_forum_member', __( 'Forum Member', 'cnw-social-bridge' ), array(
            'read'                => true,
            'cnw_create_threads'  => true,
            'cnw_reply_threads'   => true,
            'cnw_send_messages'   => true,
            'cnw_edit_own_posts'  => true,
            'cnw_delete_own_posts'=> true,
        ) );

        add_role( 'cnw_moderator', __( 'Moderator', 'cnw-social-bridge' ), array(
            'read'                => true,
            'cnw_create_threads'  => true,
            'cnw_reply_threads'   => true,
            'cnw_send_messages'   => true,
            'cnw_edit_own_posts'  => true,
            'cnw_delete_own_posts'=> true,
            'cnw_edit_any_post'   => true,
            'cnw_delete_any_post' => true,
            'cnw_close_threads'   => true,
            'cnw_pin_threads'     => true,
            'cnw_approve_replies' => true,
            'cnw_warn_users'      => true,
        ) );

        add_role( 'cnw_forum_admin', __( 'Forum Admin', 'cnw-social-bridge' ), array(
            'read'                => true,
            'cnw_create_threads'  => true,
            'cnw_reply_threads'   => true,
            'cnw_send_messages'   => true,
            'cnw_edit_own_posts'  => true,
            'cnw_delete_own_posts'=> true,
            'cnw_edit_any_post'   => true,
            'cnw_delete_any_post' => true,
            'cnw_close_threads'   => true,
            'cnw_pin_threads'     => true,
            'cnw_approve_replies' => true,
            'cnw_warn_users'      => true,
            'cnw_ban_users'       => true,
            'cnw_manage_settings' => true,
            'cnw_manage_roles'    => true,
            'cnw_view_reports'    => true,
        ) );
    }

    public function deactivate() {
        // Keep data on deactivation; roles remain until explicitly removed.
    }

    /* ------------------------------------------------------------------
     * Shortcode — renders the Vue 3 SPA (built by webpack → dist/)
     * ------------------------------------------------------------------ */

    public function render_shortcode( $atts ) {
        // Google Fonts — Poppins
        wp_enqueue_style(
            'cnw-poppins',
            'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
            array(),
            null
        );

        // Compiled CSS
        wp_enqueue_style(
            'cnw-app-style',
            CNW_SOCIAL_BRIDGE_PLUGIN_URL . 'dist/css/app.css',
            array(),
            CNW_SOCIAL_BRIDGE_VERSION
        );

        // Compiled JS (footer)
        wp_enqueue_script(
            'cnw-app-script',
            CNW_SOCIAL_BRIDGE_PLUGIN_URL . 'dist/js/app.js',
            array(),
            CNW_SOCIAL_BRIDGE_VERSION,
            true
        );

        // Pass WordPress data to Vue
        $current_user = wp_get_current_user();
        wp_localize_script( 'cnw-app-script', 'cnwData', array(
            'restUrl'     => esc_url_raw( rest_url( 'cnw-social-bridge/v1' ) ),
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'logoUrl'     => esc_url( get_option( 'cnw_social_logo_url', '' ) ),
            'currentUser' => array(
                'id'         => get_current_user_id(),
                'name'       => $current_user->display_name,
                'first_name' => get_user_meta( get_current_user_id(), 'first_name', true ),
                'last_name'  => get_user_meta( get_current_user_id(), 'last_name', true ),
                'avatar'     => get_avatar_url( get_current_user_id(), array( 'size' => 80 ) ),
            ),
        ) );

        ob_start();
        ?>
        <div id="cnw-social-bridge-app">
            <div style="text-align:center;padding:48px 20px;color:#888;font-family:sans-serif;">
                Loading Social Bridge&hellip;
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Boot
Cnw_Social_Bridge::get_instance();
