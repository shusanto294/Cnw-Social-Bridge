<?php
/**
 * Admin class — handles all WP admin menus, assets, and page rendering.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cnw_Social_Bridge_Admin {

    public function __construct() {
        add_action( 'admin_menu',            array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_cnw_save_logo', array( $this, 'handle_save_logo' ) );
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

        add_submenu_page(
            'cnw-social-bridge',
            __( 'Dashboard', 'cnw-social-bridge' ),
            __( 'Dashboard', 'cnw-social-bridge' ),
            'manage_options',
            'cnw-social-bridge',
            array( $this, 'page_dashboard' )
        );

        add_submenu_page(
            'cnw-social-bridge',
            __( 'Threads', 'cnw-social-bridge' ),
            __( 'Threads', 'cnw-social-bridge' ),
            'manage_options',
            'cnw-threads',
            array( $this, 'page_threads' )
        );

        add_submenu_page(
            'cnw-social-bridge',
            __( 'Replies', 'cnw-social-bridge' ),
            __( 'Replies', 'cnw-social-bridge' ),
            'manage_options',
            'cnw-replies',
            array( $this, 'page_replies' )
        );

        add_submenu_page(
            'cnw-social-bridge',
            __( 'Messages', 'cnw-social-bridge' ),
            __( 'Messages', 'cnw-social-bridge' ),
            'manage_options',
            'cnw-messages',
            array( $this, 'page_messages' )
        );

        add_submenu_page(
            'cnw-social-bridge',
            __( 'Forum Users', 'cnw-social-bridge' ),
            __( 'Forum Users', 'cnw-social-bridge' ),
            'manage_options',
            'cnw-users',
            array( $this, 'page_users' )
        );

        add_submenu_page(
            'cnw-social-bridge',
            __( 'Settings', 'cnw-social-bridge' ),
            __( 'Settings', 'cnw-social-bridge' ),
            'manage_options',
            'cnw-settings',
            array( $this, 'page_settings' )
        );
    }

    /* ------------------------------------------------------------------
     * Asset enqueueing
     * ------------------------------------------------------------------ */

    public function enqueue_assets( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'cnw' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'cnw-admin-style',
            CNW_SOCIAL_BRIDGE_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            CNW_SOCIAL_BRIDGE_VERSION
        );

        // Media uploader only needed on the dashboard (logo settings) page
        if ( $hook === 'toplevel_page_cnw-social-bridge' ) {
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
     * Logo save handler
     * ------------------------------------------------------------------ */

    public function handle_save_logo() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'cnw-social-bridge' ) );
        }

        check_admin_referer( 'cnw_save_logo' );

        $logo_url = isset( $_POST['cnw_logo_url'] ) ? esc_url_raw( $_POST['cnw_logo_url'] ) : '';
        update_option( 'cnw_social_logo_url', $logo_url );

        wp_redirect(
            add_query_arg(
                array(
                    'page'       => 'cnw-social-bridge',
                    'logo_saved' => '1',
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /* ------------------------------------------------------------------
     * Page renderers — each loads its own template file
     * ------------------------------------------------------------------ */

    public function page_dashboard() {
        include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-dashboard.php';
    }

    public function page_threads() {
        include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-threads.php';
    }

    public function page_replies() {
        include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-replies.php';
    }

    public function page_messages() {
        include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-messages.php';
    }

    public function page_users() {
        include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-users.php';
    }

    public function page_settings() {
        include CNW_SOCIAL_BRIDGE_PLUGIN_DIR . 'admin/pages/page-settings.php';
    }
}
