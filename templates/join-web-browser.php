<?php
/**
 * The Template for joining a meeting via the browser.
 *
 * This template can be overridden by copying it to
 * yourtheme/video-conferencing-zoom/join-web-browser.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @since      3.0.0
 * @version    4.7.0
 */

use Codemanas\VczApi\Helpers\Locales;
use Codemanas\VczApi\WebSDK\JoinRequest;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$args         = isset( $args ) && is_array( $args ) ? $args : [];
$join_request = $args['join_request'] ?? null;
$current_user = wp_get_current_user();

$topic            = $join_request instanceof JoinRequest ? $join_request->topic() : $GLOBALS['zoom']['api']->topic ?? '';
$default_lang     = $join_request instanceof JoinRequest ? $join_request->default_lang() : 'en-US';
$has_password     = $join_request instanceof JoinRequest ? $join_request->has_password() : ! empty( $GLOBALS['zoom']['password'] );
$full_name        = $join_request instanceof JoinRequest ? $join_request->get_user_name() : '';
$hide_email       = $join_request instanceof JoinRequest && $join_request->hide_email_address();
$isWebinar        = $join_request instanceof JoinRequest ? $join_request->is_webinar() : "meeting";
$show_email_field = $isWebinar || !$hide_email;
$user_email       = $isWebinar ? $current_user->user_email : '';
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
                <h3 class="vczapi-jvb__title"><?php echo esc_html( $topic ); ?></h3>
                <p class="vczapi-jvb__subtitle"><?php esc_html_e( 'Enter below details to join this Zoom Event.', 'video-conferencing-with-zoom-api' ); ?></p>
            </header>

            <?php if ( ! is_ssl() ) : ?>
                <div class="vczapi-jvb__notice vczapi-jvb__notice--warning">
                    <strong class="vczapi-jvb__notice-title"><?php esc_html_e( 'NOTICE', 'video-conferencing-with-zoom-api' ); ?></strong>
                    <p class="vczapi-jvb__notice-message">
                        <?php esc_html_e( 'Browser did not detect a valid SSL certificate. Audio and Video for Zoom meeting will not work on a non HTTPS site, please install a valid SSL certificate to allow audio and video in your Meetings via browser.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </div>
            <?php endif; ?>

            <form class="vczapi-jvb__form" id="vczapi-jvb-join-form" method="post" novalidate>
                <div class="vczapi-jvb__field">
                    <label for="vczapi-jvb-display-name" class="vczapi-jvb__label"><?php esc_html_e( 'Name', 'video-conferencing-with-zoom-api' ); ?></label>
                    <input type="text" name="display_name" id="vczapi-jvb-display-name" value="<?php echo esc_attr( $full_name ); ?>" placeholder="<?php esc_attr_e( 'Your Name Here', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input" autocomplete="name" required>
                </div>

                <?php if ( $show_email_field ) { ?>
                    <div class="vczapi-jvb__field">
                        <label for="vczapi-jvb-email" class="vczapi-jvb__label">
                            <?php esc_html_e( 'Email', 'video-conferencing-with-zoom-api' ); ?>
                        </label>
                        <input type="email"
                               name="display_email"
                               id="vczapi-jvb-email"
                               value="<?php echo esc_attr( $user_email ); ?>"
                               placeholder="<?php esc_attr_e( 'Your Email Here', 'video-conferencing-with-zoom-api' ); ?>"
                               class="vczapi-jvb__input"
                               autocomplete="email">
                    </div>
                <?php } else { ?>
                    <input type="hidden"
                           name="display_email"
                           id="vczapi-jvb-email"
                           value="<?php echo esc_attr( $current_user->user_email ); ?>">
                <?php } ?>

                <?php if ( $has_password ) : ?>
                    <div class="vczapi-jvb__field">
                        <label for="vczapi-jvb-password" class="vczapi-jvb__label"><?php esc_html_e( 'Password', 'video-conferencing-with-zoom-api' ); ?></label>
                        <input type="password" name="meeting_password" id="vczapi-jvb-password" value="" placeholder="<?php esc_attr_e( 'Meeting Password', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input" autocomplete="off" required>
                    </div>
                <?php endif; ?>

                <?php
                if ( ! empty( $default_lang ) && 'all' !== $default_lang ) {
                    ?>
                    <input name="meeting-lang" class="vczapi-jvb-locale" type="hidden" value="<?php echo esc_attr( $default_lang ); ?>">
                    <?php
                } else {
                    ?>
                    <div class="vczapi-jvb__field">
                        <label for="vczapi-jvb-locale" class="vczapi-jvb__label"><?php esc_html_e( 'Language', 'video-conferencing-with-zoom-api' ); ?></label>
                        <select name="meeting-lang" id="vczapi-jvb-locale" class="vczapi-jvb__select meeting-locale">
                            <?php
                            foreach ( Locales::getSupportedTranslationsForWeb() as $k => $lang ) {
                                ?>
                                <option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $lang ); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                ?>

                <?php // Always rendered so join failures have somewhere to go. ?>
                <div class="vczapi-jvb__notice vczapi-jvb__notice--status vczapi-jvb-meeting--info__browser"
                     id="vczapi-jvb-meeting--status"
                     role="status"
                     aria-live="polite"
                     hidden></div>

                <button type="submit" class="vczapi-jvb__submit" id="vczapi-jvb-btn">
                    <span class="vczapi-jvb__submit-label"><?php esc_html_e( 'Join Event via Browser', 'video-conferencing-with-zoom-api' ); ?></span>
                </button>
            </form>

            <div class="vczapi-jvb__notice vczapi-jvb__notice--error" id="vczapi-jvb-meeting--fatal" role="alert" hidden></div>
        </div>
    </div>
</div>
