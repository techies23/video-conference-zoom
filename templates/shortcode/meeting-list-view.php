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
use Codemanas\VczApi\Shortcodes\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Extract query directly from context payload or fall back to legacy global if needed.
$zoom_meetings = $args['query'] ?? ( $GLOBALS['zoom_meetings'] ?? null );

if ( ! $zoom_meetings instanceof \WP_Query ) {
	return;
}

$atts = shortcode_atts(
	[
		'author'       => '',
		'per_page'     => 5,
		'category'     => '',
		'order'        => 'DESC',
		'type'         => '',
		'filter'       => 'yes',
		'show_on_past' => 'yes',
		'cols'         => 3,
		'meeting_type' => 'meetings',
	],
	$args,
	'zoom_list_meetings'
);

$cols         = absint( $atts['cols'] );
$column_class = 'vczapi-col-' . ( $cols > 0 ? floor( 12 / $cols ) : 4 );
?>

<div class="vczapi-list-zoom-meetings"
     data-author="<?php echo esc_attr( $atts['author'] ); ?>"
     data-per_page="<?php echo esc_attr( $atts['per_page'] ); ?>"
     data-category="<?php echo esc_attr( $atts['category'] ); ?>"
     data-order="<?php echo esc_attr( $atts['order'] ); ?>"
     data-type="<?php echo esc_attr( $atts['type'] ); ?>"
     data-filter="<?php echo esc_attr( $atts['filter'] ); ?>"
     data-show_on_past="<?php echo esc_attr( $atts['show_on_past'] ); ?>"
     data-cols="<?php echo esc_attr( $atts['cols'] ); ?>"
     data-base_url="<?php echo esc_url( get_pagenum_link( 999999999999 ) ); ?>"
     data-meeting_type="<?php echo esc_attr( $atts['meeting_type'] ); ?>"
>
	<?php do_action( 'vczapi_before_shortcode_content_post_loop', $zoom_meetings ); ?>

    <div class="vczapi-wrap vczapi-items-wrap">
		<?php if ( $zoom_meetings->have_posts() ) : ?>
			<?php
			while ( $zoom_meetings->have_posts() ) :
				$zoom_meetings->the_post();
				$post_id         = get_the_ID();
				$meeting_details = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );
				$api_data        = $meeting_details['api'] ?? null;

				// Skip recurring meetings in free version or posts missing API data
				if ( ! vczapi_pro_version_active() && ! empty( $api_data->type ) && MeetingType::is_recurring_meeting_or_webinar( $api_data->type ) ) {
					continue;
				}

				if ( empty( $meeting_details ) || ! empty( $api_data->code ) ) {
					continue;
				}

				$host_name = apply_filters(
					'vczapi_host_name',
					get_option( 'zoom_show_author' ) ? vczapi_get_meeting_author( $post_id, $meeting_details ) : get_the_author()
				);

				do_action( 'vczapi_main_content_post_loop' );
				do_action( 'vczapi_before_loop_zoom_listing_shortcode' );
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

								<?php if ( vczapi_pro_version_active() && ! empty( $api_data->type ) && vczapi_pro_check_type( $api_data->type ) ) : ?>
									<?php
									$occurrences = $api_data->occurrences ?? [];
									if ( ! empty( $occurrences ) ) :
										$next_occurrence = \Codemanas\ZoomPro\Helpers::get_latest_occurence_by_type( $api_data->type, $api_data->timezone ?? 'UTC', $occurrences );
										?>
                                        <div class="start-date meta">
                                            <strong><?php esc_html_e( 'Next Occurrence:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                            <span><?php echo esc_html( Date::dateConverter( $next_occurrence, $api_data->timezone ?? 'UTC', 'F j, Y @ g:i a' ) ); ?></span>
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
                                        <span><?php echo esc_html( Date::dateConverter( $api_data->start_time ?? 'now', $api_data->timezone ?? 'UTC', 'F j, Y @ g:i a' ) ); ?></span>
                                    </div>
								<?php endif; ?>

                                <div class="timezone meta">
                                    <strong><?php esc_html_e( 'Timezone:', 'video-conferencing-with-zoom-api' ); ?></strong>
                                    <span><?php echo esc_html( $api_data->timezone ?? 'UTC' ); ?></span>
                                </div>

								<?php do_action( 'vczapi_additional_content_inside_zoom_listing_shortcode' ); ?>
                            </div>

                            <a href="<?php the_permalink(); ?>" class="btn vczapi-btn-link">
								<?php esc_html_e( 'View Event', 'video-conferencing-with-zoom-api' ); ?>
                            </a>
                        </div>
                    </div>
                </div>

				<?php do_action( 'vczapi_after_loop_zoom_listing_shortcode' ); ?>
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