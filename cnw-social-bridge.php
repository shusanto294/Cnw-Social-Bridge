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

        // Run any pending DB migrations on every load.
        add_action( 'init', array( __CLASS__, 'migrate_tags_description_column' ) );
        add_action( 'init', array( __CLASS__, 'migrate_saved_threads_table' ) );
        add_action( 'init', array( __CLASS__, 'migrate_notifications_table' ) );

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

        // Ensure description column exists for existing installs.
        self::migrate_tags_description_column();

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
     * DB Migrations
     * ------------------------------------------------------------------ */

    public static function migrate_tags_description_column() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // 1. Add description column to tags table if missing.
        $tags_table = $wpdb->prefix . 'cnw_social_worker_tags';
        $col = $wpdb->get_results( "SHOW COLUMNS FROM `$tags_table` LIKE 'description'" );
        if ( empty( $col ) ) {
            $wpdb->query( "ALTER TABLE `$tags_table` ADD COLUMN `description` text NULL AFTER `slug`" );
        }

        // 3. Add created_by column to tags table if missing.
        $col2 = $wpdb->get_results( "SHOW COLUMNS FROM `$tags_table` LIKE 'created_by'" );
        if ( empty( $col2 ) ) {
            $wpdb->query( "ALTER TABLE `$tags_table` ADD COLUMN `created_by` bigint(20) unsigned DEFAULT NULL AFTER `description`" );
        }

        // 4. Add created_by column to categories table if missing.
        $cats_table = $wpdb->prefix . 'cnw_social_worker_categories';
        $col3 = $wpdb->get_results( "SHOW COLUMNS FROM `$cats_table` LIKE 'created_by'" );
        if ( empty( $col3 ) ) {
            $wpdb->query( "ALTER TABLE `$cats_table` ADD COLUMN `created_by` bigint(20) unsigned DEFAULT NULL AFTER `is_active`" );
        }

        // 2. Ensure thread_tags junction table exists (safe re-create).
        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cnw_social_worker_thread_tags (
                thread_id bigint(20) unsigned NOT NULL,
                tag_id    bigint(20) unsigned NOT NULL,
                PRIMARY KEY (thread_id, tag_id),
                KEY tag_id (tag_id)
            ) $charset_collate"
        );
    }

    public static function migrate_saved_threads_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_saved_threads';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
            $charset_collate = $wpdb->get_charset_collate();
            $wpdb->query(
                "CREATE TABLE IF NOT EXISTS $table (
                    user_id    bigint(20) unsigned NOT NULL,
                    thread_id  bigint(20) unsigned NOT NULL,
                    created_at datetime            DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (user_id, thread_id),
                    KEY thread_id (thread_id)
                ) $charset_collate"
            );
        }

        // Add is_anonymous column to threads table if missing.
        $threads_table = $wpdb->prefix . 'cnw_social_worker_threads';
        $col = $wpdb->get_results( "SHOW COLUMNS FROM `$threads_table` LIKE 'is_anonymous'" );
        if ( empty( $col ) ) {
            $wpdb->query( "ALTER TABLE `$threads_table` ADD COLUMN `is_anonymous` tinyint(1) DEFAULT 0 AFTER `status`" );
        }
    }

    public static function migrate_notifications_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_notifications';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
            $charset_collate = $wpdb->get_charset_collate();
            $wpdb->query(
                "CREATE TABLE IF NOT EXISTS $table (
                    id            bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    user_id       bigint(20) unsigned NOT NULL,
                    actor_id      bigint(20) unsigned DEFAULT NULL,
                    type          varchar(50)         NOT NULL,
                    reference_type varchar(20)        DEFAULT NULL,
                    reference_id  bigint(20) unsigned DEFAULT NULL,
                    message       varchar(500)        NOT NULL,
                    is_read       tinyint(1)          DEFAULT 0,
                    created_at    datetime            DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY user_id    (user_id),
                    KEY is_read    (is_read),
                    KEY created_at (created_at)
                ) $charset_collate"
            );
        }
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
            'siteUrl'     => esc_url_raw( home_url( '/' ) ),
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'logoUrl'     => esc_url( get_option( 'cnw_social_logo_url', '' ) ),
            'currentUser' => array(
                'id'         => get_current_user_id(),
                'name'       => $current_user->display_name,
                'first_name' => get_user_meta( get_current_user_id(), 'first_name', true ),
                'last_name'  => get_user_meta( get_current_user_id(), 'last_name', true ),
                'avatar'     => get_avatar_url( get_current_user_id(), array( 'size' => 80 ) ),
                'reputation' => (int) get_user_meta( get_current_user_id(), 'cnw_reputation_total', true ),
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
