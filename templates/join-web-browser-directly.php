<?php
/**
 * The Template for joining a meeting directly via the browser, skipping the form.
 *
 * This template can be overridden by copying it to
 * yourtheme/video-conferencing-zoom/join-web-browser-directly.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @since      3.0.0
 * @version    3.3.1
 */

use Codemanas\VczApi\WebSDK\JoinRequest;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @var JoinRequest|null $join_request
 */
?>
<div id="vczapi-zoom-browser-meeting" class="vczapi-jvb vczapi-jvb--direct">
    <div class="vczapi-jvb__container">
        <div class="vczapi-jvb__card">
            <div id="vczapi-zoom-browser-meeting--container">
                <div class="vczapi-jvb__notice vczapi-jvb__notice--status vczapi-zoom-browser-meeting--info__browser"
                     id="vczapi-zoom-browser-meeting--status"
                     role="status"
                     aria-live="polite"><?php esc_html_e( 'Please wait... loading meeting', 'video-conferencing-with-zoom-api' ); ?></div>

                <?php // Always rendered so join failures have somewhere to go. ?>
                <div class="vczapi-jvb__notice vczapi-jvb__notice--error"
                     id="vczapi-zoom-browser-meeting--fatal"
                     role="alert"
                     hidden></div>
            </div>
        </div>
    </div>
</div>
