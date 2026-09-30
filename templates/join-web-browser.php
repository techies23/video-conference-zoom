<?php
/**
 * The Template for joining a meeting via the browser.
 *
 * This template can be overridden by copying it to
 * yourtheme/video-conferencing-zoom/join-web-browser.php.
 *
 * Rendered by \Codemanas\VczApi\Browser\JoinViaBrowser on the dedicated
 * /zoom-join/<token>/ endpoint. `$join_request` is a validated
 * \Codemanas\VczApi\Browser\JoinRequest; when it is absent the template falls
 * back to the legacy `$zoom` global so old overrides keep working.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @since      3.0.0
 * @version    3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$join_request = $join_request ?? null;
$current_user = wp_get_current_user();

$topic          = $join_request instanceof \Codemanas\VczApi\Browser\JoinRequest
        ? $join_request->topic()
        : (string) ( $GLOBALS['zoom']['api']->topic ?? '' );
$login_required = $join_request instanceof \Codemanas\VczApi\Browser\JoinRequest
        ? $join_request->login_required()
        : ! empty( $GLOBALS['zoom']['site_option_logged_in'] );
$has_password   = $join_request instanceof \Codemanas\VczApi\Browser\JoinRequest
        ? $join_request->has_password()
        : ! empty( $GLOBALS['zoom']['password'] );
$meeting_type   = $join_request instanceof \Codemanas\VczApi\Browser\JoinRequest
        ? 2
        : (int) ( $GLOBALS['zoom']['meeting_type'] ?? 2 );

// The meeting password is deliberately NOT rendered into this markup. It used
// to be pre-filled into the input's value attribute, which put it in page
// source, browser history and any Referer header. It now stays server-side and
// is only released by the signature endpoint, to a caller that presented the
// join token. The field is rendered empty whenever the host set a passcode.
$full_name = '';
if ( is_user_logged_in() ) {
    $full_name = trim( $current_user->first_name . ' ' . $current_user->last_name );
    if ( '' === $full_name ) {
        $full_name = (string) $current_user->display_name;
    }
}

$hide_email   = get_option( 'zoom_api_hide_in_jvb' );
$meeting_type = $join_request instanceof \Codemanas\VczApi\Browser\JoinRequest
        ? 2
        : (int) ( $GLOBALS['zoom']['meeting_type'] ?? 2 );
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

            <?php if ( $login_required && ! is_user_logged_in() ) : ?>
                <div class="vczapi-jvb__notice vczapi-jvb__notice--error" role="alert">
                    <strong class="vczapi-jvb__notice-title"><?php esc_html_e( 'Login required', 'video-conferencing-with-zoom-api' ); ?></strong>
                    <p class="vczapi-jvb__notice-message">
                        <?php esc_html_e( 'You do not have enough privilege to access this meeting. Please login to continue or contact the organiser.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </div>
            <?php else : ?>

                <form class="vczapi-jvb__form" id="vczapi-zoom-browser-meeting-join-form" method="post" novalidate>
                    <div class="vczapi-jvb__field">
                        <label for="vczapi-jvb-display-name" class="vczapi-jvb__label"><?php esc_html_e( 'Name', 'video-conferencing-with-zoom-api' ); ?></label>
                        <input type="text" name="display_name" id="vczapi-jvb-display-name" value="<?php echo esc_attr( $full_name ); ?>" placeholder="<?php esc_attr_e( 'Your Name Here', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input" autocomplete="name" required>
                    </div>

                    <?php
                    if ( empty( $hide_email ) || 2 === (int) $meeting_type ) {
                        if ( is_user_logged_in() && ! empty( $current_user->user_email ) ) {
                            ?>
                            <input type="hidden" name="display_email" id="vczapi-jvb-email" value="<?php echo esc_attr( $current_user->user_email ); ?>">
                            <?php
                        } else {
                            ?>
                            <div class="vczapi-jvb__field">
                                <label for="vczapi-jvb-email" class="vczapi-jvb__label"><?php esc_html_e( 'Email', 'video-conferencing-with-zoom-api' ); ?></label>
                                <input type="email" name="display_email" id="vczapi-jvb-email" value="" placeholder="<?php esc_attr_e( 'Your Email Here', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input" autocomplete="email">
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <?php if ( $has_password ) : ?>
                        <div class="vczapi-jvb__field">
                            <label for="meeting_password" class="vczapi-jvb__label"><?php esc_html_e( 'Password', 'video-conferencing-with-zoom-api' ); ?></label>
                            <input type="password" name="meeting_password" id="meeting_password" value="" placeholder="<?php esc_attr_e( 'Meeting Password', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-jvb__input" autocomplete="off" required>
                        </div>
                    <?php endif; ?>

                    <?php
                    $bypass_lang = apply_filters( 'vczapi_api_bypass_lang', false );
                    if ( ! $bypass_lang ) {
                        $default_jvb_lang = get_option( 'zoom_api_default_lang_jvb' );
                        if ( ! empty( $default_jvb_lang ) && 'all' !== $default_jvb_lang ) {
                            ?>
                            <input name="meeting-lang" class="meeting-locale" type="hidden" value="<?php echo esc_attr( $default_jvb_lang ); ?>">
                            <?php
                        } else {
                            ?>
                            <div class="vczapi-jvb__field">
                                <label for="vczapi-jvb-locale" class="vczapi-jvb__label"><?php esc_html_e( 'Language', 'video-conferencing-with-zoom-api' ); ?></label>
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

                    <?php // Always rendered so join failures have somewhere to go. ?>
                    <div class="vczapi-jvb__notice vczapi-jvb__notice--status vczapi-zoom-browser-meeting--info__browser"
                         id="vczapi-zoom-browser-meeting--status"
                         role="status"
                         aria-live="polite"
                         hidden></div>

                    <button type="submit" class="vczapi-jvb__submit" id="vczapi-zoom-browser-meeting-join-mtg">
                        <span class="vczapi-jvb__submit-label"><?php esc_html_e( 'Join Event via Browser', 'video-conferencing-with-zoom-api' ); ?></span>
                        <span class="vczapi-jvb__submit-spinner" aria-hidden="true"></span>
                    </button>
                </form>

                <div class="vczapi-jvb__notice vczapi-jvb__notice--error" id="vczapi-zoom-browser-meeting--fatal" role="alert" hidden></div>
            <?php endif; ?>
        </div>
    </div>
</div>
