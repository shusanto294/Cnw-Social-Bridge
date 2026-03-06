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
        add_action( 'admin_post_cnw_export_data',       array( $this, 'handle_export_data' ) );
        add_action( 'admin_post_cnw_import_data',       array( $this, 'handle_import_data' ) );
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
                        $('#cnw-avatar-preview').attr('src','" . esc_url( get_avatar_url( intval( $_GET['id'] ?? 0 ), array( 'size' => 150 ) ) ) . "');
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

        // Save phone
        $phone = sanitize_text_field( $_POST['cnw_phone'] ?? '' );
        update_user_meta( $id, 'cnw_phone', $phone );

        // Save custom avatar
        $avatar_url = esc_url_raw( $_POST['cnw_avatar_url'] ?? '' );
        if ( $avatar_url ) {
            update_user_meta( $id, 'cnw_avatar_url', $avatar_url );
        } else {
            delete_user_meta( $id, 'cnw_avatar_url' );
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-users', 'action' => 'edit', 'id' => $id, 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
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

        $mobile_logo_url = isset( $_POST['cnw_mobile_logo_url'] ) ? esc_url_raw( $_POST['cnw_mobile_logo_url'] ) : '';
        update_option( 'cnw_social_mobile_logo_url', $mobile_logo_url );

        wp_redirect( add_query_arg( array( 'page' => 'cnw-settings', 'msg' => 'saved' ), admin_url( 'admin.php' ) ) );
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

        // Collect all plugin tables.
        $tables = array(
            'threads', 'replies', 'messages', 'categories', 'tags',
            'thread_tags', 'user_followed_tags', 'votes', 'reputation',
            'saved_threads', 'notifications',
        );

        $data = array(
            'plugin_version' => CNW_SOCIAL_BRIDGE_VERSION,
            'exported_at'    => current_time( 'mysql' ),
        );

        foreach ( $tables as $t ) {
            $full = $p . $t;
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$full'" ) === $full ) {
                $data[ $t ] = $wpdb->get_results( "SELECT * FROM `$full`", ARRAY_A );
            } else {
                $data[ $t ] = array();
            }
        }

        // Collect referenced WP user data for matching on import.
        $user_ids = array();
        foreach ( $data['threads'] as $r )      { $user_ids[ (int) $r['author_id'] ] = true; }
        foreach ( $data['replies'] as $r )       { $user_ids[ (int) $r['author_id'] ] = true; }
        foreach ( $data['messages'] as $r )      { $user_ids[ (int) $r['sender_id'] ] = true; $user_ids[ (int) $r['recipient_id'] ] = true; }
        foreach ( $data['votes'] as $r )         { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $data['reputation'] as $r )    { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $data['tags'] as $r )          { if ( ! empty( $r['created_by'] ) ) $user_ids[ (int) $r['created_by'] ] = true; }
        foreach ( $data['categories'] as $r )    { if ( ! empty( $r['created_by'] ) ) $user_ids[ (int) $r['created_by'] ] = true; }
        foreach ( $data['user_followed_tags'] as $r ) { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $data['saved_threads'] as $r ) { $user_ids[ (int) $r['user_id'] ] = true; }
        foreach ( $data['notifications'] as $r ) { $user_ids[ (int) $r['user_id'] ] = true; if ( ! empty( $r['actor_id'] ) ) $user_ids[ (int) $r['actor_id'] ] = true; }
        unset( $user_ids[0] );

        $users = array();
        foreach ( array_keys( $user_ids ) as $uid ) {
            $u = get_userdata( $uid );
            if ( $u ) {
                $users[] = array(
                    'id'           => $uid,
                    'user_login'   => $u->user_login,
                    'user_email'   => $u->user_email,
                    'display_name' => $u->display_name,
                    'first_name'   => get_user_meta( $uid, 'first_name', true ),
                    'last_name'    => get_user_meta( $uid, 'last_name', true ),
                    'roles'        => $u->roles,
                );
            }
        }
        $data['users'] = $users;

        // Send as JSON download.
        $filename = 'cnw-social-bridge-export-' . gmdate( 'Y-m-d-His' ) . '.json';
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }

    public function handle_import_data() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'cnw_import_data' );

        if ( empty( $_FILES['cnw_import_file']['tmp_name'] ) ) {
            wp_redirect( add_query_arg( array( 'page' => 'cnw-import-export', 'import_error' => urlencode( 'No file uploaded.' ) ), admin_url( 'admin.php' ) ) );
            exit;
        }

        $json = file_get_contents( $_FILES['cnw_import_file']['tmp_name'] );
        $data = json_decode( $json, true );

        if ( ! $data || ! is_array( $data ) ) {
            wp_redirect( add_query_arg( array( 'page' => 'cnw-import-export', 'import_error' => urlencode( 'Invalid JSON file.' ) ), admin_url( 'admin.php' ) ) );
            exit;
        }

        global $wpdb;
        $p = $wpdb->prefix . 'cnw_social_worker_';

        // ── Step 1: Build user ID map (old → new) ──
        $user_map = array( 0 => 0 );
        $export_users = isset( $data['users'] ) ? $data['users'] : array();

        foreach ( $export_users as $eu ) {
            $old_id = (int) $eu['id'];
            // Try matching by email first, then by login.
            $existing = get_user_by( 'email', $eu['user_email'] );
            if ( ! $existing ) {
                $existing = get_user_by( 'login', $eu['user_login'] );
            }

            if ( $existing ) {
                $user_map[ $old_id ] = (int) $existing->ID;
            } else {
                // Create user with a random password.
                $new_id = wp_insert_user( array(
                    'user_login'   => $this->unique_login( $eu['user_login'] ),
                    'user_email'   => $eu['user_email'],
                    'user_pass'    => wp_generate_password( 16 ),
                    'display_name' => $eu['display_name'],
                    'first_name'   => isset( $eu['first_name'] ) ? $eu['first_name'] : '',
                    'last_name'    => isset( $eu['last_name'] ) ? $eu['last_name'] : '',
                    'role'         => ! empty( $eu['roles'] ) ? $eu['roles'][0] : 'cnw_forum_member',
                ) );
                if ( ! is_wp_error( $new_id ) ) {
                    $user_map[ $old_id ] = (int) $new_id;
                } else {
                    $user_map[ $old_id ] = 0;
                }
            }
        }

        // Helper to remap a user ID.
        $ru = function( $old ) use ( &$user_map ) {
            $old = (int) $old;
            return isset( $user_map[ $old ] ) ? $user_map[ $old ] : 0;
        };

        // ── Step 2: Categories (self-referencing parent_id) ──
        $cat_map = array( 0 => 0 );
        $cats = isset( $data['categories'] ) ? $data['categories'] : array();
        // Sort so root categories (parent_id NULL/0) come first.
        usort( $cats, function( $a, $b ) {
            $pa = empty( $a['parent_id'] ) ? 0 : (int) $a['parent_id'];
            $pb = empty( $b['parent_id'] ) ? 0 : (int) $b['parent_id'];
            return $pa - $pb;
        });
        foreach ( $cats as $row ) {
            $old_id = (int) $row['id'];
            $parent = empty( $row['parent_id'] ) ? null : ( isset( $cat_map[ (int) $row['parent_id'] ] ) ? $cat_map[ (int) $row['parent_id'] ] : null );
            $wpdb->insert( $p . 'categories', array(
                'name'        => $row['name'],
                'slug'        => $row['slug'],
                'description' => isset( $row['description'] ) ? $row['description'] : null,
                'parent_id'   => $parent ?: null,
                'icon'        => isset( $row['icon'] ) ? $row['icon'] : null,
                'color'       => isset( $row['color'] ) ? $row['color'] : null,
                'sort_order'  => isset( $row['sort_order'] ) ? (int) $row['sort_order'] : 0,
                'is_active'   => isset( $row['is_active'] ) ? (int) $row['is_active'] : 1,
                'created_by'  => $ru( isset( $row['created_by'] ) ? $row['created_by'] : 0 ) ?: null,
                'created_at'  => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
            ) );
            $cat_map[ $old_id ] = (int) $wpdb->insert_id;
        }

        // ── Step 3: Tags ──
        $tag_map = array();
        $tags = isset( $data['tags'] ) ? $data['tags'] : array();
        foreach ( $tags as $row ) {
            $old_id = (int) $row['id'];
            $wpdb->insert( $p . 'tags', array(
                'name'        => $row['name'],
                'slug'        => $row['slug'],
                'description' => isset( $row['description'] ) ? $row['description'] : null,
                'created_by'  => $ru( isset( $row['created_by'] ) ? $row['created_by'] : 0 ) ?: null,
                'created_at'  => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
            ) );
            $tag_map[ $old_id ] = (int) $wpdb->insert_id;
        }

        // ── Step 4: Threads ──
        $thread_map = array();
        $threads = isset( $data['threads'] ) ? $data['threads'] : array();
        foreach ( $threads as $row ) {
            $old_id = (int) $row['id'];
            $wpdb->insert( $p . 'threads', array(
                'author_id'    => $ru( $row['author_id'] ),
                'title'        => $row['title'],
                'content'      => $row['content'],
                'status'       => isset( $row['status'] ) ? $row['status'] : 'published',
                'is_anonymous' => isset( $row['is_anonymous'] ) ? (int) $row['is_anonymous'] : 0,
                'views'        => isset( $row['views'] ) ? (int) $row['views'] : 0,
                'created_at'   => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
                'updated_at'   => isset( $row['updated_at'] ) ? $row['updated_at'] : current_time( 'mysql' ),
            ) );
            $thread_map[ $old_id ] = (int) $wpdb->insert_id;
        }

        // ── Step 5: Replies (self-referencing parent_id) ──
        $reply_map = array();
        $replies = isset( $data['replies'] ) ? $data['replies'] : array();
        // Sort so root replies (parent_id NULL/0) come first.
        usort( $replies, function( $a, $b ) {
            $pa = empty( $a['parent_id'] ) ? 0 : (int) $a['parent_id'];
            $pb = empty( $b['parent_id'] ) ? 0 : (int) $b['parent_id'];
            return $pa - $pb;
        });
        foreach ( $replies as $row ) {
            $old_id = (int) $row['id'];
            $parent = empty( $row['parent_id'] ) ? null : ( isset( $reply_map[ (int) $row['parent_id'] ] ) ? $reply_map[ (int) $row['parent_id'] ] : null );
            $tid    = isset( $thread_map[ (int) $row['thread_id'] ] ) ? $thread_map[ (int) $row['thread_id'] ] : 0;
            $wpdb->insert( $p . 'replies', array(
                'thread_id'  => $tid,
                'author_id'  => $ru( $row['author_id'] ),
                'parent_id'  => $parent,
                'content'    => $row['content'],
                'status'     => isset( $row['status'] ) ? $row['status'] : 'approved',
                'created_at' => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
                'updated_at' => isset( $row['updated_at'] ) ? $row['updated_at'] : current_time( 'mysql' ),
            ) );
            $reply_map[ $old_id ] = (int) $wpdb->insert_id;
        }

        // ── Step 6: Messages (self-referencing parent_id) ──
        $msg_map = array();
        $messages = isset( $data['messages'] ) ? $data['messages'] : array();
        usort( $messages, function( $a, $b ) {
            $pa = empty( $a['parent_id'] ) ? 0 : (int) $a['parent_id'];
            $pb = empty( $b['parent_id'] ) ? 0 : (int) $b['parent_id'];
            return $pa - $pb;
        });
        foreach ( $messages as $row ) {
            $old_id = (int) $row['id'];
            $parent = empty( $row['parent_id'] ) ? null : ( isset( $msg_map[ (int) $row['parent_id'] ] ) ? $msg_map[ (int) $row['parent_id'] ] : null );
            $wpdb->insert( $p . 'messages', array(
                'sender_id'    => $ru( $row['sender_id'] ),
                'recipient_id' => $ru( $row['recipient_id'] ),
                'subject'      => isset( $row['subject'] ) ? $row['subject'] : null,
                'content'      => $row['content'],
                'is_read'      => isset( $row['is_read'] ) ? (int) $row['is_read'] : 0,
                'parent_id'    => $parent,
                'created_at'   => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
            ) );
            $msg_map[ $old_id ] = (int) $wpdb->insert_id;
        }

        // ── Step 7: Votes ──
        $vote_map = array();
        $votes = isset( $data['votes'] ) ? $data['votes'] : array();
        foreach ( $votes as $row ) {
            $old_id     = (int) $row['id'];
            $target_type = $row['target_type'];
            $target_id   = (int) $row['target_id'];
            if ( $target_type === 'thread' ) {
                $target_id = isset( $thread_map[ $target_id ] ) ? $thread_map[ $target_id ] : 0;
            } elseif ( $target_type === 'reply' ) {
                $target_id = isset( $reply_map[ $target_id ] ) ? $reply_map[ $target_id ] : 0;
            }
            $wpdb->insert( $p . 'votes', array(
                'user_id'     => $ru( $row['user_id'] ),
                'target_type' => $target_type,
                'target_id'   => $target_id,
                'vote_type'   => (int) $row['vote_type'],
                'created_at'  => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
            ) );
            $vote_map[ $old_id ] = (int) $wpdb->insert_id;
        }

        // ── Step 8: Reputation ──
        $rep = isset( $data['reputation'] ) ? $data['reputation'] : array();
        foreach ( $rep as $row ) {
            $ref_type = isset( $row['reference_type'] ) ? $row['reference_type'] : null;
            $ref_id   = isset( $row['reference_id'] ) ? (int) $row['reference_id'] : null;
            if ( $ref_type === 'thread' && $ref_id ) {
                $ref_id = isset( $thread_map[ $ref_id ] ) ? $thread_map[ $ref_id ] : 0;
            } elseif ( $ref_type === 'reply' && $ref_id ) {
                $ref_id = isset( $reply_map[ $ref_id ] ) ? $reply_map[ $ref_id ] : 0;
            } elseif ( $ref_type === 'vote' && $ref_id ) {
                $ref_id = isset( $vote_map[ $ref_id ] ) ? $vote_map[ $ref_id ] : 0;
            }
            $wpdb->insert( $p . 'reputation', array(
                'user_id'        => $ru( $row['user_id'] ),
                'points'         => (int) $row['points'],
                'action_type'    => $row['action_type'],
                'reference_type' => $ref_type,
                'reference_id'   => $ref_id ?: null,
                'description'    => isset( $row['description'] ) ? $row['description'] : null,
                'created_at'     => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
            ) );
        }

        // ── Step 9: Junction tables ──
        // thread_tags
        $tt = isset( $data['thread_tags'] ) ? $data['thread_tags'] : array();
        foreach ( $tt as $row ) {
            $tid = isset( $thread_map[ (int) $row['thread_id'] ] ) ? $thread_map[ (int) $row['thread_id'] ] : 0;
            $tagid = isset( $tag_map[ (int) $row['tag_id'] ] ) ? $tag_map[ (int) $row['tag_id'] ] : 0;
            if ( $tid && $tagid ) {
                $wpdb->replace( $p . 'thread_tags', array( 'thread_id' => $tid, 'tag_id' => $tagid ) );
            }
        }

        // user_followed_tags
        $uft = isset( $data['user_followed_tags'] ) ? $data['user_followed_tags'] : array();
        foreach ( $uft as $row ) {
            $uid   = $ru( $row['user_id'] );
            $tagid = isset( $tag_map[ (int) $row['tag_id'] ] ) ? $tag_map[ (int) $row['tag_id'] ] : 0;
            if ( $uid && $tagid ) {
                $wpdb->replace( $p . 'user_followed_tags', array(
                    'user_id'    => $uid,
                    'tag_id'     => $tagid,
                    'created_at' => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
                ) );
            }
        }

        // saved_threads
        $st = isset( $data['saved_threads'] ) ? $data['saved_threads'] : array();
        foreach ( $st as $row ) {
            $uid = $ru( $row['user_id'] );
            $tid = isset( $thread_map[ (int) $row['thread_id'] ] ) ? $thread_map[ (int) $row['thread_id'] ] : 0;
            if ( $uid && $tid ) {
                $wpdb->replace( $p . 'saved_threads', array(
                    'user_id'    => $uid,
                    'thread_id'  => $tid,
                    'created_at' => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
                ) );
            }
        }

        // ── Step 10: Notifications ──
        $notifs = isset( $data['notifications'] ) ? $data['notifications'] : array();
        foreach ( $notifs as $row ) {
            $ref_type = isset( $row['reference_type'] ) ? $row['reference_type'] : null;
            $ref_id   = isset( $row['reference_id'] ) ? (int) $row['reference_id'] : null;
            if ( $ref_type === 'thread' && $ref_id ) {
                $ref_id = isset( $thread_map[ $ref_id ] ) ? $thread_map[ $ref_id ] : null;
            } elseif ( $ref_type === 'reply' && $ref_id ) {
                $ref_id = isset( $reply_map[ $ref_id ] ) ? $reply_map[ $ref_id ] : null;
            } elseif ( $ref_type === 'tag' && $ref_id ) {
                $ref_id = isset( $tag_map[ $ref_id ] ) ? $tag_map[ $ref_id ] : null;
            }
            $wpdb->insert( $p . 'notifications', array(
                'user_id'        => $ru( $row['user_id'] ),
                'actor_id'       => ! empty( $row['actor_id'] ) ? $ru( $row['actor_id'] ) : null,
                'type'           => $row['type'],
                'reference_type' => $ref_type,
                'reference_id'   => $ref_id,
                'message'        => $row['message'],
                'is_read'        => isset( $row['is_read'] ) ? (int) $row['is_read'] : 0,
                'created_at'     => isset( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
            ) );
        }

        // Recalculate reputation totals for all mapped users.
        foreach ( $user_map as $new_uid ) {
            if ( $new_uid ) {
                $total = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COALESCE(SUM(points), 0) FROM {$p}reputation WHERE user_id = %d", $new_uid
                ) );
                update_user_meta( $new_uid, 'cnw_reputation_total', $total );
            }
        }

        wp_redirect( add_query_arg( array( 'page' => 'cnw-import-export', 'import_success' => '1' ), admin_url( 'admin.php' ) ) );
        exit;
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
    public function page_import_export()  { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-import-export.php'; }
    public function page_settings()       { include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-settings.php'; }
}
