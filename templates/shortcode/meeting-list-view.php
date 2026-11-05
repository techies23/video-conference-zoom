<?php
/**
 * The Template for displaying Zoom meeting shortcode listings.
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode-listing.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 * @version     4.0.0
 *
 * @var array $args Passed arguments containing shortcode attributes and WP_Query context.
 */

use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$args  = is_array( $args ?? null ) ? $args : [];
$query = $args['q'] ?? [];

$cols         = absint( $args['cols'] );
$column_class = 'vczapi-col-' . ( $cols > 0 ? floor( 12 / $cols ) : 4 );
?>

<div class="vczapi-list-zoom-meetings">
    <div class="vczapi-wrap vczapi-items-wrap">
        <?php if ( $query->have_posts() ) : ?>
            <?php
            while ( $query->have_posts() ) :
                $query->the_post();
                $post_id         = get_the_ID();
                $meeting_details = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );
                $host_name       = apply_filters(
                        'vczapi_host_name',
                        get_option( 'zoom_show_author' ) ? vczapi_get_meeting_author( $post_id, $meeting_details ) : get_the_author()
                );
                ?>

                <div class="<?php echo esc_attr( $column_class ); ?> vczapi-pb-3">
                    <div class="vczapi-list-zoom-meetings--item">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="vczapi-list-zoom-meetings--item__image">
                                <?php the_post_thumbnail(); ?>
                            </div>
                        <?php endif; ?>

                        <div class="vczapi-list-zoom-meetings--item__details">
                            <a href="<?php the_permalink(); ?>" class="vczapi-list-zoom-title-link">
                                <h3><?php the_title(); ?></h3>
                            </a>

                            <div class="vczapi-list-zoom-meetings--item__details__meta">
                                <div class="hosted-by meta">
                                    <strong><?php esc_html_e( 'Hosted By:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                    <span><?php echo esc_html( $host_name ); ?></span>
                                </div>

                                <?php if ( vczapi_pro_version_active() && ! empty( $meeting_details['type'] ) && vczapi_pro_check_type( $meeting_details['type'] ) ) : ?>
                                    <?php
                                    $occurrences = $meeting_details['occurrences'] ?? [];
                                    if ( ! empty( $occurrences ) ) :
                                        $next_occurrence = \Codemanas\ZoomPro\Helpers::get_latest_occurence_by_type( $meeting_details['type'], $meeting_details['timezone'] ?? 'UTC', $occurrences );
                                        ?>
                                        <div class="start-date meta">
                                            <strong><?php esc_html_e( 'Next Occurrence:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                            <span><?php echo esc_html( Date::dateConverter( $next_occurrence, $meeting_details['timezone '] ?? 'UTC', 'F j, Y @ g:i a' ) ); ?></span>
                                        </div>
                                    <?php else : ?>
                                        <div class="start-date meta">
                                            <strong><?php esc_html_e( 'Start Time:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                            <span><?php echo esc_html( Date::dateConverter( $meeting_details['start_date'] ?? 'now', 'UTC', 'F j, Y @ g:i a' ) ); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="start-date meta">
                                        <strong><?php esc_html_e( 'Type:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                        <span><?php esc_html_e( 'Recurring', 'video-conferencing-with-zoom-api' ); ?></span>
                                    </div>
                                <?php else : ?>
                                    <div class="start-date meta">
                                        <strong><?php esc_html_e( 'Start:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                        <span><?php echo esc_html( Date::dateConverter( $meeting_details['start_time'] ?? 'now', $meeting_details['timezone'] ?? 'UTC', 'F j, Y @ g:i a' ) ); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="timezone meta">
                                    <strong><?php esc_html_e( 'Timezone:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                    <span><?php echo esc_html( $meeting_details['timezone'] ?? 'UTC' ); ?></span>
                                </div>

                                <?php do_action( 'vczapi_additional_content_inside_zoom_listing_shortcode' ); ?>
                            </div>

                            <a href="<?php the_permalink(); ?>" class="btn vczapi-btn-link">
                                <?php esc_html_e( 'View Event', 'video-conferencing-with-zoom-api' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <p class="vczapi-no-meeting-found">
                <?php esc_html_e( 'No Meetings found.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        <?php endif; ?>
    </div>

    <?php do_action( 'vczapi_after_shortcode_content_post_loop' ); ?>

    <div class="vczapi-list-zoom-meetings--pagination">
    </div>

    <?php do_action( 'vczapi_after_main_content_post_loop_pagination' ); ?>
</div>