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
            <button id="vczapi-sync-users" class="button button-primary">
                <?php esc_html_e( 'Sync Users from Zoom', 'video-conferencing-with-zoom-api' ); ?>
            </button>
        </div>
    </div>

    <div class="vczapi-users__sync-status">
        <?php if ( ! empty( $last_synced ) ) : ?>
            <p>
                <?php
                printf(
                        esc_html__( 'Last synced: %1$s (%2$d users cached)', 'video-conferencing-with-zoom-api' ),
                        esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_synced ) ) ),
                        (int) $user_count
                );
                ?>
            </p>
        <?php else : ?>
            <p>
                <?php esc_html_e( 'No cached users yet. Click "Sync Users from Zoom" to pull your Zoom users into the local cache table.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        <?php endif; ?>
        <span id="vczapi-sync-status" class="vczapi-users__sync-status-message"></span>
    </div>

    <div class="vczapi-users__table-container">
        <table class="widefat striped vczapi-users__table">
            <thead class="vczapi-users__table-head">
            <tr class="vczapi-users__row vczapi-users__row--header">
                <th class="vczapi-users__cell vczapi-users__cell--header vczapi-users__cell--align-left"><?php esc_html_e( 'SN', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-users__cell vczapi-users__cell--header vczapi-users__cell--align-left"><?php esc_html_e( 'User ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-users__cell vczapi-users__cell--header vczapi-users__cell--align-left"><?php esc_html_e( 'Email', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-users__cell vczapi-users__cell--header vczapi-users__cell--align-left"><?php esc_html_e( 'Name', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-users__cell vczapi-users__cell--header vczapi-users__cell--align-left"><?php esc_html_e( 'Created On', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-users__cell vczapi-users__cell--header vczapi-users__cell--align-left"><?php esc_html_e( 'Last Login', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-users__cell vczapi-users__cell--header vczapi-users__cell--align-left"><?php esc_html_e( 'Last Client', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody class="vczapi-users__table-body">
            <?php
            $count = 1;
            if ( ! empty( $users ) ) :
                foreach ( $users as $user ) :
                    $name = trim( ( $user->first_name ?? '' ) . ' ' . ( $user->last_name ?? '' ) );
                    ?>
                    <tr class="vczapi-users__row">
                        <td class="vczapi-users__cell"><?php echo esc_html( $count ++ ); ?></td>
                        <td class="vczapi-users__cell"><?php echo esc_html( $user->id ); ?></td>
                        <td class="vczapi-users__cell"><?php echo esc_html( $user->email ); ?></td>
                        <td class="vczapi-users__cell"><?php echo esc_html( $name ); ?></td>
                        <td class="vczapi-users__cell"><?php echo ! empty( $user->created_at ) ? esc_html( date( 'F j, Y, g:i a', strtotime( $user->created_at ) ) ) : 'N/A'; ?></td>
                        <td class="vczapi-users__cell"><?php echo ! empty( $user->last_login_time ) ? esc_html( date( 'F j, Y, g:i a', strtotime( $user->last_login_time ) ) ) : 'N/A'; ?></td>
                        <td class="vczapi-users__cell"><?php echo ! empty( $user->last_client_version ) ? esc_html( $user->last_client_version ) : 'N/A'; ?></td>
                    </tr>
                <?php
                endforeach;
            else :
                ?>
                <tr class="vczapi-users__row vczapi-users__row--empty">
                    <td class="vczapi-users__cell vczapi-users__cell--empty" colspan="7"><?php esc_html_e( 'No users found.', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ( $pagination_links ) : ?>
        <div class="tablenav tablenav--bottom vczapi-users__pagination">
            <div class="tablenav-pages vczapi-users__pagination-pages">
                <span class="displaying-num vczapi-users__pagination-count">
                    <?php
                    printf( esc_html__( 'Page %1$d of %2$d', 'video-conferencing-with-zoom-api' ), $current_page, $page_count );
                    ?>
                </span>
                <span class="pagination-links vczapi-users__pagination-links">
                    <?php echo $pagination_links; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>