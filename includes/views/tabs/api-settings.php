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
            <a target="_blank" href="<?php echo ZVC_PLUGIN_AUTHOR; ?>/zoom-conference-wp-plugin-documentation/"><?php _e( 'this guide', 'video-conferencing-with-zoom-api' ) ?> </a> <?php _e( 'to generate the below API values from your Zoom account', 'video-conferencing-with-zoom-api' ) ?>
        </h3>

        <form action="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings" method="POST">
			<?php wp_nonce_field( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' ); ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label><?php _e( 'API Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="password" style="width: 400px;" name="zoom_api_key" id="zoom_api_key" value="<?php echo ! empty( $zoom_api_key ) ? esc_html( $zoom_api_key ) : ''; ?>">
                        <a href="javascript:void(0);" class="toggle-api">Show</a></td>
                </tr>
                <tr>
                    <th><label><?php _e( 'API Secret Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="password" style="width: 400px;" name="zoom_api_secret" id="zoom_api_secret" value="<?php echo ! empty( $zoom_api_secret ) ? esc_html( $zoom_api_secret ) : ''; ?>">
                        <a href="javascript:void(0);" class="toggle-secret">Show</a></td>
                </tr>
                <tr class="enabled-vanity-url">
                    <th><label><?php _e( 'Vanity URL', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="url" name="vanity_url" class="regular-text" value="<?php echo ( $zoom_vanity_url ) ? esc_html( $zoom_vanity_url ) : ''; ?>" placeholder="https://example.zoom.us">
                        <p class="description"><?php _e( 'If you are using Zoom Vanity URL then please insert it here else leave it empty.', 'video-conferencing-with-zoom-api' ); ?></p>
                        <a href="https://support.zoom.us/hc/en-us/articles/215062646-Guidelines-for-Vanity-URL-Requests"><?php _e( 'Read more about Vanity
                                URLs', 'video-conferencing-with-zoom-api' ); ?></a>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Meetings Deletion ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="donot_delete_zom_meeting_also" <?php ! empty( $donot_delete_zoom ) ? checked( $donot_delete_zoom, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'Do not delete your meetings on Zoom, when you delete your meeting from Zoom Meetings > All Meetings page.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Disable Auto Password Generation ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="disable_auto_pwd_generation" <?php ! empty( $disable_auto_pwd_generation ) ? checked( $disable_auto_pwd_generation, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'Checking this option will disable auto password generation for new meetings which are created from Zoom meeting > Add new section.', 'video-conferencing-with-zoom-api' ); ?></span>
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
                    <th><label><?php _e( 'Disable Join via browser ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="meeting_disable_join_via_browser" <?php ! empty( $disable_jvb ) ? checked( $disable_jvb, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'Checking this will hide all Join via Browser Buttons.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Disable Email field when join via browser ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="meeting_show_email_field" <?php ! empty( $hide_email_jvb ) ? checked( $hide_email_jvb, 'on' ) : false; ?>>
                        <span class="description"><?php _e( 'Checking this show will hide email field in Join via Browser window.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><label for="vczapi_disable_invite"><?php _e( 'Disable Invite field when join via browser ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="vczapi_disable_invite" name="vczapi_disable_invite" value="yes" <?php ! empty( $vczapi_disable_invite ) ? checked( $vczapi_disable_invite, 'yes' ) : false; ?>>
                        <span class="description"><?php _e( 'Checking this will disable invite button when user joins meeting via Join via Browser window.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Default Language for Join via browser page ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <select name="meeting-lang">
                            <option value="all" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'all' ) : false; ?>><?php _e( 'Show All', 'video-conferencing-with-zoom-api' ); ?></option>
                            <option value="en-US" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'en-US' ) : false; ?>>English</option>
                            <option value="de-DE" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'de-DE' ) : false; ?>>German Deutsch</option>
                            <option value="es-ES" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'es-ES' ) : false; ?>>Spanish Español</option>
                            <option value="fr-FR" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'fr-FR' ) : false; ?>>French Français</option>
                            <option value="jp-JP" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'jp-JP' ) : false; ?>>Japanese 日本語</option>
                            <option value="pt-PT" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'pt-PT' ) : false; ?>>Portuguese Portuguese</option>
                            <option value="ru-RU" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'ru-RU' ) : false; ?>>Russian Русский</option>
                            <option value="zh-CN" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'zh-CN' ) : false; ?>>Chinese 简体中文</option>
                            <option value="zh-TW" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'zh-TW' ) : false; ?>>Chinese 繁体中文</option>
                            <option value="ko-KO" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'ko-KO' ) : false; ?>>Korean 한국어</option>
                            <option value="vi-VN" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'vi-VN' ) : false; ?>>Vietnamese Tiếng Việt</option>
                            <option value="it-IT" <?php ! empty( $default_jvb_lang ) ? selected( $default_jvb_lang, 'it-IT' ) : false; ?>>Italian italiano</option>
                        </select>
                        <p class="description"><?php _e( 'Select a default language for your join meeting via browser page.', 'video-conferencing-with-zoom-api' ); ?></p>
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
                            <input type="radio" value="LLLL" name="zoom_api_date_time_format" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'LLLL', false ) : 'checked'; ?> class="zoom_api_date_time_format"> Wednesday, May 6, 2020 05:00 PM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="lll" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'lll', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format"> May 6, 2020 05:00 AM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="llll" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'llll', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format"> Wed, May 6, 2020 05:00 AM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="L LT" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'L LT', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format"> 05/06/2020 03:00 PM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="l LT" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'l LT', false ) : ''; ?> name="zoom_api_date_time_format" class="zoom_api_date_time_format"> 5/6/2020 03:00 PM
                        </div>
                        <div style="padding-top:10px;">
                            <input type="radio" value="custom" <?php echo ! empty( $locale_format ) ? checked( $locale_format, 'custom', false ) : ''; ?>name="zoom_api_date_time_format" class="zoom_api_date_time_format"> Custom
                            <input type="text" class="regular-text" name="zoom_api_custom_date_time_format" placeholder="Y-m-d" value="<?php echo ! empty( $custom_date_time_format ) ? $custom_date_time_format : ''; ?>">
                        </div>
                        <p class="description"><?php _e( 'Change date time formats according to your choice. Please edit this properly. Failure to correctly put value will result in failure to show date in frontend.', 'video-conferencing-with-zoom-api' ); ?></p>
                        <p class="description">
							<?php
							printf( __( 'Please see %s on how to format date', 'video-conferencing-with-zoom-api' ), '<a href="https://www.php.net/manual/en/datetime.format.php" target="_blank" rel="nofollow noopener">https://www.php.net/manual/en/datetime.format.php</a>' );
							?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Use 24-hour format', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="zoom_api_twenty_fourhour_format" <?php echo ! empty( $twentyfour_format ) ? checked( $twentyfour_format, 'on' ) : false; ?> class="zoom_api_date_time_format">
                        <span class="description"><?php _e( 'Checking this option will show 24 hour time format in all event dates.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e( 'Use full month label format ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="zoom_api_full_month_format" <?php echo ! empty( $full_month_format ) ? checked( $full_month_format, 'on' ) : false; ?> class="zoom_api_date_time_format">
                        <span class="description"><?php _e( 'Checking this option will show full month label for example: June, July, August etc.', 'video-conferencing-with-zoom-api' ); ?></span>
                    </td>
                </tr>
                </tbody>
            </table>
            <h3 class="description" style="color:red;"><?php _e( 'After you enter your keys. Do save changes before doing "Check API Connection".', 'video-conferencing-with-zoom-api' ); ?></h3>
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
            <li><a target="_blank" href="https://deepenbajracharya.com.np"><?php _e( 'Developer', 'video-conferencing-with-zoom-api' ); ?></a></li>
            <li>
                <a target="_blank" href="<?php echo admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' ); ?>"><?php _e( 'Addons', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li>
                <a target="_blank" href="https://www.facebook.com/groups/zoomwp/"><?php _e( 'Facebook Group', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
        </ul>
        <div class="zvc-information-sec">
            <h3>WooCommerce Addon</h3>
            <p>Integrate your Zoom Meetings directly to WooCommerce or WooCommerce booking products. Zoom Integration for WooCommerce allows you to automate your zoom meetings directly from your WordPress dashboard by linking zoom meetings to your WooCommerce or WooCommerce Booking products automatically. Users will receive join links in their booking confirmation emails.</p>
            <p><a href="https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/" class="button button-primary">More Details</a>
            </p>
        </div>
        <div class="zvc-information-sec">
            <h3>Need Idle Auto logout ?</h3>
            <p>Protect your WordPress users' sessions from shoulder surfers and snoopers!</p>
            <p>Use the Inactive Logout plugin to automatically terminate idle user sessions, thus protecting the site if the users leave unattended sessions.</p>
            <p>
                <a target="_blank" href="https://wordpress.org/plugins/inactive-logout/"><?php _e( 'Try inactive logout', 'video-conferencing-with-zoom-api' ); ?></a>
        </div>
    </div>
</div>
