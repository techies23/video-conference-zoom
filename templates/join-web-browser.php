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
	if ( ! empty( $zoom['api']->state ) && $zoom['api']->state === "ended" ) {
		echo "<h3>" . __( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api' ) . "</h3>";
		die;
	}

	/**
	 * Trigger before the content
	 */
	do_action( 'vczoom_jbh_before_content', $zoom );
	?>

    <div id="vczapi-zoom-browser-meeting" class="vczapi-zoom-browser-meeting-wrapper">
        <div class="container">
            <div class="row">
                <div id="vczapi-zoom-browser-meeting--container">
                    <div class="logo">
						<?php
						$custom_logo_id = get_theme_mod( 'custom_logo' );
						if ( ! empty( $custom_logo_id ) ) {
							$image = wp_get_attachment_image_src( $custom_logo_id, 'full' );
							if ( ! empty( $image ) ) {
								?>
                                <img src="<?php echo $image[0]; ?>" width="80px" alt="Logo">
								<?php
							}
						}
						?>
                        <h3><?php echo ! empty( $zoom['api']->topic ) ? $zoom['api']->topic : ''; ?></h3>
                        <p class="mb-4"><?php _e( 'Enter below details to join this Zoom Event.', 'video-conferencing-with-zoom-api' ); ?></p>
                    </div>
                    <div class="mb-4">
                        <div class="vczapi-zoom-browser-meeting--info__browser">
							<?php if ( ! is_ssl() ) { ?>
                                <p><strong style="color:red;"><?php _e( 'NOTICE', 'video-conferencing-with-zoom-api' ); ?></strong></p>
                                <p><?php _e(
										'Browser did not detect a valid SSL certificate. Audio and Video for Zoom meeting will not work on a non HTTPS site, please install a valid SSL certificate to allow audio and video in your Meetings via browser.', 'video-conferencing-with-zoom-api' ); ?>
                                </p>
							<?php } ?>
                        </div>
                    </div>
                    <form class="vczapi-zoom-browser-meeting--meeting-form" id="vczapi-zoom-browser-meeting-join-form" action="">
						<?php $full_name = ! empty( $current_user->first_name ) ? $current_user->first_name . ' ' . $current_user->last_name : $current_user->display_name; ?>
                        <div class="form-group">
                            <label for="display_name"><?php _e( 'Name', 'video-conferencing-with-zoom-api' ); ?></label>
                            <input type="text" name="display_name" id="vczapi-jvb-display-name" value="<?php echo esc_attr( $full_name ); ?>" placeholder="<?php _e( "Your Name Here", "video-conferencing-with-zoom-api" ); ?>" class="form-control" required>
                        </div>
						<?php
						$hide_email = get_option( 'zoom_api_hide_in_jvb' );
						if ( empty( $hide_email ) || ( ! empty( $zoom["meeting_type"] ) && $zoom["meeting_type"] == "2" ) ) {
							if ( ! empty( $current_user ) && ! empty( $current_user->user_email ) ) {
								?>
                                <input type="hidden" name="display_email" id="vczapi-jvb-email" value="<?php echo esc_attr( $current_user->user_email ); ?>">
								<?php
							} else {
								?>
                                <div class="form-group">
                                    <label for="display_email"><?php _e( 'Email', 'video-conferencing-with-zoom-api' ); ?></label>
                                    <input type="email" name="display_email" id="vczapi-jvb-email" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php _e( "Your Email Here", "video-conferencing-with-zoom-api" ); ?>" class="form-control">
                                </div>
							<?php }
						}
						if ( ! isset( $_GET['pak'] ) && !empty( $zoom['password'] ) ) { ?>
                            <div class="form-group">
                                <label for="meeting_password"><?php _e( 'Password', 'video-conferencing-with-zoom-api' ); ?></label>
                                <input type="password" name="meeting_password" id="meeting_password" value="<?php echo ! empty( $zoom['password'] ) ? $zoom['password'] : ''; ?>" placeholder="<?php _e( "Meeting Password", "video-conferencing-with-zoom-api" ); ?>" class="form-control" required>
                            </div>
							<?php
						}

						$bypass_lang = apply_filters( 'vczapi_api_bypass_lang', false );
						if ( ! $bypass_lang ) {
							$default_jvb_lang = get_option( 'zoom_api_default_lang_jvb' );
							if ( ! empty( $default_jvb_lang ) && $default_jvb_lang !== "all" ) {
								?>
                                <input name="meeting-lang" id="meeting_lang" type="hidden" value="<?php echo esc_html( $default_jvb_lang ); ?>">
								<?php
							} else {
								?>
                                <div class="form-group">
                                    <label for="meeting_lang"><?php _e( 'Locale', 'video-conferencing-with-zoom-api' ); ?></label>
                                    <select id="meeting_lang" name="meeting-lang" class="form-control">
                                        <option value="en-US">English</option>
                                        <option value="de-DE">German Deutsch</option>
                                        <option value="es-ES">Spanish Español</option>
                                        <option value="fr-FR">French Français</option>
                                        <option value="jp-JP">Japanese 日本語</option>
                                        <option value="pt-PT">Portuguese Portuguese</option>
                                        <option value="ru-RU">Russian Русский</option>
                                        <option value="zh-CN">Chinese 简体中文</option>
                                        <option value="zh-TW">Chinese 繁体中文</option>
                                        <option value="ko-KO">Korean 한국어</option>
                                        <option value="vi-VN">Vietnamese Tiếng Việt</option>
                                        <option value="it-IT">Italian italiano</option>
                                    </select>
                                </div>
								<?php
							}
						}
						?>
                        <button type="submit" class="btn btn-primary" id="vczapi-zoom-browser-meeting-join-mtg">
							<?php _e( 'Join Event via Browser', 'video-conferencing-with-zoom-api' ); ?>
                        </button>
                    </form>
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
