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
               href="<?php echo 'https://zoomdocs.codemanas.com/oauth/'; ?>"><?php _e( 'this guide', 'video-conferencing-with-zoom-api' ) ?> </a> <?php _e( 'to generate the below API values from your Zoom account', 'video-conferencing-with-zoom-api' ) ?>
        </h3>

        <form action="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings" method="POST">
			<?php wp_nonce_field( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' ); ?>
            <table class="form-table">
                <tbody>

				<?php
                //backward compatibility for when $zoom_cnnnection_opt is not saved
				$zoom_connection_opt = ! empty( $zoom_connection_opt ) ? $zoom_connection_opt : 'jwt';
//
				?>

                <tr class="tr-connection-opt">
                    <th colspan="2">
                        <label><?php _e( 'Connect with Zoom OAuth', 'video-conferencing-with-zoom-api' ); ?></label>
                    </th>
                </tr>

                <!-- Oauth Form -->
                <tr class="tr-oauth">
                    <td colspan="2">

						<?php

						if ( '' == $zoom_oauth_user_info['vczapi_oauth_zoom_user_token_info'] ) { ?>

                            <!-- if not connected show Connect with Zoom -->
                            <small><?php _e( 'Please click on the button below to connect with your Zoom account.', 'video-conferencing-with-zoom-api'); ?></small><br><br>
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

                    </td>
                </tr>
                <!-- Oauth Form Ends -->

                <tr class="enabled-vanity-url tr-jwt <?php echo $tr_jwt_opt_class; ?>">
                    <th><label><?php _e( 'Vanity URL', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="url" name="vanity_url" class="regular-text" value="<?php echo ( $zoom_vanity_url ) ? esc_html( $zoom_vanity_url ) : ''; ?>" placeholder="https://example.zoom.us">
                        <p class="description"><?php _e( 'If you are using Zoom Vanity URL then please insert it here else leave it empty.', 'video-conferencing-with-zoom-api' ); ?></p>
                        <a href="https://support.zoom.us/hc/en-us/articles/215062646-Guidelines-for-Vanity-URL-Requests"><?php _e( 'Read more about Vanity
                                URLs', 'video-conferencing-with-zoom-api' ); ?></a>
                    </td>
                </tr>

                <tr>
                    <th><label><?php _e( 'Hide Join Links for Non-Loggedin ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="hide_join_links_non_loggedin_users" <?php ! empty( $hide_join_link_nloggedusers ) ? checked( $hide_join_link_nloggedusers, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'Checking this option will hide join links from your shortcode for non-loggedin users.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Disable Embed password in Link ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="embed_password_join_link" <?php ! empty( $embed_password_join_link ) ? checked( $embed_password_join_link, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'Meeting password will not be included in the invite link to allow participants to join with just one click without having to enter the password.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr class="enabled-join-links-after-mtg-end">
                    <th><label><?php _e( 'Show Past Join Link ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="meeting_end_join_link" <?php ! empty( $past_join_links ) ? checked( $past_join_links, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'This will show join meeting links on frontend even after meeting time is already past.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr class="show-zoom-authors">
                    <th><label><?php _e( 'Show Zoom Author ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="meeting_show_zoom_author_original" <?php ! empty( $zoom_author_show ) ? checked( $zoom_author_show, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'Checking this show Zoom original Author in single meetings page which are created from', 'video-conferencing-with-zoom-api' ); ?>
                                <a href="<?php echo esc_url( admin_url( '/edit.php?post_type=zoom-meetings' ) ); ?>">Zoom Meetings</a></span>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Meeting Started Text', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="text" style="width: 400px;" name="zoom_api_meeting_started_text" id="zoom_api_meeting_started_text" value="<?php echo ! empty( $zoom_started ) ? esc_html( $zoom_started ) : ''; ?>" placeholder="Leave empty for default text">
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Meeting going to start Text', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="text" style="width: 400px;" name="zoom_api_meeting_goingtostart_text" id="zoom_api_meeting_goingtostart_text" value="<?php echo ! empty( $zoom_going_to_start ) ? esc_html( $zoom_going_to_start ) : ''; ?>" placeholder="Leave empty for default text">
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Meeting Ended Text', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="text" style="width: 400px;" name="zoom_api_meeting_ended_text" id="zoom_api_meeting_ended_text" value="<?php echo ! empty( $zoom_ended ) ? esc_html( $zoom_ended ) : ''; ?>" placeholder="Leave empty for default text">
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'DateTime Format', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <div>
                            <input type="radio" value="LLLL" name="zoom_api_date_time_format" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'LLLL', false ) : 'checked'; ?> class="zoom_api_date_time_format">
                            Wednesday, May 6, 2020 05:00 PM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="lll" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'lll', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                            May 6, 2020 05:00 AM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="llll" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'llll', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                            Wed, May 6, 2020 05:00 AM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="L LT" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'L LT', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                            05/06/2020 03:00 PM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="l LT" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'l LT', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                            5/6/2020 03:00 PM
                        </div>
                        <p class="description"><?php _e( 'Change date time formats according to your choice. Please edit this properly. Failure to correctly put value will result in failure to show date in frontend.', 'video-conferencing-with-zoom-api' ); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="save_zoom_settings" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'video-conferencing-with-zoom-api' ); ?>">
                <a href="javascript:void(0);" class="button button-primary check-api-connection"><?php esc_html_e( 'Check API Connection', 'video-conferencing-with-zoom-api' ); ?></a>
            </p>
        </form>
    </div>
    <div class="zvc-position-floater-right">
        <ul class="zvc-information-sec">
            <li>
                <a target="_blank" href="https://zoom.codemanas.com"><?php _e( 'Documentation', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li>
                <a target="_blank" href="https://www.codemanas.com"><?php _e( 'Contact for additional Support', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li><a target="_blank" href="https://deepenbajracharya.com.np"><?php _e( 'Developer', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li>
                <a target="_blank" href="<?php echo admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' ); ?>"><?php _e( 'Addons', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
        </ul>
        <div class="zvc-information-sec">
            <h3>WooCommerce Addon</h3>
            <p>Integrate your Zoom Meetings directly to WooCommerce or WooCommerce booking products. Zoom Integration for WooCommerce allows you to
                automate your zoom meetings directly from your WordPress dashboard by linking zoom meetings to your WooCommerce or WooCommerce Booking
                products automatically. Users will receive join links in their booking confirmation emails.</p>
            <p><a href="https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/" class="button button-primary">More Details</a>
            </p>
        </div>
        <div class="zvc-information-sec">
            <h3>Need Idle Auto logout ?</h3>
            <p>Protect your WordPress users' sessions from shoulder surfers and snoopers!</p>
            <p>Use the Inactive Logout plugin to automatically terminate idle user sessions, thus protecting the site if the users leave unattended
                sessions.</p>
            <p>
                <a target="_blank" href="https://wordpress.org/plugins/inactive-logout/"><?php _e( 'Try inactive logout', 'video-conferencing-with-zoom-api' ); ?></a>
        </div>
    </div>
</div>
