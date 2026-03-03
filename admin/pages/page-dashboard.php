<?php
/**
 * Admin Dashboard page — Platform Settings.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$logo_url   = get_option( 'cnw_social_logo_url', '' );
$logo_saved = isset( $_GET['logo_saved'] ) && $_GET['logo_saved'] === '1';
?>

<div class="wrap cnw-admin-wrap">

    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title">
            <?php esc_html_e( 'Social Bridge', 'cnw-social-bridge' ); ?>
        </h1>
        <p class="cnw-admin-subtitle">
            <?php esc_html_e( 'Platform Settings', 'cnw-social-bridge' ); ?>
        </p>
    </div>

    <?php if ( $logo_saved ) : ?>
        <div class="notice notice-success is-dismissible cnw-notice">
            <p><?php esc_html_e( 'Settings saved successfully.', 'cnw-social-bridge' ); ?></p>
        </div>
    <?php endif; ?>

    <!-- ── Branding ──────────────────────────────────────────────── -->
    <div class="cnw-section">

        <div class="cnw-section-heading">
            <h2 class="cnw-section-title">
                <?php esc_html_e( 'Branding', 'cnw-social-bridge' ); ?>
            </h2>
            <p class="cnw-section-desc">
                <?php esc_html_e( 'Customise how your Social Bridge platform looks to members.', 'cnw-social-bridge' ); ?>
            </p>
        </div>

        <div class="cnw-settings-card">

            <div class="cnw-settings-card-header">
                <span class="dashicons dashicons-format-image cnw-card-icon"></span>
                <div>
                    <h3 class="cnw-settings-card-title">
                        <?php esc_html_e( 'Platform Logo', 'cnw-social-bridge' ); ?>
                    </h3>
                    <p class="cnw-settings-card-desc">
                        <?php esc_html_e( 'Displayed in the header of the Social Bridge platform. Recommended size: 200 × 60 px (PNG or SVG with transparent background).', 'cnw-social-bridge' ); ?>
                    </p>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-logo-form">
                <?php wp_nonce_field( 'cnw_save_logo' ); ?>
                <input type="hidden" name="action"       value="cnw_save_logo">
                <input type="hidden" name="cnw_logo_url" id="cnw-logo-url" value="<?php echo esc_attr( $logo_url ); ?>">

                <!-- Preview -->
                <div class="cnw-logo-preview-wrap" id="cnw-logo-preview">
                    <?php if ( $logo_url ) : ?>
                        <img
                            src="<?php echo esc_url( $logo_url ); ?>"
                            alt="<?php esc_attr_e( 'Logo preview', 'cnw-social-bridge' ); ?>"
                            class="cnw-logo-preview-img"
                        />
                    <?php else : ?>
                        <div class="cnw-logo-empty-state">
                            <span class="dashicons dashicons-format-image"></span>
                            <p><?php esc_html_e( 'No logo selected', 'cnw-social-bridge' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Media buttons -->
                <div class="cnw-logo-actions">
                    <button type="button" id="cnw-select-logo" class="button button-secondary cnw-btn-select">
                        <span class="dashicons dashicons-upload"></span>
                        <?php echo $logo_url
                            ? esc_html__( 'Change Logo', 'cnw-social-bridge' )
                            : esc_html__( 'Select Logo', 'cnw-social-bridge' );
                        ?>
                    </button>

                    <button
                        type="button"
                        id="cnw-remove-logo"
                        class="button button-link-delete cnw-btn-remove"
                        <?php echo $logo_url ? '' : 'style="display:none"'; ?>
                    >
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e( 'Remove', 'cnw-social-bridge' ); ?>
                    </button>
                </div>

                <div class="cnw-form-footer">
                    <?php submit_button( __( 'Save', 'cnw-social-bridge' ), 'primary', 'submit', false ); ?>
                </div>

            </form>

        </div><!-- .cnw-settings-card -->

    </div><!-- .cnw-section / Branding -->

</div><!-- .wrap -->
