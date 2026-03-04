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
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_thread' ), 'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_thread' ), 'permission_callback' => array( $this, 'can_manage' ) ),
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
            array( 'methods' => 'PUT,PATCH', 'callback' => array( $this, 'update_reply' ), 'permission_callback' => array( $this, 'can_manage' ) ),
            array( 'methods' => 'DELETE',    'callback' => array( $this, 'delete_reply' ), 'permission_callback' => array( $this, 'can_manage' ) ),
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

        // ── Utility (kept for frontend) ──────────────────────────────
        register_rest_route( $ns, '/tags', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_tags' ), 'permission_callback' => '__return_true',
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
        register_rest_route( $ns, '/hot-questions', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_hot_questions' ), 'permission_callback' => '__return_true',
        ) );
        register_rest_route( $ns, '/users/(?P<id>\d+)', array(
            'methods' => 'GET', 'callback' => array( $this, 'get_user' ), 'permission_callback' => '__return_true',
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

    /* ==================================================================
     * THREADS
     * ================================================================== */

    public function get_threads( WP_REST_Request $request ) {
        global $wpdb;

        $filter   = $request->get_param( 'filter' ) ?: 'newest';
        $page     = max( 1, intval( $request->get_param( 'page' ) ?: 1 ) );
        $per_page = intval( $request->get_param( 'per_page' ) ?: 20 );
        $search   = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
        $offset   = ( $page - 1 ) * $per_page;

        $where = "WHERE t.status = 'published'";
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
        }

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $threads = $wpdb->get_results( $wpdb->prepare(
            "SELECT t.*, u.display_name AS author_name, u.ID AS author_id,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') AS reply_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = 1) AS likes
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             $where $order
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ) );

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads t $where" );
        // phpcs:enable

        // Attach tags to each thread
        foreach ( $threads as &$thread ) {
            $thread->tags = $wpdb->get_col( $wpdb->prepare(
                "SELECT tg.name FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
                 JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tt.tag_id = tg.id
                 WHERE tt.thread_id = %d",
                $thread->id
            ) );
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

        $thread = $wpdb->get_row( $wpdb->prepare(
            "SELECT t.*, u.display_name AS author_name, u.ID AS author_id
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             WHERE t.id = %d",
            $id
        ) );

        if ( ! $thread ) {
            return new WP_Error( 'not_found', 'Thread not found', array( 'status' => 404 ) );
        }

        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}cnw_social_worker_threads SET views = views + 1 WHERE id = %d",
            $id
        ) );

        // Attach tags
        $thread->tags = $wpdb->get_col( $wpdb->prepare(
            "SELECT tg.name FROM {$wpdb->prefix}cnw_social_worker_thread_tags tt
             JOIN {$wpdb->prefix}cnw_social_worker_tags tg ON tt.tag_id = tg.id
             WHERE tt.thread_id = %d",
            $id
        ) );

        return $thread;
    }

    public function create_thread( WP_REST_Request $request ) {
        global $wpdb;

        $params = $request->get_json_params();

        if ( empty( $params['title'] ) || empty( $params['content'] ) ) {
            return new WP_Error( 'missing_fields', 'Title and content are required', array( 'status' => 400 ) );
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_threads',
            array(
                'author_id' => get_current_user_id(),
                'title'     => sanitize_text_field( $params['title'] ),
                'content'   => wp_kses_post( $params['content'] ),
                'status'    => 'published',
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create thread', array( 'status' => 500 ) );
        }

        $thread_id = $wpdb->insert_id;

        // Save tags
        if ( ! empty( $params['tags'] ) && is_array( $params['tags'] ) ) {
            foreach ( $params['tags'] as $tag_name ) {
                $tag_name = sanitize_text_field( trim( $tag_name ) );
                if ( empty( $tag_name ) ) continue;

                $slug = sanitize_title( $tag_name );
                $tag_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}cnw_social_worker_tags WHERE slug = %s",
                    $slug
                ) );

                if ( ! $tag_id ) {
                    $wpdb->insert( $wpdb->prefix . 'cnw_social_worker_tags', array(
                        'name' => $tag_name,
                        'slug' => $slug,
                    ) );
                    $tag_id = $wpdb->insert_id;
                }

                $wpdb->replace( $wpdb->prefix . 'cnw_social_worker_thread_tags', array(
                    'thread_id' => $thread_id,
                    'tag_id'    => $tag_id,
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

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * REPLIES
     * ================================================================== */

    /** GET /threads/{id}/replies — replies for a specific thread (frontend uses this) */
    public function get_thread_replies( WP_REST_Request $request ) {
        global $wpdb;

        $thread_id = intval( $request['id'] );

        $replies = $wpdb->get_results( $wpdb->prepare(
            "SELECT r.*, u.display_name AS author_name, u.ID AS author_id,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'reply' AND v.target_id = r.id AND v.vote_type = 1) AS likes,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r2 WHERE r2.parent_id = r.id AND r2.status = 'approved') AS reply_count
             FROM {$wpdb->prefix}cnw_social_worker_replies r
             LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
             WHERE r.thread_id = %d AND r.status = 'approved'
             ORDER BY r.created_at ASC",
            $thread_id
        ) );

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

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_replies',
            array(
                'thread_id' => intval( $params['thread_id'] ),
                'author_id' => get_current_user_id(),
                'parent_id' => isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null,
                'content'   => wp_kses_post( $params['content'] ),
                'status'    => 'approved',
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create reply', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'id' => $wpdb->insert_id );
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

        return array( 'success' => true, 'deleted' => $id );
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

        // Check existing vote — toggle or update
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_votes
             WHERE user_id = %d AND target_type = %s AND target_id = %d",
            $user_id, $target_type, $target_id
        ) );

        if ( $existing ) {
            if ( (int) $existing->vote_type === $vote_type ) {
                // Same vote — remove it (toggle off)
                $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_votes', array( 'id' => $existing->id ) );
                return array( 'success' => true, 'action' => 'removed', 'id' => (int) $existing->id );
            } else {
                // Different vote — update
                $wpdb->update(
                    $wpdb->prefix . 'cnw_social_worker_votes',
                    array( 'vote_type' => $vote_type ),
                    array( 'id' => $existing->id )
                );
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

        return array( 'success' => true, 'action' => 'created', 'id' => $wpdb->insert_id );
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

        $result = $wpdb->insert(
            $wpdb->prefix . 'cnw_social_worker_reputation',
            array(
                'user_id'        => intval( $params['user_id'] ),
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

        return array( 'success' => true, 'id' => $wpdb->insert_id );
    }

    public function update_reputation( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $params = $request->get_json_params();
        $data   = array();

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

        return array( 'success' => true, 'id' => $id );
    }

    public function delete_reputation( WP_REST_Request $request ) {
        global $wpdb;

        $id     = intval( $request['id'] );
        $result = $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_reputation', array( 'id' => $id ) );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to delete reputation entry', array( 'status' => 500 ) );
        }

        return array( 'success' => true, 'deleted' => $id );
    }

    /* ==================================================================
     * UTILITY ENDPOINTS (kept for frontend compatibility)
     * ================================================================== */

    public function get_tags() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}cnw_social_worker_tags ORDER BY name ASC"
        );
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

    public function get_hot_questions() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT t.*, u.display_name AS author_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') AS reply_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_votes v WHERE v.target_type = 'thread' AND v.target_id = t.id AND v.vote_type = 1) AS likes
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             WHERE t.status = 'published'
             ORDER BY t.views DESC, t.created_at DESC
             LIMIT 5"
        );
    }

    public function get_user( WP_REST_Request $request ) {
        global $wpdb;

        $user_id = intval( $request['id'] );

        $user = $wpdb->get_row( $wpdb->prepare(
            "SELECT u.ID, u.display_name, u.user_email, u.user_login
             FROM {$wpdb->users} u
             WHERE u.ID = %d",
            $user_id
        ) );

        if ( ! $user ) {
            return new WP_Error( 'not_found', 'User not found', array( 'status' => 404 ) );
        }

        return $user;
    }
}
