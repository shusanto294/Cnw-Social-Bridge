<?php
/**
 * Admin Import / Export page.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Import / Export', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'Export all plugin data as a JSON file or import from a previous export. User IDs are remapped automatically — users are matched by email, or created if they don\'t exist.', 'cnw-social-bridge' ); ?></p>
    </div>

    <?php if ( ! empty( $_GET['import_success'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Import completed successfully!', 'cnw-social-bridge' ); ?></strong> <?php esc_html_e( 'All data has been imported with user ID remapping.', 'cnw-social-bridge' ); ?></p></div>
    <?php endif; ?>
    <?php if ( ! empty( $_GET['import_error'] ) ) : ?>
        <div class="notice notice-error is-dismissible"><p><strong><?php esc_html_e( 'Import failed:', 'cnw-social-bridge' ); ?></strong> <?php echo esc_html( urldecode( $_GET['import_error'] ) ); ?></p></div>
    <?php endif; ?>

    <div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:20px;">

        <!-- Export Card -->
        <div class="cnw-export-import-card">
            <h3><span class="dashicons dashicons-download" style="margin-right:6px;color:#3bbdd4;"></span> <?php esc_html_e( 'Export', 'cnw-social-bridge' ); ?></h3>
            <p><?php esc_html_e( 'Download all plugin data (threads, replies, messages, tags, categories, votes, reputation, notifications, saved threads, followed tags) as a JSON file.', 'cnw-social-bridge' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'cnw_export_data' ); ?>
                <input type="hidden" name="action" value="cnw_export_data" />
                <button type="submit" class="button button-primary"><span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:4px;"></span> <?php esc_html_e( 'Export Data', 'cnw-social-bridge' ); ?></button>
            </form>
        </div>

        <!-- Import Card -->
        <div class="cnw-export-import-card">
            <h3><span class="dashicons dashicons-upload" style="margin-right:6px;color:#22a55b;"></span> <?php esc_html_e( 'Import', 'cnw-social-bridge' ); ?></h3>
            <p><?php esc_html_e( 'Upload a previously exported JSON file. Data will be added to the existing database (not replaced). Users are matched by email first, then by login — new users are created if no match is found.', 'cnw-social-bridge' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field( 'cnw_import_data' ); ?>
                <input type="hidden" name="action" value="cnw_import_data" />
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <input type="file" name="cnw_import_file" accept=".json" required style="flex:1;min-width:200px;" />
                    <button type="submit" class="button button-secondary" onclick="return confirm('This will ADD imported data to your existing database. Continue?');"><span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span> <?php esc_html_e( 'Import Data', 'cnw-social-bridge' ); ?></button>
                </div>
            </form>
        </div>

    </div>

    <!-- How it works -->
    <div class="cnw-section" style="margin-top:30px;">
        <h2 class="cnw-section-title"><?php esc_html_e( 'How Import Works', 'cnw-social-bridge' ); ?></h2>
        <table class="wp-list-table widefat fixed striped" style="max-width:720px;">
            <tbody>
                <tr>
                    <td style="width:180px;font-weight:600;">User Matching</td>
                    <td>Users are matched by email address first, then by login name. If no match is found, a new WordPress user is created with the <code>cnw_forum_member</code> role.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">ID Remapping</td>
                    <td>All internal IDs (threads, replies, tags, categories, votes, etc.) are remapped automatically. Foreign key references are updated so all relationships stay intact.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Self-references</td>
                    <td>Nested replies, message threads, and category hierarchies (parent&ndash;child) are preserved correctly.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Data Mode</td>
                    <td>Import <strong>adds</strong> data — it does not delete or replace existing records. Run it on a fresh install for a clean migration.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
