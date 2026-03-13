<?php
/**
 * Admin Take Action — Warn or suspend users directly from the WP dashboard.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$msg = sanitize_text_field( $_GET['msg'] ?? '' );
$msg_user = sanitize_text_field( $_GET['msg_user'] ?? '' );
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Take Action', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'Warn or suspend users for policy violations.', 'cnw-social-bridge' ); ?></p>
    </div>

    <?php if ( $msg === 'warned' ) : ?>
        <div class="notice notice-success is-dismissible"><p>Warning sent to <strong><?php echo esc_html( $msg_user ); ?></strong> successfully.</p></div>
    <?php elseif ( $msg === 'suspended' ) : ?>
        <div class="notice notice-success is-dismissible"><p><strong><?php echo esc_html( $msg_user ); ?></strong> has been suspended successfully.</p></div>
    <?php elseif ( $msg === 'error' ) : ?>
        <div class="notice notice-error is-dismissible"><p>Something went wrong. Please try again.</p></div>
    <?php endif; ?>

    <div class="cnw-action-grid">
        <!-- Suspend User -->
        <div class="cnw-action-card cnw-action-card--suspend">
            <div class="cnw-action-card-header">
                <span class="dashicons dashicons-dismiss" style="color:#d63638;font-size:20px;"></span>
                <h2>Suspend a User</h2>
            </div>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'cnw_take_action_suspend' ); ?>
                <input type="hidden" name="action" value="cnw_take_action_suspend">

                <table class="form-table">
                    <tr>
                        <th><label for="suspend_user">User</label></th>
                        <td>
                            <select name="user_id" id="suspend_user" class="cnw-user-search" required>
                                <option value=""></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="suspend_reason">Reason</label></th>
                        <td><textarea name="reason" id="suspend_reason" rows="3" class="large-text" required placeholder="Reason for suspension..."></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="suspend_duration">Duration</label></th>
                        <td>
                            <select name="duration" id="suspend_duration">
                                <option value="1">1 day</option>
                                <option value="3">3 days</option>
                                <option value="7" selected>7 days</option>
                                <option value="14">14 days</option>
                                <option value="30">30 days</option>
                                <option value="0">Permanent</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button" style="background:#d63638;border-color:#d63638;color:#fff;" value="Suspend User">
                </p>
            </form>
        </div>

        <!-- Warn User -->
        <div class="cnw-action-card cnw-action-card--warn">
            <div class="cnw-action-card-header">
                <span class="dashicons dashicons-warning" style="color:#dba617;font-size:20px;"></span>
                <h2>Warn a User</h2>
            </div>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'cnw_take_action_warn' ); ?>
                <input type="hidden" name="action" value="cnw_take_action_warn">

                <table class="form-table">
                    <tr>
                        <th><label for="warn_user">User</label></th>
                        <td>
                            <select name="user_id" id="warn_user" class="cnw-user-search" required>
                                <option value=""></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="warn_reason">Reason</label></th>
                        <td><textarea name="reason" id="warn_reason" rows="3" class="large-text" required placeholder="Reason for warning..."></textarea></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button" style="background:#dba617;border-color:#dba617;color:#fff;" value="Send Warning">
                </p>
            </form>
        </div>
    </div>
</div>

<style>
.cnw-action-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 16px;
}
.cnw-action-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px 24px;
}
.cnw-action-card--suspend { border-top: 3px solid #d63638; }
.cnw-action-card--warn { border-top: 3px solid #dba617; }
.cnw-action-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.cnw-action-card-header h2 {
    margin: 0;
    font-size: 16px;
}
.cnw-action-card .form-table th {
    width: 80px;
    font-weight: 600;
    padding: 10px 0;
}
.cnw-action-card .form-table td {
    padding: 10px 0;
}
.cnw-user-search {
    width: 100%;
    max-width: 100%;
}
@media (max-width: 960px) {
    .cnw-action-grid { grid-template-columns: 1fr; }
}
</style>

<script>
jQuery(function($) {
    $('.cnw-user-search').select2({
        width: '100%',
        placeholder: 'Search by name or email...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    action: 'cnw_search_users',
                    q: params.term
                };
            },
            processResults: function(data) {
                return { results: data.results || [] };
            },
            cache: true
        }
    });
});
</script>
