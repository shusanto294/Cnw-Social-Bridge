<?php
/**
 * Admin Settings page — Logo branding + role info.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$logo_url = get_option( 'cnw_social_logo_url', '' );
$msg      = sanitize_text_field( $_GET['msg'] ?? '' );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Settings', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'Platform Settings', 'cnw-social-bridge' ); ?></p>
    </div>

    <?php if ( $msg === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'cnw-social-bridge' ); ?></p></div>
    <?php endif; ?>

    <div class="cnw-section">
        <div class="cnw-section-heading">
            <h2 class="cnw-section-title"><?php esc_html_e( 'Branding', 'cnw-social-bridge' ); ?></h2>
            <p class="cnw-section-desc"><?php esc_html_e( 'Customise how your Social Bridge platform looks to members.', 'cnw-social-bridge' ); ?></p>
        </div>

        <div class="cnw-settings-card">
            <div class="cnw-settings-card-header">
                <span class="dashicons dashicons-format-image cnw-card-icon"></span>
                <div>
                    <h3 class="cnw-settings-card-title"><?php esc_html_e( 'Platform Logo', 'cnw-social-bridge' ); ?></h3>
                    <p class="cnw-settings-card-desc"><?php esc_html_e( 'Displayed in the header. Recommended: 200x60 px (PNG or SVG).', 'cnw-social-bridge' ); ?></p>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cnw-logo-form">
                <?php wp_nonce_field( 'cnw_save_logo' ); ?>
                <input type="hidden" name="action" value="cnw_save_logo">
                <input type="hidden" name="cnw_logo_url" id="cnw-logo-url" value="<?php echo esc_attr( $logo_url ); ?>">

                <div class="cnw-logo-preview-wrap" id="cnw-logo-preview">
                    <?php if ( $logo_url ) : ?>
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo preview" class="cnw-logo-preview-img" />
                    <?php else : ?>
                        <div class="cnw-logo-empty-state">
                            <span class="dashicons dashicons-format-image"></span>
                            <p><?php esc_html_e( 'No logo selected', 'cnw-social-bridge' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="cnw-logo-actions">
                    <button type="button" id="cnw-select-logo" class="button button-secondary cnw-btn-select">
                        <span class="dashicons dashicons-upload"></span>
                        <?php echo $logo_url ? esc_html__( 'Change Logo', 'cnw-social-bridge' ) : esc_html__( 'Select Logo', 'cnw-social-bridge' ); ?>
                    </button>
                    <button type="button" id="cnw-remove-logo" class="button button-link-delete cnw-btn-remove" <?php echo $logo_url ? '' : 'style="display:none"'; ?>>
                        <span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Remove', 'cnw-social-bridge' ); ?>
                    </button>
                </div>

                <div class="cnw-form-footer">
                    <?php submit_button( __( 'Save', 'cnw-social-bridge' ), 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>
    </div>

    <div class="cnw-section">
        <div class="cnw-section-heading">
            <h2 class="cnw-section-title"><?php esc_html_e( 'Shortcode', 'cnw-social-bridge' ); ?></h2>
            <p class="cnw-section-desc"><?php esc_html_e( 'Use the shortcode below to display the Social Bridge forum on any page or post.', 'cnw-social-bridge' ); ?></p>
        </div>

        <div class="cnw-settings-card">
            <div class="cnw-settings-card-header">
                <span class="dashicons dashicons-shortcode cnw-card-icon"></span>
                <div>
                    <h3 class="cnw-settings-card-title"><?php esc_html_e( 'Forum Shortcode', 'cnw-social-bridge' ); ?></h3>
                    <p class="cnw-settings-card-desc"><?php esc_html_e( 'Copy and paste this shortcode into any page or post to display the forum dashboard.', 'cnw-social-bridge' ); ?></p>
                </div>
            </div>
            <div class="cnw-shortcode-box">
                <code id="cnw-shortcode-value" class="cnw-shortcode-code">[cnw_social_bridge]</code>
                <button type="button" id="cnw-copy-shortcode" class="button button-secondary cnw-btn-copy">
                    <span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy', 'cnw-social-bridge' ); ?>
                </button>
            </div>
        </div>
    </div>

    <div class="cnw-settings-info">
        <h2><?php esc_html_e( 'User Roles', 'cnw-social-bridge' ); ?></h2>
        <p><?php esc_html_e( 'The following custom roles are available:', 'cnw-social-bridge' ); ?></p>
        <ul>
            <li><strong><?php esc_html_e( 'Forum Member', 'cnw-social-bridge' ); ?></strong> — <?php esc_html_e( 'Can create threads, reply, and send messages.', 'cnw-social-bridge' ); ?></li>
            <li><strong><?php esc_html_e( 'Moderator', 'cnw-social-bridge' ); ?></strong> — <?php esc_html_e( 'Can moderate content, close/pin threads, and warn users.', 'cnw-social-bridge' ); ?></li>
            <li><strong><?php esc_html_e( 'Forum Admin', 'cnw-social-bridge' ); ?></strong> — <?php esc_html_e( 'Full forum management including bans and settings.', 'cnw-social-bridge' ); ?></li>
        </ul>
    </div>
</div>
