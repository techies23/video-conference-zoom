<?php
/**
 * The Template for joining meeting via browser
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/join-web-browser.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @since      3.0.0
 * @version    3.3.1
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

    <div class="vczapi-jvb">
        <div class="vczapi-jvb__container">
            <div class="vczapi-jvb__card">
                <header class="vczapi-jvb__header">
                    <?php
                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                    if ( ! empty( $custom_logo_id ) ) {
                        $image = wp_get_attachment_image_src( $custom_logo_id, 'full' );
                        if ( ! empty( $image ) ) {
                            ?>
                            <img src="<?php echo esc_url( $image[0] ); ?>" alt="<?php esc_attr_e( 'Logo', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__logo">
                            <?php
                        }
                    }
                    ?>
                    <h3 class="vczapi-jvb__title"><?php echo ! empty( $zoom['api']->topic ) ? esc_html( $zoom['api']->topic ) : ''; ?></h3>
                    <p class="vczapi-jvb__subtitle"><?php esc_html_e( 'Enter below details to join this Zoom Event.', 'video-conferencing-with-zoom-api' ); ?></p>
                </header>

                <?php if ( ! is_ssl() ) : ?>
                    <div class="vczapi-jvb__notice vczapi-jvb__notice--warning vczapi-zoom-browser-meeting--info__browser">
                        <strong class="vczapi-jvb__notice-title"><?php esc_html_e( 'NOTICE', 'video-conferencing-with-zoom-api' ); ?></strong>
                        <p class="vczapi-jvb__notice-message">
                            <?php esc_html_e( 'Browser did not detect a valid SSL certificate. Audio and Video for Zoom meeting will not work on a non HTTPS site, please install a valid SSL certificate to allow audio and video in your Meetings via browser.', 'video-conferencing-with-zoom-api' ); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <form class="vczapi-jvb__form" id="vczapi-zoom-browser-meeting-join-form" action="">
                    <?php $full_name = ! empty( $current_user->first_name ) ? $current_user->first_name . ' ' . $current_user->last_name : $current_user->display_name; ?>

                    <div class="vczapi-jvb__field">
                        <label for="vczapi-jvb-display-name" class="vczapi-jvb__label"><?php esc_html_e( 'Name', 'video-conferencing-with-zoom-api' ); ?></label>
                        <input type="text" name="display_name" id="vczapi-jvb-display-name" value="<?php echo esc_attr( $full_name ); ?>" placeholder="<?php esc_attr_e( 'Your Name Here', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input" required>
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
                            <div class="vczapi-jvb__field">
                                <label for="vczapi-jvb-email" class="vczapi-jvb__label"><?php esc_html_e( 'Email', 'video-conferencing-with-zoom-api' ); ?></label>
                                <input type="email" name="display_email" id="vczapi-jvb-email" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php esc_attr_e( 'Your Email Here', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input">
                            </div>
                            <?php
                        }
                    }

                    if ( ! isset( $_GET['pak'] ) && ! empty( $zoom['password'] ) ) : ?>
                        <div class="vczapi-jvb__field">
                            <label for="meeting_password" class="vczapi-jvb__label"><?php esc_html_e( 'Password', 'video-conferencing-with-zoom-api' ); ?></label>
                            <input type="password" name="meeting_password" id="meeting_password" value="<?php echo ! empty( $zoom['password'] ) ? esc_attr( $zoom['password'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Meeting Password', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input" required>
                        </div>
                    <?php
                    endif;

                    $bypass_lang = apply_filters( 'vczapi_api_bypass_lang', false );
                    if ( ! $bypass_lang ) {
                        $default_jvb_lang = get_option( 'zoom_api_default_lang_jvb' );
                        if ( ! empty( $default_jvb_lang ) && $default_jvb_lang !== "all" ) {
                            ?>
                            <input name="meeting-lang" class="meeting-locale" type="hidden" value="<?php echo esc_attr( $default_jvb_lang ); ?>">
                            <?php
                        } else {
                            ?>
                            <div class="vczapi-jvb__field">
                                <label for="vczapi-jvb-locale" class="vczapi-jvb__label"><?php esc_html_e( 'Locale', 'video-conferencing-with-zoom-api' ); ?></label>
                                <select name="meeting-lang" id="vczapi-jvb-locale" class="vczapi-jvb__select meeting-locale">
                                    <?php
                                    $langs = \Codemanas\VczApi\Helpers\Locales::getSupportedTranslationsForWeb();
                                    foreach ( $langs as $k => $lang ) {
                                        ?>
                                        <option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $lang ); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <button type="submit" class="vczapi-jvb__submit" id="vczapi-zoom-browser-meeting-join-mtg">
                        <?php esc_html_e( 'Join Event via Browser', 'video-conferencing-with-zoom-api' ); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php
    /**
     * Trigger after the content
     */
    do_action( 'vczoom_jbh_after_content' );
} else {
    echo '<h3 class="vczapi-jvb__unauthorized-notice">' . esc_html__( 'You do not have enough privilege to access this page. Please login to continue or contact administrator.', 'video-conferencing-with-zoom-api' ) . '</h3>';
    die;
}