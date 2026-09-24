<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args  = ! empty( $args ) && is_array( $args ) ? $args : array();
$users = ! empty( $args['users'] ) ? $args['users'] : array();
?>
<div class="wrap vczapi-sync">
    <h1 class="vczapi-sync__title"><?php esc_html_e( "Import your Zoom Meetings/Webinars to this site", "video-conferencing-with-zoom-api" ); ?></h1>
    <?php
    if ( empty( $users ) ) {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                _e( 'Zoom Users have not been synced properly. Sync from Zoom Events > Users page first and come back here.', 'video-conferencing-with-zoom-api' );
                ?>
            </p>
        </div>
        <?php
        return;
    }
    ?>

    <?php if ( ! vczapi_pro_version_active() ) : ?>
        <div class="notice notice-info">
            <p>
                <?php
                _e( 'Sync your Zoom events directly from your Zoom account to this site. Synced meetings will appear under <strong>Zoom Events → All Events</strong>. To import webinars you will require PRO version.', 'video-conferencing-with-zoom-api' );
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="vczapi-sync__admin-wrapper">
        <?php if ( ! vczapi_pro_version_active() ) : ?>
            <div class="vczapi-sync__controls">
                <label for="vczapi-sync-user-id" class="vczapi-sync__label"><?php esc_html_e( "Choose a Zoom User", "video-conferencing-with-zoom-api" ); ?></label>
                <?php
                \Codemanas\VczApi\Helpers\FormHelper::fields(
                        'user_id',
                        [
                                "type"              => "select",
                                "input_class"       => [ "vczapi-choices", "vczapi-sync__user-id" ],
                                "id"                => "vczapi-sync-user-id",
                                "options"           => $users,
                                'custom_attributes' => [
                                        'data-api-action'  => 'vczapi_get_user_by_query',
                                        'data-placeholder' => __( 'Search host by name or email...', 'video-conferencing-with-zoom-api' ),
                                        'data-searchable'  => 'true',
                                ],
                        ],
                        array_key_first( $users )
                );
                ?>
                <button type="button" id="vczapi-sync-fetch" class="button button-primary vczapi-sync__fetch-btn">
                    <?php esc_html_e( 'Fetch Meetings', 'video-conferencing-with-zoom-api' ); ?>
                </button>
            </div>

            <div id="vczapi-sync-status" class="vczapi-sync__status" aria-live="polite"></div>
            <div class="vczapi-sync__results"></div>
        <?php endif; ?>
        <?php do_action( 'vczapi_admin_after_sync_html', $users ); ?>
    </div>
</div>