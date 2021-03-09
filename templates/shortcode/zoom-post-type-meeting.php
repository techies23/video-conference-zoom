<?php
global $zoom_meeting;

if ( ! vczapi_pro_version_active() && vczapi_pro_check_type( $zoom_meeting->meeting_details->type ) || empty( $zoom_meeting ) ) {
	?>
    <div class="dpn-zvc-sidebar-box">
        <p><?php _e( 'PRO version is required for this meeting to be displayed.', 'video-conferencing-with-zoom-api' ); ?></p>
    </div>
	<?php
    return;
}

?>
<div class="vczapi-wrap dpn-zvc-single-content-wrapper">
    <div class="vczapi-col-8">
		<?php
		if ( ! empty( $zoom_meeting->thumbnail ) ) {
			echo '<div class="deepn-zvc-single-featured-img">' . $zoom_meeting->thumbnail . '</div>';
		}

		echo apply_filters( 'the_content', $zoom_meeting->post_content );
		?>
    </div>
    <div class="vczapi-col-4">
        <div class="dpn-zvc-sidebar-wrapper">
			<?php if ( ! empty( $zoom_meeting->meeting_details->start_time ) ) { ?>
                <div class="dpn-zvc-sidebar-box">
                    <div class="dpn-zvc-timer" id="dpn-zvc-timer" data-date="<?php echo $zoom_meeting->meeting_details->start_time; ?>" data-tz="<?php echo $zoom_meeting->meeting_details->timezone; ?>">
                        <div class="dpn-zvc-timer-cell">
                            <div class="dpn-zvc-timer-cell-number">
                                <div id="dpn-zvc-timer-days"></div>
                            </div>
                            <div class="dpn-zvc-timer-cell-string"><?php _e( 'days', 'video-conferencing-with-zoom-api' ); ?></div>
                        </div>
                        <div class="dpn-zvc-timer-cell">
                            <div class="dpn-zvc-timer-cell-number">
                                <div id="dpn-zvc-timer-hours"></div>
                            </div>
                            <div class="dpn-zvc-timer-cell-string"><?php _e( 'hours', 'video-conferencing-with-zoom-api' ); ?></div>
                        </div>
                        <div class="dpn-zvc-timer-cell">
                            <div class="dpn-zvc-timer-cell-number">
                                <div id="dpn-zvc-timer-minutes"></div>
                            </div>
                            <div class="dpn-zvc-timer-cell-string"><?php _e( 'minutes', 'video-conferencing-with-zoom-api' ); ?></div>
                        </div>
                        <div class="dpn-zvc-timer-cell">
                            <div class="dpn-zvc-timer-cell-number">
                                <div id="dpn-zvc-timer-seconds"></div>
                            </div>
                            <div class="dpn-zvc-timer-cell-string"><?php _e( 'seconds', 'video-conferencing-with-zoom-api' ); ?></div>
                        </div>
                    </div>
                </div>
			<?php } ?>

            <div class="dpn-zvc-sidebar-box">
                <div class="dpn-zvc-sidebar-tile">
                    <h3><?php _e( 'Details', 'video-conferencing-with-zoom-api' ); ?></h3>
                </div>
                <div class="dpn-zvc-sidebar-content">
					<?php do_action( 'vczapi_html_before_meeting_details' ); ?>

                    <div class="dpn-zvc-sidebar-content-list vczapi-hosted-by-topic-wrap">
                        <span><strong><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?>:</strong></span> <span><?php echo $zoom_meeting->post_title; ?></span>
                    </div>
                    <div class="dpn-zvc-sidebar-content-list vczapi-hosted-by-list-wrap">
                        <span><strong><?php _e( 'Hosted By', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                        <span><?php echo esc_html( $zoom_meeting->host_name ); ?></span>
                    </div>
					<?php if ( ! empty( $zoom_meeting->meeting_details->start_time ) ) { ?>
                        <div class="dpn-zvc-sidebar-content-list vczapi-hosted-by-start-time-wrap">
                            <span><strong><?php _e( 'Start', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                            <span class="sidebar-start-time"><?php echo vczapi_dateConverter( $zoom_meeting->meeting_details->start_time, $zoom_meeting->meeting_details->timezone, 'F j, Y @ g:i a' ); ?></span>
                        </div>
					<?php } ?>
					<?php if ( ! empty( $zoom_meeting->terms ) ) { ?>
                        <div class="dpn-zvc-sidebar-content-list vczapi-category-wrap">
                            <span><strong><?php _e( 'Category', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                            <span class="sidebar-category"><?php echo implode( ', ', $zoom_meeting->terms ); ?></span>
                        </div>
					<?php } ?>
					<?php if ( ! empty( $zoom_meeting->meeting_details->duration ) ) {
						$duration = vczapi_convertMinutesToHM( $zoom_meeting->meeting_details->duration, false );
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
					<?php if ( ! empty( $zoom_meeting->meeting_details->timezone ) ) { ?>
                        <div class="dpn-zvc-sidebar-content-list vczapi-timezone-wrap">
                            <span><strong><?php _e( 'Current Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                            <span class="vczapi-single-meeting-timezone"><?php echo $zoom_meeting->meeting_details->timezone; ?></span>
                        </div>
					<?php } ?>

					<?php do_action( 'vczapi_html_after_meeting_details' ); ?>

                    <p class="dpn-zvc-display-or-hide-localtimezone-notice"><?php printf( __( '%sNote%s: Countdown time is shown based on your local timezone.', 'video-conferencing-with-zoom-api' ), '<strong>', '</strong>' ); ?></p>
                </div>

                <div class="dpn-zvc-sidebar-box">
                    <div class="join-links">
						<?php
						$disable_app_join = apply_filters( 'vczoom_join_meeting_via_app_disable', false );
						if ( ! empty( $zoom_meeting->meeting_details->join_url ) && ! $disable_app_join ) {
							$join_url = ! empty( $zoom_meeting->meeting_details->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $zoom_meeting->meeting_details->join_url, $zoom_meeting->meeting_details->encrypted_password ) : $zoom_meeting->meeting_details->join_url;
							?>
                            <a target="_blank" href="<?php echo esc_url( $join_url ); ?>" class="btn btn-join-link btn-join-via-app"><?php echo apply_filters( 'vczapi_join_meeting_via_app_text', __( 'Join Meeting via Zoom App', 'video-conferencing-with-zoom-api' ) ); ?></a>
							<?php
						}

						if ( ! empty( $zoom_meeting->meeting_details->id ) && ! empty( $zoom_meeting->ID ) && empty( $zoom_meeting->fields['site_option_browser_join'] ) && ! vczapi_check_disable_joinViaBrowser() ) {
							if ( ! empty( $zoom_meeting->meeting_details->password ) ) {
								echo vczapi_get_browser_join_links( $zoom_meeting->ID, $zoom_meeting->meeting_details->id, $zoom_meeting->meeting_details->password );
							} else {
								echo vczapi_get_browser_join_links( $zoom_meeting->ID, $zoom_meeting->meeting_details->id );
							}
						}

						if ( ! empty( $zoom_meeting->meeting_details->start_url ) && vczapi_check_author( $zoom_meeting->ID ) ) { ?>
                            <a target="_blank" href="<?php echo esc_url( $zoom_meeting->meeting_details->start_url ); ?>" rel="nofollow" class="btn btn-start-link"><?php _e( 'Start Meeting', 'video-conferencing-with-zoom-api' ); ?></a>
						<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>