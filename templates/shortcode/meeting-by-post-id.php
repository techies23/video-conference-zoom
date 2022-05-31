<?php

defined( 'ABSPATH' ) || exit;

global $zoom;

if ( ! vczapi_pro_version_active() && vczapi_pro_check_type( $zoom['api']->type ) || empty( $zoom ) ) {
	return;
}
?>

<div class="vczapi-show-by-postid">
	<?php do_action( 'vczoom_single_content_right' ); ?>

    <div class="vczapi-show-by-postid-contents">
		<?php if ( ! empty( get_the_post_thumbnail_url() ) ) { ?>
            <div class="vczapi-show-by-postid-contents-image">
                <img src="<?php echo esc_url( get_the_post_thumbnail_url() ); ?>" alt="<?php echo get_the_title(); ?>">
            </div>
		<?php } ?>
        <div class="<?php echo empty( get_the_post_thumbnail_url() ) ? 'vczapi-show-by-postid-contents-sections vczapi-show-by-postid-contents-sections-full' : 'vczapi-show-by-postid-contents-sections'; ?>">
            <div class="vczapi-show-by-postid-contents-sections-description">
                <h2 class="vczapi-show-by-postid-contents-sections-description-topic"><?php echo get_the_title(); ?></h2>
				<?php if ( ! empty( $zoom['api']->start_time ) ) { ?>
                    <div class="vczapi-hosted-by-start-time-wrap">
                        <span><strong><?php _e( 'Session date', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                        <span class="sidebar-start-time"><?php echo vczapi_dateConverter( $zoom['api']->start_time, $zoom['api']->timezone, 'F j, Y @ g:i a' ); ?></span>
                    </div>
				<?php } ?>
				<?php if ( ! empty( $zoom['terms'] ) ) { ?>
                    <div class="vczapi-category-wrap">
                        <span><strong><?php _e( 'Category', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                        <span class="sidebar-category"><?php echo implode( ', ', $zoom['terms'] ); ?></span>
                    </div>
				<?php } ?>
				<?php if ( ! empty( $zoom['api']->duration ) ) {
					$duration = vczapi_convertMinutesToHM( $zoom['api']->duration, false );
					?>
                    <div class="vczapi-duration-wrap">
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
                    <div class="vczapi-timezone-wrap">
                        <span><strong><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                        <span class="vczapi-single-meeting-timezone"><?php echo $zoom['api']->timezone; ?></span>
                    </div>
				<?php } ?>

				<?php do_action( 'vczapi_html_after_meeting_details' ); ?>
            </div>
            <div class="dpn-zvc-sidebar-content"></div>
        </div>
		<?php if( ! empty( get_the_content() ) ) { ?>
            <div class="vczapi-show-by-postid-contents-sections-thecontent">
				<?php the_content(); ?>
            </div>
		<?php } ?>
    </div>
</div>
