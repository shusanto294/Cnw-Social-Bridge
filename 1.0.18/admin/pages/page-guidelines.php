<?php
/**
 * Admin Guidelines — WYSIWYG editor for community guidelines content.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$msg     = sanitize_text_field( $_GET['msg'] ?? '' );
$content = get_option( 'cnw_community_guidelines_html', '' );

if ( empty( trim( $content ) ) ) {
    $content = '<p>Welcome to Social Bridge! Our community is built on mutual respect, professionalism, and a shared commitment to supporting one another. Please review the following guidelines to help us maintain a safe and productive space for all members.</p>

<h3>1. Be Respectful and Professional</h3>
<p>Treat every member with dignity and courtesy. Disagreements are natural, but always respond thoughtfully. Personal attacks, name-calling, and discriminatory language are not tolerated.</p>

<h3>2. Protect Confidentiality</h3>
<p>Never share personally identifiable information about clients, colleagues, or other members. When discussing cases, always use anonymized details. Respect the privacy and confidentiality of all individuals involved.</p>

<h3>3. Ask Thoughtful Questions</h3>
<p>Before posting, search the community to see if your question has been asked before. Provide context and details so others can give meaningful answers. Use appropriate tags to help organize discussions.</p>

<h3>4. Give Helpful Answers</h3>
<p>Share your knowledge and experience generously. Back up your advice with evidence-based practices when possible. If you\'re unsure, say so honestly rather than guessing. Mark helpful answers to assist future readers.</p>

<h3>5. Support the Community</h3>
<p>Upvote helpful content, report inappropriate behaviour, and welcome new members. A strong community is one where everyone contributes positively and supports each other\'s growth.</p>

<h3>6. No Spam or Self-Promotion</h3>
<p>Do not post advertisements, promotional material, or irrelevant links. Content should be relevant to social work practice, professional development, or community support topics.</p>

<h3>7. Report Violations</h3>
<p>If you encounter content that violates these guidelines, please use the Report an Issue page. Our moderators will review reports promptly and take appropriate action.</p>

<h3>Consequences of Violations</h3>
<p>Violations may result in content removal, temporary suspension, or permanent ban depending on severity. Repeated minor violations will be treated with increasing seriousness. Our moderators have final discretion on all enforcement decisions.</p>

<p><em>These guidelines may be updated from time to time. Continued use of the community constitutes acceptance of the current guidelines.</em></p>';
}
?>

<div class="wrap cnw-admin-wrap">
    <div class="cnw-admin-header">
        <h1 class="cnw-admin-title"><?php esc_html_e( 'Community Guidelines', 'cnw-social-bridge' ); ?></h1>
        <p class="cnw-admin-subtitle"><?php esc_html_e( 'Edit the content displayed on the Community Guidelines page.', 'cnw-social-bridge' ); ?></p>
    </div>

    <?php if ( $msg === 'saved' ) : ?>
        <div class="notice notice-success is-dismissible"><p>Guidelines saved successfully.</p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'cnw_save_guidelines' ); ?>
        <input type="hidden" name="action" value="cnw_save_guidelines">

        <?php
        wp_editor( $content, 'cnw_guidelines_content', array(
            'textarea_name' => 'cnw_guidelines_content',
            'media_buttons' => false,
            'textarea_rows' => 20,
            'teeny'         => false,
            'quicktags'     => true,
        ) );
        ?>

        <p class="submit">
            <input type="submit" class="button button-primary" value="Save Guidelines">
        </p>
    </form>
</div>
