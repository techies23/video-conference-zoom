<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
    <h2><?php _e( "Assign Zoom Host Users to your WordPress Users", "video-conferencing-with-zoom-api" ); ?></h2>
    <div id="message" class="notice notice-warning">
        <p>
            <strong><?php _e( 'This section allows you to assign "Zoom" host to your users from WordPress. If you add a WordPress user to a Zoom Host from here then at the meeting creation that user will not see list of other host on his/her side except for administrator.', 'video-conferencing-with-zoom-api' ); ?>
                !!!</strong></p>
    </div>
    <div class="message">
		<?php
		$message = self::get_message();
		if ( isset( $message ) && ! empty( $message ) ) {
			echo $message;
		}
		?>
    </div>

    <div class="zvc_listing_table">
        <form action="" method="POST">
			<?php wp_nonce_field( '_zoom_assign_hostid_nonce_action', '_zoom_assign_hostid_nonce' ); ?>
            <table id="vczapi-get-host-users-wp" class="display">
                <thead>
                <tr>
                    <th style="text-align:left;"><?php _e( 'ID', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th style="text-align:left;"><?php _e( 'Email', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th style="text-align:left;"><?php _e( 'Full Name', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th style="text-align:left;"><?php _e( 'Host ID', 'video-conferencing-with-zoom-api' ); ?></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
            <p class="submit"><input type="submit" name="saving_host_id" class="button button-primary" value="<?php _e( 'Save', 'video-conferencing-with-zoom-api' ); ?>"></p>
        </form>
    </div>
</div>
