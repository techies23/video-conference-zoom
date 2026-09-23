<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args  = ! empty( $args ) && is_array( $args ) ? $args : array();
$users = ! empty( $args['users'] ) ? $args['users'] : array();
?>
<div class="wrap vczapi-sync">
    <h1 class="vczapi-sync__title"><?php esc_html_e( "Sync your Live Zoom Meetings/Webinars to your site", "video-conferencing-with-zoom-api" ); ?></h1>

    <?php if ( ! vczapi_pro_version_active() ) : ?>
        <div class="notice notice-info">
            <p>
                <?php
                echo wp_kses_post(
                        __( 'Sync your Zoom events directly from your Zoom account to this site. Synced meetings will appear under <strong>Zoom Events → All Events</strong>. To import webinars you will require PRO version.', 'video-conferencing-with-zoom-api' )
                );
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="vczapi-sync__admin-wrapper">
        <?php if ( ! vczapi_pro_version_active() ) : ?>
            <div class="vczapi-sync__controls">
                <label for="vczapi-sync-user-id" class="vczapi-sync__label"><?php esc_html_e( "Choose a Zoom User", "video-conferencing-with-zoom-api" ); ?></label>
                <select id="vczapi-sync-user-id" name="user_id" class="vczapi-sync__user-id">
                    <option value=""><?php esc_html_e( 'Select a User', 'video-conferencing-with-zoom-api' ); ?></option>
                    <?php foreach ( $users as $user ) : ?>
                        <option value="<?php echo esc_attr( $user->id ); ?>"><?php echo esc_html( $user->first_name . ' ( ' . $user->email . ' )' ); ?></option>
                    <?php endforeach; ?>
                </select>
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