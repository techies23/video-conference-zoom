<?php
if ( empty( $this->user_oauth_data ) ) {
	?>
    <a href="<?php echo $this->get_request_user_authentication_url(); ?>" class="button button-hero button-primary"><?php _e( 'Connect Your Account via OAuth (BETA)', 'video-conferencing-with-zoom-api' ); ?></a>
	<?php
} else {
	$connected_user_info = json_decode( $this->getMyInfo() );
	if ( isset( $connected_user_info->code ) && $connected_user_info->code == '124' ) {
		?>
        <a href="<?php echo $this->get_request_user_authentication_url(); ?>" class="button button-hero button-primary"><?php _e( 'Connect Your Account via OAuth (BETA)', 'video-conferencing-with-zoom-api' ); ?></a>
		<?php
	} elseif ( isset( $connected_user_info->code ) ) {
		echo '<h3>' . __( 'ERROR', 'video-conferencing-with-zoom-api' ) . ': ' . $connected_user_info->message . ' ' . __( 'There seems to be an error connecting with zoom API. Please try refreshing the browser.', 'video-conferencing-with-zoom-api' ) . '</h3>';
	} else {
		?>
        <h3>
			<?php
			if ( \vczapi_is_oauth_used_globally() ) {
				_e( 'This is the site wide Zoom Account', 'video-conferencing-with-zoom-api' );
			} else {
				_e( 'You are Connected to Zoom', 'video-conferencing-with-zoom-api' );
			}
			?>
        </h3>
        <div class="" style="display:flex;flex-wrap:wrap;">
			<?php if ( ! empty( $connected_user_info->pic_url ) ) { ?>
                <div class="" style="">
                    <img src="<?php echo $connected_user_info->pic_url; ?>" style="border-radius:50%;">
                </div>
			<?php } ?>
            <div class="" style="padding-left:20px">
                <ul>
                    <li><?php _e( 'Host ID', 'video-conferencing-with-zoom-api' ); ?>: <?php echo $connected_user_info->id; ?></li>
                    <li><?php _e( 'Name', 'video-conferencing-with-zoom-api' ); ?>: <?php echo $connected_user_info->first_name . ' ' . $connected_user_info->last_name; ?></li>
                    <li><?php _e( 'Email', 'video-conferencing-with-zoom-api' ); ?>: <?php echo $connected_user_info->email; ?></li>
                </ul>
            </div>
            <div style="padding-left:20px">
				<?php
				if ( \vczapi_is_oauth_used_globally() && ! \current_user_can( 'manage_options' ) ) {
					?>
                    <style>
                        #vczapi-remove-oauth-access {
                            display: none;
                        }
                    </style>
					<?php
				}
				?>
                <a id="vczapi-remove-oauth-access" href="<?php echo $this->zoom_verify_listener . '&revoke_access_token=true'; ?>" class="button button-hero button-primary" style="margin-top:10px;"><?php _e( 'Disconnect your account', 'video-conferencing-with-zoom-api' ); ?></a>
            </div>
        </div>
		<?php
	}
}