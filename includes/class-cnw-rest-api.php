<?php
/**
 * REST API class — registers all cnw-social-bridge/v1 routes with full CRUD.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cnw_Social_Bridge_REST_API {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Check if a user is currently online via their cnw_is_online flag.
     * The flag is set/cleared by: Pusher webhook (channel_vacated), beforeunload,
     * WP logout hook, and the /pusher/status endpoint.
     *
     * Respects the target user's show_online_status preference.
     * If the user has disabled it, only they themselves can see their own status.
     */
    private function is_user_online( $user_id, $requesting_user_id = 0 ) {
        // If the target user has hidden their online status, return false
        // (unless the requester is the user themselves)
        if ( $requesting_user_id !== $user_id ) {
            $prefs = $this->get_user_preferences( $user_id );
            if ( empty( $prefs['show_online_status'] ) ) {
                return false;
            }
        }
        return (bool) get_user_meta( $user_id, 'cnw_is_online', true );
    }

    /* ------------------------------------------------------------------
     * Route registration
     * ------------------------------------------------------------------ */

    public function register_routes() {
        $ns = 'cnw-social-bridge/v1';

        // ── Threads ──────────────────────────────────────────────────
        register_rest_route( $ns, '/threads', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_threads' ),  'permission_callback' => '__return_true' ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_thread' ), 'permission_callback' => array( $this, 'can_create_thread' ) ),
        ) );
        register_rest_route( $ns, '/threads/(?P<id>\d+)', array(
            array( 'methods' => 'GET',      'callback' => array( $this, 'get_thread' ),    'permission_callback' => '__return_true' ),
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_thread' ), 'permission_callback' => array( $this, 'can_manage_thread' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_thread' ), 'permission_callback' => array( $this, 'can_manage_thread' ) ),
        ) );
        register_rest_route( $ns, '/threads/(?P<id>\d+)/replies', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_thread_replies' ), 'permission_callback' => '__return_true',
        ) );

        // ── Replies ──────────────────────────────────────────────────
        register_rest_route( $ns, '/replies', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'list_replies' ),  'permission_callback' => '__return_true' ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_reply' ),  'permission_callback' => array( $this, 'can_reply' ) ),
        ) );
        register_rest_route( $ns, '/replies/(?P<id>\d+)', array(
            array( 'methods' => 'GET',      'callback' => array( $this, 'get_reply' ),    'permission_callback' => '__return_true' ),
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_reply' ), 'permission_callback' => array( $this, 'can_manage_reply' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_reply' ), 'permission_callback' => array( $this, 'can_manage_reply' ) ),
        ) );

        // ── Mark reply as solution ──────────────────────────────────
        register_rest_route( $ns, '/replies/(?P<id>\d+)/solution', array(
            'methods' => 'POST', 'callback' => array( $this, 'mark_solution' ), 'permission_callback' => array( $this, 'can_reply' ),
        ) );

        // ── Messages ─────────────────────────────────────────────────
        register_rest_route( $ns, '/messages', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_messages' ),  'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_message' ), 'permission_callback' => array( $this, 'can_send_message' ) ),
        ) );
        register_rest_route( $ns, '/messages/(?P<id>\d+)', array(
            array( 'methods' => 'GET',      'callback' => array( $this, 'get_message' ),    'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_message' ), 'permission_callback' => array( $this, 'can_send_message' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_message' ), 'permission_callback' => array( $this, 'can_send_message' ) ),
        ) );
        register_rest_route( $ns, '/messages/(?P<id>\d+)/pin', array(
            'methods' => 'POST', 'callback' => array( $this, 'toggle_pin_message' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );
        register_rest_route( $ns, '/messages/upload', array(
            'methods' => 'POST', 'callback' => array( $this, 'upload_message_attachment' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );

        // ── User-facing conversations ────────────────────────────────
        register_rest_route( $ns, '/conversations', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_conversations' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );
        register_rest_route( $ns, '/conversations/(?P<user_id>\d+)', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_conversation' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );
        register_rest_route( $ns, '/conversations/(?P<user_id>\d+)/read', array(
            'methods' => 'POST', 'callback' => array( $this, 'mark_conversation_read' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );
        register_rest_route( $ns, '/messages/unread-count', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_unread_message_count' ), 'permission_callback' => 'is_user_logged_in',
        ) );

        // ── Typing indicator ──────────────────────────────────────────
        register_rest_route( $ns, '/typing/(?P<user_id>\d+)', array(
            array( 'methods' => 'POST', 'callback' => array( $this, 'set_typing' ), 'permission_callback' => array( $this, 'can_send_message' ) ),
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_typing' ),  'permission_callback' => array( $this, 'can_send_message' ) ),
        ) );

        // ── Pusher auth ─────────────────────────────────────────────────
        register_rest_route( $ns, '/pusher/auth', array(
            'methods' => 'POST', 'callback' => array( $this, 'pusher_auth' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );

        // ── Pusher status broadcast ─────────────────────────────────────
        register_rest_route( $ns, '/pusher/status', array(
            'methods' => 'POST', 'callback' => array( $this, 'pusher_broadcast_status' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );

        // ── Pusher webhook (channel_vacated → mark user offline) ─────────
        register_rest_route( $ns, '/pusher/webhook', array(
            'methods' => 'POST', 'callback' => array( $this, 'pusher_webhook' ), 'permission_callback' => '__return_true',
        ) );

        // ── Categories ───────────────────────────────────────────────
        register_rest_route( $ns, '/categories', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_categories' ),  'permission_callback' => '__return_true' ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_category' ), 'permission_callback' => array( $this, 'can_manage' ) ),
        ) );
        register_rest_route( $ns, '/categories/(?P<id>\d+)', array(
            array( 'methods' => 'GET',      'callback' => array( $this, 'get_category' ),    'permission_callback' => '__return_true' ),
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_category' ), 'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_category' ), 'permission_callback' => array( $this, 'can_manage' ) ),
        ) );

        // ── Votes ────────────────────────────────────────────────────
        register_rest_route( $ns, '/votes', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_votes' ),  'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_vote' ), 'permission_callback' => array( $this, 'can_reply' ) ),
        ) );
        register_rest_route( $ns, '/votes/(?P<id>\d+)', array(
            array( 'methods' => 'GET',    'callback' => array( $this, 'get_vote' ),    'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'DELETE', 'callback' => array( $this, 'delete_vote' ), 'permission_callback' => array( $this, 'can_manage' ) ),
        ) );

        // ── Reputation ───────────────────────────────────────────────
        register_rest_route( $ns, '/reputation', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_reputation_list' ), 'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_reputation' ),   'permission_callback' => array( $this, 'can_manage' ) ),
        ) );
        register_rest_route( $ns, '/reputation/(?P<id>\d+)', array(
            array( 'methods' => 'GET',      'callback' => array( $this, 'get_reputation_entry' ), 'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_reputation' ),   'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_reputation' ),    'permission_callback' => array( $this, 'can_manage' ) ),
        ) );

        // ── Tags ─────────────────────────────────────────────────────
        register_rest_route( $ns, '/tags', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_tags' ),  'permission_callback' => '__return_true' ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_tag' ), 'permission_callback' => 'is_user_logged_in' ),
        ) );
        register_rest_route( $ns, '/tags/(?P<id>\d+)', array(
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_tag' ), 'permission_callback' => 'is_user_logged_in' ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_tag' ), 'permission_callback' => array( $this, 'can_manage_tag' ) ),
        ) );
        register_rest_route( $ns, '/tags/followed', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_followed_tags' ), 'permission_callback' => array( $this, 'can_create_thread' ),
        ) );
        register_rest_route( $ns, '/tags/(?P<id>\d+)/follow', array(
            'methods' => 'POST', 'callback' => array( $this, 'follow_tag' ), 'permission_callback' => array( $this, 'can_create_thread' ),
        ) );
        register_rest_route( $ns, '/tags/(?P<id>\d+)/unfollow', array(
            'methods' => 'POST', 'callback' => array( $this, 'unfollow_tag' ), 'permission_callback' => array( $this, 'can_create_thread' ),
        ) );
        // ── Saved Threads ──────────────────────────────────────────
        register_rest_route( $ns, '/threads/(?P<id>\d+)/save', array(
            'methods' => 'POST', 'callback' => array( $this, 'save_thread' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/threads/(?P<id>\d+)/unsave', array(
            'methods' => 'POST', 'callback' => array( $this, 'unsave_thread' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/saved-threads', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_saved_threads' ), 'permission_callback' => 'is_user_logged_in',
        ) );

        // ── Notifications ────────────────────────────────────────────
        register_rest_route( $ns, '/notifications', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_notifications' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/notifications/unread-count', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_unread_notification_count' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/notifications/(?P<id>\d+)/read', array(
            'methods' => 'POST', 'callback' => array( $this, 'mark_notification_read' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/notifications/read-all', array(
            'methods' => 'POST', 'callback' => array( $this, 'mark_all_notifications_read' ), 'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( $ns, '/hot-questions', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_hot_questions' ), 'permission_callback' => '__return_true',
        ) );

        // ── Reports ─────────────────────────────────────────────────────
        register_rest_route( $ns, '/reports', array(
            'methods' => 'POST', 'callback' => array( $this, 'create_report' ), 'permission_callback' => array( $this, 'can_send_message' ),
        ) );

        // ── Guidelines (stored as WP option) ────────────────────────────
        register_rest_route( $ns, '/guidelines', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_guidelines' ), 'permission_callback' => '__return_true',
        ) );

        // ── Login ───────────────────────────────────────────────────────
        register_rest_route( $ns, '/login', array(
            'methods' => 'POST', 'callback' => array( $this, 'handle_login' ), 'permission_callback' => '__return_true',
        ) );

        // ── Forgot Password ─────────────────────────────────────────────
        register_rest_route( $ns, '/forgot-password', array(
            'methods' => 'POST', 'callback' => array( $this, 'handle_forgot_password' ), 'permission_callback' => '__return_true',
        ) );

        // ── Logout ──────────────────────────────────────────────────────
        register_rest_route( $ns, '/logout', array(
            'methods' => 'POST', 'callback' => array( $this, 'handle_logout' ), 'permission_callback' => 'is_user_logged_in',
        ) );

        // ── Register ────────────────────────────────────────────────────
        register_rest_route( $ns, '/register', array(
            'methods' => 'POST', 'callback' => array( $this, 'handle_register' ), 'permission_callback' => '__return_true',
        ) );
        register_rest_route( $ns, '/users', array(
            'methods' => 'GET', 'callback' => array( $this, 'search_users' ), 'permission_callback' => '__return_true',
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_user' ), 'permission_callback' => '__return_true',
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)/reputation', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_user_reputation' ), 'permission_callback' => '__return_true',
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)/threads', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_user_threads' ), 'permission_callback' => '__return_true',
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)/replies', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_user_replies' ), 'permission_callback' => '__return_true',
        ) );
        register_rest_route( $ns, '/users/me/profile', array(
            'methods' => 'PUT', 'callback' => array( $this, 'update_user_profile' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/users/me/anonymous', array(
            'methods' => 'POST', 'callback' => array( $this, 'toggle_anonymous' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/users/me/avatar', array(
            'methods' => 'POST', 'callback' => array( $this, 'upload_avatar' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/users/me/preferences', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_preferences' ),    'permission_callback' => 'is_user_logged_in' ),
            array( 'methods' => 'PUT',  'callback' => array( $this, 'update_preferences' ), 'permission_callback' => 'is_user_logged_in' ),
        ) );

        // ── Activity ────────────────────────────────────────────────
        register_rest_route( $ns, '/users/me/activity', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_user_activity' ), 'permission_callback' => 'is_user_logged_in',
        ) );

        // ── Connections ────────────────────────────────────────────
        register_rest_route( $ns, '/connections', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_connections' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/connections/requests', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_connection_requests' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/connections/(?P<user_id>\d+)', array(
            array( 'methods' => 'POST',   'callback' => array( $this, 'send_connection_request' ), 'permission_callback' => 'is_user_logged_in' ),
            array( 'methods' => 'DELETE',  'callback' => array( $this, 'remove_connection' ),       'permission_callback' => 'is_user_logged_in' ),
        ) );
        register_rest_route( $ns, '/connections/(?P<user_id>\d+)/accept', array(
            'methods' => 'POST', 'callback' => array( $this, 'accept_connection' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/connections/(?P<user_id>\d+)/decline', array(
            'methods' => 'POST', 'callback' => array( $this, 'decline_connection' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/connections/(?P<user_id>\d+)/block', array(
            'methods' => 'POST', 'callback' => array( $this, 'block_user' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/connections/(?P<user_id>\d+)/unblock', array(
            'methods' => 'POST', 'callback' => array( $this, 'unblock_user' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/connections/status/(?P<user_id>\d+)', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_connection_status' ), 'permission_callback' => 'is_user_logged_in',
        ) );

        // ── Restrictions ───────────────────────────────────────────────
        register_rest_route( $ns, '/restrict/(?P<user_id>\d+)', array(
            'methods' => 'POST', 'callback' => array( $this, 'restrict_user' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/unrestrict/(?P<user_id>\d+)', array(
            'methods' => 'POST', 'callback' => array( $this, 'unrestrict_user' ), 'permission_callback' => 'is_user_logged_in',
        ) );
        register_rest_route( $ns, '/restrictions', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_restrictions' ), 'permission_callback' => 'is_user_logged_in',
        ) );

        // ── Moderation ────────────────────────────────────────────────────
        register_rest_route( $ns, '/threads/(?P<id>\d+)/close', array(
            'methods' => 'POST', 'callback' => array( $this, 'toggle_close_thread' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/threads/(?P<id>\d+)/pin', array(
            'methods' => 'POST', 'callback' => array( $this, 'toggle_pin_thread' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/reports', array(
            array( 'methods' => 'GET', 'callback' => array( $this, 'get_reports' ), 'permission_callback' => array( $this, 'can_moderate' ) ),
        ) );
        register_rest_route( $ns, '/reports/(?P<id>\d+)', array(
            'methods' => 'PUT', 'callback' => array( $this, 'update_report' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)/warn', array(
            'methods' => 'POST', 'callback' => array( $this, 'warn_user' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)/suspend', array(
            'methods' => 'POST', 'callback' => array( $this, 'suspend_user' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/moderation/stats', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_moderation_stats' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/moderation/warnings', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_warnings' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/moderation/warnings/(?P<id>\d+)', array(
            'methods' => 'DELETE', 'callback' => array( $this, 'delete_warning' ), 'permission_callback' => array( $this, 'can_moderate' ),
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)/warnings', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_user_warnings' ), 'permission_callback' => 'is_user_logged_in',
        ) );
    }

    /* ------------------------------------------------------------------
     * Suspension check
     * ------------------------------------------------------------------ */

    /**
     * Check if the current user is suspended.
     * Auto-clears expired suspensions.
     * Returns WP_Error if suspended, false if not.
     */
    private function get_suspension_error() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return false;
        }

        $suspended = get_user_meta( $user_id, 'cnw_suspended', true );
        if ( ! $suspended ) {
            return false;
        }

        // Verify an active suspension actually exists in the DB
        global $wpdb;
        $active = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_warnings WHERE user_id = %d AND type = 'suspension' AND is_active = 1 ORDER BY created_at DESC LIMIT 1",
            $user_id
        ) );

        if ( ! $active ) {
            // Stale meta — clean it up
            delete_user_meta( $user_id, 'cnw_suspended' );
            delete_user_meta( $user_id, 'cnw_suspended_until' );
            return false;
        }

        $until = get_user_meta( $user_id, 'cnw_suspended_until', true );

        // If there's an expiry date and it has passed, auto-clear
        if ( $until && strtotime( $until ) <= time() ) {
            delete_user_meta( $user_id, 'cnw_suspended' );
            delete_user_meta( $user_id, 'cnw_suspended_until' );
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_warnings',
                array( 'is_active' => 0 ),
                array( 'user_id' => $user_id, 'type' => 'suspension', 'is_active' => 1 )
            );
            return false;
        }

        // User is suspended
        if ( $until ) {
            $remaining = strtotime( $until ) - time();
            $days = ceil( $remaining / DAY_IN_SECONDS );
            $msg = sprintf( 'Your account is suspended for %d more day%s.', $days, $days > 1 ? 's' : '' );
        } else {
            $msg = 'Your account has been permanently suspended.';
        }

        return new WP_Error( 'account_suspended', $msg, array( 'status' => 403 ) );
    }

    /**
     * Get suspension info for a user (for profile display).
     */
    private function get_suspension_info( $user_id ) {
        $suspended = get_user_meta( $user_id, 'cnw_suspended', true );
        if ( ! $suspended ) {
            return null;
        }

        // Verify an active suspension actually exists in the DB
        global $wpdb;
        $active = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_warnings WHERE user_id = %d AND type = 'suspension' AND is_active = 1 ORDER BY created_at DESC LIMIT 1",
            $user_id
        ) );

        if ( ! $active ) {
            // Stale meta — clean it up
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
            $remaining = strtotime( $until ) - time();
            $days = ceil( $remaining / DAY_IN_SECONDS );
            return array(
                'is_suspended' => true,
                'permanent'    => false,
                'days_left'    => (int) $days,
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
     * Permission callbacks
     * ------------------------------------------------------------------ */

    public function can_create_thread() {
        return current_user_can( 'cnw_create_threads' ) || current_user_can( 'manage_options' );
    }

    public function can_reply() {
        return current_user_can( 'cnw_reply_threads' ) || current_user_can( 'manage_options' );
    }

    public function can_send_message() {
        return current_user_can( 'cnw_send_messages' ) || current_user_can( 'manage_options' );
    }

    public function can_manage() {
        return current_user_can( 'manage_options' );
    }

    public function can_moderate() {
        return current_user_can( 'cnw_close_threads' ) || current_user_can( 'manage_options' );
    }

    public function can_manage_tag( WP_REST_Request $request ) {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }
        global $wpdb;
        $tag_id = intval( $request['id'] );
        $created_by = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT created_by FROM {$wpdb->prefix}cnw_social_worker_tags WHERE id = %d",
            $tag_id
        ) );
        return $created_by && $created_by === get_current_user_id();
    }

    public function can_manage_thread( WP_REST_Request $request ) {
        if ( current_user_can( 'manage_options' ) || current_user_can( 'cnw_edit_any_post' ) || current_user_can( 'cnw_delete_any_post' ) ) {
            return true;
        }
        global $wpdb;
        $thread_id = intval( $request['id'] );
        $author_id = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d",
            $thread_id
        ) );
        return $author_id && $author_id === get_current_user_id();
    }

    public function can_manage_reply( WP_REST_Request $request ) {
        if ( current_user_can( 'manage_options' ) || current_user_can( 'cnw_edit_any_post' ) || current_user_can( 'cnw_delete_any_post' ) ) {
            return true;
        }
        global $wpdb;
        $reply_id  = intval( $request['id'] );
        $author_id = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d",
            $reply_id
        ) );
        return $author_id && $author_id === get_current_user_id();
    }

    /* ==================================================================
     * THREADS
     * ================================================================== */

    public function get_threads( WP_REST_Request $request ) {
        global $wpdb;

        $filter   = $request->get_param( 'filter' ) ?: 'newest';
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 10 );
        $search   = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
        $offset   = ( $page - 1 ) * $per_page;

        $where = "WHERE t.status IN ('published','approved')";
        $join  = '';
        $order = 'ORDER BY t.is_pinned DESC, t.created_at DESC';

        if ( $search ) {
            $like   = '%' . $wpdb->esc_like( $search ) . '%';
            $where .= $wpdb->prepare(
                " AND (t.title LIKE %s OR t.content LIKE %s OR EXISTS (
                    SELECT 1 FROM {$wpdb->prefix}cnw_social_worker_replies rs
                    WHERE rs.thread_id = t.id AND rs.status = 'approved' AND rs.content LIKE %s
                ))",
                $like, $like, $like
            );
        }

        switch ( $filter ) {
            case 'active':
                $order = 'ORDER BY t.is_pinned DESC, t.updated_at DESC';
                break;
            case 'unanswered':
                $where .= " AND NOT EXISTS (
                    SELECT 1 FROM {$wpdb->prefix}cnw_social_worker_replies r
                    WHERE r.thread_id = t.id AND r.status = 'approved'
                )";
                break;
            case 'my_questions':
                $uid = get_current_user_id();
                if ( $uid ) {
                    $where .= $wpdb->prepare( ' AND t.author_id = %d', $uid );
                }
                break;
            case 'my_answered':
                $uid = get_current_user_id();
                if ( $uid ) {
                    $where .= $wpdb->prepare( ' AND t.author_id = %d', $uid );
                    $where .= " AND EXISTS (
                        SELECT 1 FROM {$wpdb->prefix}cnw_social_worker_replies r
                        WHERE r.thread_id = t.id AND r.status = 'approved'
                    )";
                }
                break;
            case 'my_unanswered':
                $uid = get_current_user_id();
                if ( $uid ) {
                    $where .= $wpdb->prepare( ' AND t.author_id = %d', $uid );
                    $where .= " AND NOT EXISTS (
                        SELECT 1 FROM {$wpdb->prefix}cnw_social_worker_replies r
                        WHERE r.thread_id = t.id AND r.status = 'approved'
                    )";
                }
                break;
            case 'frequent':
                $order = "ORDER BY t.is_pinned DESC, (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') DESC, t.created_at DESC";
                break;
            case 'score':
                $order = "ORDER BY t.is_pinned DESC, (
                    (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = 1)
                    - (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = -1)
                ) DESC, t.created_at DESC";
                break;
        }

        $current_user_id = get_current_user_id();

        // For logged-in users on general filters, show only threads from followed tags (if they follow any)
        $personal_filters = array( 'my_questions', 'my_answered', 'my_unanswered' );
        if ( $current_user_id && ! in_array( $filter, $personal_filters, true ) ) {
            $followed_tag_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT tag_id FROM {$wpdb->prefix}cnw_social_worker_user_followed_tags WHERE user_id = %d",
                $current_user_id
            ) );
            if ( ! empty( $followed_tag_ids ) ) {
                $tag_placeholders = implode( ',', array_fill( 0, count( $followed_tag_ids ), '%d' ) );
                $join  = "LEFT JOIN {$wpdb->prefix}cnw_social_worker_thread_tags ftj ON ftj.thread_id = t.id";
                $where .= $wpdb->prepare(
                    " AND (ftj.tag_id IN ($tag_placeholders) OR t.is_pinned = 1)",
                    ...$followed_tag_ids
                );
            }
        }

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $threads = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT t.*, u.display_name AS author_name, u.ID AS author_id,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') AS reply_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = 1) AS likes,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = -1) AS dislikes,
                (SELECT v.vote_type FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.user_id = %d LIMIT 1) AS user_vote,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st WHERE st.thread_id = t.id AND st.user_id = %d) AS is_saved,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st2 WHERE st2.thread_id = t.id) AS saves_count
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             $join
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             $where $order
             LIMIT %d OFFSET %d",
            $current_user_id,
            $current_user_id,
            $per_page,
            $offset
        ) );

        $total = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT t.id) FROM {$wpdb->prefix}cnw_social_worker_threads t $join $where" );
        // phpcs:enable

        // Batch fetch all tags for these threads (avoid N+1)
        $thread_ids = array_map( function( $t ) { return (int) $t->id; }, $threads );
        $tags_by_thread = array();
        if ( ! empty( $thread_ids ) ) {
            $ids_placeholder = implode( ',', array_map( 'intval', $thread_ids ) );
            $all_tags = $wpdb->get_results(
                "SELECT tt.thread_id, tg.name
                 FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
                 JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tg.id = tt.tag_id
                 WHERE tt.thread_id IN ({$ids_placeholder})"
            );
            foreach ( $all_tags as $tag_row ) {
                $tags_by_thread[ $tag_row->thread_id ][] = $tag_row->name;
            }
        }

        // Attach tags + avatar, handle anonymous, flag reply matches
        foreach ( $threads as &$thread ) {
            $thread->has_reply_match = false;
            if ( $search ) {
                $reply_like  = '%' . $wpdb->esc_like( $search ) . '%';
                $reply_match = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies rs
                     WHERE rs.thread_id = %d AND rs.status = 'approved' AND rs.content LIKE %s",
                    $thread->id, $reply_like
                ) );
                $thread->has_reply_match = $reply_match > 0;
            }

            $thread->tags = isset( $tags_by_thread[ $thread->id ] ) ? $tags_by_thread[ $thread->id ] : array();
            if ( ! empty( $thread->is_anonymous ) && (int) $thread->is_anonymous === 1 ) {
                $thread->author_id     = 0;
                $thread->author_name   = 'Anonymous';
                $thread->author_avatar = CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
                $thread->author_reputation = 0;
            } else {
                $thread->author_avatar     = $this->get_user_avatar( (int) $thread->author_id, 80 );
                $thread->author_reputation = (int) get_user_meta( (int) $thread->author_id, 'cnw_reputation_total', true );
            }
        }

        return array(
            'threads' => $threads,
            'total'   => $total,
            'pages'   => (int) ceil( $total / $per_page ),
        );
    }

    public function get_thread( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        $current_user_id = get_current_user_id();

        $thread = $wpdb->get_row( $wpdb->prepare(
            "SELECT t.*, u.display_name AS author_name, u.ID AS author_id,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = 1) AS likes,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = -1) AS dislikes,
                (SELECT v.vote_type FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.user_id = %d LIMIT 1) AS user_vote,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st WHERE st.thread_id = t.id AND st.user_id = %d) AS is_saved,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st2 WHERE st2.thread_id = t.id) AS saves_count
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             WHERE t.id = %d",
            $current_user_id,
            $current_user_id,
            $id
        ) );

        if ( ! $thread ) {
            return new WP_Error( 'not_found', 'Thread not found', array( 'status' => 404 ) );
        }

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}cnw_social_worker_threads SET views = views + 1 WHERE id = %d",
            $id
        ) );

        // Attach tags + avatar, handle anonymous
        $thread->tags = $wpdb->get_col( $wpdb->prepare(
            "SELECT tg.name FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
             JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tt.tag_id = tg.id
             WHERE tt.thread_id = %d",
            $id
        ) );

        if ( ! empty( $thread->is_anonymous ) && (int) $thread->is_anonymous === 1 ) {
            $thread->author_id         = 0;
            $thread->author_name       = 'Anonymous';
            $thread->author_avatar     = CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
            $thread->author_reputation = 0;
        } else {
            $thread->author_avatar     = $this->get_user_avatar( (int) $thread->author_id, 80 );
            $thread->author_reputation = (int) get_user_meta( (int) $thread->author_id, 'cnw_reputation_total', true );
        }

        return $thread;
    }

    public function create_thread( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;

        // get_json_params() can return null if Content-Type parsing fails; guard against it.
        $params = $request->get_json_params();
        if ( ! is_array( $params ) ) {
            $params = json_decode( $request->get_body(), true );
        }
        if ( ! is_array( $params ) ) {
            $params = array();
        }

        if ( empty( $params['title'] ) || empty( $params['content'] ) ) {
            return new WP_Error( 'missing_fields', 'Title and content are required', array( 'status' => 400 ) );
        }

        if ( isset( $params['anonymous'] ) ) {
            $is_anonymous = ! empty( $params['anonymous'] ) ? 1 : 0;
        } else {
            $is_anonymous = (bool) get_user_meta( get_current_user_id(), 'cnw_anonymous', true ) ? 1 : 0;
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_threads',
            array(
                'author_id'    => get_current_user_id(),
                'title'        => sanitize_text_field( $params['title'] ),
                'content'      => wp_kses_post( $params['content'] ),
                'status'       => get_option( 'cnw_default_thread_status', 'pending' ),
                'is_anonymous' => $is_anonymous,
            ),
            array( '%d', '%s', '%s', '%s', '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create thread', array( 'status' => 500 ) );
        }

        $thread_id = (int) $wpdb->insert_id;

        // Award reputation: +5 for creating a thread
        $this->award_reputation( get_current_user_id(), 5, 'thread_created', 'thread', $thread_id, 'Created a new thread' );

        $thread_title = sanitize_text_field( $params['title'] );
        $this->log_activity( get_current_user_id(), 'thread_created', 'Asked a new question: ' . $thread_title, 5, null, 'thread', $thread_id, '#/thread/' . $thread_id );

        // Save tags
        $tags = isset( $params['tags'] ) && is_array( $params['tags'] ) ? $params['tags'] : array();
        foreach ( $tags as $tag_name ) {
            $tag_name = sanitize_text_field( trim( (string) $tag_name ) );
            if ( '' === $tag_name ) continue;

            $slug = sanitize_title( $tag_name );

            $tag_id = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cnw_social_worker_tags WHERE slug = %s",
                $slug
            ) );

            if ( ! $tag_id ) {
                $wpdb->insert(
                    $wpdb->prefix . 'cnw_social_worker_tags',
                    array( 'name' => $tag_name, 'slug' => $slug ),
                    array( '%s', '%s' )
                );
                $tag_id = (int) $wpdb->insert_id;
            }

            if ( $tag_id ) {
                $wpdb->query( $wpdb->prepare(
                    "INSERT IGNORE INTO {$wpdb->prefix}cnw_social_worker_thread_tags (thread_id, tag_id) VALUES (%d, %d)",
                    $thread_id,
                    $tag_id
                ) );
            }
        }

        return array( 'success' => true, 'id' => $thread_id );
    }

    public function update_thread( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $params = $request->get_json_params();
        $data   = array();

        if ( isset( $params['title'] ) )   $data['title']   = sanitize_text_field( $params['title'] );
        if ( isset( $params['content'] ) ) $data['content'] = wp_kses_post( $params['content'] );
        if ( isset( $params['status'] ) )  $data['status']  = sanitize_text_field( $params['status'] );

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'No fields to update', array( 'status' => 400 ) );
        }

        $current_user_id = get_current_user_id();

        // Get thread author before update for notification
        $thread_author = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", $id
        ) );

        $result = $wpdb->update( $wpdb->prefix . 'cnw_social_worker_threads', $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update thread', array( 'status' => 500 ) );
        }

        $this->log_activity( $current_user_id, 'thread_updated', 'Updated a question', 0, 'No points for editing', 'thread', $id, '#/thread/' . $id );

        // Notify thread author if edited by a moderator/admin
        if ( $thread_author && $thread_author !== $current_user_id ) {
            $actor = get_userdata( $current_user_id );
            $actor_name = $actor ? $actor->display_name : 'A moderator';
            $this->insert_notification(
                $thread_author, $current_user_id, 'thread_edited', 'thread', $id,
                $actor_name . ' edited your thread.'
            );
        }

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_thread( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        // Get thread author before deletion for reputation reversal
        $thread_author = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", $id
        ) );

        // Collect reply authors for reputation reversal
        $reply_authors = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE thread_id = %d", $id
        ) );

        // Remove all reputation entries referencing this thread or its replies/votes
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'thread' AND reference_id = %d", $id
        ) );
        // Remove reputation for replies belonging to this thread
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'reply' AND reference_id IN (SELECT id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE thread_id = %d)", $id
        ) );
        // Remove reputation for votes on this thread
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'vote' AND reference_id IN (SELECT id FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'thread' AND target_id = %d)", $id
        ) );
        // Remove reputation for votes on replies of this thread
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'vote' AND reference_id IN (SELECT id FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'reply' AND target_id IN (SELECT id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE thread_id = %d))", $id
        ) );

        // Delete related replies and votes first
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_replies', array( 'thread_id' => $id ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'thread' AND target_id = %d",
            $id
        ) );

        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_threads', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete thread', array( 'status' => 500 ) );
        }

        // Recalculate reputation for all affected users
        $affected_users = array_unique( array_merge( array( $thread_author ), $reply_authors ) );
        foreach ( $affected_users as $uid ) {
            if ( $uid ) $this->recalc_user_reputation( (int) $uid );
        }

        $current_user_id = get_current_user_id();
        $this->log_activity( $current_user_id, 'thread_deleted', 'Deleted a question', -5, 'Points reversed for deleted question' );

        // Notify thread author if deleted by a moderator/admin
        if ( $thread_author && $thread_author !== $current_user_id ) {
            $actor = get_userdata( $current_user_id );
            $actor_name = $actor ? $actor->display_name : 'A moderator';
            $this->insert_notification(
                $thread_author, $current_user_id, 'thread_deleted', 'thread', $id,
                $actor_name . ' deleted your thread.'
            );
        }

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * REPLIES
     * ================================================================== */

    /** GET /threads/{id}/replies — replies for a specific thread (frontend uses this) */
    public function get_thread_replies( WP_REST_Request $request ) {
        global $wpdb;

        $thread_id       = intval( $request['id'] );
        $current_user_id = get_current_user_id();

        $replies = $wpdb->get_results( $wpdb->prepare(
            "SELECT r.*, u.display_name AS author_name, u.ID AS author_id,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'reply' AND v.target_id = r.id AND v.vote_type = 1) AS likes,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'reply' AND v.target_id = r.id AND v.vote_type = -1) AS dislikes,
                (SELECT v.vote_type FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'reply' AND v.target_id = r.id AND v.user_id = %d LIMIT 1) AS user_vote,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r2 WHERE r2.parent_id = r.id AND r2.status = 'approved') AS reply_count
             FROM {$wpdb->prefix}cnw_social_worker_replies r
             LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
             WHERE r.thread_id = %d AND r.status = 'approved'
             ORDER BY r.created_at ASC",
            $current_user_id,
            $thread_id
        ) );

        foreach ( $replies as &$reply ) {
            if ( ! empty( $reply->is_anonymous ) && (int) $reply->is_anonymous === 1 ) {
                $reply->author_name       = 'Anonymous';
                $reply->author_avatar     = CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
                $reply->author_id         = 0;
                $reply->author_reputation = 0;
            } else {
                $reply->author_avatar     = $this->get_user_avatar( (int) $reply->author_id, 40 );
                $reply->author_reputation = (int) get_user_meta( (int) $reply->author_id, 'cnw_reputation_total', true );
            }
        }

        return array( 'replies' => $replies );
    }

    /** GET /replies — list all replies with pagination */
    public function list_replies( WP_REST_Request $request ) {
        global $wpdb;

        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 20 );
        $offset   = ( $page - 1 ) * $per_page;

        $replies = $wpdb->get_results( $wpdb->prepare(
            "SELECT r.*, u.display_name AS author_name, t.title AS thread_title
             FROM {$wpdb->prefix}cnw_social_worker_replies r
             LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
             LEFT JOIN {$wpdb->prefix}cnw_social_worker_threads t ON r.thread_id = t.id
             ORDER BY r.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ) );

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies" );

        return array( 'replies' => $replies, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function get_reply( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        $reply = $wpdb->get_row( $wpdb->prepare(
            "SELECT r.*, u.display_name AS author_name
             FROM {$wpdb->prefix}cnw_social_worker_replies r
             LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
             WHERE r.id = %d",
            $id
        ) );

        if ( ! $reply ) {
            return new WP_Error( 'not_found', 'Reply not found', array( 'status' => 404 ) );
        }

        return $reply;
    }

    public function create_reply( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['thread_id'] ) || empty( $params['content'] ) ) {
            return new WP_Error( 'missing_fields', 'thread_id and content are required', array( 'status' => 400 ) );
        }

        // Check if thread is closed
        $thread_check = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, is_closed FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d",
            intval( $params['thread_id'] )
        ) );
        if ( $thread_check && $thread_check->is_closed ) {
            return new WP_Error( 'thread_closed', 'This thread is closed and no longer accepting replies.', array( 'status' => 403 ) );
        }

        $user_id = get_current_user_id();
        $is_anonymous = (bool) get_user_meta( $user_id, 'cnw_anonymous', true ) ? 1 : 0;

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_replies',
            array(
                'thread_id'    => intval( $params['thread_id'] ),
                'author_id'    => $user_id,
                'parent_id'    => isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null,
                'content'      => wp_kses_post( $params['content'] ),
                'status'       => get_option( 'cnw_default_reply_status', 'approved' ),
                'is_anonymous' => $is_anonymous,
            ),
            array( '%d', '%d', '%d', '%s', '%s', '%d' )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create reply', array( 'status' => 500 ) );
        }

        $reply_id  = $wpdb->insert_id;
        $actor_id  = get_current_user_id();
        $thread_id = intval( $params['thread_id'] );
        $actor_name = wp_get_current_user()->display_name;

        // Fetch thread author to check self-reply
        $thread_author = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", $thread_id
        ) );

        // Award reputation: +2 for creating a reply, but NOT if replying to own thread
        if ( $actor_id !== $thread_author ) {
            $this->award_reputation( $actor_id, 2, 'reply_created', 'reply', $reply_id, 'Posted a reply' );
            $this->log_activity( $actor_id, 'reply_created', 'Posted a reply', 2, null, 'thread', $thread_id, '#/thread/' . $thread_id );
        } else {
            $this->log_activity( $actor_id, 'reply_created', 'Posted a reply to own thread', 0, 'No points for replying to own thread', 'thread', $thread_id, '#/thread/' . $thread_id );
        }
        if ( $thread_author && $thread_author !== $actor_id ) {
            $this->insert_notification( $thread_author, $actor_id, 'reply', 'thread', $thread_id,
                "{$actor_name} replied to your thread." );
        }

        // If replying to a reply, notify parent reply author
        if ( ! empty( $params['parent_id'] ) ) {
            $parent_author = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", intval( $params['parent_id'] )
            ) );
            if ( $parent_author && $parent_author !== $thread_author ) {
                $this->insert_notification( $parent_author, $actor_id, 'reply', 'thread', $thread_id,
                    "{$actor_name} replied to your comment." );
            }
        }

        return array( 'success' => true, 'id' => $reply_id );
    }

    public function update_reply( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $params = $request->get_json_params();
        $data   = array();

        if ( isset( $params['content'] ) )   $data['content']   = wp_kses_post( $params['content'] );
        if ( isset( $params['status'] ) )    $data['status']    = sanitize_text_field( $params['status'] );
        if ( isset( $params['parent_id'] ) ) $data['parent_id'] = intval( $params['parent_id'] );

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'No fields to update', array( 'status' => 400 ) );
        }

        $current_user_id = get_current_user_id();

        // Get reply author and thread_id before update for notification
        $reply_row = $wpdb->get_row( $wpdb->prepare(
            "SELECT author_id, thread_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $id
        ) );
        $reply_author = $reply_row ? (int) $reply_row->author_id : 0;
        $thread_id    = $reply_row ? (int) $reply_row->thread_id : 0;

        $result = $wpdb->update( $wpdb->prefix . 'cnw_social_worker_replies', $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update reply', array( 'status' => 500 ) );
        }

        $this->log_activity( $current_user_id, 'reply_updated', 'Updated a reply', 0, 'No points for editing', 'thread', $thread_id, '#/thread/' . $thread_id );

        // Notify reply author if edited by a moderator/admin
        if ( $reply_author && $reply_author !== $current_user_id ) {
            $actor = get_userdata( $current_user_id );
            $actor_name = $actor ? $actor->display_name : 'A moderator';
            $this->insert_notification(
                $reply_author, $current_user_id, 'reply_edited', 'thread', $thread_id,
                $actor_name . ' edited your reply.'
            );
        }

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_reply( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        // Get reply author and thread_id before deletion for reputation reversal and notification
        $reply_row = $wpdb->get_row( $wpdb->prepare(
            "SELECT author_id, thread_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $id
        ) );
        $reply_author = $reply_row ? (int) $reply_row->author_id : 0;
        $reply_thread_id = $reply_row ? (int) $reply_row->thread_id : 0;

        // Collect all affected user IDs (vote reputation recipients)
        $affected_users = array( $reply_author );

        // Remove reputation entries for this reply and its votes
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'reply' AND reference_id = %d", $id
        ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'vote' AND reference_id IN (SELECT id FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'reply' AND target_id = %d)", $id
        ) );
        // Also remove reputation for child replies
        $child_authors = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE parent_id = %d", $id
        ) );
        $affected_users = array_merge( $affected_users, $child_authors );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'reply' AND reference_id IN (SELECT id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE parent_id = %d)", $id
        ) );

        // Delete child replies
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_replies', array( 'parent_id' => $id ) );
        // Delete votes on this reply
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'reply' AND target_id = %d",
            $id
        ) );

        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_replies', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete reply', array( 'status' => 500 ) );
        }

        // Recalculate reputation for all affected users
        foreach ( array_unique( array_filter( $affected_users ) ) as $uid ) {
            $this->recalc_user_reputation( (int) $uid );
        }

        $current_user_id = get_current_user_id();
        $this->log_activity( $current_user_id, 'reply_deleted', 'Deleted a reply', -2, 'Points reversed for deleted reply' );

        // Notify reply author if deleted by a moderator/admin
        if ( $reply_author && $reply_author !== $current_user_id ) {
            $actor = get_userdata( $current_user_id );
            $actor_name = $actor ? $actor->display_name : 'A moderator';
            $this->insert_notification(
                $reply_author, $current_user_id, 'reply_deleted', 'thread', $reply_thread_id,
                $actor_name . ' deleted your reply.'
            );
        }

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * MARK REPLY AS SOLUTION
     * ================================================================== */

    public function mark_solution( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;

        $reply_id = intval( $request['id'] );
        $user_id  = get_current_user_id();

        // Get the reply and its thread
        $reply = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $reply_id
        ) );
        if ( ! $reply ) {
            return new WP_Error( 'not_found', 'Reply not found', array( 'status' => 404 ) );
        }

        // Only the thread author can mark/unmark a solution.
        $thread = $wpdb->get_row( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", (int) $reply->thread_id
        ) );
        if ( ! $thread || (int) $thread->author_id !== $user_id ) {
            return new WP_Error( 'forbidden', 'Only the question author can accept an answer.', array( 'status' => 403 ) );
        }

        $is_own_reply = ( (int) $reply->author_id === $user_id );

        // Cannot accept your own reply.
        if ( $is_own_reply && ! (int) $reply->is_solution ) {
            return new WP_Error( 'forbidden', 'You cannot accept your own answer.', array( 'status' => 403 ) );
        }

        $is_solution = (int) $reply->is_solution;

        $actor_name = wp_get_current_user()->display_name;

        if ( $is_solution ) {
            // Unmark solution.
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_replies',
                array( 'is_solution' => 0 ),
                array( 'id' => $reply_id )
            );
            // Remove solution reputation.
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE action_type = 'best_answer' AND reference_type = 'reply' AND reference_id = %d",
                $reply_id
            ) );
            $this->recalc_user_reputation( (int) $reply->author_id );

            // Log activity & notify the reply author about the undo.
            if ( ! $is_own_reply ) {
                $this->log_activity( (int) $reply->author_id, 'best_answer_removed', 'Your reply was unmarked as best answer (-25 points)', -25, null, 'thread', (int) $reply->thread_id, '#/thread/' . (int) $reply->thread_id );
                $this->insert_notification( (int) $reply->author_id, $user_id, 'solution_removed', 'thread', (int) $reply->thread_id,
                    "{$actor_name} unmarked your reply as best answer." );
            }
            $this->log_activity( $user_id, 'unmarked_solution', 'Unmarked a reply as best answer', 0, null, 'thread', (int) $reply->thread_id, '#/thread/' . (int) $reply->thread_id );

            return array( 'success' => true, 'action' => 'unmarked', 'is_solution' => false );
        } else {
            // Mark this reply as solution (multiple allowed per thread).
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_replies',
                array( 'is_solution' => 1 ),
                array( 'id' => $reply_id )
            );
            // Award +25 reputation to reply author (skip if marking own reply).
            if ( ! $is_own_reply ) {
                $this->award_reputation( (int) $reply->author_id, 25, 'best_answer', 'reply', $reply_id, 'Reply marked as best answer' );
                $this->log_activity( (int) $reply->author_id, 'best_answer', 'Your reply was marked as the best answer', 25, null, 'thread', (int) $reply->thread_id, '#/thread/' . (int) $reply->thread_id );

                $this->insert_notification( (int) $reply->author_id, $user_id, 'solution', 'thread', (int) $reply->thread_id,
                    "{$actor_name} marked your reply as best answer." );
            }

            $this->log_activity( $user_id, 'marked_solution', 'Marked a reply as the best answer', 0, null, 'thread', (int) $reply->thread_id, '#/thread/' . (int) $reply->thread_id );

            return array( 'success' => true, 'action' => 'marked', 'is_solution' => true );
        }
    }

    /* ==================================================================
     * MESSAGES
     * ================================================================== */

    public function get_messages( WP_REST_Request $request ) {
        global $wpdb;

        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 20 );
        $offset   = ( $page - 1 ) * $per_page;

        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT m.*, s.display_name AS sender_name, r.display_name AS recipient_name
             FROM {$wpdb->prefix}cnw_social_worker_messages m
             LEFT JOIN {$wpdb->users} s ON m.sender_id = s.ID
             LEFT JOIN {$wpdb->users} r ON m.recipient_id = r.ID
             ORDER BY m.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ) );

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_messages" );

        return array( 'messages' => $messages, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function get_message( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        $message = $wpdb->get_row( $wpdb->prepare(
            "SELECT m.*, s.display_name AS sender_name, r.display_name AS recipient_name
             FROM {$wpdb->prefix}cnw_social_worker_messages m
             LEFT JOIN {$wpdb->users} s ON m.sender_id = s.ID
             LEFT JOIN {$wpdb->users} r ON m.recipient_id = r.ID
             WHERE m.id = %d",
            $id
        ) );

        if ( ! $message ) {
            return new WP_Error( 'not_found', 'Message not found', array( 'status' => 404 ) );
        }

        return $message;
    }

    public function create_message( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;

        $params = $request->get_json_params();

        $has_content    = ! empty( $params['content'] );
        $has_attachment = ! empty( $params['attachment_url'] );
        if ( empty( $params['recipient_id'] ) || ( ! $has_content && ! $has_attachment ) ) {
            return new WP_Error( 'missing_fields', 'recipient_id and content or attachment are required', array( 'status' => 400 ) );
        }

        // Check recipient's message_privacy preference
        $sender    = get_current_user_id();
        $recipient = intval( $params['recipient_id'] );
        $recipient_prefs = $this->get_user_preferences( $recipient );
        if ( $recipient_prefs['message_privacy'] === 'connections' && ! $this->are_connected( $sender, $recipient ) ) {
            return new WP_Error( 'not_connected', 'This user only accepts messages from connections.', array( 'status' => 403 ) );
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_messages',
            array(
                'sender_id'    => get_current_user_id(),
                'recipient_id' => intval( $params['recipient_id'] ),
                'subject'      => isset( $params['subject'] ) ? sanitize_text_field( $params['subject'] ) : null,
                'content'      => isset( $params['content'] ) ? wp_kses_post( $params['content'] ) : '',
                'is_read'      => 0,
                'parent_id'    => isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null,
                'attachment_url'  => isset( $params['attachment_url'] ) ? esc_url_raw( $params['attachment_url'] ) : null,
                'attachment_name' => isset( $params['attachment_name'] ) ? sanitize_text_field( $params['attachment_name'] ) : null,
                'attachment_type' => isset( $params['attachment_type'] ) ? sanitize_text_field( $params['attachment_type'] ) : null,
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create message', array( 'status' => 500 ) );
        }

        $msg_id      = $wpdb->insert_id;
        $sender_id   = get_current_user_id();
        $recipient_id = intval( $params['recipient_id'] );
        $sender      = get_userdata( $sender_id );
        $sender_avatar = get_user_meta( $sender_id, 'cnw_avatar_url', true ) ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;

        Cnw_Social_Bridge_Pusher::trigger(
            'private-user-' . $recipient_id,
            'new-message',
            array(
                'id'            => $msg_id,
                'sender_id'     => $sender_id,
                'sender_name'   => $sender ? $sender->display_name : 'Unknown',
                'sender_avatar' => $sender_avatar,
                'recipient_id'  => $recipient_id,
                'content'       => isset( $params['content'] ) ? wp_kses_post( $params['content'] ) : '',
                'created_at'    => current_time( 'mysql' ),
                'attachment_url'  => isset( $params['attachment_url'] ) ? esc_url_raw( $params['attachment_url'] ) : null,
                'attachment_name' => isset( $params['attachment_name'] ) ? sanitize_text_field( $params['attachment_name'] ) : null,
                'attachment_type' => isset( $params['attachment_type'] ) ? sanitize_text_field( $params['attachment_type'] ) : null,
            )
        );

        return array( 'success' => true, 'id' => $msg_id );
    }

    public function update_message( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_messages';
        $id    = intval( $request['id'] );
        $me    = get_current_user_id();

        // Check ownership (admins can edit any)
        if ( ! current_user_can( 'manage_options' ) ) {
            $msg = $wpdb->get_row( $wpdb->prepare( "SELECT sender_id FROM {$table} WHERE id = %d", $id ) );
            if ( ! $msg || (int) $msg->sender_id !== $me ) {
                return new WP_Error( 'forbidden', 'You can only edit your own messages', array( 'status' => 403 ) );
            }
        }

        $params = $request->get_json_params();
        $data   = array();

        if ( isset( $params['subject'] ) ) $data['subject'] = sanitize_text_field( $params['subject'] );
        if ( isset( $params['content'] ) ) $data['content'] = wp_kses_post( $params['content'] );
        if ( isset( $params['is_read'] ) ) $data['is_read'] = intval( $params['is_read'] );

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'No fields to update', array( 'status' => 400 ) );
        }

        $result = $wpdb->update( $table, $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update message', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_message( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_messages';
        $id    = intval( $request['id'] );
        $me    = get_current_user_id();

        // Check ownership (admins can delete any)
        if ( ! current_user_can( 'manage_options' ) ) {
            $msg = $wpdb->get_row( $wpdb->prepare( "SELECT sender_id FROM {$table} WHERE id = %d", $id ) );
            if ( ! $msg || (int) $msg->sender_id !== $me ) {
                return new WP_Error( 'forbidden', 'You can only delete your own messages', array( 'status' => 403 ) );
            }
        }

        $result = $wpdb->delete( $table, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete message', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'deleted' => $id );
    }

    public function toggle_pin_message( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_messages';
        $id = intval( $request['id'] );
        $me = get_current_user_id();

        $msg = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, sender_id, recipient_id, is_pinned FROM {$table} WHERE id = %d", $id
        ) );

        if ( ! $msg ) {
            return new WP_Error( 'not_found', 'Message not found', array( 'status' => 404 ) );
        }

        // Only participants can pin
        if ( (int) $msg->sender_id !== $me && (int) $msg->recipient_id !== $me ) {
            return new WP_Error( 'forbidden', 'You are not a participant', array( 'status' => 403 ) );
        }

        $new_val = $msg->is_pinned ? 0 : 1;
        $wpdb->update( $table, array( 'is_pinned' => $new_val ), array( 'id' => $id ) );

        return array( 'success' => true, 'is_pinned' => (bool) $new_val );
    }

    public function upload_message_attachment( WP_REST_Request $request ) {
        $files = $request->get_file_params();

        if ( empty( $files['file'] ) ) {
            return new WP_Error( 'no_file', 'No file uploaded.', array( 'status' => 400 ) );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Block dangerous file types (HTML, SVG, PHP, etc.)
        $blocked_exts = array( 'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar', 'html', 'htm', 'svg', 'js', 'exe', 'bat', 'cmd', 'sh', 'cgi' );
        $ext = strtolower( pathinfo( $files['file']['name'], PATHINFO_EXTENSION ) );
        if ( in_array( $ext, $blocked_exts, true ) ) {
            return new WP_Error( 'invalid_type', 'This file type is not allowed.', array( 'status' => 400 ) );
        }

        $attachment_id = media_handle_upload( 'file', 0 );

        if ( is_wp_error( $attachment_id ) ) {
            return new WP_Error( 'upload_failed', $attachment_id->get_error_message(), array( 'status' => 500 ) );
        }

        $url  = wp_get_attachment_url( $attachment_id );
        $name = basename( $files['file']['name'] );
        $mime = get_post_mime_type( $attachment_id );

        // Determine type category
        $type = 'file';
        if ( strpos( $mime, 'image/' ) === 0 ) {
            $type = 'image';
        } elseif ( strpos( $mime, 'video/' ) === 0 ) {
            $type = 'video';
        }

        return array(
            'success'         => true,
            'attachment_url'  => $url,
            'attachment_name' => $name,
            'attachment_type' => $type,
        );
    }

    /* ==================================================================
     * CONVERSATIONS (user-facing)
     * ================================================================== */

    public function get_conversations( WP_REST_Request $request ) {
        global $wpdb;

        $me       = get_current_user_id();
        $table    = $wpdb->prefix . 'cnw_social_worker_messages';
        $conn_tbl = $wpdb->prefix . 'cnw_social_worker_connections';
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = 10;
        $offset   = ( $page - 1 ) * $per_page;

        // Count total conversations
        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END)
             FROM {$table}
             WHERE sender_id = %d OR recipient_id = %d",
            $me, $me, $me
        ) );

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                CASE WHEN m.sender_id = %d THEN m.recipient_id ELSE m.sender_id END AS other_user_id,
                u.display_name AS other_name,
                um_avatar.meta_value AS other_avatar,
                um_label.meta_value AS other_verified_label,
                m.content AS last_message,
                m.created_at AS last_date,
                m.sender_id AS last_sender_id,
                m.is_read AS last_is_read,
                (SELECT COUNT(*) FROM {$table} m2
                 WHERE m2.sender_id = CASE WHEN m.sender_id = %d THEN m.recipient_id ELSE m.sender_id END
                   AND m2.recipient_id = %d AND m2.is_read = 0) AS unread_count
             FROM {$table} m
             INNER JOIN (
                SELECT
                    CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS partner_id,
                    MAX(id) AS max_id
                FROM {$table}
                WHERE sender_id = %d OR recipient_id = %d
                GROUP BY partner_id
             ) latest ON m.id = latest.max_id
             LEFT JOIN {$wpdb->users} u ON u.ID = CASE WHEN m.sender_id = %d THEN m.recipient_id ELSE m.sender_id END
             LEFT JOIN {$wpdb->usermeta} um_avatar ON um_avatar.user_id = u.ID AND um_avatar.meta_key = 'cnw_avatar_url'
             LEFT JOIN {$wpdb->usermeta} um_label ON um_label.user_id = u.ID AND um_label.meta_key = 'cnw_verified_label'
             ORDER BY m.created_at DESC
             LIMIT %d OFFSET %d",
            $me, $me, $me, $me, $me, $me, $me, $per_page, $offset
        ) );

        foreach ( $rows as &$row ) {
            if ( empty( $row->other_avatar ) ) {
                $row->other_avatar = CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
            }
            $other_id = (int) $row->other_user_id;
            // If the other user has restricted me, hide my visibility info from them
            // If I have restricted the other user, hide their info from me
            $restricted_by_other = $this->has_restricted( $other_id, $me );
            $restricted_by_me    = $this->has_restricted( $me, $other_id );
            $row->is_online    = ( $restricted_by_other || $restricted_by_me ) ? false : $this->is_user_online( $other_id, $me );
            $row->is_connected = $this->are_connected( $me, $other_id );
            $row->blocked_by   = $this->get_blocked_by( $me, $other_id );
            // Determine if current user can message this user
            $other_prefs = $this->get_user_preferences( $other_id );
            $row->can_message = ( $other_prefs['message_privacy'] === 'connections' ) ? $row->is_connected : true;
            if ( $restricted_by_other || $restricted_by_me ) {
                $row->unread_count = 0;
                if ( (int) $row->last_sender_id === $me ) {
                    $row->last_is_read = 0; // Hide read receipt
                }
            }
        }

        return array(
            'conversations' => $rows,
            'has_more'      => ( $page * $per_page ) < $total,
        );
    }

    /**
     * Return total unread message count for the current user.
     */
    public function get_unread_message_count() {
        global $wpdb;
        $me    = get_current_user_id();
        $table = $wpdb->prefix . 'cnw_social_worker_messages';
        $r_table = $wpdb->prefix . 'cnw_social_worker_restrictions';

        // Exclude unread messages from users I've restricted or who've restricted me
        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} m
             WHERE m.recipient_id = %d AND m.is_read = 0
             AND NOT EXISTS (
                SELECT 1 FROM {$r_table} r
                WHERE (r.restricter_id = %d AND r.restricted_id = m.sender_id)
                   OR (r.restricter_id = m.sender_id AND r.restricted_id = %d)
             )",
            $me, $me, $me
        ) );
        return array( 'count' => $count );
    }

    public function get_conversation( WP_REST_Request $request ) {
        global $wpdb;

        $me       = get_current_user_id();
        $other    = intval( $request['user_id'] );
        $table    = $wpdb->prefix . 'cnw_social_worker_messages';
        $before   = intval( $request->get_param( 'before' ) ?: 0 );
        $per_page = 20;

        $before_clause = $before ? $wpdb->prepare( ' AND m.id < %d', $before ) : '';

        // Total count for this conversation
        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} m
             WHERE ((m.sender_id = %d AND m.recipient_id = %d)
                OR (m.sender_id = %d AND m.recipient_id = %d))
             {$before_clause}",
            $me, $other, $other, $me
        ) );

        // Fetch latest N messages (DESC), then reverse to chronological order
        $messages = $wpdb->get_results( $wpdb->prepare(
            "SELECT m.*, s.display_name AS sender_name,
                    um_avatar.meta_value AS sender_avatar,
                    um_label.meta_value AS sender_verified_label
             FROM {$table} m
             LEFT JOIN {$wpdb->users} s ON s.ID = m.sender_id
             LEFT JOIN {$wpdb->usermeta} um_avatar ON um_avatar.user_id = m.sender_id AND um_avatar.meta_key = 'cnw_avatar_url'
             LEFT JOIN {$wpdb->usermeta} um_label ON um_label.user_id = m.sender_id AND um_label.meta_key = 'cnw_verified_label'
             WHERE ((m.sender_id = %d AND m.recipient_id = %d)
                OR (m.sender_id = %d AND m.recipient_id = %d))
             {$before_clause}
             ORDER BY m.id DESC
             LIMIT %d",
            $me, $other, $other, $me, $per_page
        ) );

        $messages = array_reverse( $messages );

        foreach ( $messages as &$msg ) {
            if ( empty( $msg->sender_avatar ) ) {
                $msg->sender_avatar = CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
            }
        }

        $other_user   = get_userdata( $other );
        $other_avatar = get_user_meta( $other, 'cnw_avatar_url', true ) ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
        $other_label  = get_user_meta( $other, 'cnw_verified_label', true );

        $has_more = $total > $per_page;

        // Hide online status and read receipts if either side has restricted the other
        $any_restriction = $this->has_restricted( $other, $me ) || $this->has_restricted( $me, $other );
        $is_online = $any_restriction ? false : $this->is_user_online( $other, $me );

        if ( $any_restriction ) {
            foreach ( $messages as &$msg ) {
                if ( (int) $msg->sender_id === $me ) {
                    $msg->is_read = 0;
                }
            }
        }

        $is_connected = $this->are_connected( $me, $other );
        $blocked_by   = $this->get_blocked_by( $me, $other );

        // Determine if current user can actually send messages to this user
        $can_message = false;
        if ( $me ) {
            $other_prefs = $this->get_user_preferences( $other );
            if ( $other_prefs['message_privacy'] === 'connections' ) {
                $can_message = $is_connected;
            } else {
                $can_message = true;
            }
        }

        return array(
            'messages'     => $messages,
            'has_more'     => $has_more,
            'is_connected' => $is_connected,
            'can_message'  => $can_message,
            'blocked_by'   => $blocked_by,
            'other_user'   => array(
                'id'             => $other,
                'name'           => $other_user ? $other_user->display_name : 'Unknown',
                'avatar'         => $other_avatar,
                'verified_label' => $other_label,
                'is_online'      => $is_online,
            ),
        );
    }

    public function mark_conversation_read( WP_REST_Request $request ) {
        global $wpdb;

        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $table = $wpdb->prefix . 'cnw_social_worker_messages';

        $updated = $wpdb->query( $wpdb->prepare(
            "UPDATE {$table} SET is_read = 1 WHERE sender_id = %d AND recipient_id = %d AND is_read = 0",
            $other, $me
        ) );

        // Notify the sender that their messages have been read
        // But skip if either side has restricted the other
        if ( $updated > 0 && ! $this->has_restricted( $me, $other ) && ! $this->has_restricted( $other, $me ) ) {
            Cnw_Social_Bridge_Pusher::trigger(
                'private-user-' . $other,
                'messages-read',
                array( 'reader_id' => $me )
            );
        }

        return array( 'success' => true );
    }

    /**
     * Set typing status for the current user in a conversation.
     * Stores a transient that expires after 5 seconds and broadcasts via Pusher.
     */
    public function set_typing( WP_REST_Request $request ) {
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $key   = 'cnw_typing_' . $me . '_' . $other;

        set_transient( $key, time(), 5 );

        // Don't broadcast typing if either side has restricted the other
        if ( ! $this->has_restricted( $me, $other ) && ! $this->has_restricted( $other, $me ) ) {
            $user = get_userdata( $me );
            Cnw_Social_Bridge_Pusher::trigger(
                'private-user-' . $other,
                'client-typing',
                array(
                    'user_id' => $me,
                    'name'    => $user ? $user->display_name : 'Unknown',
                )
            );
        }

        return array( 'success' => true );
    }

    /**
     * Check if the other user is currently typing to the current user.
     */
    public function get_typing( WP_REST_Request $request ) {
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $key   = 'cnw_typing_' . $other . '_' . $me;

        $typing_at = get_transient( $key );

        return array( 'is_typing' => $typing_at !== false );
    }

    /**
     * Authenticate a Pusher private channel subscription.
     * Users can only subscribe to their own private-user-{id} channel.
     */
    public function pusher_auth( WP_REST_Request $request ) {
        $params       = $request->get_params();
        $socket_id    = sanitize_text_field( $params['socket_id'] ?? '' );
        $channel_name = sanitize_text_field( $params['channel_name'] ?? '' );

        if ( ! $socket_id || ! $channel_name ) {
            return new WP_Error( 'missing_params', 'socket_id and channel_name required', array( 'status' => 400 ) );
        }

        $me = get_current_user_id();
        if ( $channel_name !== 'private-user-' . $me ) {
            return new WP_Error( 'forbidden', 'You can only subscribe to your own channel', array( 'status' => 403 ) );
        }

        $auth = Cnw_Social_Bridge_Pusher::auth( $channel_name, $socket_id );
        if ( is_wp_error( $auth ) ) {
            return $auth;
        }

        // authorizeChannel returns a JSON string — decode for WP_REST_Response
        return new WP_REST_Response( json_decode( $auth, true ), 200 );
    }

    /**
     * Broadcast online/offline status to all conversation partners via Pusher.
     */
    public function pusher_broadcast_status( WP_REST_Request $request ) {
        global $wpdb;

        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            $params = json_decode( $request->get_body(), true ) ?: array();
        }
        $status = sanitize_text_field( $params['status'] ?? 'online' );
        $me     = get_current_user_id();

        // Persist the online flag
        update_user_meta( $me, 'cnw_is_online', $status === 'online' ? 1 : 0 );

        $table  = $wpdb->prefix . 'cnw_social_worker_messages';

        // Get all unique conversation partner IDs
        $partners = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS partner_id
             FROM {$table}
             WHERE sender_id = %d OR recipient_id = %d",
            $me, $me, $me
        ) );

        $payload = array(
            'user_id' => $me,
            'status'  => $status,
        );

        // Get users who have restricted me (they shouldn't see my status)
        $restricters = $this->get_restricters_of( $me );
        // Get users I have restricted (I don't want them to see my status)
        $my_restricted = $this->get_restricted_by( $me );
        $skip_ids = array_unique( array_merge( $restricters, $my_restricted ) );

        // Broadcast to each partner's private channel (skip restricted)
        foreach ( $partners as $partner_id ) {
            if ( in_array( (int) $partner_id, $skip_ids, true ) ) {
                continue;
            }
            Cnw_Social_Bridge_Pusher::trigger(
                'private-user-' . (int) $partner_id,
                'user-status',
                $payload
            );
        }

        return array( 'success' => true );
    }

    /**
     * Pusher webhook handler — called by Pusher when channels are vacated.
     * When a user's private channel is vacated (WebSocket closed), mark them offline
     * and broadcast to all conversation partners instantly.
     */
    public function pusher_webhook( WP_REST_Request $request ) {
        global $wpdb;

        // Verify the webhook signature from Pusher
        $body      = $request->get_body();
        $signature = $request->get_header( 'X-Pusher-Signature' );

        if ( ! Cnw_Social_Bridge_Pusher::verify_webhook( $body, $signature ) ) {
            return new WP_Error( 'invalid_signature', 'Invalid webhook signature', array( 'status' => 401 ) );
        }

        $payload = json_decode( $body, true );
        if ( empty( $payload['events'] ) || ! is_array( $payload['events'] ) ) {
            return array( 'ok' => true );
        }

        foreach ( $payload['events'] as $event ) {
            $event_name  = $event['name'] ?? '';
            $channel     = $event['channel'] ?? '';

            // Only handle channel_vacated for private-user-{id} channels
            if ( $event_name !== 'channel_vacated' ) {
                continue;
            }
            if ( ! preg_match( '/^private-user-(\d+)$/', $channel, $m ) ) {
                continue;
            }

            $user_id = (int) $m[1];

            // Already offline — skip
            if ( ! get_user_meta( $user_id, 'cnw_is_online', true ) ) {
                continue;
            }

            // Mark offline
            update_user_meta( $user_id, 'cnw_is_online', 0 );

            // Broadcast offline to all conversation partners
            $table    = $wpdb->prefix . 'cnw_social_worker_messages';
            $partners = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS partner_id
                 FROM {$table}
                 WHERE sender_id = %d OR recipient_id = %d",
                $user_id, $user_id, $user_id
            ) );

            $offline_payload = array( 'user_id' => $user_id, 'status' => 'offline' );
            foreach ( $partners as $partner_id ) {
                Cnw_Social_Bridge_Pusher::trigger(
                    'private-user-' . (int) $partner_id,
                    'user-status',
                    $offline_payload
                );
            }
        }

        return array( 'ok' => true );
    }

    /* ==================================================================
     * CATEGORIES
     * ================================================================== */

    public function get_categories( WP_REST_Request $request ) {
        $cats = get_transient( 'cnw_categories' );
        if ( false === $cats ) {
            global $wpdb;
            $cats = $wpdb->get_results(
                "SELECT * FROM {$wpdb->prefix}cnw_social_worker_categories ORDER BY sort_order ASC, name ASC"
            );
            set_transient( 'cnw_categories', $cats, HOUR_IN_SECONDS );
        }
        return $cats;
    }

    public function get_category( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        $cat = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_categories WHERE id = %d", $id
        ) );

        if ( ! $cat ) {
            return new WP_Error( 'not_found', 'Category not found', array( 'status' => 404 ) );
        }

        return $cat;
    }

    public function create_category( WP_REST_Request $request ) {
        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['name'] ) ) {
            return new WP_Error( 'missing_fields', 'name is required', array( 'status' => 400 ) );
        }

        $slug = sanitize_title( $params['name'] );
        if ( isset( $params['slug'] ) && $params['slug'] ) {
            $slug = sanitize_title( $params['slug'] );
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_categories',
            array(
                'name'        => sanitize_text_field( $params['name'] ),
                'slug'        => $slug,
                'description' => isset( $params['description'] ) ? sanitize_textarea_field( $params['description'] ) : null,
                'parent_id'   => isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null,
                'icon'        => isset( $params['icon'] ) ? sanitize_text_field( $params['icon'] ) : null,
                'color'       => isset( $params['color'] ) ? sanitize_hex_color( $params['color'] ) : null,
                'sort_order'  => isset( $params['sort_order'] ) ? intval( $params['sort_order'] ) : 0,
                'is_active'   => isset( $params['is_active'] ) ? intval( $params['is_active'] ) : 1,
                'created_by'  => get_current_user_id(),
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create category', array( 'status' => 500 ) );
        }

        delete_transient( 'cnw_categories' );

        $cat_id = $wpdb->insert_id;
        $this->log_activity( get_current_user_id(), 'category_created', 'Created category: ' . sanitize_text_field( $params['name'] ), 0, null, 'category', $cat_id );

        return array( 'success' => true, 'id' => $cat_id );
    }

    public function update_category( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $params = $request->get_json_params();
        $data   = array();

        if ( isset( $params['name'] ) )        $data['name']        = sanitize_text_field( $params['name'] );
        if ( isset( $params['slug'] ) )        $data['slug']        = sanitize_title( $params['slug'] );
        if ( isset( $params['description'] ) ) $data['description'] = sanitize_textarea_field( $params['description'] );
        if ( isset( $params['parent_id'] ) )   $data['parent_id']   = intval( $params['parent_id'] );
        if ( isset( $params['icon'] ) )        $data['icon']        = sanitize_text_field( $params['icon'] );
        if ( isset( $params['color'] ) )       $data['color']       = sanitize_hex_color( $params['color'] );
        if ( isset( $params['sort_order'] ) )  $data['sort_order']  = intval( $params['sort_order'] );
        if ( isset( $params['is_active'] ) )   $data['is_active']   = intval( $params['is_active'] );

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'No fields to update', array( 'status' => 400 ) );
        }

        $result = $wpdb->update( $wpdb->prefix . 'cnw_social_worker_categories', $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update category', array( 'status' => 500 ) );
        }

        delete_transient( 'cnw_categories' );

        $cat_name = isset( $data['name'] ) ? $data['name'] : '#' . $id;
        $this->log_activity( get_current_user_id(), 'category_updated', 'Updated category: ' . $cat_name, 0, null, 'category', $id );

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_category( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_categories', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete category', array( 'status' => 500 ) );
        }

        delete_transient( 'cnw_categories' );

        $this->log_activity( get_current_user_id(), 'category_deleted', 'Deleted category #' . $id, 0, null, 'category', $id );

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * VOTES
     * ================================================================== */

    public function get_votes( WP_REST_Request $request ) {
        global $wpdb;

        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 20 );
        $offset   = ( $page - 1 ) * $per_page;

        $votes = $wpdb->get_results( $wpdb->prepare(
            "SELECT v.*, u.display_name AS user_name
             FROM {$wpdb->prefix}cnw_social_worker_votes v
             LEFT JOIN {$wpdb->users} u ON v.user_id = u.ID
             ORDER BY v.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ) );

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes" );

        return array( 'votes' => $votes, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function get_vote( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        $vote = $wpdb->get_row( $wpdb->prepare(
            "SELECT v.*, u.display_name AS user_name
             FROM {$wpdb->prefix}cnw_social_worker_votes v
             LEFT JOIN {$wpdb->users} u ON v.user_id = u.ID
             WHERE v.id = %d",
            $id
        ) );

        if ( ! $vote ) {
            return new WP_Error( 'not_found', 'Vote not found', array( 'status' => 404 ) );
        }

        return $vote;
    }

    public function create_vote( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['target_type'] ) || empty( $params['target_id'] ) || ! isset( $params['vote_type'] ) ) {
            return new WP_Error( 'missing_fields', 'target_type, target_id, and vote_type are required', array( 'status' => 400 ) );
        }

        $user_id     = get_current_user_id();
        $target_type = sanitize_text_field( $params['target_type'] );
        $target_id   = intval( $params['target_id'] );
        $vote_type   = intval( $params['vote_type'] );

        // Resolve content owner
        $content_owner = 0;
        if ( $target_type === 'thread' ) {
            $content_owner = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", $target_id
            ) );
        } elseif ( $target_type === 'reply' ) {
            $content_owner = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $target_id
            ) );
        }
        $is_self_vote = ( $content_owner && $content_owner === $user_id );

        // Check existing vote — toggle or update
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_votes
             WHERE user_id = %d AND target_type = %s AND target_id = %d",
            $user_id, $target_type, $target_id
        ) );

        // Resolve thread link for activity log
        $link_thread_id = $target_id;
        if ( $target_type === 'reply' ) {
            $link_thread_id = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT thread_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $target_id
            ) );
        }
        $vote_link = '#/thread/' . $link_thread_id;

        if ( $existing ) {
            if ( (int) $existing->vote_type === $vote_type ) {
                // Same vote — remove it (toggle off)
                // Reverse the reputation for the old vote
                $wpdb->query( $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'vote' AND reference_id = %d",
                    (int) $existing->id
                ) );
                $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_votes', array( 'id' => $existing->id ) );
                if ( $content_owner ) $this->recalc_user_reputation( $content_owner );
                $this->recalc_user_reputation( $user_id );

                $remove_label = $vote_type === 1 ? 'Removed upvote from' : 'Removed downvote from';
                $remove_pts   = ( $vote_type === 1 && ! $is_self_vote ) ? -1 : 0;
                $remove_rsn   = $is_self_vote ? 'Own content — no points' : ( $vote_type === 1 ? null : 'No points for removing downvote' );
                $this->log_activity( $user_id, 'vote_removed', $remove_label . ' a ' . $target_type, $remove_pts, $remove_rsn, $target_type, $target_id, $vote_link );

                // Log for content owner: upvote removed = they lose 10 pts
                if ( ! $is_self_vote && $content_owner ) {
                    $owner_pts  = $vote_type === 1 ? -10 : 1;
                    $owner_desc = $vote_type === 1 ? 'An upvote was removed from your ' . $target_type : 'A downvote was removed from your ' . $target_type;
                    $this->log_activity( $content_owner, 'received_vote', $owner_desc, $owner_pts, null, $target_type, $target_id, $vote_link );
                }

                return array( 'success' => true, 'action' => 'removed', 'id' => (int) $existing->id );
            } else {
                // Different vote — update: reverse old reputation, apply new
                $wpdb->query( $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE reference_type = 'vote' AND reference_id = %d",
                    (int) $existing->id
                ) );
                $wpdb->update(
                    $wpdb->prefix . 'cnw_social_worker_votes',
                    array( 'vote_type' => $vote_type ),
                    array( 'id' => $existing->id )
                );
                // Award new vote reputation to content owner (not for self-votes)
                if ( ! $is_self_vote && $content_owner ) {
                    $rep_points = $vote_type === 1 ? 10 : -1;
                    $rep_desc   = $vote_type === 1 ? 'Received an upvote' : 'Received a downvote';
                    $rep_action = $vote_type === 1 ? 'received_upvote' : 'received_downvote';
                    $this->award_reputation( $content_owner, $rep_points, $rep_action, 'vote', (int) $existing->id, $rep_desc );

                    $owner_desc = $vote_type === 1 ? 'Your ' . $target_type . ' received an upvote' : 'Your ' . $target_type . ' received a downvote';
                    $this->log_activity( $content_owner, 'received_vote', $owner_desc, $rep_points, null, $target_type, $target_id, $vote_link );
                }
                // Award +1 to the voter for giving an upvote (not for self-votes)
                if ( $vote_type === 1 && ! $is_self_vote ) {
                    $this->award_reputation( $user_id, 1, 'gave_upvote', 'vote', (int) $existing->id, 'Gave an upvote' );
                }

                $change_label = $vote_type === 1 ? 'Changed vote to upvote on' : 'Changed vote to downvote on';
                $change_pts   = ( $vote_type === 1 && ! $is_self_vote ) ? 1 : 0;
                $change_rsn   = $is_self_vote ? 'Own content — no points' : ( $vote_type === 1 ? null : 'No points for downvoting' );
                $this->log_activity( $user_id, 'vote_changed', $change_label . ' a ' . $target_type, $change_pts, $change_rsn, $target_type, $target_id, $vote_link );

                return array( 'success' => true, 'action' => 'updated', 'id' => (int) $existing->id );
            }
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_votes',
            array(
                'user_id'     => $user_id,
                'target_type' => $target_type,
                'target_id'   => $target_id,
                'vote_type'   => $vote_type,
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create vote', array( 'status' => 500 ) );
        }

        $vote_id = (int) $wpdb->insert_id;

        // Award reputation to content owner (+10 for upvote, -1 for downvote) — not for self-votes
        if ( ! $is_self_vote && $content_owner ) {
            $rep_points = $vote_type === 1 ? 10 : -1;
            $rep_desc   = $vote_type === 1 ? 'Received an upvote' : 'Received a downvote';
            $rep_action = $vote_type === 1 ? 'received_upvote' : 'received_downvote';
            $this->award_reputation( $content_owner, $rep_points, $rep_action, 'vote', $vote_id, $rep_desc );
        }

        // Award +1 to the voter for giving an upvote — not for self-votes
        if ( $vote_type === 1 && ! $is_self_vote ) {
            $this->award_reputation( $user_id, 1, 'gave_upvote', 'vote', $vote_id, 'Gave an upvote' );
        }

        // Log activity for the voter
        $vote_label = $vote_type === 1 ? 'Upvoted' : 'Downvoted';
        $vote_pts   = ( $vote_type === 1 && ! $is_self_vote ) ? 1 : 0;
        $vote_rsn   = $is_self_vote ? 'Own content — no points' : ( $vote_type === 1 ? null : 'No points for downvoting' );
        $this->log_activity( $user_id, 'voted', $vote_label . ' a ' . $target_type, $vote_pts, $vote_rsn, $target_type, $target_id, $vote_link );

        // Log activity for content owner receiving the vote (not for self-votes)
        if ( ! $is_self_vote && $content_owner ) {
            $owner_pts  = $vote_type === 1 ? 10 : -1;
            $owner_desc = $vote_type === 1 ? 'Your ' . $target_type . ' received an upvote' : 'Your ' . $target_type . ' received a downvote';
            $this->log_activity( $content_owner, 'received_vote', $owner_desc, $owner_pts, null, $target_type, $target_id, $vote_link );
        }

        // Notify on upvote only (not for self-votes)
        if ( $vote_type === 1 && ! $is_self_vote ) {
            $actor_name = wp_get_current_user()->display_name;
            if ( $target_type === 'thread' && $content_owner ) {
                $this->insert_notification( $content_owner, $user_id, 'vote', 'thread', $target_id,
                    "{$actor_name} upvoted your thread." );
            } elseif ( $target_type === 'reply' ) {
                $row = $wpdb->get_row( $wpdb->prepare(
                    "SELECT author_id, thread_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $target_id
                ) );
                if ( $row ) {
                    $this->insert_notification( (int) $row->author_id, $user_id, 'vote', 'thread', (int) $row->thread_id,
                        "{$actor_name} upvoted your reply." );
                }
            }
        }

        return array( 'success' => true, 'action' => 'created', 'id' => $vote_id );
    }

    public function delete_vote( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_votes', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete vote', array( 'status' => 500 ) );
        }

        $this->log_activity( get_current_user_id(), 'vote_deleted', 'Deleted vote #' . $id, 0, null, 'vote', $id );

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * REPUTATION
     * ================================================================== */

    public function get_reputation_list( WP_REST_Request $request ) {
        global $wpdb;

        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 20 );
        $offset   = ( $page - 1 ) * $per_page;

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT rp.*, u.display_name AS user_name
             FROM {$wpdb->prefix}cnw_social_worker_reputation rp
             LEFT JOIN {$wpdb->users} u ON rp.user_id = u.ID
             ORDER BY rp.created_at DESC
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ) );

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_reputation" );

        return array( 'reputation' => $rows, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function get_reputation_entry( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT rp.*, u.display_name AS user_name
             FROM {$wpdb->prefix}cnw_social_worker_reputation rp
             LEFT JOIN {$wpdb->users} u ON rp.user_id = u.ID
             WHERE rp.id = %d",
            $id
        ) );

        if ( ! $row ) {
            return new WP_Error( 'not_found', 'Reputation entry not found', array( 'status' => 404 ) );
        }

        return $row;
    }

    public function create_reputation( WP_REST_Request $request ) {
        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['user_id'] ) || ! isset( $params['points'] ) || empty( $params['action_type'] ) ) {
            return new WP_Error( 'missing_fields', 'user_id, points, and action_type are required', array( 'status' => 400 ) );
        }

        $rep_user_id = intval( $params['user_id'] );

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_reputation',
            array(
                'user_id'        => $rep_user_id,
                'points'         => intval( $params['points'] ),
                'action_type'    => sanitize_text_field( $params['action_type'] ),
                'reference_type' => isset( $params['reference_type'] ) ? sanitize_text_field( $params['reference_type'] ) : null,
                'reference_id'   => isset( $params['reference_id'] ) ? intval( $params['reference_id'] ) : null,
                'description'    => isset( $params['description'] ) ? sanitize_text_field( $params['description'] ) : null,
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create reputation entry', array( 'status' => 500 ) );
        }

        $this->recalc_user_reputation( $rep_user_id );

        $rep_user = get_userdata( $rep_user_id );
        $rep_name = $rep_user ? $rep_user->display_name : '#' . $rep_user_id;
        $this->log_activity( get_current_user_id(), 'reputation_created', 'Awarded ' . intval( $params['points'] ) . ' reputation to ' . $rep_name, 0, null, 'user', $rep_user_id );

        return array( 'success' => true, 'id' => $wpdb->insert_id );
    }

    public function update_reputation( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $params = $request->get_json_params();
        $data   = array();

        // Get old user_id in case it changes
        $old_user_id = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE id = %d", $id
        ) );

        if ( isset( $params['points'] ) )         $data['points']         = intval( $params['points'] );
        if ( isset( $params['action_type'] ) )    $data['action_type']    = sanitize_text_field( $params['action_type'] );
        if ( isset( $params['reference_type'] ) ) $data['reference_type'] = sanitize_text_field( $params['reference_type'] );
        if ( isset( $params['reference_id'] ) )   $data['reference_id']   = intval( $params['reference_id'] );
        if ( isset( $params['description'] ) )    $data['description']    = sanitize_text_field( $params['description'] );

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'No fields to update', array( 'status' => 400 ) );
        }

        $result = $wpdb->update( $wpdb->prefix . 'cnw_social_worker_reputation', $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update reputation entry', array( 'status' => 500 ) );
        }

        // Recalculate for affected user(s)
        $this->recalc_user_reputation( $old_user_id );
        if ( isset( $params['user_id'] ) && intval( $params['user_id'] ) !== $old_user_id ) {
            $this->recalc_user_reputation( intval( $params['user_id'] ) );
        }

        $this->log_activity( get_current_user_id(), 'reputation_updated', 'Updated reputation entry #' . $id, 0, null, 'reputation', $id );

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_reputation( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        // Get user_id before deleting
        $rep_user_id = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE id = %d", $id
        ) );

        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_reputation', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete reputation entry', array( 'status' => 500 ) );
        }

        if ( $rep_user_id ) {
            $this->recalc_user_reputation( $rep_user_id );
        }

        $this->log_activity( get_current_user_id(), 'reputation_deleted', 'Deleted reputation entry #' . $id, 0, null, 'reputation', $id );

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * UTILITY ENDPOINTS (kept for frontend compatibility)
     * ================================================================== */

    public function get_tags() {
        global $wpdb;
        $tags_table   = $wpdb->prefix . 'cnw_social_worker_tags';
        $tt_table     = $wpdb->prefix . 'cnw_social_worker_thread_tags';
        return $wpdb->get_results(
            "SELECT t.*, COALESCE(tc.cnt, 0) AS question_count
             FROM $tags_table t
             LEFT JOIN (
                 SELECT tag_id, COUNT(*) AS cnt FROM $tt_table GROUP BY tag_id
             ) tc ON tc.tag_id = t.id
             ORDER BY t.name ASC"
        );
    }

    public function create_tag( WP_REST_Request $request ) {
        global $wpdb;

        $body = $request->get_json_params();
        if ( empty( $body['name'] ) ) {
            return new WP_Error( 'missing_name', 'Tag name is required.', array( 'status' => 400 ) );
        }

        $name = sanitize_text_field( $body['name'] );
        $slug = sanitize_title( $name );
        if ( ! $slug ) {
            $slug = 'tag-' . time();
        }

        $table = $wpdb->prefix . 'cnw_social_worker_tags';

        // Return existing tag if slug already exists.
        $existing_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table WHERE slug = %s", $slug
        ) );
        if ( $existing_id ) {
            return array( 'success' => true, 'id' => (int) $existing_id, 'existing' => true );
        }

        $inserted = $wpdb->insert( $table, array( 'name' => $name, 'slug' => $slug, 'created_by' => get_current_user_id() ), array( '%s', '%s', '%d' ) );

        if ( false === $inserted ) {
            return new WP_Error( 'db_error', $wpdb->last_error ?: 'Insert failed.', array( 'status' => 500 ) );
        }

        $new_tag_id = (int) $wpdb->insert_id;

        $this->log_activity( get_current_user_id(), 'tag_created', 'Created tag: ' . $name, 0, null, 'tag', $new_tag_id );

        // Auto-follow the tag for the creator.
        $wpdb->replace(
            $wpdb->prefix . 'cnw_social_worker_user_followed_tags',
            array( 'user_id' => get_current_user_id(), 'tag_id' => $new_tag_id, 'created_at' => current_time( 'mysql' ) ),
            array( '%d', '%d', '%s' )
        );

        return array( 'success' => true, 'id' => $new_tag_id );
    }

    public function update_tag( WP_REST_Request $request ) {
        global $wpdb;
        $tag_id  = intval( $request['id'] );
        $user_id = get_current_user_id();
        $table   = $wpdb->prefix . 'cnw_social_worker_tags';

        $tag = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$table` WHERE id = %d", $tag_id ) );
        if ( ! $tag ) {
            return new WP_Error( 'not_found', 'Tag not found.', array( 'status' => 404 ) );
        }
        if ( (int) $tag->created_by !== $user_id && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'forbidden', 'Not allowed.', array( 'status' => 403 ) );
        }

        $body = $request->get_json_params();
        $data    = array();
        $formats = array();

        if ( ! empty( $body['name'] ) ) {
            $data['name'] = sanitize_text_field( $body['name'] );
            $data['slug'] = sanitize_title( $body['name'] );
            $formats[]    = '%s';
            $formats[]    = '%s';
        }
        if ( ! empty( $body['slug'] ) ) {
            $data['slug'] = sanitize_title( $body['slug'] );
            if ( ! isset( $data['name'] ) ) {
                $formats[] = '%s';
            }
        }
        if ( array_key_exists( 'description', $body ) ) {
            $data['description'] = sanitize_textarea_field( $body['description'] ) ?: null;
            $formats[]           = '%s';
        }

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'Nothing to update.', array( 'status' => 400 ) );
        }

        $wpdb->update( $table, $data, array( 'id' => $tag_id ), $formats, array( '%d' ) );

        $tag_label = isset( $data['name'] ) ? $data['name'] : '#' . $tag_id;
        $this->log_activity( $user_id, 'tag_updated', 'Updated tag: ' . $tag_label, 0, null, 'tag', $tag_id );

        return array( 'success' => true );
    }

    public function delete_tag( WP_REST_Request $request ) {
        global $wpdb;
        $tag_id = intval( $request['id'] );

        // Remove from junction tables first
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_thread_tags', array( 'tag_id' => $tag_id ) );
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_user_followed_tags', array( 'tag_id' => $tag_id ) );

        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_tags', array( 'id' => $tag_id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete tag', array( 'status' => 500 ) );
        }

        $this->log_activity( get_current_user_id(), 'tag_deleted', 'Deleted tag #' . $tag_id, 0, null, 'tag', $tag_id );

        return array( 'success' => true, 'deleted' => $tag_id );
    }

    public function get_followed_tags() {
        global $wpdb;
        $user_id = get_current_user_id();
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT t.* FROM {$wpdb->prefix}cnw_social_worker_tags t
             JOIN {$wpdb->prefix}cnw_social_worker_user_followed_tags uf ON t.id = uf.tag_id
             WHERE uf.user_id = %d ORDER BY t.name ASC",
            $user_id
        ) );
    }

    public function follow_tag( WP_REST_Request $request ) {
        global $wpdb;
        $user_id = get_current_user_id();
        $tag_id  = intval( $request['id'] );

        $wpdb->replace(
            $wpdb->prefix . 'cnw_social_worker_user_followed_tags',
            array( 'user_id' => $user_id, 'tag_id' => $tag_id )
        );

        $tag_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}cnw_social_worker_tags WHERE id = %d", $tag_id ) );
        $this->log_activity( $user_id, 'tag_followed', 'Followed tag: ' . ( $tag_name ?: '#' . $tag_id ), 0, 'No points for following tags' );

        return array( 'success' => true );
    }

    public function unfollow_tag( WP_REST_Request $request ) {
        global $wpdb;
        $user_id = get_current_user_id();
        $tag_id  = intval( $request['id'] );

        $wpdb->delete(
            $wpdb->prefix . 'cnw_social_worker_user_followed_tags',
            array( 'user_id' => $user_id, 'tag_id' => $tag_id )
        );

        $tag_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}cnw_social_worker_tags WHERE id = %d", $tag_id ) );
        $this->log_activity( $user_id, 'tag_unfollowed', 'Unfollowed tag: ' . ( $tag_name ?: '#' . $tag_id ), 0, 'No points for unfollowing tags' );

        return array( 'success' => true );
    }

    /* ==================================================================
     * SAVED THREADS
     * ================================================================== */

    public function save_thread( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;
        $user_id   = get_current_user_id();
        $thread_id = intval( $request['id'] );

        $wpdb->replace(
            $wpdb->prefix . 'cnw_social_worker_saved_threads',
            array( 'user_id' => $user_id, 'thread_id' => $thread_id )
        );

        $this->log_activity( $user_id, 'thread_saved', 'Saved a thread', 0, 'No points for saving', 'thread', $thread_id, '#/thread/' . $thread_id );

        // Notify thread author
        $owner = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", $thread_id
        ) );
        if ( $owner ) {
            $actor_name = wp_get_current_user()->display_name;
            $this->insert_notification( $owner, $user_id, 'save', 'thread', $thread_id,
                "{$actor_name} marked your thread as helpful." );
        }

        return array( 'success' => true );
    }

    public function unsave_thread( WP_REST_Request $request ) {
        global $wpdb;
        $user_id   = get_current_user_id();
        $thread_id = intval( $request['id'] );

        $wpdb->delete(
            $wpdb->prefix . 'cnw_social_worker_saved_threads',
            array( 'user_id' => $user_id, 'thread_id' => $thread_id )
        );

        $this->log_activity( $user_id, 'thread_unsaved', 'Unsaved a thread', 0, 'No points for unsaving', 'thread', $thread_id, '#/thread/' . $thread_id );

        return array( 'success' => true );
    }

    public function get_saved_threads( WP_REST_Request $request ) {
        global $wpdb;

        $current_user_id = get_current_user_id();
        $page     = max( 1, (int) $request->get_param( 'page' ) ?: 1 );
        $per_page = 10;
        $offset   = ( $page - 1 ) * $per_page;

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st
             JOIN {$wpdb->prefix}cnw_social_worker_threads t ON st.thread_id = t.id
             WHERE st.user_id = %d AND t.status IN ('published','approved')",
            $current_user_id
        ) );

        $threads = $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*, u.display_name AS author_name, u.ID AS author_id,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') AS reply_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = 1) AS likes,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = -1) AS dislikes,
                (SELECT v.vote_type FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.user_id = %d LIMIT 1) AS user_vote,
                1 AS is_saved,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st2 WHERE st2.thread_id = t.id) AS saves_count
             FROM {$wpdb->prefix}cnw_social_worker_saved_threads st
             JOIN {$wpdb->prefix}cnw_social_worker_threads t ON st.thread_id = t.id
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             WHERE st.user_id = %d AND t.status IN ('published','approved')
             ORDER BY st.created_at DESC
             LIMIT %d OFFSET %d",
            $current_user_id,
            $current_user_id,
            $per_page,
            $offset
        ) );

        // Batch fetch all tags for saved threads (avoid N+1)
        $saved_thread_ids = array_map( function( $t ) { return (int) $t->id; }, $threads );
        $saved_tags_by_thread = array();
        if ( ! empty( $saved_thread_ids ) ) {
            $ids_placeholder = implode( ',', array_map( 'intval', $saved_thread_ids ) );
            $all_tags = $wpdb->get_results(
                "SELECT tt.thread_id, tg.name
                 FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
                 JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tg.id = tt.tag_id
                 WHERE tt.thread_id IN ({$ids_placeholder})"
            );
            foreach ( $all_tags as $tag_row ) {
                $saved_tags_by_thread[ $tag_row->thread_id ][] = $tag_row->name;
            }
        }

        // Attach tags + avatar, handle anonymous
        foreach ( $threads as &$thread ) {
            $thread->tags = isset( $saved_tags_by_thread[ $thread->id ] ) ? $saved_tags_by_thread[ $thread->id ] : array();
            if ( ! empty( $thread->is_anonymous ) && (int) $thread->is_anonymous === 1 ) {
                $thread->author_id     = 0;
                $thread->author_name   = 'Anonymous';
                $thread->author_avatar = CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
            } else {
                $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
            }
        }

        return array(
            'threads' => $threads,
            'total'   => $total,
            'pages'   => ceil( $total / $per_page ),
        );
    }

    public function get_hot_questions() {
        global $wpdb;

        $current_user_id = get_current_user_id();

        $threads = $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*, u.display_name AS author_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') AS reply_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = 1) AS likes,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = -1) AS dislikes,
                (SELECT v.vote_type FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.user_id = %d LIMIT 1) AS user_vote,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st WHERE st.thread_id = t.id AND st.user_id = %d) AS is_saved,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st2 WHERE st2.thread_id = t.id) AS saves_count
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             WHERE t.status IN ('published','approved')
             ORDER BY t.views DESC, t.created_at DESC
             LIMIT 3",
            $current_user_id,
            $current_user_id
        ) );

        foreach ( $threads as &$thread ) {
            if ( ! empty( $thread->is_anonymous ) && (int) $thread->is_anonymous === 1 ) {
                $thread->author_id     = 0;
                $thread->author_name   = 'Anonymous';
                $thread->author_avatar = CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
            } else {
                $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
            }
        }

        return $threads;
    }

    public function search_users( WP_REST_Request $request ) {
        global $wpdb;

        $search   = sanitize_text_field( $request->get_param( 'search' ) );
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 20 );

        // Exclude anonymous users
        $anon_ids = $wpdb->get_col(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'cnw_anonymous' AND meta_value = '1'"
        );

        // Exclude permanently suspended users (active suspension with no expiry)
        $perm_suspended_ids = $wpdb->get_col(
            "SELECT DISTINCT user_id FROM {$wpdb->prefix}cnw_social_worker_warnings WHERE type = 'suspension' AND is_active = 1 AND expires_at IS NULL"
        );

        $args = array(
            'number'  => $per_page,
            'paged'   => $page,
            'orderby' => 'display_name',
            'order'   => 'ASC',
        );

        $exclude = array_merge( $anon_ids, $perm_suspended_ids );
        $current = get_current_user_id();
        if ( $current ) {
            $exclude[] = $current;
        }
        if ( ! empty( $exclude ) ) {
            $args['exclude'] = $exclude;
        }

        if ( ! empty( $search ) && strlen( $search ) >= 2 ) {
            $args['search']         = '*' . $search . '*';
            $args['search_columns'] = array( 'user_login', 'display_name', 'user_email' );

            // Also include users matching phone number in usermeta
            $phone_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'cnw_phone' AND meta_value LIKE %s",
                '%' . $wpdb->esc_like( $search ) . '%'
            ) );
            if ( ! empty( $phone_ids ) ) {
                $args['include'] = $phone_ids;
                unset( $args['search'], $args['search_columns'] );
            }
        }

        $query = new WP_User_Query( $args );

        // If we have phone matches, also run the name/email search and merge
        if ( ! empty( $search ) && strlen( $search ) >= 2 && ! empty( $phone_ids ) ) {
            $args2 = $args;
            unset( $args2['include'] );
            $args2['search']         = '*' . $search . '*';
            $args2['search_columns'] = array( 'user_login', 'display_name', 'user_email' );
            $query2 = new WP_User_Query( $args2 );
            $merged = array();
            $seen   = array();
            foreach ( array_merge( $query->get_results(), $query2->get_results() ) as $mu ) {
                if ( ! isset( $seen[ $mu->ID ] ) ) {
                    $merged[]        = $mu;
                    $seen[ $mu->ID ] = true;
                }
            }
            // Replace query results with merged (use reflection or just process merged directly)
            $query_results = $merged;
        } else {
            $query_results = $query->get_results();
        }
        $users = array();

        // Bulk-load connection statuses for current user
        $conn_map = array();
        if ( $current ) {
            $conn_table = $wpdb->prefix . 'cnw_social_worker_connections';
            $conn_rows = $wpdb->get_results( $wpdb->prepare(
                "SELECT sender_id, receiver_id, status FROM $conn_table
                 WHERE sender_id = %d OR receiver_id = %d",
                $current, $current
            ) );
            foreach ( $conn_rows as $cr ) {
                $other_id = ( (int) $cr->sender_id === $current ) ? (int) $cr->receiver_id : (int) $cr->sender_id;
                if ( $cr->status === 'accepted' ) {
                    $conn_map[ $other_id ] = 'connected';
                } elseif ( $cr->status === 'blocked' ) {
                    $conn_map[ $other_id ] = 'blocked';
                } elseif ( $cr->status === 'pending' ) {
                    $conn_map[ $other_id ] = ( (int) $cr->sender_id === $current ) ? 'pending_sent' : 'pending_received';
                }
            }
        }

        foreach ( $query_results as $u ) {
            $avatar = get_user_meta( $u->ID, 'cnw_avatar_url', true ) ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
            $reputation = (int) get_user_meta( $u->ID, 'cnw_reputation_total', true );

            $thread_count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE author_id = %d", $u->ID
            ) );
            $reply_count = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies WHERE author_id = %d", $u->ID
            ) );

            $any_restriction = $current && ( $this->has_restricted( $current, $u->ID ) || $this->has_restricted( $u->ID, $current ) );
            $users[] = array(
                'id'                 => $u->ID,
                'name'               => $u->display_name,
                'avatar'             => $avatar,
                'verified_label'     => get_user_meta( $u->ID, 'cnw_verified_label', true ),
                'professional_title' => get_user_meta( $u->ID, 'cnw_professional_title', true ),
                'reputation'         => $reputation,
                'thread_count'       => $thread_count,
                'reply_count'        => $reply_count,
                'user_registered'    => $u->user_registered,
                'is_online'          => $any_restriction ? false : $this->is_user_online( $u->ID, $current ),
                'connection_status'  => $conn_map[ $u->ID ] ?? 'none',
            );
        }

        $total = $query->get_total();

        return array( 'users' => $users, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function get_user( WP_REST_Request $request ) {
        global $wpdb;

        $user_id = intval( $request['id'] );

        $user = $wpdb->get_row( $wpdb->prepare(
            "SELECT u.ID, u.display_name, u.user_email, u.user_login, u.user_registered
             FROM {$wpdb->users} u
             WHERE u.ID = %d",
            $user_id
        ) );

        if ( ! $user ) {
            return new WP_Error( 'not_found', 'User not found', array( 'status' => 404 ) );
        }

        $first_name         = get_user_meta( $user_id, 'first_name', true );
        $last_name          = get_user_meta( $user_id, 'last_name', true );
        $phone              = get_user_meta( $user_id, 'cnw_phone', true );
        $bio                = get_user_meta( $user_id, 'description', true );
        $anonymous          = (bool) get_user_meta( $user_id, 'cnw_anonymous', true );
        $reputation         = (int) get_user_meta( $user_id, 'cnw_reputation_total', true );
        $avatar             = $this->get_user_avatar( $user_id, 150 );
        $verified_label     = get_user_meta( $user_id, 'cnw_verified_label', true );
        $professional_title = get_user_meta( $user_id, 'cnw_professional_title', true );

        $helpful_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st
             INNER JOIN {$wpdb->prefix}cnw_social_worker_replies r ON r.thread_id = st.thread_id
             WHERE r.author_id = %d",
            $user_id
        ) );

        $thread_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE author_id = %d",
            $user_id
        ) );

        $reply_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies WHERE author_id = %d",
            $user_id
        ) );

        $current_user_id = get_current_user_id();
        $is_own          = $current_user_id === $user_id;
        $is_admin        = current_user_can( 'manage_options' );
        $target_prefs    = $this->get_user_preferences( $user_id );

        // Enforce profile_visibility: if set to "connections" and viewer is not connected (and not own/admin), return limited data
        $profile_restricted = false;
        if ( ! $is_own && ! $is_admin && $target_prefs['profile_visibility'] === 'connections' ) {
            if ( ! $current_user_id || ! $this->are_connected( $current_user_id, $user_id ) ) {
                $profile_restricted = true;
            }
        }

        // Enforce show_activity: if user disabled it, hide from others
        $show_activity = $is_own || $is_admin || ! empty( $target_prefs['show_activity'] );

        // Determine if current user can message this user
        $can_message = false;
        if ( $is_own ) {
            $can_message = false; // can't message yourself
        } elseif ( $current_user_id ) {
            if ( $target_prefs['message_privacy'] === 'connections' ) {
                $can_message = $this->are_connected( $current_user_id, $user_id );
            } else {
                $can_message = true;
            }
        }

        if ( $profile_restricted ) {
            // Return minimal profile data
            return array(
                'id'                 => (int) $user->ID,
                'display_name'       => $user->display_name,
                'avatar'             => $avatar,
                'verified_label'     => $verified_label,
                'professional_title' => $professional_title,
                'is_own'             => false,
                'profile_restricted' => true,
                'suspension'         => $this->get_suspension_info( $user_id ),
                'preferences'        => null,
            );
        }

        return array(
            'id'              => (int) $user->ID,
            'display_name'    => $user->display_name,
            'first_name'      => $first_name,
            'last_name'       => $last_name,
            'email'           => $is_own ? $user->user_email : '',
            'phone'           => $is_own ? $phone : '',
            'bio'             => $bio,
            'avatar'          => $avatar,
            'user_registered' => $user->user_registered,
            'anonymous'       => $anonymous,
            'reputation'      => $reputation,
            'helpful_count'   => $helpful_count,
            'thread_count'    => $thread_count,
            'reply_count'     => $reply_count,
            'verified_label'     => $verified_label,
            'professional_title' => $professional_title,
            'is_own'             => $is_own,
            'show_activity'      => $show_activity,
            'can_message'        => $can_message,
            'suspension'         => $this->get_suspension_info( $user_id ),
            'preferences'        => $is_own ? $this->get_user_preferences( $user_id ) : null,
        );
    }

    public function get_user_threads( WP_REST_Request $request ) {
        global $wpdb;

        $user_id  = intval( $request['id'] );
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = 10;
        $offset   = ( $page - 1 ) * $per_page;
        $current  = get_current_user_id();

        $threads = $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*, u.display_name AS author_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id) AS reply_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st2 WHERE st2.thread_id = t.id) AS saves_count
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             WHERE t.author_id = %d
             ORDER BY t.created_at DESC
             LIMIT %d OFFSET %d",
            $user_id, $per_page, $offset
        ) );

        // Batch fetch all tags for user threads (avoid N+1)
        $ut_ids = array_map( function( $t ) { return (int) $t->id; }, $threads );
        $ut_tags_by_thread = array();
        if ( ! empty( $ut_ids ) ) {
            $ids_placeholder = implode( ',', array_map( 'intval', $ut_ids ) );
            $all_tags = $wpdb->get_results(
                "SELECT tt.thread_id, tg.name
                 FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
                 JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tg.id = tt.tag_id
                 WHERE tt.thread_id IN ({$ids_placeholder})"
            );
            foreach ( $all_tags as $tag_row ) {
                $ut_tags_by_thread[ $tag_row->thread_id ][] = $tag_row->name;
            }
        }

        foreach ( $threads as &$thread ) {
            $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
            $thread->tags = isset( $ut_tags_by_thread[ $thread->id ] ) ? $ut_tags_by_thread[ $thread->id ] : array();
        }

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE author_id = %d",
            $user_id
        ) );

        return array( 'threads' => $threads, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function get_user_replies( WP_REST_Request $request ) {
        global $wpdb;

        $user_id  = intval( $request['id'] );
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = 10;
        $offset   = ( $page - 1 ) * $per_page;

        $replies = $wpdb->get_results( $wpdb->prepare(
            "SELECT r.*, t.title AS thread_title, u.display_name AS author_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_saved_threads st WHERE st.thread_id = r.thread_id) AS saves_count
             FROM {$wpdb->prefix}cnw_social_worker_replies r
             LEFT JOIN {$wpdb->prefix}cnw_social_worker_threads t ON r.thread_id = t.id
             LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
             WHERE r.author_id = %d
             ORDER BY r.created_at DESC
             LIMIT %d OFFSET %d",
            $user_id, $per_page, $offset
        ) );

        // Batch fetch all tags for reply parent threads (avoid N+1)
        $reply_thread_ids = array_unique( array_map( function( $r ) { return (int) $r->thread_id; }, $replies ) );
        $ur_tags_by_thread = array();
        if ( ! empty( $reply_thread_ids ) ) {
            $ids_placeholder = implode( ',', array_map( 'intval', $reply_thread_ids ) );
            $all_tags = $wpdb->get_results(
                "SELECT tt.thread_id, tg.name
                 FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
                 JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tg.id = tt.tag_id
                 WHERE tt.thread_id IN ({$ids_placeholder})"
            );
            foreach ( $all_tags as $tag_row ) {
                $ur_tags_by_thread[ $tag_row->thread_id ][] = $tag_row->name;
            }
        }

        foreach ( $replies as &$reply ) {
            $reply->author_avatar = $this->get_user_avatar( (int) $reply->author_id, 80 );
            $reply->tags = isset( $ur_tags_by_thread[ $reply->thread_id ] ) ? $ur_tags_by_thread[ $reply->thread_id ] : array();
        }

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies WHERE author_id = %d",
            $user_id
        ) );

        return array( 'replies' => $replies, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function update_user_profile( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $body    = $request->get_json_params();

        if ( isset( $body['email'] ) ) {
            $new_email = sanitize_email( $body['email'] );
            if ( $new_email && is_email( $new_email ) ) {
                $existing = get_user_by( 'email', $new_email );
                if ( $existing && (int) $existing->ID !== $user_id ) {
                    return new WP_Error( 'email_exists', 'This email address is already in use.', array( 'status' => 400 ) );
                }
                wp_update_user( array( 'ID' => $user_id, 'user_email' => $new_email ) );
            }
        }
        if ( isset( $body['first_name'] ) ) {
            update_user_meta( $user_id, 'first_name', sanitize_text_field( $body['first_name'] ) );
        }
        if ( isset( $body['last_name'] ) ) {
            update_user_meta( $user_id, 'last_name', sanitize_text_field( $body['last_name'] ) );
        }
        if ( isset( $body['phone'] ) ) {
            update_user_meta( $user_id, 'cnw_phone', sanitize_text_field( $body['phone'] ) );
        }
        if ( isset( $body['bio'] ) ) {
            update_user_meta( $user_id, 'description', sanitize_textarea_field( $body['bio'] ) );
        }
        if ( isset( $body['verified_label'] ) ) {
            update_user_meta( $user_id, 'cnw_verified_label', sanitize_text_field( $body['verified_label'] ) );
        }
        if ( isset( $body['professional_title'] ) ) {
            update_user_meta( $user_id, 'cnw_professional_title', sanitize_text_field( $body['professional_title'] ) );
        }

        $this->log_activity( $user_id, 'profile_updated', 'Updated profile information', 0, 'No points for profile update' );

        return array( 'success' => true );
    }

    /* ------------------------------------------------------------------
     * User Preferences (privacy + notifications)
     * ------------------------------------------------------------------ */

    /**
     * Internal helper to read preferences for a given user.
     */
    private function get_user_preferences( $user_id ) {
        $saved = get_user_meta( $user_id, 'cnw_preferences', true );
        return is_array( $saved ) ? array_merge( self::$preference_defaults, $saved ) : self::$preference_defaults;
    }

    /** Default preference values. */
    private static $preference_defaults = array(
        // Privacy
        'profile_visibility'  => 'everyone',     // everyone | connections
        'message_privacy'     => 'connections',   // everyone | connections
        'show_online_status'  => true,
        'show_activity'       => true,
        // Notifications
        'notify_replies'      => true,
        'notify_mentions'     => true,
        'notify_votes'        => true,
        'notify_solutions'    => true,
        'notify_connections'  => true,
        'notify_messages'     => true,
        'notify_moderation'   => true,
        'email_notifications' => 'inactive',     // always | inactive | none
    );

    /**
     * Get preferences for the current user.
     */
    public function get_preferences( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $saved   = get_user_meta( $user_id, 'cnw_preferences', true );
        $prefs   = is_array( $saved ) ? array_merge( self::$preference_defaults, $saved ) : self::$preference_defaults;

        return $prefs;
    }

    /**
     * Update preferences for the current user.
     */
    public function update_preferences( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $body    = $request->get_json_params();
        $saved   = get_user_meta( $user_id, 'cnw_preferences', true );
        $prefs   = is_array( $saved ) ? array_merge( self::$preference_defaults, $saved ) : self::$preference_defaults;

        // Whitelist allowed keys
        $allowed_strings = array(
            'profile_visibility'  => array( 'everyone', 'connections' ),
            'message_privacy'     => array( 'everyone', 'connections' ),
            'email_notifications' => array( 'always', 'inactive', 'none' ),
        );
        foreach ( $allowed_strings as $key => $valid ) {
            if ( isset( $body[ $key ] ) && in_array( $body[ $key ], $valid, true ) ) {
                $prefs[ $key ] = $body[ $key ];
            }
        }

        $allowed_booleans = array(
            'show_online_status', 'show_activity',
            'notify_replies', 'notify_mentions', 'notify_votes',
            'notify_solutions', 'notify_connections', 'notify_messages',
            'notify_moderation',
        );
        foreach ( $allowed_booleans as $key ) {
            if ( isset( $body[ $key ] ) ) {
                $prefs[ $key ] = (bool) $body[ $key ];
            }
        }

        // Detect if show_online_status changed
        $old_show_online = is_array( $saved ) && isset( $saved['show_online_status'] )
            ? (bool) $saved['show_online_status']
            : self::$preference_defaults['show_online_status'];
        $new_show_online = $prefs['show_online_status'];

        update_user_meta( $user_id, 'cnw_preferences', $prefs );

        // If show_online_status changed, broadcast status update to conversation partners
        if ( $old_show_online !== $new_show_online ) {
            global $wpdb;
            $table    = $wpdb->prefix . 'cnw_social_worker_messages';
            $partners = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS partner_id
                 FROM {$table}
                 WHERE sender_id = %d OR recipient_id = %d",
                $user_id, $user_id, $user_id
            ) );

            // If hiding online status, broadcast offline; if showing, broadcast current real status
            $is_online = get_user_meta( $user_id, 'cnw_is_online', true );
            $status    = ( $new_show_online && $is_online ) ? 'online' : 'offline';
            $payload   = array( 'user_id' => $user_id, 'status' => $status );

            $restricters   = $this->get_restricters_of( $user_id );
            $my_restricted = $this->get_restricted_by( $user_id );
            $skip_ids      = array_unique( array_merge( $restricters, $my_restricted ) );

            foreach ( $partners as $partner_id ) {
                if ( in_array( (int) $partner_id, $skip_ids, true ) ) {
                    continue;
                }
                Cnw_Social_Bridge_Pusher::trigger(
                    'private-user-' . (int) $partner_id,
                    'user-status',
                    $payload
                );
            }
        }

        return $prefs;
    }

    /**
     * Get the user's avatar URL, preferring a custom upload over Gravatar.
     */
    private function get_user_avatar( $user_id, $size = 150 ) {
        $custom = get_user_meta( $user_id, 'cnw_avatar_url', true );
        if ( $custom ) {
            return $custom;
        }
        return CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
    }

    public function upload_avatar( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $files   = $request->get_file_params();

        if ( empty( $files['file'] ) ) {
            return new WP_Error( 'no_file', 'No file uploaded.', array( 'status' => 400 ) );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Validate file type using server-side check (not client-supplied MIME)
        $allowed_mimes = array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp' );
        $file_info = wp_check_filetype_and_ext( $files['file']['tmp_name'], $files['file']['name'], $allowed_mimes );
        if ( ! $file_info['type'] ) {
            return new WP_Error( 'invalid_type', 'Only JPEG, PNG, GIF, and WebP images are allowed.', array( 'status' => 400 ) );
        }

        $attachment_id = media_handle_upload( 'file', 0 );

        if ( is_wp_error( $attachment_id ) ) {
            return new WP_Error( 'upload_failed', $attachment_id->get_error_message(), array( 'status' => 500 ) );
        }

        $url = wp_get_attachment_url( $attachment_id );
        update_user_meta( $user_id, 'cnw_avatar_url', esc_url_raw( $url ) );
        update_user_meta( $user_id, 'cnw_avatar_attachment_id', $attachment_id );

        $this->log_activity( $user_id, 'avatar_updated', 'Updated profile photo', 0, 'No points for updating photo' );

        return array( 'success' => true, 'avatar' => $url );
    }

    public function toggle_anonymous( WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $current   = (bool) get_user_meta( $user_id, 'cnw_anonymous', true );
        $new_value = ! $current;
        update_user_meta( $user_id, 'cnw_anonymous', $new_value ? '1' : '' );

        $label = $new_value ? 'Enabled anonymous mode' : 'Disabled anonymous mode';
        $this->log_activity( $user_id, 'anonymous_toggled', $label, 0, 'No points for toggling anonymous mode' );

        return array( 'anonymous' => $new_value );
    }

    public function get_user_reputation( WP_REST_Request $request ) {
        $user_id = intval( $request['id'] );

        $total = (int) get_user_meta( $user_id, 'cnw_reputation_total', true );

        return array( 'user_id' => $user_id, 'total' => $total );
    }

    /* ==================================================================
     * NOTIFICATIONS
     * ================================================================== */

    public function get_notifications( WP_REST_Request $request ) {
        global $wpdb;

        $user_id  = get_current_user_id();
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 20 );
        $offset   = ( $page - 1 ) * $per_page;

        $notifications = $wpdb->get_results( $wpdb->prepare(
            "SELECT n.*, u.display_name AS actor_name
             FROM {$wpdb->prefix}cnw_social_worker_notifications n
             LEFT JOIN {$wpdb->users} u ON n.actor_id = u.ID
             WHERE n.user_id = %d
             ORDER BY n.created_at DESC
             LIMIT %d OFFSET %d",
            $user_id, $per_page, $offset
        ) );

        foreach ( $notifications as &$n ) {
            $n->actor_avatar = $n->actor_id
                ? $this->get_user_avatar( (int) $n->actor_id, 40 )
                : CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;
        }

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_notifications WHERE user_id = %d",
            $user_id
        ) );

        return array( 'notifications' => $notifications, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function get_unread_notification_count() {
        global $wpdb;
        $user_id = get_current_user_id();

        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_notifications WHERE user_id = %d AND is_read = 0",
            $user_id
        ) );

        return array( 'count' => $count );
    }

    public function mark_notification_read( WP_REST_Request $request ) {
        global $wpdb;
        $user_id = get_current_user_id();
        $id      = intval( $request['id'] );

        $wpdb->update(
            $wpdb->prefix . 'cnw_social_worker_notifications',
            array( 'is_read' => 1 ),
            array( 'id' => $id, 'user_id' => $user_id )
        );

        return array( 'success' => true );
    }

    public function mark_all_notifications_read() {
        global $wpdb;
        $user_id = get_current_user_id();

        $wpdb->update(
            $wpdb->prefix . 'cnw_social_worker_notifications',
            array( 'is_read' => 1 ),
            array( 'user_id' => $user_id, 'is_read' => 0 )
        );

        return array( 'success' => true );
    }

    /* ==================================================================
     * REPUTATION HELPERS
     * ================================================================== */

    /**
     * Award reputation points and update the cached total.
     */
    private function award_reputation( $user_id, $points, $action_type, $reference_type = null, $reference_id = null, $description = null ) {
        global $wpdb;

        if ( ! $user_id || $user_id <= 0 ) {
            return;
        }

        $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_reputation',
            array(
                'user_id'        => (int) $user_id,
                'points'         => (int) $points,
                'action_type'    => $action_type,
                'reference_type' => $reference_type,
                'reference_id'   => $reference_id ? (int) $reference_id : null,
                'description'    => $description,
            )
        );

        $this->recalc_user_reputation( $user_id );
    }

    /**
     * Recalculate and cache a user's total reputation in usermeta.
     */
    private function recalc_user_reputation( $user_id ) {
        global $wpdb;

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE(SUM(points), 0) FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE user_id = %d",
            (int) $user_id
        ) );

        update_user_meta( (int) $user_id, 'cnw_reputation_total', $total );
    }

    /**
     * Insert a notification row.
     */
    /** Notification type → user preference key mapping. */
    private static $notif_pref_map = array(
        'reply'               => 'notify_replies',
        'vote'                => 'notify_votes',
        'save'                => 'notify_votes',
        'solution'            => 'notify_solutions',
        'solution_removed'    => 'notify_solutions',
        'connection_request'  => 'notify_connections',
        'connection_accepted' => 'notify_connections',
        'mention'             => 'notify_mentions',
        'thread_edited'       => 'notify_moderation',
        'thread_deleted'      => 'notify_moderation',
        'thread_closed'       => 'notify_moderation',
        'thread_reopened'     => 'notify_moderation',
        'thread_pinned'       => 'notify_moderation',
        'thread_unpinned'     => 'notify_moderation',
        'reply_edited'        => 'notify_moderation',
        'reply_deleted'       => 'notify_moderation',
    );

    private function insert_notification( $user_id, $actor_id, $type, $ref_type, $ref_id, $message ) {
        global $wpdb;

        // Don't notify yourself.
        if ( (int) $user_id === (int) $actor_id ) {
            return;
        }

        // Check user's notification preference for this type
        // Moderation types (warning, suspension) always go through
        if ( isset( self::$notif_pref_map[ $type ] ) ) {
            $prefs    = $this->get_user_preferences( (int) $user_id );
            $pref_key = self::$notif_pref_map[ $type ];
            if ( empty( $prefs[ $pref_key ] ) ) {
                return; // User has disabled this notification type
            }
        }

        $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_notifications',
            array(
                'user_id'        => (int) $user_id,
                'actor_id'       => (int) $actor_id,
                'type'           => $type,
                'reference_type' => $ref_type,
                'reference_id'   => (int) $ref_id,
                'message'        => $message,
            )
        );

        $actor = get_userdata( (int) $actor_id );
        $actor_avatar = get_user_meta( (int) $actor_id, 'cnw_avatar_url', true ) ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR;

        Cnw_Social_Bridge_Pusher::trigger(
            'private-user-' . (int) $user_id,
            'new-notification',
            array(
                'id'             => $wpdb->insert_id,
                'type'           => $type,
                'reference_type' => $ref_type,
                'reference_id'   => (int) $ref_id,
                'message'        => $message,
                'actor_id'       => (int) $actor_id,
                'actor_name'     => $actor ? $actor->display_name : 'Unknown',
                'actor_avatar'   => $actor_avatar,
                'created_at'     => current_time( 'mysql' ),
            )
        );
    }

    /* ==================================================================
     * LOGIN
     * ================================================================== */

    /* ==================================================================
     * REPORTS
     * ================================================================== */

    /* ==================================================================
     * MODERATION
     * ================================================================== */

    public function toggle_close_thread( WP_REST_Request $request ) {
        global $wpdb;
        $id = (int) $request['id'];
        $table = $wpdb->prefix . 'cnw_social_worker_threads';
        $thread = $wpdb->get_row( $wpdb->prepare( "SELECT id, is_closed, author_id FROM {$table} WHERE id = %d", $id ) );
        if ( ! $thread ) {
            return new WP_Error( 'not_found', 'Thread not found.', array( 'status' => 404 ) );
        }
        $new_val = $thread->is_closed ? 0 : 1;
        $wpdb->update( $table, array( 'is_closed' => $new_val ), array( 'id' => $id ) );

        $current_user_id = get_current_user_id();
        $action = $new_val ? 'thread_closed' : 'thread_reopened';
        $desc   = $new_val ? 'Closed thread #' . $id : 'Reopened thread #' . $id;
        $this->log_activity( $current_user_id, $action, $desc, 0, null, 'thread', $id, '#/thread/' . $id );

        // Notify thread author
        $thread_author = (int) $thread->author_id;
        if ( $thread_author && $thread_author !== $current_user_id ) {
            $actor = get_userdata( $current_user_id );
            $actor_name = $actor ? $actor->display_name : 'A moderator';
            $notif_type = $new_val ? 'thread_closed' : 'thread_reopened';
            $notif_msg  = $new_val
                ? $actor_name . ' closed your thread.'
                : $actor_name . ' reopened your thread.';
            $this->insert_notification( $thread_author, $current_user_id, $notif_type, 'thread', $id, $notif_msg );
        }

        return array( 'success' => true, 'is_closed' => (bool) $new_val );
    }

    public function toggle_pin_thread( WP_REST_Request $request ) {
        global $wpdb;
        $id = (int) $request['id'];
        $table = $wpdb->prefix . 'cnw_social_worker_threads';
        $thread = $wpdb->get_row( $wpdb->prepare( "SELECT id, is_pinned, author_id FROM {$table} WHERE id = %d", $id ) );
        if ( ! $thread ) {
            return new WP_Error( 'not_found', 'Thread not found.', array( 'status' => 404 ) );
        }
        $new_val = $thread->is_pinned ? 0 : 1;
        $wpdb->update( $table, array( 'is_pinned' => $new_val ), array( 'id' => $id ) );

        $current_user_id = get_current_user_id();
        $action = $new_val ? 'thread_pinned' : 'thread_unpinned';
        $desc   = $new_val ? 'Pinned thread #' . $id : 'Unpinned thread #' . $id;
        $this->log_activity( $current_user_id, $action, $desc, 0, null, 'thread', $id, '#/thread/' . $id );

        // Notify thread author
        $thread_author = (int) $thread->author_id;
        if ( $thread_author && $thread_author !== $current_user_id ) {
            $actor = get_userdata( $current_user_id );
            $actor_name = $actor ? $actor->display_name : 'A moderator';
            $notif_type = $new_val ? 'thread_pinned' : 'thread_unpinned';
            $notif_msg  = $new_val
                ? $actor_name . ' pinned your thread.'
                : $actor_name . ' unpinned your thread.';
            $this->insert_notification( $thread_author, $current_user_id, $notif_type, 'thread', $id, $notif_msg );
        }

        return array( 'success' => true, 'is_pinned' => (bool) $new_val );
    }

    public function get_reports( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_reports';
        $page = max( 1, (int) $request->get_param( 'page' ) ?: 1 );
        $per_page = 10;
        $offset = ( $page - 1 ) * $per_page;
        $status_filter = sanitize_text_field( $request->get_param( 'status' ) ?: '' );

        $where = '1=1';
        if ( $status_filter ) {
            $where = $wpdb->prepare( 'status = %s', $status_filter );
        }

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where}" );
        $reports = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );

        // Attach reporter info and thread_id for reply reports
        foreach ( $reports as &$r ) {
            $user = get_userdata( $r->user_id );
            $r->reporter_name = $user ? $user->display_name : 'Unknown';
            $r->reporter_avatar = $user ? ( get_user_meta( $r->user_id, 'cnw_avatar_url', true ) ?: '' ) : '';

            // For reply reports, look up the thread_id
            if ( ! empty( $r->content_type ) && $r->content_type === 'reply' && ! empty( $r->content_id ) ) {
                $r->thread_id = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT thread_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d",
                    (int) $r->content_id
                ) );
            }
        }

        return array( 'reports' => $reports, 'total' => $total, 'pages' => ceil( $total / $per_page ) );
    }

    public function update_report( WP_REST_Request $request ) {
        global $wpdb;
        $id = (int) $request['id'];
        $params = $request->get_json_params();
        $table = $wpdb->prefix . 'cnw_social_worker_reports';

        $report = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $report ) {
            return new WP_Error( 'not_found', 'Report not found.', array( 'status' => 404 ) );
        }

        $update = array( 'updated_at' => current_time( 'mysql' ) );
        if ( isset( $params['status'] ) ) {
            $update['status'] = sanitize_text_field( $params['status'] );
        }
        if ( isset( $params['admin_notes'] ) ) {
            $update['admin_notes'] = wp_kses_post( $params['admin_notes'] );
        }
        $wpdb->update( $table, $update, array( 'id' => $id ) );

        // Invalidate admin badge cache and notify moderators
        delete_transient( 'cnw_admin_badge_counts' );
        $this->notify_moderators_new_report();

        $status_label = isset( $update['status'] ) ? $update['status'] : 'updated';
        $this->log_activity( get_current_user_id(), 'report_updated', 'Updated report #' . $id . ' status to ' . $status_label, 0, null, 'report', $id );

        return array( 'success' => true );
    }

    public function warn_user( WP_REST_Request $request ) {
        global $wpdb;
        $user_id = (int) $request['id'];
        $params = $request->get_json_params();

        if ( empty( $params['reason'] ) ) {
            return new WP_Error( 'missing_reason', 'A reason is required.', array( 'status' => 400 ) );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return new WP_Error( 'not_found', 'User not found.', array( 'status' => 404 ) );
        }

        $reason = sanitize_textarea_field( $params['reason'] );

        $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_warnings',
            array(
                'user_id'      => $user_id,
                'moderator_id' => get_current_user_id(),
                'type'         => 'warning',
                'reason'       => $reason,
                'is_active'    => 1,
            )
        );

        $insert_id = $wpdb->insert_id;

        // Send email notification
        $site_name = get_bloginfo( 'name' );
        $subject   = sprintf( '[%s] You have received a warning', $site_name );
        $message   = sprintf(
            "Hello %s,\n\nYou have received a warning from the moderation team at %s.\n\nReason: %s\n\nPlease review the community guidelines to avoid further action.\n\nRegards,\n%s Moderation Team",
            $user->display_name,
            $site_name,
            $reason,
            $site_name
        );
        wp_mail( $user->user_email, $subject, $message );

        // Create in-app notification
        $this->insert_notification(
            $user_id,
            get_current_user_id(),
            'warning',
            'user',
            $user_id,
            'You have received a warning from the moderation team.'
        );

        $this->log_activity( get_current_user_id(), 'user_warned', 'Issued a warning to ' . $user->display_name, 0, null, 'user', $user_id, '#/user/' . $user_id );

        return array( 'success' => true, 'id' => $insert_id );
    }

    public function suspend_user( WP_REST_Request $request ) {
        global $wpdb;
        $user_id = (int) $request['id'];
        $params = $request->get_json_params();

        if ( empty( $params['reason'] ) ) {
            return new WP_Error( 'missing_reason', 'A reason is required.', array( 'status' => 400 ) );
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return new WP_Error( 'not_found', 'User not found.', array( 'status' => 404 ) );
        }

        $reason = sanitize_textarea_field( $params['reason'] );
        $duration = isset( $params['duration'] ) ? (int) $params['duration'] : null;
        $expires_at = null;
        if ( $duration ) {
            $expires_at = gmdate( 'Y-m-d H:i:s', time() + ( $duration * DAY_IN_SECONDS ) );
        }

        $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_warnings',
            array(
                'user_id'      => $user_id,
                'moderator_id' => get_current_user_id(),
                'type'         => 'suspension',
                'reason'       => $reason,
                'duration'     => $duration,
                'expires_at'   => $expires_at,
                'is_active'    => 1,
            )
        );

        $insert_id = $wpdb->insert_id;

        // Mark user as suspended in user meta
        update_user_meta( $user_id, 'cnw_suspended', 1 );
        update_user_meta( $user_id, 'cnw_suspended_until', $expires_at );

        // Send email notification
        $site_name = get_bloginfo( 'name' );
        if ( $duration ) {
            $duration_text = sprintf( '%d day%s', $duration, $duration > 1 ? 's' : '' );
            $subject = sprintf( '[%s] Your account has been suspended for %s', $site_name, $duration_text );
            $message = sprintf(
                "Hello %s,\n\nYour account on %s has been suspended for %s.\n\nReason: %s\n\nYour suspension will expire on: %s\n\nIf you believe this was a mistake, please contact the moderation team.\n\nRegards,\n%s Moderation Team",
                $user->display_name,
                $site_name,
                $duration_text,
                $reason,
                wp_date( 'F j, Y \a\t g:i A', strtotime( $expires_at ) ),
                $site_name
            );
        } else {
            $subject = sprintf( '[%s] Your account has been permanently suspended', $site_name );
            $message = sprintf(
                "Hello %s,\n\nYour account on %s has been permanently suspended.\n\nReason: %s\n\nIf you believe this was a mistake, please contact the moderation team.\n\nRegards,\n%s Moderation Team",
                $user->display_name,
                $site_name,
                $reason,
                $site_name
            );
        }
        wp_mail( $user->user_email, $subject, $message );

        // Create in-app notification
        $suspend_msg = $duration
            ? sprintf( 'Your account has been suspended for %d day%s.', $duration, $duration > 1 ? 's' : '' )
            : 'Your account has been permanently suspended.';
        $this->insert_notification(
            $user_id,
            get_current_user_id(),
            'suspension',
            'user',
            $user_id,
            $suspend_msg
        );

        $suspend_desc = $duration
            ? sprintf( 'Suspended %s for %d day%s', $user->display_name, $duration, $duration > 1 ? 's' : '' )
            : 'Permanently suspended ' . $user->display_name;
        $this->log_activity( get_current_user_id(), 'user_suspended', $suspend_desc, 0, null, 'user', $user_id, '#/user/' . $user_id );

        return array( 'success' => true, 'id' => $insert_id );
    }

    public function get_moderation_stats( WP_REST_Request $request ) {
        global $wpdb;
        $reports_table = $wpdb->prefix . 'cnw_social_worker_reports';
        $warnings_table = $wpdb->prefix . 'cnw_social_worker_warnings';

        $open_reports = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reports_table} WHERE status = 'open'" );
        $in_progress_reports = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reports_table} WHERE status = 'in_progress'" );
        $resolved_reports = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reports_table} WHERE status = 'resolved'" );
        $total_reports = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reports_table}" );
        $closed_reports = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$reports_table} WHERE status = 'closed'" );
        $total_warnings = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$warnings_table} WHERE type = 'warning'" );
        $active_suspensions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$warnings_table} WHERE type = 'suspension' AND is_active = 1" );

        return array(
            'open_reports'       => $open_reports,
            'in_progress_reports'=> $in_progress_reports,
            'resolved_reports'   => $resolved_reports,
            'closed_reports'     => $closed_reports,
            'total_reports'      => $total_reports,
            'total_warnings'     => $total_warnings,
            'active_suspensions' => $active_suspensions,
        );
    }

    public function get_warnings( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_warnings';
        $page = max( 1, (int) $request->get_param( 'page' ) ?: 1 );
        $per_page = 20;
        $offset = ( $page - 1 ) * $per_page;

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        $warnings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) );

        foreach ( $warnings as &$w ) {
            $user = get_userdata( $w->user_id );
            $w->user_name = $user ? $user->display_name : 'Unknown';
            $w->user_avatar = $user ? ( get_user_meta( $w->user_id, 'cnw_avatar_url', true ) ?: '' ) : '';
            $mod = get_userdata( $w->moderator_id );
            $w->moderator_name = $mod ? $mod->display_name : 'Unknown';
        }

        return array( 'warnings' => $warnings, 'total' => $total, 'pages' => ceil( $total / $per_page ) );
    }

    public function delete_warning( WP_REST_Request $request ) {
        global $wpdb;
        $id    = (int) $request['id'];
        $table = $wpdb->prefix . 'cnw_social_worker_warnings';

        $warning = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $warning ) {
            return new WP_Error( 'not_found', 'Warning not found.', array( 'status' => 404 ) );
        }

        // If deleting an active suspension, clear the user's suspended meta
        if ( $warning->type === 'suspension' && (int) $warning->is_active === 1 ) {
            delete_user_meta( (int) $warning->user_id, 'cnw_suspended' );
            delete_user_meta( (int) $warning->user_id, 'cnw_suspended_until' );
        }

        $wpdb->delete( $table, array( 'id' => $id ) );

        $warn_type = $warning->type === 'suspension' ? 'suspension' : 'warning';
        $warn_user = get_userdata( (int) $warning->user_id );
        $warn_name = $warn_user ? $warn_user->display_name : 'user #' . $warning->user_id;
        $this->log_activity( get_current_user_id(), 'warning_deleted', 'Removed ' . $warn_type . ' for ' . $warn_name, 0, null, 'user', (int) $warning->user_id, '#/user/' . $warning->user_id );

        return array( 'success' => true );
    }

    public function get_user_warnings( WP_REST_Request $request ) {
        global $wpdb;
        $user_id  = (int) $request['id'];
        $current  = get_current_user_id();
        $table    = $wpdb->prefix . 'cnw_social_worker_warnings';

        // Only the user themselves or a moderator can view warnings
        if ( $current !== $user_id && ! current_user_can( 'cnw_close_threads' ) && ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'forbidden', 'You cannot view this user\'s warnings.', array( 'status' => 403 ) );
        }

        $page     = max( 1, (int) ( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = 20;
        $offset   = ( $page - 1 ) * $per_page;

        $total    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE user_id = %d", $user_id ) );
        $warnings = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $user_id, $per_page, $offset
        ) );

        foreach ( $warnings as &$w ) {
            $mod = get_userdata( $w->moderator_id );
            $w->moderator_name = $mod ? $mod->display_name : 'Unknown';
        }

        return array( 'warnings' => $warnings, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    public function create_report( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['type'] ) || empty( $params['subject'] ) || empty( $params['description'] ) ) {
            return new WP_Error( 'missing_fields', 'Type, subject and description are required.', array( 'status' => 400 ) );
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_reports',
            array(
                'user_id'     => get_current_user_id(),
                'type'        => sanitize_text_field( $params['type'] ),
                'subject'     => sanitize_text_field( $params['subject'] ),
                'description' => wp_kses_post( $params['description'] ),
                'link'        => isset( $params['link'] ) ? esc_url_raw( $params['link'] ) : null,
                'priority'    => sanitize_text_field( $params['priority'] ?? 'medium' ),
                'content_type' => isset( $params['content_type'] ) ? sanitize_text_field( $params['content_type'] ) : null,
                'content_id'   => isset( $params['content_id'] ) ? (int) $params['content_id'] : null,
                'status'      => 'open',
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to submit report.', array( 'status' => 500 ) );
        }

        // Invalidate admin badge cache
        delete_transient( 'cnw_admin_badge_counts' );

        // Notify all moderators in real-time so their report count updates
        $this->notify_moderators_new_report();

        $this->log_activity( get_current_user_id(), 'report_created', 'Submitted a report: ' . sanitize_text_field( $params['subject'] ), 0, null, 'report', $wpdb->insert_id );

        return array( 'success' => true, 'id' => $wpdb->insert_id );
    }

    /**
     * Send a Pusher event to all moderators when a new report is filed.
     */
    private function notify_moderators_new_report() {
        $moderators = get_users( array(
            'role__in' => array( 'cnw_moderator', 'cnw_forum_admin', 'administrator' ),
            'fields'   => 'ID',
        ) );

        $me = get_current_user_id();
        foreach ( $moderators as $mod_id ) {
            if ( (int) $mod_id === $me ) continue;
            Cnw_Social_Bridge_Pusher::trigger(
                'private-user-' . (int) $mod_id,
                'new-report',
                array( 'reporter_id' => $me )
            );
        }
    }

    /* ==================================================================
     * GUIDELINES
     * ================================================================== */

    public function get_guidelines( WP_REST_Request $request ) {
        $html = get_option( 'cnw_community_guidelines_html', '' );
        return array( 'html' => $html );
    }

    /* ==================================================================
     * REGISTRATION
     * ================================================================== */

    public function handle_register( WP_REST_Request $request ) {
        $rl = $this->rate_limit( 'register', 5, 600 ); // 5 attempts per 10 min
        if ( is_wp_error( $rl ) ) return $rl;

        $first_name = sanitize_text_field( $request->get_param( 'first_name' ) );
        $last_name  = sanitize_text_field( $request->get_param( 'last_name' ) );
        $username   = sanitize_user( $request->get_param( 'username' ) );
        $email      = sanitize_email( $request->get_param( 'email' ) );
        $password   = $request->get_param( 'password' );

        if ( empty( $first_name ) || empty( $last_name ) || empty( $username ) || empty( $email ) || empty( $password ) ) {
            return new WP_Error( 'missing_fields', 'All fields are required.', array( 'status' => 400 ) );
        }

        if ( strlen( $password ) < 8 ) {
            return new WP_Error( 'weak_password', 'Password must be at least 8 characters.', array( 'status' => 400 ) );
        }

        if ( ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', 'Please enter a valid email address.', array( 'status' => 400 ) );
        }

        if ( username_exists( $username ) ) {
            return new WP_Error( 'username_taken', 'This username is already taken.', array( 'status' => 400 ) );
        }

        if ( email_exists( $email ) ) {
            return new WP_Error( 'email_taken', 'An account with this email already exists.', array( 'status' => 400 ) );
        }

        $user_id = wp_insert_user( array(
            'user_login'  => $username,
            'user_email'  => $email,
            'user_pass'   => $password,
            'first_name'  => $first_name,
            'last_name'   => $last_name,
            'display_name' => trim( $first_name . ' ' . $last_name ),
            'role'        => 'cnw_forum_member',
        ) );

        if ( is_wp_error( $user_id ) ) {
            return new WP_Error( 'registration_failed', $user_id->get_error_message(), array( 'status' => 400 ) );
        }

        update_user_meta( $user_id, 'cnw_verified_label', 'Verified Social Worker' );
        update_user_meta( $user_id, 'cnw_professional_title', 'Licensed Clinical Social Worker' );

        $this->log_activity( $user_id, 'registered', 'Registered a new account', 0, 'No points for registration' );

        return array( 'success' => true, 'message' => 'Account created successfully.' );
    }

    public function handle_logout() {
        $this->log_activity( get_current_user_id(), 'logout', 'Logged out of the forum', 0, 'No points for logout' );
        wp_logout();
        return array( 'success' => true );
    }

    public function handle_forgot_password( WP_REST_Request $request ) {
        $rl = $this->rate_limit( 'forgot_pw', 3, 600 ); // 3 attempts per 10 min
        if ( is_wp_error( $rl ) ) return $rl;

        $user_login = sanitize_text_field( $request->get_param( 'user_login' ) );

        if ( empty( $user_login ) ) {
            return new WP_Error( 'missing_field', 'Please enter your username or email.', array( 'status' => 400 ) );
        }

        // Always return success to avoid user enumeration.
        $result = retrieve_password( $user_login );

        // Even if it fails, we respond with the same message.
        return array( 'success' => true, 'message' => 'If an account exists with that username or email, a password reset link has been sent.' );
    }

    public function handle_login( WP_REST_Request $request ) {
        $rl = $this->rate_limit( 'login', 10, 300 ); // 10 attempts per 5 min
        if ( is_wp_error( $rl ) ) return $rl;

        $username = sanitize_text_field( $request->get_param( 'username' ) );
        $password = $request->get_param( 'password' );

        if ( empty( $username ) || empty( $password ) ) {
            return new WP_Error( 'missing_fields', 'Username and password are required.', array( 'status' => 400 ) );
        }

        $user = wp_signon( array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        ), is_ssl() );

        if ( is_wp_error( $user ) ) {
            return new WP_Error( 'login_failed', 'Invalid username or password.', array( 'status' => 401 ) );
        }

        // Set the current user so we can generate a fresh nonce.
        wp_set_current_user( $user->ID );

        $this->log_activity( $user->ID, 'login', 'Logged in to the forum', 0, 'No points for login' );

        return array(
            'success'     => true,
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'currentUser' => array(
                'id'         => $user->ID,
                'name'       => $user->display_name,
                'first_name' => get_user_meta( $user->ID, 'first_name', true ),
                'last_name'  => get_user_meta( $user->ID, 'last_name', true ),
                'avatar'     => $this->get_user_avatar( $user->ID, 80 ),
                'reputation' => (int) get_user_meta( $user->ID, 'cnw_reputation_total', true ),
            ),
        );
    }

    /* ------------------------------------------------------------------
     * Activity log
     * ------------------------------------------------------------------ */

    /**
     * Simple rate limiter using transients. Returns WP_Error if limit exceeded.
     */
    private function rate_limit( $action, $max_attempts = 5, $window = 300 ) {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key  = 'cnw_rl_' . $action . '_' . md5( $ip );
        $hits = (int) get_transient( $key );
        if ( $hits >= $max_attempts ) {
            return new WP_Error( 'rate_limited', 'Too many attempts. Please try again later.', array( 'status' => 429 ) );
        }
        set_transient( $key, $hits + 1, $window );
        return false;
    }

    private function log_activity( $user_id, $action_type, $description, $points = 0, $reason = null, $reference_type = null, $reference_id = null, $link = null ) {
        global $wpdb;

        if ( ! $user_id || $user_id <= 0 ) {
            return;
        }

        $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_activity',
            array(
                'user_id'        => (int) $user_id,
                'action_type'    => $action_type,
                'description'    => $description,
                'points'         => (int) $points,
                'reason'         => $reason,
                'reference_type' => $reference_type,
                'reference_id'   => $reference_id ? (int) $reference_id : null,
                'link'           => $link,
            )
        );
    }

    public function get_user_activity( WP_REST_Request $request ) {
        global $wpdb;

        $user_id  = get_current_user_id();
        $page     = max( 1, (int) $request->get_param( 'page' ) ?: 1 );
        $per_page = 10;
        $offset   = ( $page - 1 ) * $per_page;

        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_activity WHERE user_id = %d",
            $user_id
        ) );

        $activities = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_activity
             WHERE user_id = %d
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d",
            $user_id,
            $per_page,
            $offset
        ) );

        return array(
            'activities' => $activities,
            'total'      => $total,
            'pages'      => ceil( $total / $per_page ),
        );
    }

    /* ------------------------------------------------------------------
     * Connections
     * ------------------------------------------------------------------ */

    /**
     * Check if two users are connected (accepted).
     */
    private function are_connected( $user_a, $user_b ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_connections';
        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table
             WHERE status = 'accepted'
               AND ((sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d))",
            $user_a, $user_b, $user_b, $user_a
        ) );
    }

    /**
     * Check if there is a block between two users.
     * Returns: null (no block), 'me' (I blocked them), 'them' (they blocked me).
     */
    private function get_blocked_by( $me, $other ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_connections';
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT sender_id FROM $table
             WHERE status = 'blocked'
               AND ((sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d))",
            $me, $other, $other, $me
        ) );
        if ( ! $row ) return null;
        return (int) $row->sender_id === $me ? 'me' : 'them';
    }

    /**
     * Get connection status between current user and another user.
     * Returns: none | pending_sent | pending_received | connected
     */
    public function get_connection_status( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $table = $wpdb->prefix . 'cnw_social_worker_connections';

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT sender_id, receiver_id, status FROM $table
             WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)",
            $me, $other, $other, $me
        ) );

        if ( ! $row ) {
            return array( 'status' => 'none' );
        }
        if ( $row->status === 'blocked' ) {
            return array( 'status' => 'blocked' );
        }
        if ( $row->status === 'accepted' ) {
            return array( 'status' => 'connected' );
        }
        // pending
        if ( (int) $row->sender_id === $me ) {
            return array( 'status' => 'pending_sent' );
        }
        return array( 'status' => 'pending_received' );
    }

    /**
     * Send a connection request.
     */
    public function send_connection_request( WP_REST_Request $request ) {
        $suspension = $this->get_suspension_error();
        if ( is_wp_error( $suspension ) ) return $suspension;

        global $wpdb;
        $me       = get_current_user_id();
        $other    = intval( $request['user_id'] );
        $table    = $wpdb->prefix . 'cnw_social_worker_connections';

        if ( $me === $other ) {
            return new WP_Error( 'invalid', 'Cannot connect to yourself', array( 'status' => 400 ) );
        }

        // Check if a row already exists
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, sender_id, status FROM $table
             WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)",
            $me, $other, $other, $me
        ) );

        if ( $existing ) {
            if ( $existing->status === 'blocked' ) {
                return new WP_Error( 'blocked', 'Unable to send connection request.', array( 'status' => 403 ) );
            }
            if ( $existing->status === 'accepted' ) {
                return array( 'status' => 'connected' );
            }
            if ( $existing->status === 'pending' ) {
                // If the other person sent us a request, auto-accept
                if ( (int) $existing->sender_id === $other ) {
                    $wpdb->update( $table, array( 'status' => 'accepted' ), array( 'id' => $existing->id ) );
                    return array( 'status' => 'connected' );
                }
                return array( 'status' => 'pending_sent' );
            }
            // If declined previously, allow re-sending
            $wpdb->update( $table,
                array( 'sender_id' => $me, 'receiver_id' => $other, 'status' => 'pending' ),
                array( 'id' => $existing->id )
            );
            return array( 'status' => 'pending_sent' );
        }

        $wpdb->insert( $table, array(
            'sender_id'   => $me,
            'receiver_id' => $other,
            'status'      => 'pending',
        ) );

        // Send notification to receiver (with avatar via helper)
        $sender_name = wp_get_current_user()->display_name;
        $this->insert_notification( $other, $me, 'connection_request', 'user', $me, $sender_name . ' sent you a connection request.' );

        // Log activity for both users
        $other_user = get_userdata( $other );
        $other_name = $other_user ? $other_user->display_name : 'Unknown';
        $this->log_activity( $me, 'connection_request', 'You sent a connection request to ' . $other_name, 0, null, 'user', $other );
        $this->log_activity( $other, 'connection_request', $sender_name . ' sent you a connection request.', 0, null, 'user', $me );

        return array( 'status' => 'pending_sent' );
    }

    /**
     * Accept a connection request.
     */
    public function accept_connection( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $table = $wpdb->prefix . 'cnw_social_worker_connections';

        $updated = $wpdb->update(
            $table,
            array( 'status' => 'accepted' ),
            array( 'sender_id' => $other, 'receiver_id' => $me, 'status' => 'pending' )
        );

        if ( ! $updated ) {
            return new WP_Error( 'not_found', 'No pending request from this user', array( 'status' => 404 ) );
        }

        // Notify the original sender (with avatar via helper)
        $accepter_name = wp_get_current_user()->display_name;
        $this->insert_notification( $other, $me, 'connection_accepted', 'user', $me, $accepter_name . ' accepted your connection request.' );

        // Log activity for both users
        $other_user = get_userdata( $other );
        $other_name = $other_user ? $other_user->display_name : 'Unknown';
        $this->log_activity( $me, 'connection_accepted', 'You accepted a connection request from ' . $other_name, 0, null, 'user', $other );
        $this->log_activity( $other, 'connection_accepted', $accepter_name . ' accepted your connection request.', 0, null, 'user', $me );

        return array( 'status' => 'connected' );
    }

    /**
     * Decline a connection request.
     */
    public function decline_connection( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $table = $wpdb->prefix . 'cnw_social_worker_connections';

        $deleted = $wpdb->delete( $table, array( 'sender_id' => $other, 'receiver_id' => $me, 'status' => 'pending' ) );

        if ( $deleted ) {
            // Notify the original sender that their request was declined
            $decliner_name = wp_get_current_user()->display_name;
            $this->insert_notification( $other, $me, 'connection_declined', 'user', $me, $decliner_name . ' declined your connection request.' );

            // Log activity for both users
            $other_user = get_userdata( $other );
            $other_name = $other_user ? $other_user->display_name : 'Unknown';
            $this->log_activity( $me, 'connection_declined', 'You declined a connection request from ' . $other_name, 0, null, 'user', $other );
            $this->log_activity( $other, 'connection_declined', $decliner_name . ' declined your connection request.', 0, null, 'user', $me );
        }

        return array( 'status' => 'none' );
    }

    /**
     * Remove a connection (unfriend) or cancel a sent request.
     */
    public function remove_connection( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $table = $wpdb->prefix . 'cnw_social_worker_connections';

        $wpdb->query( $wpdb->prepare(
            "DELETE FROM $table WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)",
            $me, $other, $other, $me
        ) );

        // Notify the other user in real time
        Cnw_Social_Bridge_Pusher::trigger(
            'private-user-' . $other,
            'connection-removed',
            array( 'remover_id' => $me )
        );

        $other_user = get_userdata( $other );
        $other_name = $other_user ? $other_user->display_name : 'user #' . $other;
        $this->log_activity( $me, 'connection_removed', 'Removed connection with ' . $other_name, 0, null, 'user', $other, '#/user/' . $other );

        return array( 'status' => 'none' );
    }

    /**
     * Block a user — sets connection status to 'blocked'.
     */
    public function block_user( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $table = $wpdb->prefix . 'cnw_social_worker_connections';

        if ( $me === $other ) {
            return new WP_Error( 'invalid', 'Cannot block yourself', array( 'status' => 400 ) );
        }

        // Check if the other user already blocked me — cannot override their block
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT sender_id, status FROM $table
             WHERE status = 'blocked'
               AND ((sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d))",
            $me, $other, $other, $me
        ) );

        if ( $existing && (int) $existing->sender_id === $other ) {
            return new WP_Error( 'blocked_by_other', 'This user has already blocked you.', array( 'status' => 403 ) );
        }

        // Delete any existing non-block row between the two users
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM $table WHERE status != 'blocked' AND ((sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d))",
            $me, $other, $other, $me
        ) );

        // Insert or update blocked row if not already blocked by me
        if ( ! $existing ) {
            $wpdb->insert( $table, array(
                'sender_id'   => $me,
                'receiver_id' => $other,
                'status'      => 'blocked',
            ) );
        }

        // Notify the blocked user in real time
        Cnw_Social_Bridge_Pusher::trigger(
            'private-user-' . $other,
            'connection-blocked',
            array( 'blocker_id' => $me )
        );

        $blocked_user = get_userdata( $other );
        $blocked_name = $blocked_user ? $blocked_user->display_name : 'user #' . $other;
        $this->log_activity( $me, 'user_blocked', 'Blocked ' . $blocked_name, 0, null, 'user', $other );

        return array( 'status' => 'blocked' );
    }

    /**
     * Unblock a user (only the blocker can unblock).
     */
    public function unblock_user( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );
        $table = $wpdb->prefix . 'cnw_social_worker_connections';

        $wpdb->query( $wpdb->prepare(
            "DELETE FROM $table WHERE sender_id = %d AND receiver_id = %d AND status = 'blocked'",
            $me, $other
        ) );

        // Notify the unblocked user in real time
        Cnw_Social_Bridge_Pusher::trigger(
            'private-user-' . $other,
            'connection-unblocked',
            array( 'unblocker_id' => $me )
        );

        $unblocked_user = get_userdata( $other );
        $unblocked_name = $unblocked_user ? $unblocked_user->display_name : 'user #' . $other;
        $this->log_activity( $me, 'user_unblocked', 'Unblocked ' . $unblocked_name, 0, null, 'user', $other );

        return array( 'status' => 'none' );
    }

    /**
     * List my accepted connections.
     */
    public function get_connections( WP_REST_Request $request ) {
        global $wpdb;
        $me       = get_current_user_id();
        $table    = $wpdb->prefix . 'cnw_social_worker_connections';
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = 20;
        $offset   = ( $page - 1 ) * $per_page;
        $search   = sanitize_text_field( $request->get_param( 'search' ) );

        $search_join  = '';
        $search_where = '';
        $search_params = array();
        if ( ! empty( $search ) && strlen( $search ) >= 2 ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $search_where = ' AND u.display_name LIKE %s';
            $search_params[] = $like;
        }

        $base_params = array( $me, $me );
        $count_sql = "SELECT COUNT(*)
            FROM $table c
            JOIN {$wpdb->users} u ON u.ID = IF(c.sender_id = %d, c.receiver_id, c.sender_id)
            WHERE c.status = 'accepted' AND (c.sender_id = %d OR c.receiver_id = %d) $search_where";
        $count_params = array( $me, $me, $me );
        $total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, array_merge( $count_params, $search_params ) ) );

        $sql = "SELECT u.ID, u.display_name, u.user_registered, c.created_at AS connected_since
            FROM $table c
            JOIN {$wpdb->users} u ON u.ID = IF(c.sender_id = %d, c.receiver_id, c.sender_id)
            WHERE c.status = 'accepted' AND (c.sender_id = %d OR c.receiver_id = %d) $search_where
            ORDER BY u.display_name ASC
            LIMIT %d OFFSET %d";
        $sql_params = array_merge( array( $me, $me, $me ), $search_params, array( $per_page, $offset ) );
        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $sql_params ) );

        $users = array();
        foreach ( $rows as $row ) {
            $uid = (int) $row->ID;
            $users[] = array(
                'id'                 => $uid,
                'name'               => $row->display_name,
                'avatar'             => get_user_meta( $uid, 'cnw_avatar_url', true ) ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR,
                'verified_label'     => get_user_meta( $uid, 'cnw_verified_label', true ),
                'professional_title' => get_user_meta( $uid, 'cnw_professional_title', true ),
                'reputation'         => (int) get_user_meta( $uid, 'cnw_reputation_total', true ),
                'thread_count'       => (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE author_id = %d", $uid
                ) ),
                'reply_count'        => (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies WHERE author_id = %d", $uid
                ) ),
                'is_online'          => ( $this->has_restricted( $me, $uid ) || $this->has_restricted( $uid, $me ) ) ? false : $this->is_user_online( $uid, $me ),
                'connected_since'    => $row->connected_since,
                'connection_status'  => 'connected',
            );
        }

        return array( 'users' => $users, 'total' => $total, 'pages' => (int) ceil( $total / $per_page ) );
    }

    /**
     * List pending connection requests I received.
     */
    public function get_connection_requests( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $table = $wpdb->prefix . 'cnw_social_worker_connections';

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT c.id, c.sender_id, c.created_at, u.display_name
             FROM $table c
             JOIN {$wpdb->users} u ON u.ID = c.sender_id
             WHERE c.receiver_id = %d AND c.status = 'pending'
             ORDER BY c.created_at DESC",
            $me
        ) );

        $requests = array();
        foreach ( $rows as $row ) {
            $uid = (int) $row->sender_id;
            $requests[] = array(
                'id'                 => (int) $row->id,
                'user_id'            => $uid,
                'name'               => $row->display_name,
                'avatar'             => get_user_meta( $uid, 'cnw_avatar_url', true ) ?: CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR,
                'verified_label'     => get_user_meta( $uid, 'cnw_verified_label', true ),
                'professional_title' => get_user_meta( $uid, 'cnw_professional_title', true ),
                'reputation'         => (int) get_user_meta( $uid, 'cnw_reputation_total', true ),
                'thread_count'       => (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE author_id = %d", $uid
                ) ),
                'reply_count'        => (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies WHERE author_id = %d", $uid
                ) ),
                'is_online'          => ( $this->has_restricted( $me, $uid ) || $this->has_restricted( $uid, $me ) ) ? false : $this->is_user_online( $uid, $me ),
                'created_at'         => $row->created_at,
            );
        }

        return array( 'requests' => $requests );
    }

    /* ==================================================================
     * RESTRICTIONS
     * ================================================================== */

    /**
     * Check if $restricter has restricted $restricted.
     */
    private function has_restricted( $restricter_id, $restricted_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_restrictions';
        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE restricter_id = %d AND restricted_id = %d",
            $restricter_id, $restricted_id
        ) );
    }

    /**
     * Get all user IDs that have restricted the given user.
     */
    private function get_restricters_of( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_restrictions';
        return array_map( 'intval', $wpdb->get_col( $wpdb->prepare(
            "SELECT restricter_id FROM {$table} WHERE restricted_id = %d",
            $user_id
        ) ) );
    }

    /**
     * Get all user IDs that the given user has restricted.
     */
    private function get_restricted_by( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_restrictions';
        return array_map( 'intval', $wpdb->get_col( $wpdb->prepare(
            "SELECT restricted_id FROM {$table} WHERE restricter_id = %d",
            $user_id
        ) ) );
    }

    public function restrict_user( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );

        if ( $me === $other ) {
            return new WP_Error( 'invalid', 'Cannot restrict yourself.', array( 'status' => 400 ) );
        }

        $table = $wpdb->prefix . 'cnw_social_worker_restrictions';
        $wpdb->replace( $table, array(
            'restricter_id' => $me,
            'restricted_id' => $other,
        ) );

        // Notify the restricted user in real-time so their UI updates
        Cnw_Social_Bridge_Pusher::trigger(
            'private-user-' . $other,
            'user-restricted',
            array( 'restricter_id' => $me )
        );

        $restricted_user = get_userdata( $other );
        $restricted_name = $restricted_user ? $restricted_user->display_name : 'user #' . $other;
        $this->log_activity( $me, 'user_restricted', 'Restricted ' . $restricted_name, 0, null, 'user', $other );

        return array( 'success' => true );
    }

    public function unrestrict_user( WP_REST_Request $request ) {
        global $wpdb;
        $me    = get_current_user_id();
        $other = intval( $request['user_id'] );

        $table = $wpdb->prefix . 'cnw_social_worker_restrictions';
        $wpdb->delete( $table, array(
            'restricter_id' => $me,
            'restricted_id' => $other,
        ) );

        // Notify the unrestricted user in real-time
        Cnw_Social_Bridge_Pusher::trigger(
            'private-user-' . $other,
            'user-unrestricted',
            array( 'restricter_id' => $me )
        );

        $unrestricted_user = get_userdata( $other );
        $unrestricted_name = $unrestricted_user ? $unrestricted_user->display_name : 'user #' . $other;
        $this->log_activity( $me, 'user_unrestricted', 'Unrestricted ' . $unrestricted_name, 0, null, 'user', $other );

        return array( 'success' => true );
    }

    public function get_restrictions() {
        $me = get_current_user_id();
        return array(
            'restricted'     => $this->get_restricted_by( $me ),
            'restricted_by'  => $this->get_restricters_of( $me ),
        );
    }
}
