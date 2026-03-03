<?php
/**
 * Admin Settings page.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Settings', 'cnw-social-bridge' ); ?></h1>
    </div>

    <form method="post" action="options.php">
        <?php
        settings_fields( 'cnw_settings_group' );
        do_settings_sections( 'cnw-social-bridge-settings' );
        submit_button();
        ?>
    </form>

    <div class="cnw-settings-info">
        <h2><?php esc_html_e( 'User Roles', 'cnw-social-bridge' ); ?></h2>
        <p><?php esc_html_e( 'The following custom roles are available:', 'cnw-social-bridge' ); ?></p>
        <ul>
            <li>
                <strong><?php esc_html_e( 'Forum Member', 'cnw-social-bridge' ); ?></strong>
                — <?php esc_html_e( 'Can create threads, reply, and send messages.', 'cnw-social-bridge' ); ?>
            </li>
            <li>
                <strong><?php esc_html_e( 'Moderator', 'cnw-social-bridge' ); ?></strong>
                — <?php esc_html_e( 'Can moderate content, close/pin threads, and warn users.', 'cnw-social-bridge' ); ?>
            </li>
            <li>
                <strong><?php esc_html_e( 'Forum Admin', 'cnw-social-bridge' ); ?></strong>
                — <?php esc_html_e( 'Full forum management including bans and settings.', 'cnw-social-bridge' ); ?>
            </li>
        </ul>
    </div>
</div>
