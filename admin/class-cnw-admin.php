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
        add_action( 'admin_post_cnw_save_pusher',           array( $this, 'handle_save_pusher' ) );
        add_action( 'admin_post_cnw_save_thread_settings', array( $this, 'handle_save_thread_settings' ) );
        add_action( 'admin_post_cnw_save_reply_settings',  array( $this, 'handle_save_reply_settings' ) );
        add_action( 'admin_post_cnw_save_guidelines',   array( $this, 'handle_save_guidelines' ) );
        add_action( 'admin_post_cnw_update_report',     array( $this, 'handle_update_report' ) );
        add_action( 'admin_post_cnw_delete_report',     array( $this, 'handle_delete_report' ) );
        add_action( 'admin_post_cnw_bulk_reports',      array( $this, 'handle_bulk_reports' ) );

        // Bulk action handlers
        add_action( 'admin_post_cnw_bulk_threads',          array( $this, 'handle_bulk_threads' ) );
        add_action( 'admin_post_cnw_quick_status_thread', array( $this, 'handle_quick_status_thread' ) );
        add_action( 'admin_post_cnw_bulk_replies',          array( $this, 'handle_bulk_replies' ) );
        add_action( 'admin_post_cnw_quick_status_reply',  array( $this, 'handle_quick_status_reply' ) );
        add_action( 'admin_post_cnw_bulk_messages',    array( $this, 'handle_bulk_messages' ) );
        add_action( 'admin_post_cnw_bulk_tags',         array( $this, 'handle_bulk_tags' ) );
        add_action( 'admin_post_cnw_bulk_categories',  array( $this, 'handle_bulk_categories' ) );
        add_action( 'admin_post_cnw_bulk_votes',       array( $this, 'handle_bulk_votes' ) );
        add_action( 'admin_post_cnw_bulk_reputation',  array( $this, 'handle_bulk_reputation' ) );
        add_action( 'admin_post_cnw_create_user',          array( $this, 'handle_create_user' ) );
        add_action( 'admin_post_cnw_save_user',           array( $this, 'handle_save_user' ) );
        add_action( 'admin_post_cnw_delete_user',       array( $this, 'handle_delete_user' ) );
        add_action( 'admin_post_cnw_bulk_users',         array( $this, 'handle_bulk_users' ) );
        add_action( 'admin_post_cnw_send_password_reset', array( $this, 'handle_send_password_reset' ) );
        add_action( 'admin_post_cnw_export_data',       array( $this, 'handle_export_data' ) );
        add_action( 'admin_post_cnw_import_data',       array( $this, 'handle_import_upload' ) );
        add_action( 'wp_ajax_cnw_import_step',           array( $this, 'ajax_import_step' ) );
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

        // Count open reports for menu badge
        global $wpdb;
        $open_reports = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_reports WHERE status = 'open'"
        );
        $reports_label = 'Reports';
        if ( $open_reports > 0 ) {
            $reports_label = sprintf( 'Reports <span class="awaiting-mod">%d</span>', $open_reports );
        }

        $pending_threads = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_threads WHERE status = 'pending'"
        );
        $threads_label = 'Threads';
        if ( $pending_threads > 0 ) {
            $threads_label = sprintf( 'Threads <span class="awaiting-mod">%d</span>', $pending_threads );
        }

        $pending_replies = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cnw_social_worker_replies WHERE status = 'pending'"
        );
        $replies_label = 'Replies';
        if ( $pending_replies > 0 ) {
            $replies_label = sprintf( 'Replies <span class="awaiting-mod">%d</span>', $pending_replies );
        }

        $submenus = array(
            array( 'cnw-social-bridge', 'Dashboard',  'cnw-social-bridge', 'page_dashboard' ),
            array( 'cnw-social-bridge', 'Forum Users', 'cnw-users',         'page_users' ),
            array( 'cnw-social-bridge', $threads_label, 'cnw-threads',       'page_threads' ),
            array( 'cnw-social-bridge', 'Tags',        'cnw-tags',          'page_tags' ),
            array( 'cnw-social-bridge', 'Categories',  'cnw-categories',    'page_categories' ),
            array( 'cnw-social-bridge', 'Votes',       'cnw-votes',         'page_votes' ),
            array( 'cnw-social-bridge', $replies_label, 'cnw-replies',       'page_replies' ),
            array( 'cnw-social-bridge', 'Messages',    'cnw-messages',      'page_messages' ),
            array( 'cnw-social-bridge', 'Reputation',  'cnw-reputation',    'page_reputation' ),
            array( 'cnw-social-bridge', 'Guidelines',  'cnw-guidelines',    'page_guidelines' ),
            array( 'cnw-social-bridge', $reports_label, 'cnw-reports',       'page_reports' ),
            array( 'cnw-social-bridge', 'Import / Export', 'cnw-import-export', 'page_import_export' ),
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

        // Avatar picker on user edit page
        $page = isset( $_GET['page'] ) ? $_GET['page'] : '';
        $act  = isset( $_GET['action'] ) ? $_GET['action'] : '';
        if ( $page === 'cnw-users' && $act === 'edit' ) {
            wp_enqueue_media();
            wp_add_inline_script( 'jquery', "
                jQuery(function($){
                    var frame;
                    $('#cnw-avatar-upload').on('click',function(e){
                        e.preventDefault();
                        if(frame){frame.open();return;}
                        frame=wp.media({title:'Choose Profile Photo',button:{text:'Use as Profile Photo'},multiple:false,library:{type:'image'}});
                        frame.on('select',function(){
                            var url=frame.state().get('selection').first().toJSON().url;
                            $('#cnw_avatar_url').val(url);
                            $('#cnw-avatar-preview').attr('src',url);
                            $('#cnw-avatar-remove').show();
                        });
                        frame.open();
                    });
                    $('#cnw-avatar-remove').on('click',function(e){
                        e.preventDefault();
                        $('#cnw_avatar_url').val('');
                        $('#cnw-avatar-preview').attr('src','" . esc_url( CNW_SOCIAL_BRIDGE_DEFAULT_AVATAR ) . "');
                        $(this).hide();
                    });
                });
            " );
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
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_bulk_threads' );

        global $wpdb;
        $bulk  = sanitize_text_field( $_POST['bulk_action'] ?? '' );
        $ids   = array_map( 'intval', (array) ( $_POST['bulk_ids'] ?? array() ) );
        $table = $wpdb->prefix . 'cnw_social_worker_threads';
        $count = count( $ids );
        $msg   = 'bulk_updated';

        if ( ! empty( $ids ) ) {
            $ph = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

            if ( in_array( $bulk, array( 'approve', 'reject', 'pending' ), true ) ) {
                $status_map = array( 'approve' => 'approved', 'reject' => 'rejected', 'pending' => 'pending' );
                $new_status = $status_map[ $bulk ];
                $wpdb->query( $wpdb->prepare( "UPDATE $table SET status = %s WHERE id IN ($ph)", $new_status, ...$ids ) );
            } elseif ( $bulk === 'delete' ) {
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_replies WHERE thread_id IN ($ph)", ...$ids ) );
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'thread' AND target_id IN ($ph)", ...$ids ) );
                $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($ph)", ...$ids ) );
                $msg = 'bulk_deleted';
            }
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-threads', 'msg' => $msg, 'count' => $count ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_quick_status_thread() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_quick_status_thread' );

        global $wpdb;
        $id     = intval( $_GET['id'] ?? 0 );
        $status = sanitize_text_field( $_GET['status'] ?? '' );

        if ( $id && in_array( $status, array( 'approved', 'rejected', 'pending' ), true ) ) {
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_threads',
                array( 'status' => $status ),
                array( 'id' => $id ),
                array( '%s' ),
                array( '%d' )
            );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-threads', 'msg' => 'status_updated' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_bulk_replies() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_bulk_replies' );

        global $wpdb;
        $bulk  = sanitize_text_field( $_POST['bulk_action'] ?? '' );
        $ids   = array_map( 'intval', (array) ( $_POST['bulk_ids'] ?? array() ) );
        $table = $wpdb->prefix . 'cnw_social_worker_replies';
        $count = count( $ids );
        $msg   = 'bulk_updated';

        if ( ! empty( $ids ) ) {
            $ph = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

            if ( in_array( $bulk, array( 'approve', 'reject', 'pending', 'spam' ), true ) ) {
                $status_map = array( 'approve' => 'approved', 'reject' => 'rejected', 'pending' => 'pending', 'spam' => 'spam' );
                $new_status = $status_map[ $bulk ];
                $wpdb->query( $wpdb->prepare( "UPDATE $table SET status = %s WHERE id IN ($ph)", $new_status, ...$ids ) );
            } elseif ( $bulk === 'delete' ) {
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_replies WHERE parent_id IN ($ph)", ...$ids ) );
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}cnw_social_worker_votes WHERE target_type = 'reply' AND target_id IN ($ph)", ...$ids ) );
                $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($ph)", ...$ids ) );
                $msg = 'bulk_deleted';
            }
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-replies', 'msg' => $msg, 'count' => $count ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_quick_status_reply() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_quick_status_reply' );

        global $wpdb;
        $id     = intval( $_GET['id'] ?? 0 );
        $status = sanitize_text_field( $_GET['status'] ?? '' );

        if ( $id && in_array( $status, array( 'approved', 'rejected', 'pending', 'spam' ), true ) ) {
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_replies',
                array( 'status' => $status ),
                array( 'id' => $id ),
                array( '%s' ),
                array( '%d' )
            );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-replies', 'msg' => 'status_updated' ), admin_url( 'admin.php' ) ) );
        exit;
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

    public function handle_create_user() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_create_user' );

        $user_login   = sanitize_user( $_POST['user_login'] ?? '' );
        $user_email   = sanitize_email( $_POST['user_email'] ?? '' );
        $display_name = sanitize_text_field( $_POST['display_name'] ?? '' );
        $first_name   = sanitize_text_field( $_POST['first_name'] ?? '' );
        $last_name    = sanitize_text_field( $_POST['last_name'] ?? '' );
        $password     = $_POST['user_pass'] ?? '';
        $password_confirm = $_POST['user_pass_confirm'] ?? '';
        $role         = sanitize_text_field( $_POST['role'] ?? 'cnw_forum_member' );

        if ( $password !== $password_confirm ) {
            wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'action' => 'add', 'msg' => 'error_password' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        $user_id = wp_insert_user( array(
            'user_login'   => $user_login,
            'user_email'   => $user_email,
            'user_pass'    => $password,
            'display_name' => $display_name,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'role'         => $role,
        ) );

        if ( is_wp_error( $user_id ) ) {
            wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'action' => 'add', 'msg' => 'error_create' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // Save CNW meta fields
        $phone = sanitize_text_field( $_POST['cnw_phone'] ?? '' );
        if ( $phone ) update_user_meta( $user_id, 'cnw_phone', $phone );

        $verified_label = sanitize_text_field( $_POST['cnw_verified_label'] ?? '' );
        if ( $verified_label ) update_user_meta( $user_id, 'cnw_verified_label', $verified_label );

        $professional_title = sanitize_text_field( $_POST['cnw_professional_title'] ?? '' );
        if ( $professional_title ) update_user_meta( $user_id, 'cnw_professional_title', $professional_title );

        $avatar_url = esc_url_raw( $_POST['cnw_avatar_url'] ?? '' );
        if ( $avatar_url ) update_user_meta( $user_id, 'cnw_avatar_url', $avatar_url );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'action' => 'edit', 'id' => $user_id, 'msg' => 'created' ), admin_url( 'admin.php' ) ) );
        exit;
    }

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

        // Save phone
        $phone = sanitize_text_field( $_POST['cnw_phone'] ?? '' );
        update_user_meta( $id, 'cnw_phone', $phone );

        // Save verified label & professional title
        $verified_label = sanitize_text_field( $_POST['cnw_verified_label'] ?? '' );
        update_user_meta( $id, 'cnw_verified_label', $verified_label );
        $professional_title = sanitize_text_field( $_POST['cnw_professional_title'] ?? '' );
        update_user_meta( $id, 'cnw_professional_title', $professional_title );

        // Save custom avatar
        $avatar_url = esc_url_raw( $_POST['cnw_avatar_url'] ?? '' );
        if ( $avatar_url ) {
            update_user_meta( $id, 'cnw_avatar_url', $avatar_url );
        } else {
            delete_user_meta( $id, 'cnw_avatar_url' );
        }

        // Password reset
        $new_password = $_POST['new_password'] ?? '';
        $new_password_confirm = $_POST['new_password_confirm'] ?? '';
        $redirect_msg = 'saved';

        if ( ! empty( $new_password ) ) {
            if ( $new_password !== $new_password_confirm ) {
                wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'action' => 'edit', 'id' => $id, 'msg' => 'error_password' ), admin_url( 'admin.php' ) ) );
                exit;
            }
            wp_set_password( $new_password, $id );
            $redirect_msg = 'password_updated';
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'action' => 'edit', 'id' => $id, 'msg' => $redirect_msg ), admin_url( 'admin.php' ) ) );
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

    public function handle_send_password_reset() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_send_password_reset' );

        $id   = intval( $_GET['id'] ?? 0 );
        $user = get_userdata( $id );
        $msg  = 'reset_failed';

        if ( $user ) {
            $result = retrieve_password( $user->user_login );
            if ( ! is_wp_error( $result ) ) {
                $msg = 'reset_sent';
            }
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'action' => 'edit', 'id' => $id, 'msg' => $msg ), admin_url( 'admin.php' ) ) );
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

        $mobile_logo_url = isset( $_POST['cnw_mobile_logo_url'] ) ? esc_url_raw( $_POST['cnw_mobile_logo_url'] ) : '';
        update_option( 'cnw_social_mobile_logo_url', $mobile_logo_url );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-settings', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * PUSHER handler
     * ------------------------------------------------------------------ */

    public function handle_save_pusher() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_pusher' );

        update_option( 'cnw_pusher_app_id',  sanitize_text_field( $_POST['cnw_pusher_app_id']  ?? '' ) );
        update_option( 'cnw_pusher_key',     sanitize_text_field( $_POST['cnw_pusher_key']     ?? '' ) );
        update_option( 'cnw_pusher_secret',  sanitize_text_field( $_POST['cnw_pusher_secret']  ?? '' ) );
        update_option( 'cnw_pusher_host',    sanitize_text_field( $_POST['cnw_pusher_host']    ?? '' ) );
        update_option( 'cnw_pusher_port',    sanitize_text_field( $_POST['cnw_pusher_port']    ?? '443' ) );
        update_option( 'cnw_pusher_cluster', sanitize_text_field( $_POST['cnw_pusher_cluster'] ?? 'mt1' ) );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-settings', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_save_thread_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_thread_settings' );

        $status = sanitize_text_field( $_POST['cnw_default_thread_status'] ?? 'pending' );
        if ( ! in_array( $status, array( 'pending', 'approved', 'rejected' ), true ) ) {
            $status = 'pending';
        }
        update_option( 'cnw_default_thread_status', $status );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-settings', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_save_reply_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_reply_settings' );

        $status = sanitize_text_field( $_POST['cnw_default_reply_status'] ?? 'approved' );
        if ( ! in_array( $status, array( 'pending', 'approved', 'rejected' ), true ) ) {
            $status = 'approved';
        }
        update_option( 'cnw_default_reply_status', $status );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-settings', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * GUIDELINES handler
     * ------------------------------------------------------------------ */

    public function handle_save_guidelines() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_save_guidelines' );

        $content = wp_kses_post( $_POST['cnw_guidelines_content'] ?? '' );
        update_option( 'cnw_community_guidelines_html', $content );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-guidelines', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * REPORTS handlers
     * ------------------------------------------------------------------ */

    public function handle_update_report() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_update_report' );

        global $wpdb;
        $id     = intval( $_POST['id'] ?? 0 );
        $status = sanitize_text_field( $_POST['status'] ?? 'open' );
        $notes  = wp_kses_post( $_POST['admin_notes'] ?? '' );

        if ( $id ) {
            $wpdb->update(
                $wpdb->prefix . 'cnw_social_worker_reports',
                array( 'status' => $status, 'admin_notes' => $notes, 'updated_at' => current_time( 'mysql' ) ),
                array( 'id' => $id )
            );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-reports', 'msg' => 'updated' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_delete_report() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_delete_report' );

        global $wpdb;
        $id = intval( $_POST['id'] ?? 0 );
        if ( $id ) {
            $wpdb->delete( $wpdb->prefix . 'cnw_social_worker_reports', array( 'id' => $id ) );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-reports', 'msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function handle_bulk_reports() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_bulk_reports' );

        global $wpdb;
        $action = sanitize_text_field( $_POST['bulk_action'] ?? '' );
        $ids    = array_map( 'intval', (array) ( $_POST['ids'] ?? array() ) );
        $count  = 0;
        $table  = $wpdb->prefix . 'cnw_social_worker_reports';

        if ( ! empty( $ids ) ) {
            if ( $action === 'delete' ) {
                foreach ( $ids as $id ) {
                    $wpdb->delete( $table, array( 'id' => $id ) );
                    $count++;
                }
            } elseif ( in_array( $action, array( 'mark_open', 'mark_in_progress', 'mark_resolved', 'mark_closed' ), true ) ) {
                $status = str_replace( 'mark_', '', $action );
                foreach ( $ids as $id ) {
                    $wpdb->update( $table, array( 'status' => $status, 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $id ) );
                    $count++;
                }
            }
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-reports', 'msg' => 'bulk_done', 'count' => $count ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * EXPORT / IMPORT handlers
     * ------------------------------------------------------------------ */

    public function handle_export_data() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_export_data' );

        global $wpdb;
        $p = $wpdb->prefix . 'cnw_social_worker_';

        // All plugin tables.
        $tables = array(
            'threads', 'replies', 'messages', 'categories', 'tags',
            'thread_tags', 'user_followed_tags', 'votes', 'reputation',
            'saved_threads', 'notifications', 'activity', 'reports', 'connections',
            'restrictions', 'warnings',
        );

        $table_data = array();
        foreach ( $tables as $t ) {
            $full = $p . $t;
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$full'" ) === $full ) {
                $table_data[ $t ] = $wpdb->get_results( "SELECT * FROM `$full`", ARRAY_A );
            } else {
                $table_data[ $t ] = array();
            }
        }

        // Collect referenced WP user IDs.
        $user_ids = array();
        foreach ( $table_data['threads'] as $r )               { $user_ids[ (int) $r['author_id'] ] = true; }
        foreach ( $table_data['replies'] as $r )                { $user_ids[ (int) $r['author_id'] ] = true; }
        foreach ( $table_data['messages'] as $r )               { $user_ids[ (int) $r['sender_id'] ] = true; $user_ids[ (int) $r['recipient_id'] ] = true; }
        foreach ( $table_data['votes'] as $r )                  { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $table_data['reputation'] as $r )             { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $table_data['activity'] as $r )               { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $table_data['tags'] as $r )                   { if ( ! empty( $r['created_by'] ) ) $user_ids[ (int) $r['created_by'] ] = true; }
        foreach ( $table_data['categories'] as $r )             { if ( ! empty( $r['created_by'] ) ) $user_ids[ (int) $r['created_by'] ] = true; }
        foreach ( $table_data['user_followed_tags'] as $r )     { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $table_data['saved_threads'] as $r )          { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $table_data['notifications'] as $r )          { $user_ids[ (int) $r['user_id'] ] = true; if ( ! empty( $r['actor_id'] ) ) $user_ids[ (int) $r['actor_id'] ] = true; }
        foreach ( $table_data['reports'] as $r )               { if ( ! empty( $r['user_id'] ) ) $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $table_data['connections'] as $r )           { $user_ids[ (int) $r['sender_id'] ] = true; $user_ids[ (int) $r['receiver_id'] ] = true; }
        foreach ( $table_data['restrictions'] as $r )          { $user_ids[ (int) $r['restricter_id'] ] = true; $user_ids[ (int) $r['restricted_id'] ] = true; }
        foreach ( $table_data['warnings'] as $r )              { $user_ids[ (int) $r['user_id'] ] = true; $user_ids[ (int) $r['moderator_id'] ] = true; }
        unset( $user_ids[0] );

        // Build users array with all CNW meta.
        $users = array();
        $cnw_meta_keys = array( 'cnw_phone', 'cnw_avatar_url', 'cnw_avatar_attachment_id', 'cnw_verified_label', 'cnw_professional_title', 'cnw_anonymous', 'cnw_reputation_total', 'description', 'cnw_suspended', 'cnw_suspended_until' );
        foreach ( array_keys( $user_ids ) as $uid ) {
            $u = get_userdata( $uid );
            if ( ! $u ) continue;
            $user_entry = array(
                'id'           => $uid,
                'user_login'   => $u->user_login,
                'user_email'   => $u->user_email,
                'user_pass'    => $u->user_pass,
                'display_name' => $u->display_name,
                'first_name'   => get_user_meta( $uid, 'first_name', true ),
                'last_name'    => get_user_meta( $uid, 'last_name', true ),
                'roles'        => $u->roles,
            );
            foreach ( $cnw_meta_keys as $mk ) {
                $user_entry[ $mk ] = get_user_meta( $uid, $mk, true );
            }
            $users[] = $user_entry;
        }

        // Plugin settings.
        $settings = array(
            'cnw_social_bridge_version'        => get_option( 'cnw_social_bridge_version', '' ),
            'cnw_pusher_host'                  => get_option( 'cnw_pusher_host', '' ),
            'cnw_pusher_port'                  => get_option( 'cnw_pusher_port', '443' ),
            'cnw_pusher_cluster'               => get_option( 'cnw_pusher_cluster', 'mt1' ),
            'cnw_pusher_app_id'                => get_option( 'cnw_pusher_app_id', '' ),
            'cnw_pusher_key'                   => get_option( 'cnw_pusher_key', '' ),
            'cnw_pusher_secret'                => get_option( 'cnw_pusher_secret', '' ),
            'cnw_community_guidelines'         => get_option( 'cnw_community_guidelines', '' ),
            'cnw_community_guidelines_html'    => get_option( 'cnw_community_guidelines_html', '' ),
            'cnw_default_thread_status'        => get_option( 'cnw_default_thread_status', 'pending' ),
            'cnw_default_reply_status'         => get_option( 'cnw_default_reply_status', 'approved' ),
            'cnw_social_logo_url'              => get_option( 'cnw_social_logo_url', '' ),
        );

        // Build ZIP.
        $tmp_file = wp_tempnam( 'cnw-export' ) . '.zip';
        $zip = new ZipArchive();
        if ( $zip->open( $tmp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
            wp_die( 'Could not create ZIP file.' );
        }

        $json_flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        $zip->addFromString( 'meta.json', wp_json_encode( array(
            'plugin_version' => CNW_SOCIAL_BRIDGE_VERSION,
            'exported_at'    => current_time( 'mysql' ),
            'format'         => 'cnw-social-bridge-export',
            'format_version' => 2,
        ), $json_flags ) );
        $zip->addFromString( 'users.json',              wp_json_encode( $users, $json_flags ) );
        $zip->addFromString( 'settings.json',           wp_json_encode( $settings, $json_flags ) );
        foreach ( $tables as $t ) {
            $zip->addFromString( $t . '.json', wp_json_encode( $table_data[ $t ], $json_flags ) );
        }

        // Include avatar image files in the ZIP.
        $upload_dir  = wp_get_upload_dir();
        $upload_base = $upload_dir['basedir']; // e.g. /path/wp-content/uploads
        $upload_url  = $upload_dir['baseurl']; // e.g. http://localhost/wordpress/wp-content/uploads
        foreach ( $users as &$ue ) {
            $avatar_url = $ue['cnw_avatar_url'] ?? '';
            if ( ! $avatar_url ) continue;
            // Check if the URL belongs to this site's uploads
            if ( strpos( $avatar_url, $upload_url ) === 0 ) {
                $rel  = substr( $avatar_url, strlen( $upload_url ) ); // e.g. /2024/01/photo.jpg
                $file = $upload_base . $rel;
                if ( file_exists( $file ) ) {
                    $zip->addFile( $file, 'avatars/' . $ue['id'] . '-' . basename( $file ) );
                    $ue['_avatar_file'] = $ue['id'] . '-' . basename( $file );
                }
            }
        }
        unset( $ue );
        // Re-write users.json with _avatar_file references
        $zip->deleteName( 'users.json' );
        $zip->addFromString( 'users.json', wp_json_encode( $users, $json_flags ) );

        // Re-write settings.json
        $zip->deleteName( 'settings.json' );
        $zip->addFromString( 'settings.json', wp_json_encode( $settings, $json_flags ) );

        $zip->close();

        $filename = 'cnw-social-bridge-export-' . gmdate( 'Y-m-d-His' ) . '.zip';
        header( 'Content-Type: application/zip' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . filesize( $tmp_file ) );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        readfile( $tmp_file );
        @unlink( $tmp_file );
        exit;
    }

    /* ------------------------------------------------------------------
     * Import — Step 1: Upload ZIP and extract to temp dir
     * ------------------------------------------------------------------ */
    public function handle_import_upload() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_import_data' );

        $redirect_args = array( 'page' => 'cnw-import-export' );

        if ( empty( $_FILES['cnw_import_file']['tmp_name'] ) ) {
            $redirect_args['import_error'] = urlencode( 'No file uploaded.' );
            wp_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
            exit;
        }

        $file = $_FILES['cnw_import_file'];
        $ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        // Determine upload dir for extraction.
        $upload_dir = wp_upload_dir();
        $extract_dir = trailingslashit( $upload_dir['basedir'] ) . 'cnw-import-' . wp_generate_password( 12, false );
        wp_mkdir_p( $extract_dir );

        if ( $ext === 'zip' ) {
            $zip = new ZipArchive();
            if ( $zip->open( $file['tmp_name'] ) !== true ) {
                $redirect_args['import_error'] = urlencode( 'Could not open ZIP file.' );
                wp_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
                exit;
            }
            $zip->extractTo( $extract_dir );
            $zip->close();
        } elseif ( $ext === 'json' ) {
            // Legacy single-JSON support: split into individual files.
            $json = file_get_contents( $file['tmp_name'] );
            $data = json_decode( $json, true );
            if ( ! $data || ! is_array( $data ) ) {
                $this->rmdir_recursive( $extract_dir );
                $redirect_args['import_error'] = urlencode( 'Invalid JSON file.' );
                wp_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
                exit;
            }
            // Write each key as a separate JSON file.
            $json_flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
            $meta = array(
                'plugin_version' => isset( $data['plugin_version'] ) ? $data['plugin_version'] : '',
                'exported_at'    => isset( $data['exported_at'] ) ? $data['exported_at'] : '',
                'format'         => 'cnw-social-bridge-export',
                'format_version' => 1,
            );
            file_put_contents( $extract_dir . '/meta.json', wp_json_encode( $meta, $json_flags ) );
            $keys = array( 'users', 'categories', 'tags', 'threads', 'thread_tags', 'replies', 'messages', 'votes', 'reputation', 'activity', 'saved_threads', 'user_followed_tags', 'notifications', 'settings' );
            foreach ( $keys as $k ) {
                $val = isset( $data[ $k ] ) ? $data[ $k ] : array();
                file_put_contents( $extract_dir . '/' . $k . '.json', wp_json_encode( $val, $json_flags ) );
            }
        } else {
            $this->rmdir_recursive( $extract_dir );
            $redirect_args['import_error'] = urlencode( 'Unsupported file type. Please upload a .zip or .json file.' );
            wp_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
            exit;
        }

        // Validate that essential files exist.
        if ( ! file_exists( $extract_dir . '/meta.json' ) ) {
            $this->rmdir_recursive( $extract_dir );
            $redirect_args['import_error'] = urlencode( 'Invalid export file: missing meta.json.' );
            wp_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
            exit;
        }

        // Store the extract directory and ID maps in a transient.
        $import_id = wp_generate_password( 16, false );
        set_transient( 'cnw_import_' . $import_id, array(
            'dir'        => $extract_dir,
            'user_map'   => array(),
            'cat_map'    => array(),
            'tag_map'    => array(),
            'thread_map' => array(),
            'reply_map'  => array(),
            'msg_map'    => array(),
            'vote_map'   => array(),
        ), HOUR_IN_SECONDS );

        $redirect_args['import_ready'] = $import_id;
        wp_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ------------------------------------------------------------------
     * Import — AJAX batch steps
     * ------------------------------------------------------------------ */
    public function ajax_import_step() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }
        check_ajax_referer( 'cnw_import_ajax', '_nonce' );

        $import_id = sanitize_text_field( $_POST['import_id'] ?? '' );
        $step      = sanitize_text_field( $_POST['step'] ?? '' );

        $state = get_transient( 'cnw_import_' . $import_id );
        if ( ! $state || empty( $state['dir'] ) ) {
            wp_send_json_error( 'Import session expired or invalid.' );
        }

        $dir = $state['dir'];
        global $wpdb;
        $p = $wpdb->prefix . 'cnw_social_worker_';

        // Helper: read a JSON file from the extracted dir.
        $read_json = function( $name ) use ( $dir ) {
            $file = $dir . '/' . $name . '.json';
            if ( ! file_exists( $file ) ) return array();
            $data = json_decode( file_get_contents( $file ), true );
            return is_array( $data ) ? $data : array();
        };

        // Helper: remap user ID.
        $ru = function( $old ) use ( &$state ) {
            $old = (int) $old;
            return isset( $state['user_map'][ $old ] ) ? $state['user_map'][ $old ] : 0;
        };

        $count = 0;

        switch ( $step ) {

            case 'users':
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';

                $users = $read_json( 'users' );
                $state['user_map'] = array( 0 => 0 );
                $cnw_meta_keys = array( 'cnw_phone', 'cnw_verified_label', 'cnw_professional_title', 'cnw_anonymous', 'description', 'cnw_suspended', 'cnw_suspended_until' );
                foreach ( $users as $eu ) {
                    $old_id = (int) $eu['id'];
                    $existing = get_user_by( 'email', $eu['user_email'] );
                    if ( ! $existing ) {
                        $existing = get_user_by( 'login', $eu['user_login'] );
                    }
                    if ( $existing ) {
                        $state['user_map'][ $old_id ] = (int) $existing->ID;
                    } else {
                        $new_id = wp_insert_user( array(
                            'user_login'   => $this->unique_login( $eu['user_login'] ),
                            'user_email'   => $eu['user_email'],
                            'user_pass'    => wp_generate_password( 16 ),
                            'display_name' => $eu['display_name'] ?? '',
                            'first_name'   => $eu['first_name'] ?? '',
                            'last_name'    => $eu['last_name'] ?? '',
                            'role'         => ! empty( $eu['roles'] ) ? $eu['roles'][0] : 'cnw_forum_member',
                        ) );
                        if ( ! is_wp_error( $new_id ) ) {
                            $state['user_map'][ $old_id ] = (int) $new_id;
                            // Restore the original password hash so users keep their passwords.
                            if ( ! empty( $eu['user_pass'] ) ) {
                                global $wpdb;
                                $wpdb->update( $wpdb->users, array( 'user_pass' => $eu['user_pass'] ), array( 'ID' => $new_id ) );
                                clean_user_cache( $new_id );
                            }
                        } else {
                            $state['user_map'][ $old_id ] = 0;
                        }
                    }
                    // Restore CNW user meta.
                    $new_uid = $state['user_map'][ $old_id ];
                    if ( $new_uid ) {
                        foreach ( $cnw_meta_keys as $mk ) {
                            if ( isset( $eu[ $mk ] ) && $eu[ $mk ] !== '' ) {
                                update_user_meta( $new_uid, $mk, $eu[ $mk ] );
                            }
                        }

                        // Import avatar file if included in the export
                        $avatar_file = $eu['_avatar_file'] ?? '';
                        if ( $avatar_file ) {
                            $src = $dir . '/avatars/' . $avatar_file;
                            if ( file_exists( $src ) ) {
                                $upload = wp_upload_bits( basename( $avatar_file ), null, file_get_contents( $src ) );
                                if ( empty( $upload['error'] ) ) {
                                    $filetype      = wp_check_filetype( $upload['file'] );
                                    $attachment_id = wp_insert_attachment( array(
                                        'post_mime_type' => $filetype['type'],
                                        'post_title'     => sanitize_file_name( basename( $upload['file'] ) ),
                                        'post_status'    => 'inherit',
                                    ), $upload['file'] );
                                    if ( ! is_wp_error( $attachment_id ) ) {
                                        $meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
                                        wp_update_attachment_metadata( $attachment_id, $meta );
                                        $new_url = wp_get_attachment_url( $attachment_id );
                                        update_user_meta( $new_uid, 'cnw_avatar_url', esc_url_raw( $new_url ) );
                                        update_user_meta( $new_uid, 'cnw_avatar_attachment_id', $attachment_id );
                                    }
                                }
                            }
                        } elseif ( ! empty( $eu['cnw_avatar_url'] ) ) {
                            // Fallback: store the old URL (may work if same domain)
                            update_user_meta( $new_uid, 'cnw_avatar_url', $eu['cnw_avatar_url'] );
                            if ( ! empty( $eu['cnw_avatar_attachment_id'] ) ) {
                                update_user_meta( $new_uid, 'cnw_avatar_attachment_id', $eu['cnw_avatar_attachment_id'] );
                            }
                        }
                    }
                    $count++;
                }
                break;

            case 'categories':
                $cats = $read_json( 'categories' );
                $state['cat_map'] = array( 0 => 0 );
                usort( $cats, function( $a, $b ) {
                    return ( empty( $a['parent_id'] ) ? 0 : (int) $a['parent_id'] ) - ( empty( $b['parent_id'] ) ? 0 : (int) $b['parent_id'] );
                });
                foreach ( $cats as $row ) {
                    $old_id = (int) $row['id'];
                    $parent = empty( $row['parent_id'] ) ? null : ( $state['cat_map'][ (int) $row['parent_id'] ] ?? null );
                    $wpdb->insert( $p . 'categories', array(
                        'name'        => $row['name'],
                        'slug'        => $row['slug'],
                        'description' => $row['description'] ?? null,
                        'parent_id'   => $parent ?: null,
                        'icon'        => $row['icon'] ?? null,
                        'color'       => $row['color'] ?? null,
                        'sort_order'  => (int) ( $row['sort_order'] ?? 0 ),
                        'is_active'   => (int) ( $row['is_active'] ?? 1 ),
                        'created_by'  => $ru( $row['created_by'] ?? 0 ) ?: null,
                        'created_at'  => $row['created_at'] ?? current_time( 'mysql' ),
                    ) );
                    $state['cat_map'][ $old_id ] = (int) $wpdb->insert_id;
                    $count++;
                }
                break;

            case 'tags':
                $tags = $read_json( 'tags' );
                $state['tag_map'] = array();
                foreach ( $tags as $row ) {
                    $old_id = (int) $row['id'];
                    $wpdb->insert( $p . 'tags', array(
                        'name'        => $row['name'],
                        'slug'        => $row['slug'],
                        'description' => $row['description'] ?? null,
                        'created_by'  => $ru( $row['created_by'] ?? 0 ) ?: null,
                        'created_at'  => $row['created_at'] ?? current_time( 'mysql' ),
                    ) );
                    $state['tag_map'][ $old_id ] = (int) $wpdb->insert_id;
                    $count++;
                }
                break;

            case 'threads':
                $threads = $read_json( 'threads' );
                $state['thread_map'] = array();
                foreach ( $threads as $row ) {
                    $old_id = (int) $row['id'];
                    $wpdb->insert( $p . 'threads', array(
                        'author_id'    => $ru( $row['author_id'] ),
                        'title'        => $row['title'],
                        'content'      => $row['content'],
                        'status'       => $row['status'] ?? 'published',
                        'is_anonymous' => (int) ( $row['is_anonymous'] ?? 0 ),
                        'views'        => (int) ( $row['views'] ?? 0 ),
                        'is_pinned'    => (int) ( $row['is_pinned'] ?? 0 ),
                        'is_closed'    => (int) ( $row['is_closed'] ?? 0 ),
                        'created_at'   => $row['created_at'] ?? current_time( 'mysql' ),
                        'updated_at'   => $row['updated_at'] ?? current_time( 'mysql' ),
                    ) );
                    $state['thread_map'][ $old_id ] = (int) $wpdb->insert_id;
                    $count++;
                }
                break;

            case 'thread_tags':
                $tt = $read_json( 'thread_tags' );
                foreach ( $tt as $row ) {
                    $tid   = $state['thread_map'][ (int) $row['thread_id'] ] ?? 0;
                    $tagid = $state['tag_map'][ (int) $row['tag_id'] ] ?? 0;
                    if ( $tid && $tagid ) {
                        $wpdb->replace( $p . 'thread_tags', array( 'thread_id' => $tid, 'tag_id' => $tagid ) );
                        $count++;
                    }
                }
                break;

            case 'replies':
                $replies = $read_json( 'replies' );
                $state['reply_map'] = array();
                usort( $replies, function( $a, $b ) {
                    return ( empty( $a['parent_id'] ) ? 0 : (int) $a['parent_id'] ) - ( empty( $b['parent_id'] ) ? 0 : (int) $b['parent_id'] );
                });
                foreach ( $replies as $row ) {
                    $old_id = (int) $row['id'];
                    $parent = empty( $row['parent_id'] ) ? null : ( $state['reply_map'][ (int) $row['parent_id'] ] ?? null );
                    $tid    = $state['thread_map'][ (int) $row['thread_id'] ] ?? 0;
                    $wpdb->insert( $p . 'replies', array(
                        'thread_id'    => $tid,
                        'author_id'    => $ru( $row['author_id'] ),
                        'parent_id'    => $parent,
                        'content'      => $row['content'],
                        'status'       => $row['status'] ?? 'approved',
                        'is_solution'  => (int) ( $row['is_solution'] ?? 0 ),
                        'is_anonymous' => (int) ( $row['is_anonymous'] ?? 0 ),
                        'created_at'   => $row['created_at'] ?? current_time( 'mysql' ),
                        'updated_at'   => $row['updated_at'] ?? current_time( 'mysql' ),
                    ) );
                    $state['reply_map'][ $old_id ] = (int) $wpdb->insert_id;
                    $count++;
                }
                break;

            case 'messages':
                $messages = $read_json( 'messages' );
                $state['msg_map'] = array();
                usort( $messages, function( $a, $b ) {
                    return ( empty( $a['parent_id'] ) ? 0 : (int) $a['parent_id'] ) - ( empty( $b['parent_id'] ) ? 0 : (int) $b['parent_id'] );
                });
                foreach ( $messages as $row ) {
                    $old_id = (int) $row['id'];
                    $parent = empty( $row['parent_id'] ) ? null : ( $state['msg_map'][ (int) $row['parent_id'] ] ?? null );
                    $wpdb->insert( $p . 'messages', array(
                        'sender_id'    => $ru( $row['sender_id'] ),
                        'recipient_id' => $ru( $row['recipient_id'] ),
                        'subject'      => $row['subject'] ?? null,
                        'content'      => $row['content'],
                        'is_read'      => (int) ( $row['is_read'] ?? 0 ),
                        'parent_id'    => $parent,
                        'created_at'   => $row['created_at'] ?? current_time( 'mysql' ),
                    ) );
                    $state['msg_map'][ $old_id ] = (int) $wpdb->insert_id;
                    $count++;
                }
                break;

            case 'votes':
                $votes = $read_json( 'votes' );
                $state['vote_map'] = array();
                foreach ( $votes as $row ) {
                    $old_id      = (int) $row['id'];
                    $target_type = $row['target_type'];
                    $target_id   = (int) $row['target_id'];
                    if ( $target_type === 'thread' ) {
                        $target_id = $state['thread_map'][ $target_id ] ?? 0;
                    } elseif ( $target_type === 'reply' ) {
                        $target_id = $state['reply_map'][ $target_id ] ?? 0;
                    }
                    $wpdb->insert( $p . 'votes', array(
                        'user_id'     => $ru( $row['user_id'] ),
                        'target_type' => $target_type,
                        'target_id'   => $target_id,
                        'vote_type'   => (int) $row['vote_type'],
                        'created_at'  => $row['created_at'] ?? current_time( 'mysql' ),
                    ) );
                    $state['vote_map'][ $old_id ] = (int) $wpdb->insert_id;
                    $count++;
                }
                break;

            case 'reputation':
                $rep = $read_json( 'reputation' );
                foreach ( $rep as $row ) {
                    $ref_type = $row['reference_type'] ?? null;
                    $ref_id   = isset( $row['reference_id'] ) ? (int) $row['reference_id'] : null;
                    if ( $ref_type === 'thread' && $ref_id ) {
                        $ref_id = $state['thread_map'][ $ref_id ] ?? 0;
                    } elseif ( $ref_type === 'reply' && $ref_id ) {
                        $ref_id = $state['reply_map'][ $ref_id ] ?? 0;
                    } elseif ( $ref_type === 'vote' && $ref_id ) {
                        $ref_id = $state['vote_map'][ $ref_id ] ?? 0;
                    }
                    $wpdb->insert( $p . 'reputation', array(
                        'user_id'        => $ru( $row['user_id'] ),
                        'points'         => (int) $row['points'],
                        'action_type'    => $row['action_type'],
                        'reference_type' => $ref_type,
                        'reference_id'   => $ref_id ?: null,
                        'description'    => $row['description'] ?? null,
                        'created_at'     => $row['created_at'] ?? current_time( 'mysql' ),
                    ) );
                    $count++;
                }
                break;

            case 'activity':
                $activity = $read_json( 'activity' );
                foreach ( $activity as $row ) {
                    $ref_type = $row['reference_type'] ?? null;
                    $ref_id   = isset( $row['reference_id'] ) ? (int) $row['reference_id'] : null;
                    if ( $ref_type === 'thread' && $ref_id ) {
                        $ref_id = $state['thread_map'][ $ref_id ] ?? 0;
                    } elseif ( $ref_type === 'reply' && $ref_id ) {
                        $ref_id = $state['reply_map'][ $ref_id ] ?? 0;
                    }
                    $wpdb->insert( $p . 'activity', array(
                        'user_id'        => $ru( $row['user_id'] ),
                        'action_type'    => $row['action_type'],
                        'description'    => $row['description'] ?? '',
                        'points'         => (int) ( $row['points'] ?? 0 ),
                        'reason'         => $row['reason'] ?? null,
                        'reference_type' => $ref_type,
                        'reference_id'   => $ref_id ?: null,
                        'link'           => $row['link'] ?? null,
                        'created_at'     => $row['created_at'] ?? current_time( 'mysql' ),
                    ) );
                    $count++;
                }
                break;

            case 'notifications':
                // Also handles saved_threads and user_followed_tags.
                $st = $read_json( 'saved_threads' );
                foreach ( $st as $row ) {
                    $uid = $ru( $row['user_id'] );
                    $tid = $state['thread_map'][ (int) $row['thread_id'] ] ?? 0;
                    if ( $uid && $tid ) {
                        $wpdb->replace( $p . 'saved_threads', array(
                            'user_id'    => $uid,
                            'thread_id'  => $tid,
                            'created_at' => $row['created_at'] ?? current_time( 'mysql' ),
                        ) );
                        $count++;
                    }
                }
                $uft = $read_json( 'user_followed_tags' );
                foreach ( $uft as $row ) {
                    $uid   = $ru( $row['user_id'] );
                    $tagid = $state['tag_map'][ (int) $row['tag_id'] ] ?? 0;
                    if ( $uid && $tagid ) {
                        $wpdb->replace( $p . 'user_followed_tags', array(
                            'user_id'    => $uid,
                            'tag_id'     => $tagid,
                            'created_at' => $row['created_at'] ?? current_time( 'mysql' ),
                        ) );
                        $count++;
                    }
                }
                $notifs = $read_json( 'notifications' );
                foreach ( $notifs as $row ) {
                    $ref_type = $row['reference_type'] ?? null;
                    $ref_id   = isset( $row['reference_id'] ) ? (int) $row['reference_id'] : null;
                    if ( $ref_type === 'thread' && $ref_id ) {
                        $ref_id = $state['thread_map'][ $ref_id ] ?? null;
                    } elseif ( $ref_type === 'reply' && $ref_id ) {
                        $ref_id = $state['reply_map'][ $ref_id ] ?? null;
                    } elseif ( $ref_type === 'tag' && $ref_id ) {
                        $ref_id = $state['tag_map'][ $ref_id ] ?? null;
                    } elseif ( $ref_type === 'user' && $ref_id ) {
                        $ref_id = $ru( $ref_id );
                    }
                    $wpdb->insert( $p . 'notifications', array(
                        'user_id'        => $ru( $row['user_id'] ),
                        'actor_id'       => ! empty( $row['actor_id'] ) ? $ru( $row['actor_id'] ) : null,
                        'type'           => $row['type'],
                        'reference_type' => $ref_type,
                        'reference_id'   => $ref_id,
                        'message'        => $row['message'],
                        'is_read'        => (int) ( $row['is_read'] ?? 0 ),
                        'created_at'     => $row['created_at'] ?? current_time( 'mysql' ),
                    ) );
                    $count++;
                }
                break;

            case 'reports':
                $reports = $read_json( 'reports' );
                foreach ( $reports as $row ) {
                    // Remap content_id based on content_type
                    $content_id = ! empty( $row['content_id'] ) ? (int) $row['content_id'] : null;
                    $content_type = $row['content_type'] ?? null;
                    if ( $content_type === 'thread' && $content_id ) {
                        $content_id = $state['thread_map'][ $content_id ] ?? null;
                    } elseif ( $content_type === 'reply' && $content_id ) {
                        $content_id = $state['reply_map'][ $content_id ] ?? null;
                    }
                    $wpdb->insert( $p . 'reports', array(
                        'user_id'      => $ru( $row['user_id'] ?? 0 ) ?: 0,
                        'type'         => $row['type'] ?? '',
                        'subject'      => $row['subject'] ?? '',
                        'description'  => $row['description'] ?? '',
                        'link'         => $row['link'] ?? null,
                        'content_type' => $content_type,
                        'content_id'   => $content_id,
                        'priority'     => $row['priority'] ?? 'medium',
                        'status'       => $row['status'] ?? 'open',
                        'admin_notes'  => $row['admin_notes'] ?? null,
                        'created_at'   => $row['created_at'] ?? current_time( 'mysql' ),
                        'updated_at'   => $row['updated_at'] ?? current_time( 'mysql' ),
                    ) );
                    $count++;
                }
                break;

            case 'connections':
                $connections = $read_json( 'connections' );
                foreach ( $connections as $row ) {
                    $sender   = $ru( $row['sender_id'] ?? 0 );
                    $receiver = $ru( $row['receiver_id'] ?? 0 );
                    if ( $sender && $receiver ) {
                        $wpdb->insert( $p . 'connections', array(
                            'sender_id'   => $sender,
                            'receiver_id' => $receiver,
                            'status'      => $row['status'] ?? 'pending',
                            'created_at'  => $row['created_at'] ?? current_time( 'mysql' ),
                            'updated_at'  => $row['updated_at'] ?? current_time( 'mysql' ),
                        ) );
                        $count++;
                    }
                }
                break;

            case 'restrictions':
                $restrictions = $read_json( 'restrictions' );
                foreach ( $restrictions as $row ) {
                    $restricter = $ru( $row['restricter_id'] ?? 0 );
                    $restricted = $ru( $row['restricted_id'] ?? 0 );
                    if ( $restricter && $restricted ) {
                        $wpdb->replace( $p . 'restrictions', array(
                            'restricter_id' => $restricter,
                            'restricted_id' => $restricted,
                            'created_at'    => $row['created_at'] ?? current_time( 'mysql' ),
                        ) );
                        $count++;
                    }
                }
                break;

            case 'warnings':
                $warnings = $read_json( 'warnings' );
                foreach ( $warnings as $row ) {
                    $user_id      = $ru( $row['user_id'] ?? 0 );
                    $moderator_id = $ru( $row['moderator_id'] ?? 0 );
                    if ( $user_id && $moderator_id ) {
                        $wpdb->insert( $p . 'warnings', array(
                            'user_id'      => $user_id,
                            'moderator_id' => $moderator_id,
                            'type'         => $row['type'] ?? 'warning',
                            'reason'       => $row['reason'] ?? '',
                            'duration'     => isset( $row['duration'] ) ? (int) $row['duration'] : null,
                            'expires_at'   => $row['expires_at'] ?? null,
                            'is_active'    => (int) ( $row['is_active'] ?? 1 ),
                            'created_at'   => $row['created_at'] ?? current_time( 'mysql' ),
                        ) );
                        $count++;
                    }
                }
                break;

            case 'settings':
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';

                $settings = $read_json( 'settings' );
                if ( ! empty( $settings ) && is_array( $settings ) ) {
                    $allowed = array(
                        'cnw_pusher_host', 'cnw_pusher_port', 'cnw_pusher_cluster',
                        'cnw_pusher_app_id', 'cnw_pusher_key', 'cnw_pusher_secret',
                        'cnw_community_guidelines', 'cnw_community_guidelines_html',
                        'cnw_default_thread_status', 'cnw_default_reply_status',
                        'cnw_social_logo_url',
                    );
                    foreach ( $allowed as $key ) {
                        if ( isset( $settings[ $key ] ) && $settings[ $key ] !== '' ) {
                            update_option( $key, $settings[ $key ] );
                            $count++;
                        }
                    }
                }
                break;

            case 'finalize':
                // Recalculate reputation totals.
                foreach ( $state['user_map'] as $new_uid ) {
                    if ( $new_uid ) {
                        $total = (int) $wpdb->get_var( $wpdb->prepare(
                            "SELECT COALESCE(SUM(points), 0) FROM {$p}reputation WHERE user_id = %d", $new_uid
                        ) );
                        update_user_meta( $new_uid, 'cnw_reputation_total', $total );
                        $count++;
                    }
                }
                // Cleanup.
                $this->rmdir_recursive( $dir );
                delete_transient( 'cnw_import_' . $import_id );
                wp_send_json_success( array( 'count' => $count, 'done' => true ) );
                return;

            default:
                wp_send_json_error( 'Unknown step: ' . $step );
                return;
        }

        // Save updated state (with new ID maps) back to the transient.
        set_transient( 'cnw_import_' . $import_id, $state, HOUR_IN_SECONDS );
        wp_send_json_success( array( 'count' => $count ) );
    }

    private function rmdir_recursive( $dir ) {
        if ( ! is_dir( $dir ) ) return;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ( $items as $item ) {
            $item->isDir() ? rmdir( $item->getRealPath() ) : unlink( $item->getRealPath() );
        }
        rmdir( $dir );
    }

    private function unique_login( $login ) {
        $login = sanitize_user( $login, true );
        if ( ! username_exists( $login ) ) {
            return $login;
        }
        $i = 2;
        while ( username_exists( $login . $i ) ) {
            $i++;
        }
        return $login . $i;
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
    public function page_users()          { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-users.php'; }
    public function page_guidelines()     { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-guidelines.php'; }
    public function page_reports()        { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-reports.php'; }
    public function page_import_export()  { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-import-export.php'; }
    public function page_settings()       { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-settings.php'; }
}
