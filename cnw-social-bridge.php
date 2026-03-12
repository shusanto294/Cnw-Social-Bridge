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

        // Mark user offline on WordPress logout
        add_action( 'wp_logout', array( $this, 'on_user_logout' ) );

        // Email notification cron
        add_action( 'cnw_send_email_notifications', array( $this, 'process_email_notifications' ) );
        add_filter( 'cron_schedules', array( $this, 'add_cron_intervals' ) );

        // Ensure DB migration + cron are set up (runs once via version check)
        add_action( 'init', array( $this, 'maybe_run_upgrades' ) );

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

    /**
     * On WordPress logout — mark user offline and broadcast via Pusher instantly.
     */
    public function on_user_logout( $user_id ) {
        global $wpdb;

        update_user_meta( $user_id, 'cnw_is_online', 0 );

        // Broadcast offline to all conversation partners via Pusher
        $table    = $wpdb->prefix . 'cnw_social_worker_messages';
        $partners = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS partner_id
             FROM {$table}
             WHERE sender_id = %d OR recipient_id = %d",
            $user_id, $user_id, $user_id
        ) );

        $payload = array( 'user_id' => $user_id, 'status' => 'offline' );
        foreach ( $partners as $partner_id ) {
            Cnw_Social_Bridge_Pusher::trigger(
                'private-user-' . (int) $partner_id,
                'user-status',
                $payload
            );
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

        // ── Add emailed column to notifications ──────────────────────────
        $notif_table = $wpdb->prefix . 'cnw_social_worker_notifications';
        $row = $wpdb->get_results( "SHOW COLUMNS FROM {$notif_table} LIKE 'emailed'" );
        if ( empty( $row ) ) {
            $wpdb->query( "ALTER TABLE {$notif_table} ADD COLUMN emailed tinyint(1) DEFAULT 0 AFTER is_read" );
            $wpdb->query( "ALTER TABLE {$notif_table} ADD KEY emailed (emailed)" );
        }

        $this->create_user_roles();

        // Schedule email notification cron (every 5 minutes)
        if ( ! wp_next_scheduled( 'cnw_send_email_notifications' ) ) {
            wp_schedule_event( time(), 'cnw_every_5_min', 'cnw_send_email_notifications' );
        }

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

    /**
     * Run DB migrations and schedule cron if not yet done.
     */
    public function maybe_run_upgrades() {
        global $wpdb;

        // Add emailed column if missing
        $notif_table = $wpdb->prefix . 'cnw_social_worker_notifications';
        $col = $wpdb->get_results( "SHOW COLUMNS FROM {$notif_table} LIKE 'emailed'" );
        if ( empty( $col ) ) {
            $wpdb->query( "ALTER TABLE {$notif_table} ADD COLUMN emailed tinyint(1) DEFAULT 0 AFTER is_read" );
            $wpdb->query( "ALTER TABLE {$notif_table} ADD KEY emailed (emailed)" );
        }

        // Schedule cron if not already scheduled
        if ( ! wp_next_scheduled( 'cnw_send_email_notifications' ) ) {
            wp_schedule_event( time(), 'cnw_every_5_min', 'cnw_send_email_notifications' );
        }
    }

    /**
     * Register custom cron interval.
     */
    public function add_cron_intervals( $schedules ) {
        $schedules['cnw_every_5_min'] = array(
            'interval' => 300,
            'display'  => __( 'Every 5 Minutes', 'cnw-social-bridge' ),
        );
        return $schedules;
    }

    /** Notification type → preference key mapping. */
    private static $notif_pref_map = array(
        'reply'               => 'notify_replies',
        'vote'                => 'notify_votes',
        'save'                => 'notify_votes',
        'solution'            => 'notify_solutions',
        'solution_removed'    => 'notify_solutions',
        'connection_request'  => 'notify_connections',
        'connection_accepted' => 'notify_connections',
        'mention'             => 'notify_mentions',
    );

    /** Default preference values (mirrors REST API defaults). */
    private static $pref_defaults = array(
        'notify_replies'      => true,
        'notify_mentions'     => true,
        'notify_votes'        => true,
        'notify_solutions'    => true,
        'notify_connections'  => true,
        'notify_messages'     => true,
        'email_notifications' => 'inactive',
    );

    /**
     * Get merged preferences for a user (with defaults).
     */
    private function get_user_prefs( $user_id ) {
        $saved = get_user_meta( $user_id, 'cnw_preferences', true );
        return is_array( $saved ) ? array_merge( self::$pref_defaults, $saved ) : self::$pref_defaults;
    }

    /**
     * Cron callback — send email notification digests.
     *
     * Runs every 5 minutes. Fetches the oldest 50 un-emailed, unread
     * notifications, groups them by user, checks each user's preferences,
     * and sends one digest email per user.
     */
    public function process_email_notifications() {
        global $wpdb;

        $table         = $wpdb->prefix . 'cnw_social_worker_notifications';
        $batch_size    = 50;  // Process oldest 50 notifications per run
        $max_in_email  = 20;  // Max items shown in one email

        // Mark any already-read notifications as emailed so they stop showing up
        $wpdb->query( "UPDATE {$table} SET emailed = 1 WHERE emailed = 0 AND is_read = 1" );

        // Fetch the oldest 50 un-emailed, unread notifications
        $notifications = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE emailed = 0 AND is_read = 0 ORDER BY created_at ASC LIMIT %d",
            $batch_size
        ) );

        if ( empty( $notifications ) ) {
            return;
        }

        // Group by user_id
        $by_user = array();
        foreach ( $notifications as $notif ) {
            $uid = (int) $notif->user_id;
            if ( ! isset( $by_user[ $uid ] ) ) {
                $by_user[ $uid ] = array();
            }
            $by_user[ $uid ][] = $notif;
        }

        $site_name = get_bloginfo( 'name' );
        $site_url  = home_url( '/' );

        foreach ( $by_user as $user_id => $user_notifs ) {
            $user = get_userdata( $user_id );

            // Collect all IDs in this batch for this user
            $all_ids = array_map( function( $n ) { return (int) $n->id; }, $user_notifs );

            if ( ! $user || ! $user->user_email ) {
                // No valid user — mark as emailed and skip
                $this->mark_notifs_emailed( $table, $all_ids );
                continue;
            }

            $prefs         = $this->get_user_prefs( $user_id );
            $email_setting = isset( $prefs['email_notifications'] ) ? $prefs['email_notifications'] : 'inactive';

            if ( $email_setting === 'none' ) {
                // User opted out — mark as emailed, no email
                $this->mark_notifs_emailed( $table, $all_ids );
                continue;
            }

            if ( $email_setting === 'inactive' ) {
                $is_online = (bool) get_user_meta( $user_id, 'cnw_is_online', true );
                if ( $is_online ) {
                    continue; // Skip — will retry next cron run
                }
            }

            // Filter by individual notification type preferences
            $eligible = array();
            foreach ( $user_notifs as $notif ) {
                $pref_key = isset( self::$notif_pref_map[ $notif->type ] ) ? self::$notif_pref_map[ $notif->type ] : null;
                if ( $pref_key === null || ! empty( $prefs[ $pref_key ] ) ) {
                    $eligible[] = $notif;
                }
            }

            // Mark ALL as emailed (even filtered-out ones)
            $this->mark_notifs_emailed( $table, $all_ids );

            if ( empty( $eligible ) ) {
                continue;
            }

            // Cap shown items; pass total for summary
            $total_eligible = count( $eligible );
            $to_show        = array_slice( $eligible, 0, $max_in_email );

            $this->send_notification_email( $user, $to_show, $total_eligible, $site_name, $site_url );
        }
    }

    /**
     * Mark a batch of notification IDs as emailed.
     */
    private function mark_notifs_emailed( $table, $ids ) {
        global $wpdb;
        if ( empty( $ids ) ) return;
        $placeholders = implode( ',', array_map( 'intval', $ids ) );
        $wpdb->query( "UPDATE {$table} SET emailed = 1 WHERE id IN ({$placeholders})" );
    }

    /**
     * Build and send a notification digest email.
     */
    /**
     * Build and send a notification digest email.
     *
     * @param WP_User $user           Recipient user object.
     * @param array   $notifications  Notifications to show (already capped to max).
     * @param int     $total_eligible Total eligible count (may be > count of $notifications).
     * @param string  $site_name      Site name for branding.
     * @param string  $site_url       Site URL for links.
     */
    private function send_notification_email( $user, $notifications, $total_eligible, $site_name, $site_url ) {
        $shown = count( $notifications );
        $subject = sprintf(
            '[%s] You have %d new %s',
            $site_name,
            $total_eligible,
            $total_eligible === 1 ? 'notification' : 'notifications'
        );

        // Build HTML email
        $body = '<div style="font-family: \'Poppins\', Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f7f7f7; padding: 0;">';

        // Header
        $body .= '<div style="background: linear-gradient(90deg, #3AA9DA 0%, #5FBF91 100%); padding: 24px 30px; text-align: center;">';
        $body .= '<h1 style="margin: 0; color: #fff; font-size: 20px; font-weight: 600;">' . esc_html( $site_name ) . '</h1>';
        $body .= '</div>';

        // Greeting
        $body .= '<div style="background: #fff; padding: 24px 30px; border-bottom: 1px solid #e2e4e8;">';
        $body .= '<p style="margin: 0 0 8px; font-size: 16px; color: #1a1a2e;">Hi ' . esc_html( $user->display_name ) . ',</p>';
        $body .= '<p style="margin: 0; font-size: 14px; color: #555565;">You have ' . $total_eligible . ' new ' . ( $total_eligible === 1 ? 'notification' : 'notifications' ) . ' waiting for you:</p>';
        $body .= '</div>';

        // Notification list
        $body .= '<div style="background: #fff; padding: 10px 30px 20px;">';
        foreach ( $notifications as $notif ) {
            $icon = $this->get_notif_icon( $notif->type );
            $time = human_time_diff( strtotime( $notif->created_at ), current_time( 'timestamp' ) ) . ' ago';

            $body .= '<div style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">';
            $body .= '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>';
            $body .= '<td style="width: 40px; vertical-align: top;"><div style="width: 32px; height: 32px; border-radius: 50%; background: #e6f7fb; text-align: center; line-height: 32px; font-size: 16px;">' . $icon . '</div></td>';
            $body .= '<td style="vertical-align: top;">';
            $body .= '<p style="margin: 0 0 2px; font-size: 14px; color: #1a1a2e;">' . esc_html( $notif->message ) . '</p>';
            $body .= '<p style="margin: 0; font-size: 12px; color: #888899;">' . esc_html( $time ) . '</p>';
            $body .= '</td>';
            $body .= '</tr></table>';
            $body .= '</div>';
        }

        // If there are more than shown
        if ( $total_eligible > $shown ) {
            $remaining = $total_eligible - $shown;
            $body .= '<p style="padding: 12px 0 0; font-size: 13px; color: #555565; text-align: center;">...and ' . $remaining . ' more ' . ( $remaining === 1 ? 'notification' : 'notifications' ) . '</p>';
        }

        $body .= '</div>';

        // CTA button
        $body .= '<div style="background: #fff; padding: 10px 30px 24px; text-align: center;">';
        $body .= '<a href="' . esc_url( $site_url ) . '" style="display: inline-block; padding: 10px 28px; background: #3AA9DA; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">View All Notifications</a>';
        $body .= '</div>';

        // Footer
        $body .= '<div style="padding: 16px 30px; text-align: center; font-size: 12px; color: #888899;">';
        $body .= '<p style="margin: 0;">You can manage your email preferences in your <a href="' . esc_url( $site_url ) . '#/profile?tab=settings" style="color: #3bbdd4;">profile settings</a>.</p>';
        $body .= '</div>';

        $body .= '</div>';

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $user->user_email, $subject, $body, $headers );
    }

    /**
     * Get emoji icon for notification type.
     */
    private function get_notif_icon( $type ) {
        $icons = array(
            'reply'               => '&#128172;',
            'vote'                => '&#11014;',
            'save'                => '&#128278;',
            'solution'            => '&#9989;',
            'solution_removed'    => '&#10060;',
            'connection_request'  => '&#129309;',
            'connection_accepted' => '&#127881;',
            'mention'             => '&#64;',
            'warning'             => '&#9888;',
            'suspension'          => '&#128683;',
        );
        return isset( $icons[ $type ] ) ? $icons[ $type ] : '&#128276;';
    }

    public function deactivate() {
        // Clear scheduled cron
        $timestamp = wp_next_scheduled( 'cnw_send_email_notifications' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'cnw_send_email_notifications' );
        }
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
