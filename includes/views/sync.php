<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$users = video_conferencing_zoom_api_get_user_transients();
?>
<div class="wrap">
    <h3><?php _e( "Sync your Live Zoom Meetings/Webinars to your site", "video-conferencing-with-zoom-api" ); ?></h3>
	<?php if ( ! vczapi_pro_version_active() ) { ?>
        <div class="vczapi-notification">
            <p><?php _e( "Sync your Zoom events from your Zoom Account to this site directly. Synced meetings will be inside Zoom Events > All Events page. You will require PRO version of the plugin in order to import your Zoom Webinars.", "video-conferencing-with-zoom-api" ); ?></p>
        </div>
	<?php } ?>

    <div class="vczapi-sync-admin-wrapper">
		<?php if ( ! vczapi_pro_version_active() ) { ?>
            <form action="" method="POST">
                <label><?php _e( "Choose a Zoom User", "video-conferencing-with-zoom-api" ); ?></label> : <select class="vczapi-sync-user-id zvc-hacking-select">
                    <option value=""><?php _e( 'Select a User', 'video-conferencing-with-zoom-api' ); ?></option>
					<?php foreach ( $users as $user ) { ?>
                        <option value="<?php echo $user->id; ?>"><?php echo $user->first_name . ' ( ' . $user->email . ' )'; ?></option>
					<?php } ?>
                </select>
            </form>

            <div class="vczapi-status-notification"></div>
            <div class="results"></div>
		<?php } ?>
		<?php do_action( 'vczapi_admin_after_sync_html', $users ); ?>
    </div>
</div>
