<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$oauth = \Codemanas\VczApi\Includes\Api\OAuth::instance();
$keys  = \Codemanas\VczApi\Includes\Fields::get_option( 'jwt_keys' );
?>
<div class="vczapi-row">
    <div class="col-12">
        <div class="vczapi-white-background">
            <h3><?php _e( 'Please follow', 'video-conferencing-with-zoom-api' ) ?>
                <a target="_blank" href="<?php echo ZVC_PLUGIN_AUTHOR; ?>/zoom-conference-wp-plugin-documentation/"><?php _e( 'this guide', 'video-conferencing-with-zoom-api' ) ?> </a> <?php _e( 'to generate the below API values from your Zoom account', 'video-conferencing-with-zoom-api' ) ?>
            </h3>

            <a href="<?php echo $oauth->getUserAuthenticationUrl(); ?>" class="button button-hero button-primary"><?php _e( 'Connect Your Account with OAuth (BETA)', 'video-conferencing-with-zoom-api' ); ?></a>
            <a href="javascript:void(0);" class="button button-hero button-primary"><?php _e( 'Connect Your Account with JWT', 'video-conferencing-with-zoom-api' ); ?></a>
            <div class="vczapi-api-connection-oauth">
				<?php
				if ( ! empty( $oauth->userData ) ) {
					$connected_user_info = $oauth->getMyInfo();
					if ( isset( $connected_user_info->code ) && $connected_user_info->code == '124' ) {
						?>
                        <a href="<?php echo $oauth->getUserAuthenticationUrl(); ?>" class="button button-hero button-primary"><?php _e( 'Connect Your Account with OAuth (BETA)', 'video-conferencing-with-zoom-api' ); ?></a>
						<?php
					} else if ( isset( $connected_user_info->code ) ) {
						echo '<h3>' . __( 'ERROR', 'video-conferencing-with-zoom-api' ) . ' :: ' . $connected_user_info->message . ' :: ' . __( 'There seems to be an error connecting with zoom API. Please try refreshing your browser, check debug logs or contact support.', 'video-conferencing-with-zoom-api' ) . '</h3>';
					} else {
						?>
                        <h3><?php _e( 'You are Connected to Zoom', 'video-conferencing-with-zoom-api' ); ?></h3>
                        <div class="vczapi-row">
                            <div class="col-12" style="display: flex; margin: 20px 0;">
								<?php if ( ! empty( $connected_user_info->pic_url ) ) { ?>
                                    <img src="<?php echo $connected_user_info->pic_url; ?>" style="border-radius:50%;" alt="Zoom Image">
								<?php } ?>
                                <div style="padding-left:20px">
                                    <ul>
                                        <li><strong><?php _e( 'Host ID', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $connected_user_info->id; ?></li>
                                        <li><strong><?php _e( 'Name', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $connected_user_info->first_name . ' ' . $connected_user_info->last_name; ?></li>
                                        <li><strong><?php _e( 'Email', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $connected_user_info->email; ?></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-12">
                                <a id="vczapi-remove-oauth-access" href="<?php echo $oauth->zoom_verify_listener . '&revoke_access_token=true'; ?>" class="button button-hero button-primary" style="margin-top:10px;"><?php _e( 'Disconnect your account', 'video-conferencing-with-zoom-api' ); ?></a>
                            </div>
                        </div>
						<?php
					}
				}
				?>
            </div>
            <div class="vczapi-api-connection-jwt" style="margin-top:20px;">
                <form method="POST" id="vczapi-api-connection-form">
                    <table class="form-table">
                        <tbody>
                        <tr class="vczapi-client-key">
                            <th><label><?php _e( 'API Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="password" style="width: 400px;" name="zoom_api_key" id="zoom_api_key" value="<?php echo ! empty( $keys ) ? esc_html( $keys['api_key'] ) : ''; ?>">
                                <a href="javascript:void(0);" class="toggle-api">Show</a></td>
                        </tr>
                        <tr class="vczapi-client-secret">
                            <th><label><?php _e( 'API Secret Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="password" style="width: 400px;" name="zoom_api_secret" id="zoom_api_secret" value="<?php echo ! empty( $keys ) ? esc_html( $keys['api_secret'] ) : ''; ?>">
                                <a href="javascript:void(0);" class="toggle-secret">Show</a></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" name="save_zoom_api_connection" id="save-jwt-connection" class="button button-primary" value="<?php esc_html_e( 'Save', 'video-conferencing-with-zoom-api' ); ?>">
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
