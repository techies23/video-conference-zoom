<?php
/**
 * The Template for joining meeting via browser
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/join-web-browser.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @since      3.0.0
 * @version   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $zoom;
global $current_user;

if ( video_conference_zoom_check_login() ) {

	/**
	 * Trigger before the content
	 */
	do_action( 'vczoom_jbh_before_content', $zoom );
	?>

    <div id="vczapi-zoom-browser-meeting" class="vczapi-zoom-browser-meeting-wrapper">
        <div class="container">
            <div class="row">
                <div id="vczapi-zoom-browser-meeting--container">
                    <h4>Please wait....loading meeting</h4>
                </div>
            </div>
        </div>
    </div>
	<?php
	/**
	 * Trigger before the content
	 */
	do_action( 'vczoom_jbh_after_content' );
} else {
	echo "<h3>" . __( 'You do not have enough priviledge to access this page. Please login to continue or contact administrator.', 'video-conferencing-with-zoom-api' ) . "</h3>";
	die;
}
