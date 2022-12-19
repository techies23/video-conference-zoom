<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-listing.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @since     3.2.2
 * @version     3.6.0
 */

global $zoom;

if ( ! vczapi_pro_version_active() && ( $zoom['api']->type === 8 || $zoom['api']->type === 3 ) || empty( $zoom ) || ! empty( $zoom['api']->code ) ) {
	return;
}

do_action( 'vczapi_before_loop_zoom_listing_shortcode' );

$columns = ! empty( $zoom['columns'] ) ? $zoom['columns'] : 'vczapi-col-4';
?>
    <div class="<?php echo esc_attr( $columns ); ?> vczapi-pb-3">
        <div class="vczapi-list-zoom-meetings--item">
			<?php if ( has_post_thumbnail() ) { ?>
                <div class="vczapi-list-zoom-meetings--item__image">
					<?php the_post_thumbnail(); ?>
                </div><!--Image End-->
			<?php } ?>
            <div class="vczapi-list-zoom-meetings--item__details">
                <a href="<?php echo esc_url( get_the_permalink() ) ?>" class="vczapi-list-zoom-title-link">
                    <h3><?php the_title(); ?></h3></a>
                <div class="vczapi-list-zoom-meetings--item__details__meta">
                    <div class="hosted-by meta">
                        <strong><?php _e( 'Hosted By:', 'video-conferencing-with-zoom-api' ); ?></strong>
                        <span><?php echo apply_filters( 'vczapi_host_name', $zoom['host_name'] ); ?></span>
                    </div>
					<?php
					if ( vczapi_pro_version_active() && ! empty( $zoom['api']->type ) && vczapi_pro_check_type( $zoom['api']->type ) ) {
						$type      = ! empty( $zoom['api']->type ) ? $zoom['api']->type : false;
						$timezone  = ! empty( $zoom['api']->timezone ) ? $zoom['api']->timezone : false;
						$occurence = ! empty( $zoom['api']->occurrences ) ? $zoom['api']->occurrences : false;
						if ( ! empty( $occurence ) ) {
							$start_time = Codemanas\ZoomPro\Helpers::get_latest_occurence_by_type( $type, $timezone, $occurence );
							?>
                            <div class="start-date meta">
                                <strong><?php _e( 'Next Occurrence', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                <span><?php echo vczapi_dateConverter( $start_time, $timezone, 'F j, Y @ g:i a' ); ?></span>
                            </div>
							<?php
						} else {
							?>
                            <div class="start-date meta">
                                <strong><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                <span><?php echo vczapi_dateConverter( $zoom['start_date'], 'UTC', 'F j, Y @ g:i a' ); ?></span>
                            </div>
							<?php
						}
						?>
                        <div class="start-date meta">
                            <strong><?php _e( 'Type', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                            <span><?php _e( 'Recurring', 'video-conferencing-with-zoom-api' ); ?></span>
                        </div>
						<?php
					} else {
						?>
                        <div class="start-date meta">
                            <strong><?php _e( 'Start', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                            <span><?php echo vczapi_dateConverter( $zoom['api']->start_time, $zoom['api']->timezone, 'F j, Y @ g:i a' ); ?></span>
                        </div>
					<?php } ?>
                    <div class="timezone meta">
                        <strong><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                        <span><?php echo esc_html( $zoom['api']->timezone ); ?></span>
                    </div>

					<?php do_action( 'vczapi_additional_content_inside_zoom_listing_shortcode' ); ?>
                </div>
                <a href="<?php echo esc_url( get_the_permalink() ) ?>"
                   class="btn vczapi-btn-link"><?php _e( 'View Event', 'video-conferencing-with-zoom-api' ); ?></a>
            </div><!--Details end-->
        </div><!--List item end-->
    </div>

<?php do_action( 'vczapi_after_loop_zoom_listing_shortcode' ); ?>