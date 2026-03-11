<?php
/**
 * Plugin Name: Cnw Social Bridge
 * Description: A social forum plugin with threads, replies, messages, and user roles.
 * Version: 1.0.12
 * Author: CNW
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cnw-social-bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Constants ────────────────────────────────────────────────────────────────
define( 'CNW_SOCIAL_BRIDGE_VERSION',    '1.0.12' );
define( 'CNW_SOCIAL_BRIDGE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CNW_SOCIAL_BRIDGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR', plugin_dir_url( __FILE__ ) . 'assets/images/default-avatar.png' );

// ── Composer autoload (Pusher SDK etc.) ──────────────────────────────────────
if ( file_exists( CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'vendor/autoload.php';
}

// ── Includes ─────────────────────────────────────────────────────────────────
require_once CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/class-cnw-admin.php';
require_once CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'includes/class-cnw-rest-api.php';
require_once CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'includes/class-cnw-pusher.php';

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

        // Hide WP admin bar for forum-only roles.
        add_action( 'init', array( $this, 'maybe_hide_admin_bar' ) );

        // Sub-modules
        new Cnw_Social_Bridge_Admin();
        new Cnw_Social_Bridge_REST_API();
    }

    /**
     * Hide the WordPress admin bar for users whose roles are all forum-specific.
     */
    public function maybe_hide_admin_bar() {
        if ( ! is_user_logged_in() ) {
            return;
        }
        $user       = wp_get_current_user();
        $cnw_roles  = array( 'cnw_forum_member', 'cnw_moderator', 'cnw_forum_admin' );
        $user_roles = (array) $user->roles;

        // If every role the user has is a CNW forum role, hide the admin bar.
        if ( ! empty( $user_roles ) && empty( array_diff( $user_roles, $cnw_roles ) ) ) {
            show_admin_bar( false );
        }
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
            is_anonymous tinyint(1)         DEFAULT 0,
            views      int(11)             DEFAULT 0,
            created_at datetime            DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY author_id  (author_id),
            KEY status     (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        $sql_replies = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_replies (
            id           bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            thread_id    bigint(20) unsigned NOT NULL,
            author_id    bigint(20) unsigned NOT NULL,
            parent_id    bigint(20) unsigned DEFAULT NULL,
            content      longtext            NOT NULL,
            status       varchar(20)         DEFAULT 'approved',
            is_solution  tinyint(1)          DEFAULT 0,
            is_anonymous tinyint(1)          DEFAULT 0,
            created_at   datetime            DEFAULT CURRENT_TIMESTAMP,
            updated_at   datetime            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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

        $sql_categories = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_categories (
            id          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name        varchar(100)        NOT NULL,
            slug        varchar(100)        NOT NULL,
            description text                DEFAULT NULL,
            parent_id   bigint(20) unsigned DEFAULT NULL,
            icon        varchar(50)         DEFAULT NULL,
            color       varchar(7)          DEFAULT NULL,
            sort_order  int(11)             DEFAULT 0,
            is_active   tinyint(1)          DEFAULT 1,
            created_by  bigint(20) unsigned DEFAULT NULL,
            created_at  datetime            DEFAULT CURRENT_TIMESTAMP,
            updated_at  datetime            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY parent_id  (parent_id),
            KEY sort_order (sort_order),
            KEY is_active  (is_active),
            KEY created_by (created_by)
        ) $charset_collate;";

        $sql_tags = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_tags (
            id          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name        varchar(100)        NOT NULL,
            slug        varchar(100)        NOT NULL,
            description text                NULL,
            created_by  bigint(20) unsigned DEFAULT NULL,
            created_at  datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY created_by (created_by)
        ) $charset_collate;";

        $sql_thread_tags = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_thread_tags (
            thread_id  bigint(20) unsigned NOT NULL,
            tag_id     bigint(20) unsigned NOT NULL,
            PRIMARY KEY (thread_id, tag_id),
            KEY tag_id (tag_id)
        ) $charset_collate;";

        $sql_user_followed_tags = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_user_followed_tags (
            user_id    bigint(20) unsigned NOT NULL,
            tag_id     bigint(20) unsigned NOT NULL,
            created_at datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, tag_id),
            KEY tag_id (tag_id)
        ) $charset_collate;";

        $sql_votes = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_votes (
            id          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id     bigint(20) unsigned NOT NULL,
            target_type enum('thread','reply') NOT NULL,
            target_id   bigint(20) unsigned NOT NULL,
            vote_type   tinyint(1)          NOT NULL COMMENT '1=upvote, -1=downvote',
            created_at  datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_target (user_id, target_type, target_id),
            KEY target_type (target_type),
            KEY target_id   (target_id),
            KEY vote_type   (vote_type)
        ) $charset_collate;";

        $sql_reputation = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_reputation (
            id             bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id        bigint(20) unsigned NOT NULL,
            points         int(11)             NOT NULL,
            action_type    varchar(50)         NOT NULL COMMENT 'thread_created, reply_created, received_upvote, received_downvote, etc.',
            reference_type enum('thread','reply','vote') DEFAULT NULL,
            reference_id   bigint(20) unsigned DEFAULT NULL,
            description    varchar(255)        DEFAULT NULL,
            created_at     datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id        (user_id),
            KEY action_type    (action_type),
            KEY reference_type (reference_type),
            KEY reference_id   (reference_id),
            KEY created_at     (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_threads );
        dbDelta( $sql_replies );
        dbDelta( $sql_messages );
        dbDelta( $sql_categories );
        dbDelta( $sql_tags );
        // Junction tables with composite primary keys — use $wpdb->query
        // as dbDelta can have issues with composite PKs.
        $wpdb->query( $sql_thread_tags );
        $wpdb->query( $sql_user_followed_tags );
        dbDelta( $sql_votes );
        dbDelta( $sql_reputation );

        $sql_activity = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_activity (
            id             bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id        bigint(20) unsigned NOT NULL,
            action_type    varchar(50)         NOT NULL,
            description    varchar(500)        NOT NULL,
            points         int(11)             DEFAULT 0,
            reason         varchar(255)        DEFAULT NULL,
            reference_type varchar(20)         DEFAULT NULL,
            reference_id   bigint(20) unsigned DEFAULT NULL,
            link           varchar(500)        DEFAULT NULL,
            created_at     datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id    (user_id),
            KEY action_type (action_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql_activity );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_saved_threads (
                user_id    bigint(20) unsigned NOT NULL,
                thread_id  bigint(20) unsigned NOT NULL,
                created_at datetime            DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, thread_id),
                KEY thread_id (thread_id)
            ) $charset_collate"
        );

        $sql_notifications = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_notifications (
            id            bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id       bigint(20) unsigned NOT NULL,
            actor_id      bigint(20) unsigned DEFAULT NULL,
            type          varchar(50)         NOT NULL COMMENT 'reply, vote, save, follow, mention',
            reference_type varchar(20)        DEFAULT NULL COMMENT 'thread, reply, tag',
            reference_id  bigint(20) unsigned DEFAULT NULL,
            message       varchar(500)        NOT NULL,
            is_read       tinyint(1)          DEFAULT 0,
            created_at    datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id    (user_id),
            KEY is_read    (is_read),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql_notifications );

        $sql_reports = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_reports (
            id           bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id      bigint(20) unsigned NOT NULL,
            type         varchar(50)         NOT NULL,
            subject      varchar(255)        NOT NULL,
            description  longtext            NOT NULL,
            link         varchar(500)        DEFAULT NULL,
            priority     varchar(20)         DEFAULT 'medium',
            status       varchar(20)         DEFAULT 'open',
            admin_notes  longtext            DEFAULT NULL,
            created_at   datetime            DEFAULT CURRENT_TIMESTAMP,
            updated_at   datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id    (user_id),
            KEY status     (status),
            KEY priority   (priority),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta( $sql_reports );

        $sql_connections = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_connections (
            id          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            sender_id   bigint(20) unsigned NOT NULL,
            receiver_id bigint(20) unsigned NOT NULL,
            status      varchar(20)         DEFAULT 'pending',
            created_at  datetime            DEFAULT CURRENT_TIMESTAMP,
            updated_at  datetime            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY pair (sender_id, receiver_id),
            KEY receiver_id (receiver_id),
            KEY status      (status)
        ) $charset_collate;";
        dbDelta( $sql_connections );

        $sql_restrictions = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_restrictions (
            id            bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            restricter_id bigint(20) unsigned NOT NULL,
            restricted_id bigint(20) unsigned NOT NULL,
            created_at    datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY pair (restricter_id, restricted_id),
            KEY restricted_id (restricted_id)
        ) $charset_collate;";
        dbDelta( $sql_restrictions );

        // ── Add moderation columns to threads ─────────────────────────────
        $threads_table = $wpdb->prefix . 'cnw_social_worker_threads';
        $row = $wpdb->get_results( "SHOW COLUMNS FROM {$threads_table} LIKE 'is_pinned'" );
        if ( empty( $row ) ) {
            $wpdb->query( "ALTER TABLE {$threads_table} ADD COLUMN is_pinned tinyint(1) DEFAULT 0 AFTER views" );
        }
        $row = $wpdb->get_results( "SHOW COLUMNS FROM {$threads_table} LIKE 'is_closed'" );
        if ( empty( $row ) ) {
            $wpdb->query( "ALTER TABLE {$threads_table} ADD COLUMN is_closed tinyint(1) DEFAULT 0 AFTER is_pinned" );
        }

        // ── Warnings / suspensions table ──────────────────────────────────
        $sql_warnings = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_warnings (
            id          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id     bigint(20) unsigned NOT NULL,
            moderator_id bigint(20) unsigned NOT NULL,
            type        varchar(20)         NOT NULL COMMENT 'warning or suspension',
            reason      text                NOT NULL,
            duration    int(11)             DEFAULT NULL COMMENT 'suspension days, NULL = permanent',
            expires_at  datetime            DEFAULT NULL,
            is_active   tinyint(1)          DEFAULT 1,
            created_at  datetime            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id     (user_id),
            KEY moderator_id (moderator_id),
            KEY type        (type),
            KEY is_active   (is_active),
            KEY expires_at  (expires_at)
        ) $charset_collate;";
        dbDelta( $sql_warnings );

        // ── Add content reference columns to reports ──────────────────────
        $reports_table = $wpdb->prefix . 'cnw_social_worker_reports';
        $row = $wpdb->get_results( "SHOW COLUMNS FROM {$reports_table} LIKE 'content_type'" );
        if ( empty( $row ) ) {
            $wpdb->query( "ALTER TABLE {$reports_table} ADD COLUMN content_type varchar(20) DEFAULT NULL AFTER link" );
            $wpdb->query( "ALTER TABLE {$reports_table} ADD COLUMN content_id bigint(20) unsigned DEFAULT NULL AFTER content_type" );
        }

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

    /**
     * Get current user's suspension info for frontend.
     */
    private function get_current_user_suspension() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) return null;

        $suspended = get_user_meta( $user_id, 'cnw_suspended', true );
        if ( ! $suspended ) return null;

        // Verify an active suspension actually exists in the DB
        global $wpdb;
        $active = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_warnings WHERE user_id = %d AND type = 'suspension' AND is_active = 1 ORDER BY created_at DESC LIMIT 1",
            $user_id
        ) );

        if ( ! $active ) {
            delete_user_meta( $user_id, 'cnw_suspended' );
            delete_user_meta( $user_id, 'cnw_suspended_until' );
            return null;
        }

        $until = get_user_meta( $user_id, 'cnw_suspended_until', true );

        // Auto-clear expired
        if ( $until && strtotime( $until ) <= time() ) {
            delete_user_meta( $user_id, 'cnw_suspended' );
            delete_user_meta( $user_id, 'cnw_suspended_until' );
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_warnings',
                array( 'is_active' => 0 ),
                array( 'user_id' => $user_id, 'type' => 'suspension', 'is_active' => 1 )
            );
            return null;
        }

        if ( $until ) {
            $days = (int) ceil( ( strtotime( $until ) - time() ) / DAY_IN_SECONDS );
            return array(
                'is_suspended' => true,
                'permanent'    => false,
                'days_left'    => $days,
                'until'        => $until,
            );
        }

        return array(
            'is_suspended' => true,
            'permanent'    => true,
            'days_left'    => null,
            'until'        => null,
        );
    }

    /* ------------------------------------------------------------------
     * Shortcode — renders the Vue 3 SPA (built by webpack → dist/)
     * ------------------------------------------------------------------ */

    public function render_shortcode( $atts ) {
        // Google Fonts — Poppins
        wp_enqueue_style(
            'cnw-poppins',
            'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
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
            'siteUrl'     => esc_url_raw( home_url( '/' ) ),
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'defaultAvatar' => CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR,
            'pusherKey'     => get_option( 'cnw_pusher_key', '' ),
            'pusherCluster' => get_option( 'cnw_pusher_cluster', 'mt1' ),
            'pusherHost'    => get_option( 'cnw_pusher_host', '' ),
            'pusherPort'    => (int) get_option( 'cnw_pusher_port', 443 ),
            'loginUrl'      => wp_login_url(),
            'registerUrl'   => wp_registration_url(),
            'currentUser' => array(
                'id'         => get_current_user_id(),
                'name'       => $current_user->display_name,
                'first_name' => get_user_meta( get_current_user_id(), 'first_name', true ),
                'last_name'  => get_user_meta( get_current_user_id(), 'last_name', true ),
                'avatar'     => get_user_meta( get_current_user_id(), 'cnw_avatar_url', true ) ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR,
                'reputation' => (int) get_user_meta( get_current_user_id(), 'cnw_reputation_total', true ),
                'anonymous'  => (bool) get_user_meta( get_current_user_id(), 'cnw_anonymous', true ),
                'roles' => (array) $current_user->roles,
                'canModerate' => current_user_can( 'cnw_close_threads' ) || current_user_can( 'manage_options' ),
                'suspension' => $this->get_current_user_suspension(),
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
