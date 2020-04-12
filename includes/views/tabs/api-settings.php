<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="zvc-cover" style="display: none;"></div>
<div class="zvc-row" style="margin-top:10px;">
    <div class="zvc-position-floater-left" style="width: 70%;margin-right:10px;border-top:1px solid #ccc;">
        <h3><?php _e( 'Please follow', 'video-conferencing-with-zoom-api' ) ?>
            <a target="_blank"
               href="<?php echo ZVC_PLUGIN_AUTHOR; ?>/zoom-conference-wp-plugin-documentation/"><?php _e( 'this guide', 'video-conferencing-with-zoom-api' ) ?> </a> <?php _e( 'to generate the below API values from your Zoom account', 'video-conferencing-with-zoom-api' ) ?>
        </h3>

        <form action="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings" method="POST">
			<?php wp_nonce_field( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' ); ?>
            <table class="form-table">
                <tbody>

				<?php
                //backward compatibility for when $zoom_cnnnection_opt is not saved
				$zoom_connection_opt = ! empty( $zoom_connection_opt ) ? $zoom_connection_opt : 'jwt';
				if ( $zoom_connection_opt === 'oauth' ) {

					$tr_oauth_opt_class = 'tr-oauth--show';
					$tr_jwt_opt_class   = '';

				} else if ( $zoom_connection_opt === 'jwt' ) {

					$tr_oauth_opt_class = '';
					$tr_jwt_opt_class   = 'tr-jwt--show';
				}
				?>

                <tr class="tr-connection-opt">
                    <th><label><?php _e( 'Connection Options', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <label for="zoom_connection_opt_oauth"><input type="radio" id="zoom_connection_opt_oauth" name="zoom_connection_opt"
                                                                      value="oauth" <?php checked( 'oauth', $zoom_connection_opt, true ); ?>>OAuth</label>


                        <label for="zoom_connection_opt_jwt"><input type="radio" id="zoom_connection_opt_jwt" name="zoom_connection_opt" value="jwt" <?php checked( 'jwt', $zoom_connection_opt, true ); ?>> JWT</label>

                    </td>
                </tr>

                <!-- Oauth Form -->

                <tr class="tr-oauth <?php echo $tr_oauth_opt_class; ?>">
                    <th colspan="2">

						<?php

						if ( '' == $zoom_oauth_user_info['vczapi_oauth_zoom_user_token_info'] ) { ?>

                            <!-- if not connected show Connect with Zoom -->
                            <a class="connect-button" href="<?php echo esc_url( $zoom_oauth_url ); ?>">
                                <img width="25" height="25" src="<?php echo ZVC_PLUGIN_IMAGES_PATH . '/connect-zoom-icon.png'; ?>">
                                <span>Connect Zoom</span>
                            </a>

							<?php

						} else {

							$revoke_url = admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' );
							$revoke_url = add_query_arg( array( 'revoke_access' => 'true' ), $revoke_url );

							?>

                            <!-- if connected show Revoke Access -->
                            <a class="connect-button" href="<?php echo esc_url( $revoke_url ); ?>">
                                <img width="25" height="25" src="<?php echo ZVC_PLUGIN_IMAGES_PATH . '/revoke-zoom-icon.png'; ?>">
                                <span>Revoke Access</span>
                            </a>
						<?php } ?>

                    </th>
                </tr>
                <!-- Oauth Form Ends -->

                <!-- JWT Form -->
                <tr class="tr-jwt <?php echo $tr_jwt_opt_class; ?>">
                    <th><label><?php _e( 'API Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="password" style="width: 400px;" name="zoom_api_key" id="zoom_api_key"
                               value="<?php echo ! empty( $zoom_api_key ) ? esc_html( $zoom_api_key ) : ''; ?>">
                        <a href="javascript:void(0);" class="toggle-api">Show</a></td>
                </tr>
                <tr class="tr-jwt <?php echo $tr_jwt_opt_class; ?>">
                    <th><label><?php _e( 'API Secret Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="password" style="width: 400px;" name="zoom_api_secret" id="zoom_api_secret"
                               value="<?php echo ! empty( $zoom_api_secret ) ? esc_html( $zoom_api_secret ) : ''; ?>">
                        <a href="javascript:void(0);" class="toggle-secret">Show</a></td>
                </tr>
                <tr class="enabled-vanity-url tr-jwt <?php echo $tr_jwt_opt_class; ?>">
                    <th><label><?php _e( 'Vanity URL', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="url" name="vanity_url" class="regular-text"
                               value="<?php echo ( $zoom_vanity_url ) ? esc_html( $zoom_vanity_url ) : ''; ?>"
                               placeholder="https://example.zoom.us">
                        <p class="description"><?php _e( 'If you are using Zoom Vanity URL then please insert it here else leave it empty.', 'video-conferencing-with-zoom-api' ); ?></p>
                        <a href="https://support.zoom.us/hc/en-us/articles/215062646-Guidelines-for-Vanity-URL-Requests"><?php _e( 'Read more about Vanity
                                URLs', 'video-conferencing-with-zoom-api' ); ?></a>
                    </td>
                </tr>
                <!-- JWT Form Ends -->

                <tr class="enabled-join-links-after-mtg-end">
                    <th><label><?php _e( 'Show Past Join Link ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="meeting_end_join_link" <?php checked( $past_join_links, 'on' ); ?>>
                        <span class="description"><?php _e( 'This will show join meeting links on frontend even after meeting time is already past.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr class="show-zoom-authors">
                    <th><label><?php _e( 'Show Zoom Author ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox"
                               name="meeting_show_zoom_author_original" <?php checked( $zoom_author_show, 'on' ); ?>>
                        <span class="description"><?php _e( 'Checking this show Zoom original Author in single meetings page which are created from', 'video-conferencing-with-zoom-api' ); ?>
                                <a href="<?php echo esc_url( admin_url( '/edit.php?post_type=zoom-meetings' ) ); ?>">Zoom Meetings</a></span>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Meeting Started Text', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="text" style="width: 400px;" name="zoom_api_meeting_started_text"
                               id="zoom_api_meeting_started_text"
                               value="<?php echo ! empty( $zoom_started ) ? esc_html( $zoom_started ) : ''; ?>"
                               placeholder="Leave empty for default text">
                </tr>
                <tr>
                    <th><label><?php _e( 'Meeting going to start Text', 'video-conferencing-with-zoom-api' ); ?></label>
                    </th>
                    <td>
                        <input type="text" style="width: 400px;" name="zoom_api_meeting_goingtostart_text"
                               id="zoom_api_meeting_goingtostart_text"
                               value="<?php echo ! empty( $zoom_going_to_start ) ? esc_html( $zoom_going_to_start ) : ''; ?>"
                               placeholder="Leave empty for default text">
                </tr>
                <tr>
                    <th><label><?php _e( 'Meeting Ended Text', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="text" style="width: 400px;" name="zoom_api_meeting_ended_text"
                               id="zoom_api_meeting_ended_text"
                               value="<?php echo ! empty( $zoom_ended ) ? esc_html( $zoom_ended ) : ''; ?>"
                               placeholder="Leave empty for default text">
                </tr>
                </tbody>
            </table>
            <h3 class="description" style="color:red;"><?php _e( 'After you enter your keys. Do save changes before doing "Check API Connection".', 'video-conferencing-with-zoom-api' ); ?></h3>
            <p class="submit">
                <input type="submit" name="save_zoom_settings" id="submit" class="button button-primary"
                       value="<?php esc_html_e( 'Save Changes', 'video-conferencing-with-zoom-api' ); ?>">
                <a href="javascript:void(0);"
                   class="button button-primary check-api-connection"><?php esc_html_e( 'Check API Connection', 'video-conferencing-with-zoom-api' ); ?></a>
            </p>
        </form>
    </div>
    <div class="zvc-position-floater-right">
        <ul class="zvc-information-sec">
            <li>
                <a target="_blank"
                   href="https://zoom.codemanas.com"><?php _e( 'Documentation', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li>
                <a target="_blank"
                   href="<?php echo ZVC_PLUGIN_AUTHOR; ?>/say-hello/"><?php _e( 'Contact for additional Support', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li><a target="_blank"
                   href="https://deepenbajracharya.com.np"><?php _e( 'Developer', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li>
                <a target="_blank"
                   href="<?php echo admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' ); ?>"><?php _e( 'Addons', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
        </ul>
        <div class="zvc-information-sec">
            <h3>WooCommerce Addon</h3>
            <p>Integrate your Zoom Meetings directly to WooCommerce or WooCommerce booking products. Zoom Integration
                for WooCommerce allows you to
                automate your zoom meetings directly from your WordPress dashboard by linking zoom meetings to your
                WooCommerce or WooCommerce Booking
                products automatically. Users will receive join links in their booking confirmation emails.</p>
            <p><a href="https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/"
                  class="button button-primary">More Details</a>
            </p>
        </div>
        <div class="zvc-information-sec">
            <h3>Need Idle Auto logout ?</h3>
            <p>Protect your WordPress users' sessions from shoulder surfers and snoopers!</p>
            <p>Use the Inactive Logout plugin to automatically terminate idle user sessions, thus protecting the site if
                the users leave unattended
                sessions.</p>
            <p>
                <a target="_blank"
                   href="https://wordpress.org/plugins/inactive-logout/"><?php _e( 'Try inactive logout', 'video-conferencing-with-zoom-api' ); ?></a>
        </div>
    </div>
</div>
