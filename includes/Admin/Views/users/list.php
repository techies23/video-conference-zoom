<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args             = ! empty( $args ) && is_array( $args ) ? $args : [];
$current_page     = ! empty( $args['current_page'] ) ? (int) $args['current_page'] : 1;
$page_count       = ! empty( $args['page_count'] ) ? (int) $args['page_count'] : 1;
$users            = ! empty( $args['data'] ) ? $args['data'] : array();
$error            = ! empty( $args['error'] ) ? $args['error'] : null;
$last_synced      = ! empty( $args['last_synced'] ) ? $args['last_synced'] : null;
$user_count       = ! empty( $args['user_count'] ) ? (int) $args['user_count'] : 0;
$pagination_links = paginate_links( array(
        'base'      => add_query_arg( 'pg', '%#%' ),
        'format'    => '',
        'current'   => max( 1, $current_page ),
        'total'     => max( 1, $page_count ),
        'type'      => 'plain',
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
) );
?>
<div class="wrap vczapi-users">
    <div class="vczapi-users__header">
        <div class="vczapi-users__header-title">
            <h1 class="vczapi-users__title"><?php esc_html_e( "Zoom Users", "video-conferencing-with-zoom-api" ); ?></h1>
        </div>

        <div class="vczapi-users__header-actions">
            <button id="vczapi-sync-users" class="button button-primary vczapi-users__sync-btn">
                <?php esc_html_e( 'Sync Users from Zoom', 'video-conferencing-with-zoom-api' ); ?>
            </button>
        </div>
    </div>

    <div class="vczapi-users__sync-status">
        <?php if ( ! empty( $last_synced ) ) : ?>
            <p class="vczapi-users__sync-text">
                <?php
                printf(
                        esc_html__( 'Last synced: %1$s (%2$d users cached)', 'video-conferencing-with-zoom-api' ),
                        esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_synced ) ) ),
                        (int) $user_count
                );
                ?>
            </p>
        <?php else : ?>
            <p class="vczapi-users__sync-text">
                <?php esc_html_e( 'No cached users yet. Click "Sync Users from Zoom" to pull your Zoom users into the local cache table.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        <?php endif; ?>
        <span id="vczapi-sync-status" class="vczapi-users__sync-status-message"></span>
    </div>

    <!-- Reusable Table Component Wrapper -->
    <div class="vczapi-table-wrapper vczapi-users__table-wrapper">
        <div class="vczapi-users-table"></div>
    </div>
</div>