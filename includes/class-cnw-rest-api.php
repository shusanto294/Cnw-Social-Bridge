<?php
/**
 * REST API class — registers all cnw-social-bridge/v1 routes and callbacks.
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

        register_rest_route( $ns, '/threads', array(
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_threads' ),
                'permission_callback' => '__return_true',
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'create_thread' ),
                'permission_callback' => array( $this, 'can_create_thread' ),
            ),
        ) );

        register_rest_route( $ns, '/threads/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_thread' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/threads/(?P<id>\d+)/replies', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_replies' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/replies', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_reply' ),
            'permission_callback' => array( $this, 'can_reply' ),
        ) );

        register_rest_route( $ns, '/tags', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_tags' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/hot-questions', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_hot_questions' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $ns, '/users/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_user' ),
            'permission_callback' => '__return_true',
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

    /* ------------------------------------------------------------------
     * Endpoint callbacks
     * ------------------------------------------------------------------ */

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
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') AS reply_count
             FROM {$wpdb->prefix}cnw_social_worker_threads t
             LEFT JOIN {$wpdb->users} u ON t.author_id = u.ID
             $where $order
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ) );

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads t $where" );
        // phpcs:enable

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

        return array( 'success' => true, 'id' => $wpdb->insert_id );
    }

    public function get_replies( WP_REST_Request $request ) {
        global $wpdb;

        $thread_id = intval( $request['id'] );

        $replies = $wpdb->get_results( $wpdb->prepare(
            "SELECT r.*, u.display_name AS author_name, u.ID AS author_id
             FROM {$wpdb->prefix}cnw_social_worker_replies r
             LEFT JOIN {$wpdb->users} u ON r.author_id = u.ID
             WHERE r.thread_id = %d AND r.status = 'approved'
             ORDER BY r.created_at ASC",
            $thread_id
        ) );

        return array( 'replies' => $replies );
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

    public function get_tags() {
        return array(
            array( 'id' => 1, 'name' => 'Emergency Housing',      'slug' => 'emergency-housing' ),
            array( 'id' => 2, 'name' => 'Mental Health Services',  'slug' => 'mental-health' ),
            array( 'id' => 3, 'name' => 'Youth & Family Support',  'slug' => 'youth-family' ),
            array( 'id' => 4, 'name' => 'Crisis Intervention',     'slug' => 'crisis-intervention' ),
            array( 'id' => 5, 'name' => 'Domestic Violence',       'slug' => 'domestic-violence' ),
            array( 'id' => 6, 'name' => 'Substance Use Services',  'slug' => 'substance-use' ),
            array( 'id' => 7, 'name' => 'Benefits & Eligibility',  'slug' => 'benefits' ),
            array( 'id' => 8, 'name' => 'Legal & Advocacy',        'slug' => 'legal' ),
        );
    }

    public function get_hot_questions() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT t.*, u.display_name AS author_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies r WHERE r.thread_id = t.id AND r.status = 'approved') AS reply_count
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
