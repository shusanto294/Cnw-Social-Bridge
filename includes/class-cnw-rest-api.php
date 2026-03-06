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
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_message' ), 'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_message' ), 'permission_callback' => array( $this, 'can_manage' ) ),
        ) );

        // ── Categories ───────────────────────────────────────────────
        register_rest_route( $ns, '/categories', array(
            array( 'methods' => 'GET',  'callback' => array( $this, 'get_categories' ),  'permission_callback' => '__return_true' ),
            array( 'methods' => 'POST', 'callback' => array( $this, 'create_category' ), 'permission_callback' => array( $this, 'can_create_thread' ) ),
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
        if ( current_user_can( 'manage_options' ) ) {
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
        if ( current_user_can( 'manage_options' ) ) {
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

        $where = "WHERE t.status = 'published'";
        $join  = '';
        $order = 'ORDER BY t.created_at DESC';

        if ( $search ) {
            $like   = '%' . $wpdb->esc_like( $search ) . '%';
            $where .= $wpdb->prepare( ' AND (t.title LIKE %s OR t.content LIKE %s)', $like, $like );
        }

        switch ( $filter ) {
            case 'active':
                $order = 'ORDER BY t.updated_at DESC';
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
                $join  = "INNER JOIN {$wpdb->prefix}cnw_social_worker_thread_tags ftj ON ftj.thread_id = t.id";
                $where .= $wpdb->prepare(
                    " AND ftj.tag_id IN ($tag_placeholders)",
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

        // Attach tags + avatar, handle anonymous
        foreach ( $threads as &$thread ) {
            $thread->tags = $wpdb->get_col( $wpdb->prepare(
                "SELECT tg.name FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
                 JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tt.tag_id = tg.id
                 WHERE tt.thread_id = %d",
                $thread->id
            ) );
            if ( ! empty( $thread->is_anonymous ) && (int) $thread->is_anonymous === 1 ) {
                $thread->author_name   = 'Anonymous';
                $thread->author_avatar = get_avatar_url( 0, array( 'size' => 80, 'default' => 'mystery' ) );
            } else {
                $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
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
            $thread->author_name   = 'Anonymous';
            $thread->author_avatar = get_avatar_url( 0, array( 'size' => 80, 'default' => 'mystery' ) );
        } else {
            $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
        }

        return $thread;
    }

    public function create_thread( WP_REST_Request $request ) {
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
                'status'       => 'published',
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

        $result = $wpdb->update( $wpdb->prefix . 'cnw_social_worker_threads', $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update thread', array( 'status' => 500 ) );
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
                $reply->author_name   = 'Anonymous';
                $reply->author_avatar = get_avatar_url( 0, array( 'size' => 40, 'default' => 'mystery' ) );
                $reply->author_id     = 0;
            } else {
                $reply->author_avatar = $this->get_user_avatar( (int) $reply->author_id, 40 );
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
        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['thread_id'] ) || empty( $params['content'] ) ) {
            return new WP_Error( 'missing_fields', 'thread_id and content are required', array( 'status' => 400 ) );
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
                'status'       => 'approved',
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

        // Award reputation: +2 for creating a reply
        $this->award_reputation( $actor_id, 2, 'reply_created', 'reply', $reply_id, 'Posted a reply' );

        // Notify thread author
        $thread_author = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", $thread_id
        ) );
        if ( $thread_author ) {
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

        $result = $wpdb->update( $wpdb->prefix . 'cnw_social_worker_replies', $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update reply', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_reply( WP_REST_Request $request ) {
        global $wpdb;

        $id = intval( $request['id'] );

        // Get reply author before deletion for reputation reversal
        $reply_author = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $id
        ) );

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

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * MARK REPLY AS SOLUTION
     * ================================================================== */

    public function mark_solution( WP_REST_Request $request ) {
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

        $is_own_reply = ( (int) $reply->author_id === $user_id );

        $is_solution = (int) $reply->is_solution;

        if ( $is_solution ) {
            // Unmark solution
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_replies',
                array( 'is_solution' => 0 ),
                array( 'id' => $reply_id )
            );
            // Remove solution reputation
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE action_type = 'best_answer' AND reference_type = 'reply' AND reference_id = %d",
                $reply_id
            ) );
            $this->recalc_user_reputation( (int) $reply->author_id );
            return array( 'success' => true, 'action' => 'unmarked', 'is_solution' => false );
        } else {
            // Unmark any existing solution for this thread
            $old_solution = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE thread_id = %d AND is_solution = 1",
                (int) $reply->thread_id
            ) );
            if ( $old_solution ) {
                $wpdb->update(
                    $wpdb->prefix . 'cnw_social_worker_replies',
                    array( 'is_solution' => 0 ),
                    array( 'id' => (int) $old_solution->id )
                );
                $wpdb->query( $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE action_type = 'best_answer' AND reference_type = 'reply' AND reference_id = %d",
                    (int) $old_solution->id
                ) );
                $this->recalc_user_reputation( (int) $old_solution->author_id );
            }

            // Mark this reply as solution
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_replies',
                array( 'is_solution' => 1 ),
                array( 'id' => $reply_id )
            );
            // Award +25 reputation to reply author (skip if marking own reply)
            if ( ! $is_own_reply ) {
                $this->award_reputation( (int) $reply->author_id, 25, 'best_answer', 'reply', $reply_id, 'Reply marked as solution' );

                // Notify the reply author
                $actor_name = wp_get_current_user()->display_name;
                $this->insert_notification( (int) $reply->author_id, $user_id, 'solution', 'thread', (int) $reply->thread_id,
                    "{$actor_name} marked your reply as helpful." );
            }

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
        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['recipient_id'] ) || empty( $params['content'] ) ) {
            return new WP_Error( 'missing_fields', 'recipient_id and content are required', array( 'status' => 400 ) );
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_messages',
            array(
                'sender_id'    => get_current_user_id(),
                'recipient_id' => intval( $params['recipient_id'] ),
                'subject'      => isset( $params['subject'] ) ? sanitize_text_field( $params['subject'] ) : null,
                'content'      => wp_kses_post( $params['content'] ),
                'is_read'      => 0,
                'parent_id'    => isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null,
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create message', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'id' => $wpdb->insert_id );
    }

    public function update_message( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $params = $request->get_json_params();
        $data   = array();

        if ( isset( $params['subject'] ) ) $data['subject'] = sanitize_text_field( $params['subject'] );
        if ( isset( $params['content'] ) ) $data['content'] = wp_kses_post( $params['content'] );
        if ( isset( $params['is_read'] ) ) $data['is_read'] = intval( $params['is_read'] );

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'No fields to update', array( 'status' => 400 ) );
        }

        $result = $wpdb->update( $wpdb->prefix . 'cnw_social_worker_messages', $data, array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to update message', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_message( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_messages', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete message', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * CATEGORIES
     * ================================================================== */

    public function get_categories( WP_REST_Request $request ) {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_categories ORDER BY sort_order ASC, name ASC"
        );
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

        return array( 'success' => true, 'id' => $wpdb->insert_id );
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

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_category( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_categories', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete category', array( 'status' => 500 ) );
        }

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
        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['target_type'] ) || empty( $params['target_id'] ) || ! isset( $params['vote_type'] ) ) {
            return new WP_Error( 'missing_fields', 'target_type, target_id, and vote_type are required', array( 'status' => 400 ) );
        }

        $user_id     = get_current_user_id();
        $target_type = sanitize_text_field( $params['target_type'] );
        $target_id   = intval( $params['target_id'] );
        $vote_type   = intval( $params['vote_type'] );

        // Block self-voting: users cannot vote on their own content
        $self_check_author = 0;
        if ( $target_type === 'thread' ) {
            $self_check_author = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_threads WHERE id = %d", $target_id
            ) );
        } elseif ( $target_type === 'reply' ) {
            $self_check_author = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT author_id FROM {$wpdb->prefix}cnw_social_worker_replies WHERE id = %d", $target_id
            ) );
        }
        if ( $self_check_author && $self_check_author === $user_id ) {
            return new WP_Error( 'self_vote', 'You cannot vote on your own content', array( 'status' => 403 ) );
        }

        // Check existing vote — toggle or update
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_votes
             WHERE user_id = %d AND target_type = %s AND target_id = %d",
            $user_id, $target_type, $target_id
        ) );

        // Content owner already resolved during self-vote check
        $content_owner = $self_check_author;

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
                // Award new vote reputation to content owner
                if ( $content_owner && $content_owner !== $user_id ) {
                    $rep_points = $vote_type === 1 ? 10 : -1;
                    $rep_desc   = $vote_type === 1 ? 'Received an upvote' : 'Received a downvote';
                    $rep_action = $vote_type === 1 ? 'received_upvote' : 'received_downvote';
                    $this->award_reputation( $content_owner, $rep_points, $rep_action, 'vote', (int) $existing->id, $rep_desc );
                }
                // Award +1 to the voter for giving an upvote
                if ( $vote_type === 1 ) {
                    $this->award_reputation( $user_id, 1, 'gave_upvote', 'vote', (int) $existing->id, 'Gave an upvote' );
                }
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

        // Award reputation to content owner (+10 for upvote, -1 for downvote)
        if ( $content_owner && $content_owner !== $user_id ) {
            $rep_points = $vote_type === 1 ? 10 : -1;
            $rep_desc   = $vote_type === 1 ? 'Received an upvote' : 'Received a downvote';
            $rep_action = $vote_type === 1 ? 'received_upvote' : 'received_downvote';
            $this->award_reputation( $content_owner, $rep_points, $rep_action, 'vote', $vote_id, $rep_desc );
        }

        // Award +1 to the voter for giving an upvote
        if ( $vote_type === 1 ) {
            $this->award_reputation( $user_id, 1, 'gave_upvote', 'vote', $vote_id, 'Gave an upvote' );
        }

        // Notify on upvote only
        if ( $vote_type === 1 ) {
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

        return array( 'success' => true, 'id' => (int) $wpdb->insert_id );
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
        if ( array_key_exists( 'description', $body ) ) {
            $data['description'] = sanitize_textarea_field( $body['description'] ) ?: null;
            $formats[]           = '%s';
        }

        if ( empty( $data ) ) {
            return new WP_Error( 'no_data', 'Nothing to update.', array( 'status' => 400 ) );
        }

        $wpdb->update( $table, $data, array( 'id' => $tag_id ), $formats, array( '%d' ) );
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

        return array( 'success' => true );
    }

    /* ==================================================================
     * SAVED THREADS
     * ================================================================== */

    public function save_thread( WP_REST_Request $request ) {
        global $wpdb;
        $user_id   = get_current_user_id();
        $thread_id = intval( $request['id'] );

        $wpdb->replace(
            $wpdb->prefix . 'cnw_social_worker_saved_threads',
            array( 'user_id' => $user_id, 'thread_id' => $thread_id )
        );

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

        return array( 'success' => true );
    }

    public function get_saved_threads( WP_REST_Request $request ) {
        global $wpdb;

        $current_user_id = get_current_user_id();

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
             WHERE st.user_id = %d AND t.status = 'published'
             ORDER BY st.created_at DESC",
            $current_user_id,
            $current_user_id
        ) );

        // Attach tags + avatar, handle anonymous
        foreach ( $threads as &$thread ) {
            $thread->tags = $wpdb->get_col( $wpdb->prepare(
                "SELECT tg.name FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
                 JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tt.tag_id = tg.id
                 WHERE tt.thread_id = %d",
                $thread->id
            ) );
            if ( ! empty( $thread->is_anonymous ) && (int) $thread->is_anonymous === 1 ) {
                $thread->author_name   = 'Anonymous';
                $thread->author_avatar = get_avatar_url( 0, array( 'size' => 80, 'default' => 'mystery' ) );
            } else {
                $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
            }
        }

        return array( 'threads' => $threads );
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
             WHERE t.status = 'published'
             ORDER BY t.views DESC, t.created_at DESC
             LIMIT 3",
            $current_user_id,
            $current_user_id
        ) );

        foreach ( $threads as &$thread ) {
            if ( ! empty( $thread->is_anonymous ) && (int) $thread->is_anonymous === 1 ) {
                $thread->author_name   = 'Anonymous';
                $thread->author_avatar = get_avatar_url( 0, array( 'size' => 80, 'default' => 'mystery' ) );
            } else {
                $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
            }
        }

        return $threads;
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

        $first_name = get_user_meta( $user_id, 'first_name', true );
        $last_name  = get_user_meta( $user_id, 'last_name', true );
        $phone      = get_user_meta( $user_id, 'cnw_phone', true );
        $bio        = get_user_meta( $user_id, 'description', true );
        $anonymous  = (bool) get_user_meta( $user_id, 'cnw_anonymous', true );
        $reputation = (int) get_user_meta( $user_id, 'cnw_reputation_total', true );
        $avatar     = $this->get_user_avatar( $user_id, 150 );

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

        // Only show email/phone to the profile owner
        $is_own = get_current_user_id() === $user_id;

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
            'is_own'          => $is_own,
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

        foreach ( $threads as &$thread ) {
            $thread->author_avatar = $this->get_user_avatar( (int) $thread->author_id, 80 );
            $tag_names = $wpdb->get_col( $wpdb->prepare(
                "SELECT tg.name FROM {$wpdb->prefix}cnw_social_worker_tags tg
                 INNER JOIN {$wpdb->prefix}cnw_social_worker_thread_tags tt ON tg.id = tt.tag_id
                 WHERE tt.thread_id = %d",
                $thread->id
            ) );
            $thread->tags = $tag_names;
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

        foreach ( $replies as &$reply ) {
            $reply->author_avatar = $this->get_user_avatar( (int) $reply->author_id, 80 );
            // Get tags from parent thread
            $tag_names = $wpdb->get_col( $wpdb->prepare(
                "SELECT tg.name FROM {$wpdb->prefix}cnw_social_worker_tags tg
                 INNER JOIN {$wpdb->prefix}cnw_social_worker_thread_tags tt ON tg.id = tt.tag_id
                 WHERE tt.thread_id = %d",
                $reply->thread_id
            ) );
            $reply->tags = $tag_names;
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

        return array( 'success' => true );
    }

    /**
     * Get the user's avatar URL, preferring a custom upload over Gravatar.
     */
    private function get_user_avatar( $user_id, $size = 150 ) {
        $custom = get_user_meta( $user_id, 'cnw_avatar_url', true );
        if ( $custom ) {
            return $custom;
        }
        return get_avatar_url( $user_id, array( 'size' => $size ) );
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

        // Validate file type
        $allowed = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
        if ( ! in_array( $files['file']['type'], $allowed, true ) ) {
            return new WP_Error( 'invalid_type', 'Only JPEG, PNG, GIF, and WebP images are allowed.', array( 'status' => 400 ) );
        }

        $attachment_id = media_handle_upload( 'file', 0 );

        if ( is_wp_error( $attachment_id ) ) {
            return new WP_Error( 'upload_failed', $attachment_id->get_error_message(), array( 'status' => 500 ) );
        }

        $url = wp_get_attachment_url( $attachment_id );
        update_user_meta( $user_id, 'cnw_avatar_url', esc_url_raw( $url ) );
        update_user_meta( $user_id, 'cnw_avatar_attachment_id', $attachment_id );

        return array( 'success' => true, 'avatar' => $url );
    }

    public function toggle_anonymous( WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $current   = (bool) get_user_meta( $user_id, 'cnw_anonymous', true );
        $new_value = ! $current;
        update_user_meta( $user_id, 'cnw_anonymous', $new_value ? '1' : '' );

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
                : get_avatar_url( 0, array( 'size' => 40, 'default' => 'mystery' ) );
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
    private function insert_notification( $user_id, $actor_id, $type, $ref_type, $ref_id, $message ) {
        global $wpdb;

        // Don't notify yourself.
        if ( (int) $user_id === (int) $actor_id ) {
            return;
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
    }

    /* ==================================================================
     * LOGIN
     * ================================================================== */

    public function handle_register( WP_REST_Request $request ) {
        $first_name = sanitize_text_field( $request->get_param( 'first_name' ) );
        $last_name  = sanitize_text_field( $request->get_param( 'last_name' ) );
        $username   = sanitize_user( $request->get_param( 'username' ) );
        $email      = sanitize_email( $request->get_param( 'email' ) );
        $password   = $request->get_param( 'password' );

        if ( empty( $first_name ) || empty( $last_name ) || empty( $username ) || empty( $email ) || empty( $password ) ) {
            return new WP_Error( 'missing_fields', 'All fields are required.', array( 'status' => 400 ) );
        }

        if ( strlen( $password ) < 6 ) {
            return new WP_Error( 'weak_password', 'Password must be at least 6 characters.', array( 'status' => 400 ) );
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

        return array( 'success' => true, 'message' => 'Account created successfully.' );
    }

    public function handle_logout() {
        wp_logout();
        return array( 'success' => true );
    }

    public function handle_forgot_password( WP_REST_Request $request ) {
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

        return array(
            'success'     => true,
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'currentUser' => array(
                'id'         => $user->ID,
                'name'       => $user->display_name,
                'first_name' => get_user_meta( $user->ID, 'first_name', true ),
                'last_name'  => get_user_meta( $user->ID, 'last_name', true ),
                'avatar'     => $this->get_user_avatar( $user->ID, 80 ),
            ),
        );
    }
}
