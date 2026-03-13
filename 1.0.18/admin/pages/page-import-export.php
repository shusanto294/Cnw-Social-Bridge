<?php
/**
 * Admin Import / Export page.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$import_id = isset( $_GET['import_ready'] ) ? sanitize_text_field( $_GET['import_ready'] ) : '';
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Import / Export', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'Export all plugin data as a ZIP file or import from a previous export. User IDs are remapped automatically — users are matched by email, or created if they don\'t exist.', 'cnw-social-bridge' ); ?></p>
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
            <p><?php esc_html_e( 'Download all plugin data (users, threads, replies, messages, tags, categories, votes, reputation, activity, notifications, saved threads, followed tags, settings) as a ZIP file.', 'cnw-social-bridge' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'cnw_export_data' ); ?>
                <input type="hidden" name="action" value="cnw_export_data" />
                <button type="submit" class="button button-primary"><span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:4px;"></span> <?php esc_html_e( 'Export Data (.zip)', 'cnw-social-bridge' ); ?></button>
            </form>
        </div>

        <!-- Import Card -->
        <div class="cnw-export-import-card">
            <h3><span class="dashicons dashicons-upload" style="margin-right:6px;color:#22a55b;"></span> <?php esc_html_e( 'Import', 'cnw-social-bridge' ); ?></h3>
            <p><?php esc_html_e( 'Upload a previously exported ZIP file (or legacy JSON). Data is imported in batches via AJAX and added to the existing database (not replaced). Users are matched by email first, then by login — new users are created if no match is found.', 'cnw-social-bridge' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" id="cnw-import-upload-form">
                <?php wp_nonce_field( 'cnw_import_data' ); ?>
                <input type="hidden" name="action" value="cnw_import_data" />
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <input type="file" name="cnw_import_file" accept=".zip,.json" required style="flex:1;min-width:200px;" />
                    <button type="submit" class="button button-secondary" onclick="return confirm('This will ADD imported data to your existing database. Continue?');"><span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span> <?php esc_html_e( 'Upload & Import', 'cnw-social-bridge' ); ?></button>
                </div>
            </form>
        </div>

    </div>

    <!-- Import Progress Panel (hidden until import begins) -->
    <div id="cnw-import-progress" style="display:none;margin-top:30px;">
        <div class="cnw-section">
            <h2 class="cnw-section-title"><?php esc_html_e( 'Import Progress', 'cnw-social-bridge' ); ?></h2>
            <div id="cnw-import-steps" style="max-width:600px;">
                <!-- Steps will be populated by JS -->
            </div>
            <div id="cnw-import-result" style="margin-top:16px;display:none;"></div>
        </div>
    </div>

    <!-- How it works -->
    <div class="cnw-section" style="margin-top:30px;">
        <h2 class="cnw-section-title"><?php esc_html_e( 'How Import Works', 'cnw-social-bridge' ); ?></h2>
        <table class="wp-list-table widefat fixed striped" style="max-width:720px;">
            <tbody>
                <tr>
                    <td style="width:180px;font-weight:600;">User Matching</td>
                    <td>Users are matched by email address first, then by login name. If no match is found, a new WordPress user is created with the <code>cnw_forum_member</code> role. All CNW user meta (phone, avatar, verified label, professional title, anonymous setting) is restored.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">ID Remapping</td>
                    <td>All internal IDs (threads, replies, tags, categories, votes, etc.) are remapped automatically. Foreign key references are updated so all relationships stay intact.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Batch Processing</td>
                    <td>Import runs in sequential AJAX batches: Users &rarr; Categories &rarr; Tags &rarr; Threads &rarr; Thread Tags &rarr; Replies &rarr; Messages &rarr; Votes &rarr; Reputation &rarr; Activity &rarr; Notifications &rarr; Settings &rarr; Finalize. This prevents timeouts on large datasets.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Self-references</td>
                    <td>Nested replies, message threads, and category hierarchies (parent&ndash;child) are preserved correctly.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">Data Mode</td>
                    <td>Import <strong>adds</strong> data — it does not delete or replace existing records. Run it on a fresh install for a clean migration.</td>
                </tr>
                <tr>
                    <td style="font-weight:600;">File Format</td>
                    <td>The export file is a <strong>.zip</strong> containing separate JSON files for each data type. Legacy single-file <strong>.json</strong> exports are also supported.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.cnw-import-step {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    margin-bottom: 6px;
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 13px;
    transition: all .2s;
}
.cnw-import-step.active {
    background: #f0f8ff;
    border-color: #3bbdd4;
}
.cnw-import-step.done {
    background: #f0faf4;
    border-color: #22a55b;
}
.cnw-import-step.error {
    background: #fef1f1;
    border-color: #d63638;
}
.cnw-import-step .step-icon {
    width: 24px;
    text-align: center;
    flex-shrink: 0;
}
.cnw-import-step .step-label {
    flex: 1;
    font-weight: 500;
}
.cnw-import-step .step-count {
    color: #666;
    font-size: 12px;
}
.cnw-import-step .spinner {
    float: none;
    margin: 0;
    visibility: visible;
}
</style>

<?php if ( $import_id ) : ?>
<script>
(function() {
    var importId = <?php echo wp_json_encode( $import_id ); ?>;
    var nonce    = <?php echo wp_json_encode( wp_create_nonce( 'cnw_import_ajax' ) ); ?>;
    var ajaxUrl  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

    var steps = [
        { key: 'users',         label: 'Users' },
        { key: 'categories',    label: 'Categories' },
        { key: 'tags',          label: 'Tags' },
        { key: 'threads',       label: 'Threads' },
        { key: 'thread_tags',   label: 'Thread Tags' },
        { key: 'replies',       label: 'Replies' },
        { key: 'messages',      label: 'Messages' },
        { key: 'votes',         label: 'Votes' },
        { key: 'reputation',    label: 'Reputation' },
        { key: 'activity',      label: 'Activity Logs' },
        { key: 'notifications', label: 'Notifications, Saved Threads & Followed Tags' },
        { key: 'reports',       label: 'Reports' },
        { key: 'connections',   label: 'Connections' },
        { key: 'restrictions',  label: 'Restrictions' },
        { key: 'warnings',      label: 'Warnings & Suspensions' },
        { key: 'settings',      label: 'Settings' },
        { key: 'finalize',      label: 'Finalize & Recalculate Totals' },
    ];

    var panel   = document.getElementById('cnw-import-progress');
    var stepsEl = document.getElementById('cnw-import-steps');
    var resultEl = document.getElementById('cnw-import-result');

    // Build step UI.
    panel.style.display = 'block';
    steps.forEach(function(s) {
        var div = document.createElement('div');
        div.className = 'cnw-import-step';
        div.id = 'step-' + s.key;
        div.innerHTML =
            '<span class="step-icon"><span class="dashicons dashicons-minus" style="color:#ccc;"></span></span>' +
            '<span class="step-label">' + s.label + '</span>' +
            '<span class="step-count"></span>';
        stepsEl.appendChild(div);
    });

    var currentIdx = 0;

    function runStep() {
        if (currentIdx >= steps.length) {
            resultEl.style.display = 'block';
            resultEl.innerHTML = '<div class="notice notice-success"><p><strong>Import completed successfully!</strong> All data has been imported with relationships preserved.</p></div>';
            return;
        }

        var step = steps[currentIdx];
        var el = document.getElementById('step-' + step.key);
        el.className = 'cnw-import-step active';
        el.querySelector('.step-icon').innerHTML = '<span class="spinner is-active" style="margin:0;"></span>';

        var formData = new FormData();
        formData.append('action', 'cnw_import_step');
        formData.append('_nonce', nonce);
        formData.append('import_id', importId);
        formData.append('step', step.key);

        fetch(ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                if (resp.success) {
                    el.className = 'cnw-import-step done';
                    el.querySelector('.step-icon').innerHTML = '<span class="dashicons dashicons-yes-alt" style="color:#22a55b;"></span>';
                    var cnt = resp.data.count || 0;
                    var label = cnt + ' record' + (cnt !== 1 ? 's' : '');
                    if (resp.data.debug) { label += ' — ' + resp.data.debug; }
                    el.querySelector('.step-count').textContent = label;
                    currentIdx++;
                    runStep();
                } else {
                    el.className = 'cnw-import-step error';
                    el.querySelector('.step-icon').innerHTML = '<span class="dashicons dashicons-dismiss" style="color:#d63638;"></span>';
                    el.querySelector('.step-count').textContent = resp.data || 'Error';
                    resultEl.style.display = 'block';
                    resultEl.innerHTML = '<div class="notice notice-error"><p><strong>Import stopped:</strong> ' + (resp.data || 'Unknown error') + '</p></div>';
                }
            })
            .catch(function(err) {
                el.className = 'cnw-import-step error';
                el.querySelector('.step-icon').innerHTML = '<span class="dashicons dashicons-dismiss" style="color:#d63638;"></span>';
                el.querySelector('.step-count').textContent = 'Network error';
                resultEl.style.display = 'block';
                resultEl.innerHTML = '<div class="notice notice-error"><p><strong>Import stopped:</strong> ' + err.message + '</p></div>';
            });
    }

    // Start the import chain.
    runStep();
})();
</script>
<?php endif; ?>
