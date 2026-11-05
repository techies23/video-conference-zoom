<?php

use Codemanas\VczApi\Helpers\Locales;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="zvc-cover" style="display: none;"></div>
<div class="zvc-row" style="margin-top:10px;">
    <div class="zvc-position-floater-left" style="width: 70%;margin-right:10px;border-top:1px solid #ccc;">
        <form action="<?php echo esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=api-settings' ) ); ?>"
              method="POST">
            <?php wp_nonce_field( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' ); ?>

            <div id="vczapi-s2sOauth-credentials" class="vczapi-admin-accordion expanded">
                <div class="vczapi-admin-accordion--header">
                    <div class="vczapi-admin-accordion--header-title">
                        <h3><?php esc_html_e( 'General Settings', 'video-conferencing-with-zoom-api' ); ?></h3>
                    </div>
                    <div class="vczapi-admin-accordion--header-trigger">
                        <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                    </div>
                </div>
                <div class="vczapi-admin-accordion--content show">
                    <table class="form-table">
                        <tbody>
                        <tr class="enabled-vanity-url">
                            <th><label for="zoom_vanity_url"><?php esc_html_e( 'Vanity URL', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="url" name="vanity_url" id="zoom_vanity_url" class="regular-text"
                                       value="<?php echo ! empty( $zoom_vanity_url ) ? esc_url( $zoom_vanity_url ) : ''; ?>"
                                       placeholder="https://example.zoom.us">
                                <p class="description"><?php esc_html_e( 'If you are using Zoom Vanity URL then please insert it here else leave it empty.', 'video-conferencing-with-zoom-api' ); ?></p>
                                <a href="<?php echo esc_url( 'https://support.zoom.us/hc/en-us/articles/215062646-Guidelines-for-Vanity-URL-Requests' ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Read more about Vanity URLs', 'video-conferencing-with-zoom-api' ); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="donot_delete_zom_meeting_also"><?php esc_html_e( 'Meetings Deletion ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="donot_delete_zom_meeting_also" name="donot_delete_zom_meeting_also" <?php checked( ! empty( $donot_delete_zoom ) ? $donot_delete_zoom : '', 'on' ); ?>>
                                <span class="description"><?php esc_html_e( 'Do not delete your meetings on Zoom, when you delete your meeting from Zoom Meetings > All Meetings page.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="disable_countdown_timer"><?php esc_html_e( 'Disable Countdown Timer', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="disable_countdown_timer" name="disable_countdown_timer" <?php checked( ! empty( $settings['disable_countdown_timer'] ) ? $settings['disable_countdown_timer'] : '', 'on' ); ?> class="form-control">
                                <span class="description"><?php esc_html_e( 'This setting will disable countdown timer on single Zoom Events page. Check this option if you want to disable the countdown.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="disable_auto_pwd_generation"><?php esc_html_e( 'Disable Auto Password Generation ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="disable_auto_pwd_generation" name="disable_auto_pwd_generation" <?php checked( ! empty( $disable_auto_pwd_generation ) ? $disable_auto_pwd_generation : '', 'on' ); ?>>
                                <span class="description"><?php esc_html_e( 'Checking this option will disable auto password generation for new meetings which are created from Zoom meeting > Add new section.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="hide_join_links_non_loggedin_users"><?php esc_html_e( 'Hide Join Links for Non-Loggedin ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="hide_join_links_non_loggedin_users"
                                       name="hide_join_links_non_loggedin_users" <?php checked( ! empty( $hide_join_link_nloggedusers ) ? $hide_join_link_nloggedusers : '', 'on' ); ?>>
                                <span class="description"><?php esc_html_e( 'Checking this option will hide join links from your shortcode for non-loggedin users.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="embed_password_join_link"><?php esc_html_e( 'Disable Embed password in Link ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="embed_password_join_link"
                                       name="embed_password_join_link" <?php checked( ! empty( $embed_password_join_link ) ? $embed_password_join_link : '', 'on' ); ?>>
                                <span class="description"><?php esc_html_e( 'Meeting password will not be included in the invite link to allow participants to join with just one click without having to enter the password.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr class="enabled-join-links-after-mtg-end">
                            <th><label for="meeting_end_join_link"><?php esc_html_e( 'Show Past Join Link ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="meeting_end_join_link"
                                       name="meeting_end_join_link" <?php checked( ! empty( $past_join_links ) ? $past_join_links : '', 'on' ); ?>>
                                <span class="description"><?php esc_html_e( 'This will show join meeting links on frontend even after meeting time is already past.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr class="show-zoom-authors">
                            <th><label for="meeting_show_zoom_author_original"><?php esc_html_e( 'Show Zoom Author ?', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="meeting_show_zoom_author_original"
                                       name="meeting_show_zoom_author_original" <?php checked( ! empty( $zoom_author_show ) ? $zoom_author_show : '', 'on' ); ?>>
                                <span class="description">
									<?php esc_html_e( 'Checking this show Zoom original Author in single meetings page which are created from', 'video-conferencing-with-zoom-api' ); ?>
									<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=zoom-meetings' ) ); ?>"><?php esc_html_e( 'Zoom Meetings', 'video-conferencing-with-zoom-api' ); ?></a>
								</span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="zoom_api_meeting_goingtostart_text"><?php esc_html_e( 'Meeting going to start Text', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="text" style="width: 400px;" name="zoom_api_meeting_goingtostart_text"
                                       id="zoom_api_meeting_goingtostart_text"
                                       value="<?php echo ! empty( $zoom_going_to_start ) ? esc_attr( $zoom_going_to_start ) : ''; ?>"
                                       placeholder="<?php esc_attr_e( 'Click join button below to join the meeting now !', 'video-conferencing-with-zoom-api' ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="zoom_api_meeting_ended_text"><?php esc_html_e( 'Meeting Ended Text', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="text" style="width: 400px;" name="zoom_api_meeting_ended_text"
                                       id="zoom_api_meeting_ended_text"
                                       value="<?php echo ! empty( $zoom_ended ) ? esc_attr( $zoom_ended ) : ''; ?>"
                                       placeholder="<?php esc_attr_e( 'This meeting has been ended by the host.', 'video-conferencing-with-zoom-api' ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="zoom_api_debugger_logs"><?php esc_html_e( 'Enable Logs', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="zoom_api_debugger_logs"
                                       name="zoom_api_debugger_logs" <?php checked( ! empty( $settings['debugger_logs'] ) ? $settings['debugger_logs'] : '', 'on' ); ?>
                                       class="zoom_api_debugger_logs">
                                <span class="description">
									<?php esc_html_e( 'This can be helpful in finding issues related to Zoom.', 'video-conferencing-with-zoom-api' ); ?>
									<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ); ?>">
										<?php esc_html_e( 'Logs are here.', 'video-conferencing-with-zoom-api' ); ?>
									</a>
								</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!--Join via Browser Settings-->
            <div id="vczapi-jvb-credentials" class="vczapi-admin-accordion expanded">
                <div class="vczapi-admin-accordion--header">
                    <div class="vczapi-admin-accordion--header-title">
                        <h3><?php esc_html_e( 'Join via Web Browser Settings', 'video-conferencing-with-zoom-api' ); ?></h3>
                    </div>
                    <div class="vczapi-admin-accordion--header-trigger">
                        <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                    </div>
                </div>
                <div class="vczapi-admin-accordion--content show">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><label for="meeting_disable_join_via_browser"><?php esc_html_e( 'Disable Join via browser ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="meeting_disable_join_via_browser"
                                       name="meeting_disable_join_via_browser" <?php checked( ! empty( $disable_jvb ) ? $disable_jvb : '', 'on' ); ?>>
                                <span class="description"><?php esc_html_e( 'Checking this will hide all Join via Browser Buttons.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="meeting_show_email_field"><?php esc_html_e( 'Disable Email field when join via browser ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="meeting_show_email_field"
                                       name="meeting_show_email_field" <?php checked( ! empty( $hide_email_jvb ) ? $hide_email_jvb : '', 'on' ); ?>>
                                <span class="description"><?php esc_html_e( 'Checking this show will hide email field in Join via Browser window. Email field is shown if the event is a webinar because email field is required in order to join a webinar.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="vczapi_disable_invite"><?php esc_html_e( 'Disable Invite field when join via browser ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="vczapi_disable_invite" name="vczapi_disable_invite"
                                       value="yes" <?php checked( ! empty( $vczapi_disable_invite ) ? $vczapi_disable_invite : '', 'yes' ); ?>>
                                <span class="description"><?php esc_html_e( 'Checking this will disable invite button when user joins meeting via Join via Browser window.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="vczapi_enable_direct_join"><?php esc_html_e( 'Enable direct join via web browser?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="vczapi_enable_direct_join" name="vczapi_enable_direct_join"
                                       value="yes" <?php checked( ! empty( $settings['enable_direct_join_via_browser'] ) ? $settings['enable_direct_join_via_browser'] : '', 'yes' ); ?>>
                                <span class="description"><?php esc_html_e( 'Checking this will allow users to join via web browser directly. Without needing to enter any names or passwords.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="meeting-lang"><?php esc_html_e( 'Default Language for Join via browser page ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <select name="meeting-lang" id="meeting-lang">
                                    <option value="all" <?php selected( ! empty( $default_jvb_lang ) ? $default_jvb_lang : '', 'all' ); ?>><?php esc_html_e( 'Show All', 'video-conferencing-with-zoom-api' ); ?></option>
                                    <?php
                                    $langs = Locales::getSupportedTranslationsForWeb();
                                    foreach ( $langs as $k => $lang ) {
                                        ?>
                                        <option value="<?php echo esc_attr( $k ); ?>" <?php selected( ! empty( $default_jvb_lang ) ? $default_jvb_lang : '', $k ); ?>><?php echo esc_html( $lang ); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="description"><?php esc_html_e( 'Select a default language for your join meeting via browser page.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!--Date Settings-->
            <div id="vczapi-date-credentials" class="vczapi-admin-accordion expanded">
                <div class="vczapi-admin-accordion--header">
                    <div class="vczapi-admin-accordion--header-title">
                        <h3><?php esc_html_e( 'Date Settings', 'video-conferencing-with-zoom-api' ); ?></h3>
                    </div>
                    <div class="vczapi-admin-accordion--header-trigger">
                        <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                    </div>
                </div>
                <div class="vczapi-admin-accordion--content show">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><label><?php esc_html_e( 'DateTime Format', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <div>
                                    <input type="radio" id="date_format_llll_full" value="LLLL"
                                           name="zoom_api_date_time_format" <?php checked( ! empty( $locale_format ) ? $locale_format : 'LLLL', 'LLLL' ); ?>
                                           class="zoom_api_date_time_format">
                                    <label for="date_format_llll_full"><?php esc_html_e( 'Wednesday, May 6, 2020 05:00 PM', 'video-conferencing-with-zoom-api' ); ?></label>
                                </div>
                                <div style="padding-top:10px;">
                                    <input type="radio" id="date_format_lll"
                                           value="lll" <?php checked( ! empty( $locale_format ) ? $locale_format : '', 'lll' ); ?>
                                           name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                                    <label for="date_format_lll"><?php esc_html_e( 'May 6, 2020 05:00 AM', 'video-conferencing-with-zoom-api' ); ?></label>
                                </div>
                                <div style="padding-top:10px;">
                                    <input type="radio" id="date_format_llll"
                                           value="llll" <?php checked( ! empty( $locale_format ) ? $locale_format : '', 'llll' ); ?>
                                           name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                                    <label for="date_format_llll"><?php esc_html_e( 'Wed, May 6, 2020 05:00 AM', 'video-conferencing-with-zoom-api' ); ?></label>
                                </div>
                                <div style="padding-top:10px;">
                                    <input type="radio" id="date_format_l_lt_upper"
                                           value="L LT" <?php checked( ! empty( $locale_format ) ? $locale_format : '', 'L LT' ); ?>
                                           name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                                    <label for="date_format_l_lt_upper"><?php esc_html_e( '05/06/2020 03:00 PM', 'video-conferencing-with-zoom-api' ); ?></label>
                                </div>
                                <div style="padding-top:10px;">
                                    <input type="radio" id="date_format_l_lt_lower"
                                           value="l LT" <?php checked( ! empty( $locale_format ) ? $locale_format : '', 'l LT' ); ?>
                                           name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                                    <label for="date_format_l_lt_lower"><?php esc_html_e( '5/6/2020 03:00 PM', 'video-conferencing-with-zoom-api' ); ?></label>
                                </div>
                                <div style="padding-top:10px;">
                                    <input type="radio" id="date_format_custom" value="custom"
                                            <?php checked( ! empty( $locale_format ) ? $locale_format : '', 'custom' ); ?>
                                           name="zoom_api_date_time_format" class="zoom_api_date_time_format">
                                    <label for="date_format_custom"><?php esc_html_e( 'Custom', 'video-conferencing-with-zoom-api' ); ?></label>
                                    <input type="text" class="regular-text" name="zoom_api_custom_date_time_format"
                                           placeholder="Y-m-d"
                                           value="<?php echo ! empty( $custom_date_time_format ) ? esc_attr( $custom_date_time_format ) : ''; ?>">
                                </div>
                                <p class="description"><?php esc_html_e( 'Change date time formats according to your choice. Please edit this properly. Failure to correctly put value will result in failure to show date in frontend.', 'video-conferencing-with-zoom-api' ); ?></p>
                                <p class="description">
                                    <?php
                                    printf(
                                    /* translators: %s: Link to PHP DateTime format documentation */
                                            esc_html__( 'Please see %s on how to format date', 'video-conferencing-with-zoom-api' ),
                                            '<a href="' . esc_url( 'https://www.php.net/manual/en/datetime.format.php' ) . '" target="_blank" rel="nofollow noopener">' . esc_html__( 'PHP DateTime Format Documentation', 'video-conferencing-with-zoom-api' ) . '</a>'
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="zoom_api_twenty_fourhour_format"><?php esc_html_e( 'Use 24-hour format', 'video-conferencing-with-zoom-api' ); ?></label></th>
                            <td>
                                <input type="checkbox" id="zoom_api_twenty_fourhour_format"
                                       name="zoom_api_twenty_fourhour_format" <?php checked( ! empty( $twentyfour_format ) ? $twentyfour_format : '', 'on' ); ?>
                                       class="zoom_api_date_time_format">
                                <span class="description"><?php esc_html_e( 'Checking this option will show 24 hour time format in all event dates.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="zoom_api_full_month_format"><?php esc_html_e( 'Use full month label format ?', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="zoom_api_full_month_format"
                                       name="zoom_api_full_month_format" <?php checked( ! empty( $full_month_format ) ? $full_month_format : '', 'on' ); ?>
                                       class="zoom_api_date_time_format">
                                <span class="description"><?php esc_html_e( 'Checking this option will show full month label for example: June, July, August etc.', 'video-conferencing-with-zoom-api' ); ?></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="submit">
                <input type="submit" name="save_zoom_settings" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'video-conferencing-with-zoom-api' ); ?>">
            </p>
        </form>
    </div>
    <div class="zvc-position-floater-right">
        <?php require_once ZVC_PLUGIN_VIEWS_PATH . '/additional-info.php'; ?>
    </div>
</div>