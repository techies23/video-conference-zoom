<?php
/**
 * The template for displaying meeting details of zoom
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/fragments/meeting-details.php.
 *
 * @author      Deepen Bajracharya (CodeManas)
 * @created     3.0.0
 * @updated     3.6.0
 */

global $zoom;

if ( ! vczapi_pro_version_active() && vczapi_pro_check_type( $zoom['api']->type ) || empty( $zoom ) ) {
	return;
}
?>
<div class="dpn-zvc-sidebar-box">
    <div class="dpn-zvc-sidebar-tile">
        <h3><?php _e( 'Details', 'video-conferencing-with-zoom-api' ); ?></h3>
    </div>
    <div class="dpn-zvc-sidebar-content">

		<?php do_action( 'vczapi_html_before_meeting_details' ); ?>

        <div class="dpn-zvc-sidebar-content-list vczapi-hosted-by-topic-wrap">
            <span><strong><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?>:</strong></span> <span><?php the_title(); ?></span>
        </div>
        <div class="dpn-zvc-sidebar-content-list vczapi-hosted-by-list-wrap">
            <span><strong><?php _e( 'Hosted By', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
            <span><?php echo esc_html( $zoom['host_name'] ); ?></span>
        </div>
		<?php if ( ! empty( $zoom['api']->start_time ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list vczapi-hosted-by-start-time-wrap">
                <span><strong><?php _e( 'Start', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span class="sidebar-start-time"><?php echo vczapi_dateConverter( $zoom['api']->start_time, $zoom['api']->timezone, 'F j, Y @ g:i a' ); ?></span>
            </div>
		<?php } ?>
		<?php if ( ! empty( $zoom['terms'] ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list vczapi-category-wrap">
                <span><strong><?php _e( 'Category', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span class="sidebar-category"><?php echo implode( ', ', $zoom['terms'] ); ?></span>
            </div>
		<?php } ?>
		<?php if ( ! empty( $zoom['api']->duration ) ) {
			$duration = vczapi_convertMinutesToHM( $zoom['api']->duration, false );
			?>
            <div class="dpn-zvc-sidebar-content-list vczapi-duration-wrap">
                <span><strong><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span>
                    <?php
                    if ( ! empty( $duration['hr'] ) ) {
	                    echo _n( $duration['hr'] . ' hour', $duration['hr'] . ' hours', absint( $duration['hr'] ), 'video-conferencing-with-zoom-api' ) . ' ' . _n( $duration['min'] . ' minute', $duration['min'] . ' minutes', absint( $duration['min'] ), 'video-conferencing-with-zoom-api' );
                    } else {
	                    echo _n( $duration['min'] . ' minute', $duration['min'] . ' minutes', absint( $duration['min'] ), 'video-conferencing-with-zoom-api' );
                    }
                    ?>
                </span>
            </div>
		<?php } ?>
		<?php if ( ! empty( $zoom['api']->timezone ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list vczapi-timezone-wrap">
                <span><strong><?php _e( 'Current Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span class="vczapi-single-meeting-timezone"><?php echo $zoom['api']->timezone; ?></span>
            </div>
		<?php } ?>

		<?php do_action( 'vczapi_html_after_meeting_details' ); ?>

        <p class="dpn-zvc-display-or-hide-localtimezone-notice"><?php printf( __( '%sNote%s: Countdown time is shown based on your local timezone.', 'video-conferencing-with-zoom-api' ), '<strong>', '</strong>' ); ?></p>
    </div>
</div>