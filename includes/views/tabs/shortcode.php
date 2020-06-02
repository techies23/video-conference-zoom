<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="zvc-row">
    <div class="zvc-position-floater-left">
        <section class="zoom-api-example-section">
            <h3><?php _e( 'Using Shortcode Example', 'video-conferencing-with-zoom-api' ); ?></h3>
            <p><?php _e( 'Below are few examples of how you can add shortcodes manually into your posts.', 'video-conferencing-with-zoom-api' ); ?></p>

            <div class="zoom-api-basic-usage">
                <h3><?php _e( 'Basic Usage', 'video-conferencing-with-zoom-api' ); ?>:</h3>
                <code>[zoom_api_link meeting_id="123456789" link_only="no"]</code>
                <div class="zoom-api-basic-usage-description">
                    <label><?php _e( 'Description', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <p><?php _e( 'Show a list with meeting details for a specific meeting ID with join links.', 'video-conferencing-with-zoom-api' ); ?></p>
                    <label><?php _e( 'Parameters', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <ul>
                        <li><strong>meeting_id</strong> : Your meeting ID.</li>
                        <li><strong>link_only</strong> : Yes or No - Adding yes will show join link only. Removing this parameter from shortcode will
                            output description.
                        </li>
                    </ul>
                </div>
            </div>
            <div class="zoom-api-basic-usage" style="margin-top: 20px;border-top:1px solid #ccc;">
                <h3><?php _e( 'Listing Zoom Meetings', 'video-conferencing-with-zoom-api' ); ?>:</h3>
                <code>[zoom_list_meetings per_page="5" category="test,test2,test3" order="ASC" type="upcoming"]</code>
                <div class="zoom-api-basic-usage-description">
                    <label><?php _e( 'Description', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <p><?php _e( 'Shows a list of meetings with start time, date and link to the meetings page. This is customizable by overriding from your
                        theme folder.', 'video-conferencing-with-zoom-api' ); ?></p>
                    <label><?php _e( 'Parameters', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <ul>
                        <li><strong>per_page</strong> : Posts per page.</li>
                        <li><strong>category</strong> : Show linked categories.</li>
                        <li><strong>order</strong> : ASC or DESC based on post created time.</li>
                        <li><strong>type</strong> : "upcoming" or "past" - To show only upcoming meeting based on start time (Update to meeting is
                            required for old post type meetings).
                        </li>
                    </ul>
                </div>
            </div>
            <div class="zoom-api-basic-usage" style="margin-top: 20px;border-top:1px solid #ccc;">
                <h3><?php _e( 'List Host ID', 'video-conferencing-with-zoom-api' ); ?>:</h3>
                <code>[zoom_list_host_meetings host="YOUR_HOST_ID"]</code>
                <div class="zoom-api-basic-usage-description">
                    <label><?php _e( 'Description', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <p><?php _e( 'Show a list with meeting table based on HOST ID in frontend.', 'video-conferencing-with-zoom-api' ); ?></p>
                    <label><?php _e( 'Parameters', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <ul>
                        <li><strong>host</strong> : Your HOST ID.</li>
                    </ul>
                </div>
            </div>
            <div class="zoom-api-basic-usage" style="margin-top: 20px;border-top:1px solid #ccc;">
                <h3><?php _e( 'Embed Zoom Meeting in your Browser', 'video-conferencing-with-zoom-api' ); ?>:</h3>
                <code>[zoom_join_via_browser meeting_id="YOUR_MEETING_ID" login_required="no" help="yes" title="Test" height="500px"
                    disable_countdown="yes"]</code>
                <div class="zoom-api-basic-usage-description">
                    <label><?php _e( 'Description', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <p><?php _e( 'Embeds your meeting in an IFRAME for any page or post you insert this shortcode into.', 'video-conferencing-with-zoom-api' ); ?></p>
                    <p style="color: red;">Although this embed feature is here. I do no garauntee this would work properly as this is not natively supported by Zoom itself. This is here only because of user requests. USE THIS AT OWN RISK !!</p>
                    <label><?php _e( 'Parameters', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <ul>
                        <li><strong>meeting_id</strong> : Your MEETING ID.</li>
                        <li><strong>login_required</strong> : "yes or no", Requires login to view or join.</li>
                        <li><strong>help</strong> : "yes or no", Help text.</li>
                        <li><strong>title</strong> : Title of your Embed Session</li>
                        <li><strong>height</strong> : Height of embedded video IFRAME.</li>
                        <li><strong>disable_countdown</strong> : "yes or no", enable or disable countdown.</li>
                    </ul>
                </div>
            </div>
            <div class="zoom-api-basic-usage" style="margin-top: 20px;border-top:1px solid #ccc;">
                <h3><?php _e( 'Show webinars based on HOST ID in frontend.', 'video-conferencing-with-zoom-api' ); ?>:</h3>
                <code>[zoom_list_host_webinars host="YOUR_HOST_ID"]</code>
                <div class="zoom-api-basic-usage-description">
                    <label><?php _e( 'Description', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <p><?php _e( 'Embeds your meeting in an IFRAME for any page or post you insert this shortcode into.', 'video-conferencing-with-zoom-api' ); ?></p>
                    <label><?php _e( 'Parameters', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <ul>
                        <li><strong>host</strong> : Your HOST ID. Grab it from wp-admin > Zoom Meetings > Users ( USER ID ).</li>
                    </ul>
                </div>
            </div>
            <div class="zoom-api-basic-usage" style="margin-top: 20px;border-top:1px solid #ccc;">
                <h3><?php _e( 'Show webinar based meeting ID.', 'video-conferencing-with-zoom-api' ); ?>:</h3>
                <code>[zoom_api_webinar webinar_id="YOUR_WEBINAR_ID" link_only="no"]</code>
                <div class="zoom-api-basic-usage-description">
                    <label><?php _e( 'Description', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <p><?php _e( 'Shows a Webinar detail based on a specific Webinar ID.', 'video-conferencing-with-zoom-api' ); ?></p>
                    <label><?php _e( 'Parameters', 'video-conferencing-with-zoom-api' ); ?>:</label>
                    <ul>
                        <li><strong>webinar_id</strong> : WEBINAR ID.</li>
                        <li><strong>link_only</strong> : yes or no.</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
</div>
