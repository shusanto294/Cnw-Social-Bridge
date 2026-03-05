<?php
/**
 * Shared pagination & search helpers for admin list pages.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render a search box + pagination nav for an admin list page.
 *
 * @param string $page        The admin page slug (e.g. 'cnw-threads').
 * @param string $search      Current search term.
 * @param int    $current_page Current page number.
 * @param int    $total_pages  Total number of pages.
 * @param int    $total        Total number of rows.
 * @param int    $per_page     Items per page.
 */
function cnw_admin_search_box( $page, $search ) {
    ?>
    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="cnw-search-form">
        <input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>">
        <label class="screen-reader-text" for="cnw-search-input">Search</label>
        <input type="search" id="cnw-search-input" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search...">
        <button type="submit" class="button">Search</button>
        <?php if ( $search ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page ) ); ?>" class="button">Clear</a>
        <?php endif; ?>
    </form>
    <?php
}

function cnw_admin_pagination( $page, $current_page, $total_pages, $total, $search = '' ) {
    if ( $total_pages <= 1 ) {
        echo '<div class="cnw-pagination-info">' . esc_html( $total ) . ' item(s)</div>';
        return;
    }
    $base = admin_url( 'admin.php?page=' . $page );
    if ( $search ) {
        $base = add_query_arg( 's', rawurlencode( $search ), $base );
    }
    ?>
    <div class="cnw-pagination">
        <span class="cnw-pagination-info"><?php echo esc_html( $total ); ?> item(s) &mdash; Page <?php echo esc_html( $current_page ); ?> of <?php echo esc_html( $total_pages ); ?></span>
        <span class="cnw-pagination-links">
            <?php if ( $current_page > 1 ) : ?>
                <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', 1, $base ) ); ?>">&laquo;</a>
                <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $current_page - 1, $base ) ); ?>">&lsaquo;</a>
            <?php else : ?>
                <span class="button disabled">&laquo;</span>
                <span class="button disabled">&lsaquo;</span>
            <?php endif; ?>
            <?php if ( $current_page < $total_pages ) : ?>
                <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $current_page + 1, $base ) ); ?>">&rsaquo;</a>
                <a class="button" href="<?php echo esc_url( add_query_arg( 'paged', $total_pages, $base ) ); ?>">&raquo;</a>
            <?php else : ?>
                <span class="button disabled">&rsaquo;</span>
                <span class="button disabled">&raquo;</span>
            <?php endif; ?>
        </span>
    </div>
    <?php
}
