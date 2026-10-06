<?php
/**
 * Shortcode template: Meeting by Post ID
 *
 * @var array $args Context passed via vczapi_get_template().
 */

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\Links;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$args = is_array( $args ?? null ) ? $args : [];
$zoom = $args['zoom'] ?? ( $GLOBALS['zoom'] ?? [] );

// Extract variables with null-coalescing fallbacks
$postId    = absint( $args['post_id'] ?? get_the_ID() );
$meetingId = $args['id'] ?? '';
$joinUrl   = $args['join_url'] ?? '';
$password  = $args['password'] ?? '';
$startTime = $args['start_time'] ?? '';
$timezone  = $args['timezone'] ?? '';
$duration  = $args['duration'] ?? 0;
$terms     = $zoom['terms'] ?? [];

$hasThumbnail = has_post_thumbnail( $postId );
$joinBtn      = ! empty( $joinUrl ) ? Links::getPwdEmbeddedJoinLink( $joinUrl, $password ) : '';
$joinViaWeb   = ! empty( $meetingId ) ? Links::getJoinViaBrowserJoinLinks( [
        'link_only' => true,
        'post_id'   => $postId,
], $meetingId ) : '';

$wrapperClasses = [ 'vczapi-post-card', $hasThumbnail ? 'vczapi-post-card--has-media' : 'vczapi-post-card--no-media' ];
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapperClasses ) ); ?>">
    <div class="vczapi-post-card__body vczapi-meeting-by-post-id">
        <?php if ( $hasThumbnail ) : ?>
            <div class="vczapi-post-card__media">
                <?php echo get_the_post_thumbnail( $postId, 'full', [
                        'class' => 'vczapi-post-card__image',
                        'alt'   => esc_attr( get_the_title( $postId ) ),
                ] ); ?>
            </div>
        <?php endif; ?>

        <div class="vczapi-post-card__content">
            <header class="vczapi-post-card__header">
                <h2 class="vczapi-post-card__title"><?php echo esc_html( get_the_title( $postId ) ); ?></h2>
            </header>

            <dl class="vczapi-post-card__details">

                <?php if ( ! empty( $startTime ) ) : ?>
                    <div class="vczapi-post-card__detail-item vczapi-post-card__detail-item--start-time">
                        <dt class="vczapi-post-card__label"><?php esc_html_e( 'Session date', 'video-conferencing-with-zoom-api' ); ?>:</dt>
                        <dd class="vczapi-post-card__value">
                            <?php echo esc_html( Date::dateConverter( $startTime, $timezone, 'F j, Y @ g:i a' ) ); ?>
                        </dd>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $terms ) ) : ?>
                    <div class="vczapi-post-card__detail-item vczapi-post-card__detail-item--category">
                        <dt class="vczapi-post-card__label"><?php esc_html_e( 'Category', 'video-conferencing-with-zoom-api' ); ?>:</dt>
                        <dd class="vczapi-post-card__value">
                            <?php echo esc_html( implode( ', ', (array) $terms ) ); ?>
                        </dd>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $duration ) ) :
                    $formattedDuration = Date::convertMinutesToFormat( $duration, false );
                    ?>
                    <div class="vczapi-post-card__detail-item vczapi-post-card__detail-item--duration">
                        <dt class="vczapi-post-card__label"><?php esc_html_e( 'Duration', 'video-conferencing-with-zoom-api' ); ?>:</dt>
                        <dd class="vczapi-post-card__value">
                            <?php
                            if ( ! empty( $formattedDuration['hr'] ) ) {
                                printf(
                                /* translators: 1: Hours count, 2: Minutes count */
                                        esc_html__( '%1$s %2$s', 'video-conferencing-with-zoom-api' ),
                                        sprintf( _n( '%s hour', '%s hours', $formattedDuration['hr'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $formattedDuration['hr'] ) ),
                                        sprintf( _n( '%s minute', '%s minutes', $formattedDuration['min'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $formattedDuration['min'] ) )
                                );
                            } else {
                                printf(
                                        _n( '%s minute', '%s minutes', $formattedDuration['min'], 'video-conferencing-with-zoom-api' ),
                                        number_format_i18n( $formattedDuration['min'] )
                                );
                            }
                            ?>
                        </dd>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $timezone ) ) : ?>
                    <div class="vczapi-post-card__detail-item vczapi-post-card__detail-item--timezone">
                        <dt class="vczapi-post-card__label"><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</dt>
                        <dd class="vczapi-post-card__value"><?php echo esc_html( $timezone ); ?></dd>
                    </div>
                <?php endif; ?>

            </dl>

            <?php if ( ! empty( $joinBtn ) || ! empty( $joinViaWeb ) ) : ?>
                <footer class="vczapi-post-card__actions">
                    <?php if ( ! empty( $joinBtn ) ) : ?>
                        <a href="<?php echo esc_url( $joinBtn ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="vczapi-post-card__btn vczapi-post-card__btn--join">
                            <?php esc_html_e( 'Join Meeting', 'video-conferencing-with-zoom-api' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( ! empty( $joinViaWeb ) ) : ?>
                        <a href="<?php echo esc_url( $joinViaWeb ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="vczapi-post-card__btn vczapi-post-card__btn--browser">
                            <?php esc_html_e( 'Join via Browser', 'video-conferencing-with-zoom-api' ); ?>
                        </a>
                    <?php endif; ?>
                </footer>
            <?php endif; ?>

        </div>
    </div>
</div>