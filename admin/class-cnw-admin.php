<?php
/**
 * Admin class — handles all WP admin menus, assets, CRUD action handlers, and page rendering.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/pagination-helpers.php';

class Cnw_Social_Bridge_Admin {

    public function __construct() {
        add_action( 'admin_menu',            array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // CRUD action handlers
        add_action( 'admin_post_cnw_save_thread',      array( $this, 'handle_save_thread' ) );
        add_action( 'admin_post_cnw_delete_thread',     array( $this, 'handle_delete_thread' ) );
        add_action( 'admin_post_cnw_save_reply',        array( $this, 'handle_save_reply' ) );
        add_action( 'admin_post_cnw_delete_reply',      array( $this, 'handle_delete_reply' ) );
        add_action( 'admin_post_cnw_save_message',      array( $this, 'handle_save_message' ) );
        add_action( 'admin_post_cnw_delete_message',    array( $this, 'handle_delete_message' ) );
        add_action( 'admin_post_cnw_save_tag',            array( $this, 'handle_save_tag' ) );
        add_action( 'admin_post_cnw_delete_tag',          array( $this, 'handle_delete_tag' ) );
        add_action( 'admin_post_cnw_save_category',     array( $this, 'handle_save_category' ) );
        add_action( 'admin_post_cnw_delete_category',   array( $this, 'handle_delete_category' ) );
        add_action( 'admin_post_cnw_save_vote',         array( $this, 'handle_save_vote' ) );
        add_action( 'admin_post_cnw_delete_vote',       array( $this, 'handle_delete_vote' ) );
        add_action( 'admin_post_cnw_save_reputation',   array( $this, 'handle_save_reputation' ) );
        add_action( 'admin_post_cnw_delete_reputation',  array( $this, 'handle_delete_reputation' ) );
        add_action( 'admin_post_cnw_save_logo',         array( $this, 'handle_save_logo' ) );

        // Bulk action handlers
        add_action( 'admin_post_cnw_bulk_threads',     array( $this, 'handle_bulk_threads' ) );
        add_action( 'admin_post_cnw_bulk_replies',     array( $this, 'handle_bulk_replies' ) );
        add_action( 'admin_post_cnw_bulk_messages',    array( $this, 'handle_bulk_messages' ) );
        add_action( 'admin_post_cnw_bulk_tags',         array( $this, 'handle_bulk_tags' ) );
        add_action( 'admin_post_cnw_bulk_categories',  array( $this, 'handle_bulk_categories' ) );
        add_action( 'admin_post_cnw_bulk_votes',       array( $this, 'handle_bulk_votes' ) );
        add_action( 'admin_post_cnw_bulk_reputation',  array( $this, 'handle_bulk_reputation' ) );
        add_action( 'admin_post_cnw_save_user',           array( $this, 'handle_save_user' ) );
        add_action( 'admin_post_cnw_delete_user',       array( $this, 'handle_delete_user' ) );
        add_action( 'admin_post_cnw_bulk_users',         array( $this, 'handle_bulk_users' ) );
    }

    /* ------------------------------------------------------------------
     * Admin menu registration
     * ------------------------------------------------------------------ */

    public function register_menus() {
        add_menu_page(
            __( 'Cnw Social Bridge', 'cnw-social-bridge' ),
            __( 'Cnw Social Bridge', 'cnw-social-bridge' ),
            'manage_options',
            'cnw-social-bridge',
            array( $this, 'page_dashboard' ),
            'dashicons-groups',
            30
        );

        $submenus = array(
            array( 'cnw-social-bridge', 'Dashboard',  'cnw-social-bridge', 'page_dashboard' ),
            array( 'cnw-social-bridge', 'Forum Users', 'cnw-users',         'page_users' ),
            array( 'cnw-social-bridge', 'Threads',     'cnw-threads',       'page_threads' ),
            array( 'cnw-social-bridge', 'Tags',        'cnw-tags',          'page_tags' ),
            array( 'cnw-social-bridge', 'Categories',  'cnw-categories',    'page_categories' ),
            array( 'cnw-social-bridge', 'Votes',       'cnw-votes',         'page_votes' ),
            array( 'cnw-social-bridge', 'Replies',     'cnw-replies',       'page_replies' ),
            array( 'cnw-social-bridge', 'Messages',    'cnw-messages',      'page_messages' ),
            array( 'cnw-social-bridge', 'Reputation',  'cnw-reputation',    'page_reputation' ),
            array( 'cnw-social-bridge', 'Settings',    'cnw-settings',      'page_settings' ),
        );

        foreach ( $submenus as $s ) {
            add_submenu_page( $s[0], __( $s[1], 'cnw-social-bridge' ), __( $s[1], 'cnw-social-bridge' ), 'manage_options', $s[2], array( $this, $s[3] ) );
        }
    }

    /* ------------------------------------------------------------------
     * Asset enqueueing
     * ------------------------------------------------------------------ */

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'cnw' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'cnw-admin-style',
            CNW_SOCIAL_BRIDGE_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            CNW_SOCIAL_BRIDGE_VERSION
        );

        // Inline JS for bulk select-all checkbox
        wp_add_inline_script( 'jquery', "
            jQuery(function($){
                $(document).on('change','.cnw-select-all',function(){
                    var checked = $(this).prop('checked');
                    $(this).closest('form').find('.cnw-bulk-cb').prop('checked', checked);
                });
                $(document).on('submit','.cnw-bulk-form',function(e){
                    var action = $(this).find('[name=bulk_action]').val();
                    if(!action){ e.preventDefault(); alert('Please select a bulk action.'); return; }
                    var checked = $(this).find('.cnw-bulk-cb:checked');
                    if(!checked.length){ e.preventDefault(); alert('Please select at least one item.'); return; }
                    if(action==='delete' && !confirm('Delete '+checked.length+' selected item(s)? This cannot be undone.')){ e.preventDefault(); }
                });
                $(document).on('click','#cnw-copy-shortcode',function(){
                    var code = $('#cnw-shortcode-value').text();
                    if(navigator.clipboard){
                        navigator.clipboard.writeText(code).then(function(){
                            var btn = $('#cnw-copy-shortcode');
                            btn.text('Copied!');
                            setTimeout(function(){ btn.html('<span class=\"dashicons dashicons-clipboard\"></span> Copy'); },2000);
                        });
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = code; document.body.appendChild(ta);
                        ta.select(); document.execCommand('copy');
                        document.body.removeChild(ta);
                        var btn = $('#cnw-copy-shortcode');
                        btn.text('Copied!');
                        setTimeout(function(){ btn.html('<span class=\"dashicons dashicons-clipboard\"></span> Copy'); },2000);
                    }
                });
            });
        " );

        // Select2 for searchable dropdowns on add/edit forms
        $page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        $act  = isset( $_GET['action'] ) ? $_GET['action'] : '';
        if ( strpos( $hook, 'cnw' ) !== false && in_array( $act, array( 'add', 'edit' ), true ) && $page !== 'cnw-settings' ) {
            wp_enqueue_style(
                'select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                array(),
                '4.1.0'
            );
            wp_enqueue_script(
                'select2',
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                array( 'jquery' ),
                '4.1.0',
                true
            );
            wp_add_inline_script( 'select2', "
                jQuery(function($){
                    var always = ['user_id','author_id','sender_id','recipient_id','thread_id','parent_id'];
                    $('.cnw-crud-form select').each(function(){
                        var id = $(this).attr('id') || '';
                        var opts = $(this).find('option').length;
                        if(opts > 8 || always.indexOf(id) !== -1){
                            $(this).select2({
                                width: '360px',
                                placeholder: $(this).find('option:first').text(),
                                allowClear: false,
                                minimumResultsForSearch: 0
                            });
                        }
                    });
                });
            " );
        }

        if ( strpos( $hook, 'cnw-settings' ) !== false ) {
            wp_enqueue_media();
            wp_enqueue_script(
                'cnw-admin-logo',
                CNW_SOCIAL_BRIDGE_PLUGIN_URL . 'admin/js/cnw-admin-logo.js',
                array( 'jquery' ),
                CNW_SOCIAL_BRIDGE_VERSION,
                true
            );
        }
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------ */

    private function verify_and_redirect( $nonce_action, $redirect_page, $extra_args = array() ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'cnw-social-bridge' ) );
        }
        check_admin_referer( $nonce_action );

        $args = array_merge( array( 'page' => $redirect_page ), $extra_args );
        wp_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * THREAD handlers
     * ------------------------------------------------------------------ */

    public function handle_save_thread() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_thread' );

        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_threads';
        $id    = intval( $_POST['id'] ?? 0 );

        $data = array(
            'title'     => sanitize_text_field( $_POST['title'] ?? '' ),
            'content'   => wp_kses_post( $_POST['content'] ?? '' ),
            'status'    => sanitize_text_field( $_POST['status'] ?? 'published' ),
            'author_id' => intval( $_POST['author_id'] ?? get_current_user_id() ),
        );

        if ( $id ) {
            $wpdb->update( $table, $data, array( 'id' => $id ) );
        } else {
            $wpdb->insert( $table, $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-threads', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_thread() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_thread' );

        global $wpdb;
        $id = intval( $_GET['id'] ?? 0 );
        if ( $id ) {
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_replies', array( 'thread_id' => $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'thread' AND target_id = %d", $id ) );
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_threads', array( 'id' => $id ) );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-threads', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * REPLY handlers
     * ------------------------------------------------------------------ */

    public function handle_save_reply() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_reply' );

        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_replies';
        $id    = intval( $_POST['id'] ?? 0 );

        $data = array(
            'thread_id' => intval( $_POST['thread_id'] ?? 0 ),
            'author_id' => intval( $_POST['author_id'] ?? get_current_user_id() ),
            'parent_id' => intval( $_POST['parent_id'] ?? 0 ) ?: null,
            'content'   => wp_kses_post( $_POST['content'] ?? '' ),
            'status'    => sanitize_text_field( $_POST['status'] ?? 'approved' ),
        );

        if ( $id ) {
            $wpdb->update( $table, $data, array( 'id' => $id ) );
        } else {
            $wpdb->insert( $table, $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-replies', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_reply() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_reply' );

        global $wpdb;
        $id = intval( $_GET['id'] ?? 0 );
        if ( $id ) {
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_replies', array( 'parent_id' => $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'reply' AND target_id = %d", $id ) );
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_replies', array( 'id' => $id ) );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-replies', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * MESSAGE handlers
     * ------------------------------------------------------------------ */

    public function handle_save_message() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_message' );

        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_messages';
        $id    = intval( $_POST['id'] ?? 0 );

        $data = array(
            'sender_id'    => intval( $_POST['sender_id'] ?? get_current_user_id() ),
            'recipient_id' => intval( $_POST['recipient_id'] ?? 0 ),
            'subject'      => sanitize_text_field( $_POST['subject'] ?? '' ),
            'content'      => wp_kses_post( $_POST['content'] ?? '' ),
            'is_read'      => intval( $_POST['is_read'] ?? 0 ),
            'parent_id'    => intval( $_POST['parent_id'] ?? 0 ) ?: null,
        );

        if ( $id ) {
            $wpdb->update( $table, $data, array( 'id' => $id ) );
        } else {
            $wpdb->insert( $table, $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-messages', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_message() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_message' );

        global $wpdb;
        $id = intval( $_GET['id'] ?? 0 );
        if ( $id ) {
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_messages', array( 'id' => $id ) );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-messages', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * TAG handlers
     * ------------------------------------------------------------------ */

    public function handle_save_tag() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_tag' );

        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_tags';
        $id    = intval( $_POST['id'] ?? 0 );

        $name = sanitize_text_field( $_POST['name'] ?? '' );
        $data = array(
            'name'        => $name,
            'slug'        => sanitize_title( $_POST['slug'] ?? '' ) ?: sanitize_title( $name ),
            'description' => sanitize_textarea_field( $_POST['description'] ?? '' ) ?: null,
        );

        if ( $id ) {
            $wpdb->update( $table, $data, array( 'id' => $id ) );
        } else {
            $data['created_by'] = get_current_user_id();
            $wpdb->insert( $table, $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-tags', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_tag() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_tag' );

        global $wpdb;
        $id = intval( $_GET['id'] ?? 0 );
        if ( $id ) {
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_thread_tags', array( 'tag_id' => $id ) );
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_user_followed_tags', array( 'tag_id' => $id ) );
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_tags', array( 'id' => $id ) );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-tags', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * CATEGORY handlers
     * ------------------------------------------------------------------ */

    public function handle_save_category() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_category' );

        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_categories';
        $id    = intval( $_POST['id'] ?? 0 );

        $name = sanitize_text_field( $_POST['name'] ?? '' );
        $data = array(
            'name'        => $name,
            'slug'        => sanitize_title( $_POST['slug'] ?? '' ) ?: sanitize_title( $name ),
            'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
            'parent_id'   => intval( $_POST['parent_id'] ?? 0 ) ?: null,
            'icon'        => sanitize_text_field( $_POST['icon'] ?? '' ),
            'color'       => sanitize_hex_color( $_POST['color'] ?? '' ) ?: null,
            'sort_order'  => intval( $_POST['sort_order'] ?? 0 ),
            'is_active'   => intval( $_POST['is_active'] ?? 1 ),
        );

        if ( $id ) {
            $wpdb->update( $table, $data, array( 'id' => $id ) );
        } else {
            $data['created_by'] = get_current_user_id();
            $wpdb->insert( $table, $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-categories', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_category() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_category' );

        global $wpdb;
        $id = intval( $_GET['id'] ?? 0 );
        if ( $id ) {
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_categories', array( 'id' => $id ) );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-categories', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * VOTE handlers
     * ------------------------------------------------------------------ */

    public function handle_save_vote() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_vote' );

        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_votes';
        $id    = intval( $_POST['id'] ?? 0 );

        $data = array(
            'user_id'     => intval( $_POST['user_id'] ?? get_current_user_id() ),
            'target_type' => sanitize_text_field( $_POST['target_type'] ?? 'thread' ),
            'target_id'   => intval( $_POST['target_id'] ?? 0 ),
            'vote_type'   => intval( $_POST['vote_type'] ?? 1 ),
        );

        if ( $id ) {
            $wpdb->update( $table, $data, array( 'id' => $id ) );
        } else {
            $wpdb->insert( $table, $data );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-votes', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_vote() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_vote' );

        global $wpdb;
        $id = intval( $_GET['id'] ?? 0 );
        if ( $id ) {
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_votes', array( 'id' => $id ) );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-votes', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * REPUTATION handlers
     * ------------------------------------------------------------------ */

    public function handle_save_reputation() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_reputation' );

        global $wpdb;
        $table = $wpdb->prefix . 'cnw_social_worker_reputation';
        $id    = intval( $_POST['id'] ?? 0 );

        $data = array(
            'user_id'        => intval( $_POST['user_id'] ?? 0 ),
            'points'         => intval( $_POST['points'] ?? 0 ),
            'action_type'    => sanitize_text_field( $_POST['action_type'] ?? '' ),
            'reference_type' => sanitize_text_field( $_POST['reference_type'] ?? '' ) ?: null,
            'reference_id'   => intval( $_POST['reference_id'] ?? 0 ) ?: null,
            'description'    => sanitize_text_field( $_POST['description'] ?? '' ),
        );

        $user_id_for_recalc = intval( $data['user_id'] );

        if ( $id ) {
            // If editing, we may also need to recalc the old user if user_id changed
            $old_user_id = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT user_id FROM $table WHERE id = %d", $id
            ) );
            $wpdb->update( $table, $data, array( 'id' => $id ) );
            if ( $old_user_id && $old_user_id !== $user_id_for_recalc ) {
                $this->recalc_reputation_total( $old_user_id );
            }
        } else {
            $wpdb->insert( $table, $data );
        }

        $this->recalc_reputation_total( $user_id_for_recalc );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-reputation', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_reputation() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_reputation' );

        global $wpdb;
        $id = intval( $_GET['id'] ?? 0 );
        if ( $id ) {
            // Get user_id before deleting so we can recalculate
            $user_id = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE id = %d", $id
            ) );
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_reputation', array( 'id' => $id ) );
            if ( $user_id ) {
                $this->recalc_reputation_total( $user_id );
            }
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-reputation', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    private function recalc_reputation_total( $user_id ) {
        global $wpdb;
        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE(SUM(points), 0) FROM {$wpdb->prefix}cnw_social_worker_reputation WHERE user_id = %d",
            (int) $user_id
        ) );
        update_user_meta( (int) $user_id, 'cnw_reputation_total', $total );
    }

    /* ------------------------------------------------------------------
     * BULK ACTION handlers
     * ------------------------------------------------------------------ */

    private function bulk_delete( $nonce_action, $redirect_page, $table_suffix, $cleanup_cb = null ) {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( $nonce_action );

        $bulk = sanitize_text_field( $_POST['bulk_action'] ?? '' );
        $ids  = array_map( 'intval', (array) ( $_POST['bulk_ids'] ?? array() ) );

        if ( $bulk === 'delete' && ! empty( $ids ) ) {
            global $wpdb;
            $table = $wpdb->prefix . 'cnw_social_worker_' . $table_suffix;

            if ( $cleanup_cb ) {
                call_user_func( $cleanup_cb, $wpdb, $ids );
            }

            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($placeholders)", ...$ids ) );
        }

        $count = count( $ids );
        wp_redirect( add_query_arg( array( 'page' => $redirect_page, 'msg' => 'bulk_deleted', 'count' => $count ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_bulk_threads() {
        $this->bulk_delete( 'cnw_bulk_threads', 'cnw-threads', 'threads', function( $wpdb, $ids ) {
            $ph = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_replies WHERE thread_id IN ($ph)", ...$ids ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'thread' AND target_id IN ($ph)", ...$ids ) );
        } );
    }

    public function handle_bulk_replies() {
        $this->bulk_delete( 'cnw_bulk_replies', 'cnw-replies', 'replies', function( $wpdb, $ids ) {
            $ph = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_replies WHERE parent_id IN ($ph)", ...$ids ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'reply' AND target_id IN ($ph)", ...$ids ) );
        } );
    }

    public function handle_bulk_messages() {
        $this->bulk_delete( 'cnw_bulk_messages', 'cnw-messages', 'messages' );
    }

    public function handle_bulk_tags() {
        $this->bulk_delete( 'cnw_bulk_tags', 'cnw-tags', 'tags', function( $wpdb, $ids ) {
            $ph = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_thread_tags WHERE tag_id IN ($ph)", ...$ids ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_user_followed_tags WHERE tag_id IN ($ph)", ...$ids ) );
        } );
    }

    public function handle_bulk_categories() {
        $this->bulk_delete( 'cnw_bulk_categories', 'cnw-categories', 'categories' );
    }

    public function handle_bulk_votes() {
        $this->bulk_delete( 'cnw_bulk_votes', 'cnw-votes', 'votes' );
    }

    public function handle_bulk_reputation() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_bulk_reputation' );

        $bulk = sanitize_text_field( $_POST['bulk_action'] ?? '' );
        $ids  = array_map( 'intval', (array) ( $_POST['bulk_ids'] ?? array() ) );

        if ( $bulk === 'delete' && ! empty( $ids ) ) {
            global $wpdb;
            $table = $wpdb->prefix . 'cnw_social_worker_reputation';
            $ph    = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

            // Collect affected user IDs before deleting
            $affected_users = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT user_id FROM $table WHERE id IN ($ph)", ...$ids
            ) );

            $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($ph)", ...$ids ) );

            // Recalculate totals for affected users
            foreach ( $affected_users as $uid ) {
                $this->recalc_reputation_total( (int) $uid );
            }
        }

        $count = count( $ids );
        wp_redirect( add_query_arg( array( 'page' => 'cnw-reputation', 'msg' => 'bulk_deleted', 'count' => $count ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * USER handlers
     * ------------------------------------------------------------------ */

    public function handle_save_user() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_user' );

        $id = intval( $_POST['id'] ?? 0 );
        if ( ! $id ) wp_die( 'Invalid user.' );

        $userdata = array(
            'ID'           => $id,
            'display_name' => sanitize_text_field( $_POST['display_name'] ?? '' ),
            'user_email'   => sanitize_email( $_POST['user_email'] ?? '' ),
            'first_name'   => sanitize_text_field( $_POST['first_name'] ?? '' ),
            'last_name'    => sanitize_text_field( $_POST['last_name'] ?? '' ),
        );

        $role = sanitize_text_field( $_POST['role'] ?? '' );
        if ( $role ) {
            $userdata['role'] = $role;
        }

        wp_update_user( $userdata );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    private function delete_user_forum_data( $user_id ) {
        global $wpdb;
        $uid = (int) $user_id;
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_votes',      array( 'user_id' => $uid ) );
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_replies',    array( 'author_id' => $uid ) );
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_threads',    array( 'author_id' => $uid ) );
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_messages',   array( 'sender_id' => $uid ) );
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_messages',   array( 'recipient_id' => $uid ) );
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_reputation', array( 'user_id' => $uid ) );
        $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_user_followed_tags', array( 'user_id' => $uid ) );
        delete_user_meta( $uid, 'cnw_reputation_total' );
    }

    public function handle_delete_user() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_user' );

        $id = intval( $_GET['id'] ?? 0 );
        if ( $id && $id !== get_current_user_id() ) {
            $this->delete_user_forum_data( $id );
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user( $id );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_bulk_users() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_bulk_users' );

        $bulk = sanitize_text_field( $_POST['bulk_action'] ?? '' );
        $ids  = array_map( 'intval', (array) ( $_POST['bulk_ids'] ?? array() ) );
        $current = get_current_user_id();

        if ( $bulk === 'delete' && ! empty( $ids ) ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            $ids = array_filter( $ids, function( $id ) use ( $current ) { return $id !== $current; } );
            foreach ( $ids as $uid ) {
                $this->delete_user_forum_data( $uid );
                wp_delete_user( $uid );
            }
        }

        $count = count( $ids );
        wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'msg' => 'bulk_deleted', 'count' => $count ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * LOGO handler (Settings page)
     * ------------------------------------------------------------------ */

    public function handle_save_logo() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_logo' );

        $logo_url = isset( $_POST['cnw_logo_url'] ) ? esc_url_raw( $_POST['cnw_logo_url'] ) : '';
        update_option( 'cnw_social_logo_url', $logo_url );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-settings', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * Page renderers
     * ------------------------------------------------------------------ */

    public function page_dashboard()   { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-dashboard.php'; }
    public function page_threads()     { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-threads.php'; }
    public function page_replies()     { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-replies.php'; }
    public function page_messages()    { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-messages.php'; }
    public function page_tags()         { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-tags.php'; }
    public function page_categories()  { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-categories.php'; }
    public function page_votes()       { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-votes.php'; }
    public function page_reputation()  { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-reputation.php'; }
    public function page_users()       { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-users.php'; }
    public function page_settings()    { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-settings.php'; }
}
