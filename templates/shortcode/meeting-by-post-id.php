<?php

defined( 'ABSPATH' ) || exit;

global $zoom;

?>
<div class="vczapi-show-by-postid">
	<?php
	if ( vczapi_pro_version_active() && vczapi_pro_check_type( $zoom['api']->type ) || empty( $zoom ) ) {
		?>
        <div class="vczapi-show-by-postid-contents">
			<?php do_action( 'vczoom_single_content_right' ); ?>
        </div>
	<?php } else { ?>

		<?php do_action( 'vczoom_single_content_right' ); ?>

        <div class="vczapi-show-by-postid-contents vczapi-show-by-postid-flex">
			<?php if ( ! empty( get_the_post_thumbnail_url() ) ) { ?>
                <div class="vczapi-show-by-postid-contents-image">
                    <img src="<?php echo esc_url( get_the_post_thumbnail_url() ); ?>"
                         alt="<?php echo get_the_title(); ?>">
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
	                    echo sprintf( _n( '%s hour', '%s hours', $duration['hr'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $duration['hr'] ) ) . ' ' . sprintf( _n( '%s minute', '%s minutes', $duration['min'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $duration['min'] ) );
                    } else {
	                    printf( _n( '%s minute', '%s minutes', $duration['min'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $duration['min'] ) );
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

                <!--join links rendered using js-->
                <!--<div class="dpn-zvc-sidebar-content"></div>-->

                <div class="dpn-zvc-meeting-by-post-id-links ">
					<?php
					$args = [
						'link_only' => true
					];

					if ( ! empty( $zoom['api']->password ) ) {
						$args['password'] = $zoom['api']->password;
					}
					$browser_join = \Codemanas\VczApi\Helpers\Links::getJoinViaBrowserJoinLinks( $args, $zoom['api']->id );
					$join_url     = $zoom['api']->join_url;

					if ( ! empty( $join_url ) ) {
						?>
                        <a target="_blank" href="<?php echo esc_url( $join_url ); ?>"
                           title="Join via App"><?php _e( 'Join via Zoom App', 'video-conferencing-with-zoom-api' ); ?></a>
                        <a target="_blank" href="<?php echo esc_url( $browser_join ); ?>"
                           title="Join via Browser"><?php _e( 'Join via Browser', 'video-conferencing-with-zoom-api' ); ?></a>
					<?php } ?>

                </div>
            </div>
        </div>
		<?php if ( ! empty( get_the_content() ) ) { ?>
            <div class="vczapi-show-by-postid-contents-sections-thecontent">
				<?php the_content(); ?>
            </div>
			<?php
		}
	}
	?>
</div>


