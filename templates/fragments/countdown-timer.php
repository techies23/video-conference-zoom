<?php
/**
 * The template for displaying meeting countdown timer
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/fragments/countdown-timer.php.
 *
 * @author      Deepen Bajracharya
 * @since       3.0.0
 * @version     3.6.0
 */

global $zoom;

if ( ! empty( $zoom['shortcode'] ) && ! empty( $zoom['parameters']['countdown'] ) && $zoom['parameters']['countdown'] == "false" ) {
	return;
}

if ( ! vczapi_pro_version_active() && vczapi_pro_check_type( $zoom['api']->type ) || empty( $zoom ) ) {
	?>
    <div class="dpn-zvc-sidebar-box">
        <p><?php _e( 'PRO version is required for this meeting to be displayed.', 'video-conferencing-with-zoom-api' ); ?></p>
    </div>
	<?php
}

$countdown_html = '<div class="dpn-zvc-timer-cell">
                    <div class="dpn-zvc-timer-cell-number">
                        <div id="dpn-zvc-timer-days">00</div>
                    </div>
                    <div class="dpn-zvc-timer-cell-string">' . __( 'days', 'video-conferencing-with-zoom-api' ) . '</div>
                </div>
                <div class="dpn-zvc-timer-cell">
                    <div class="dpn-zvc-timer-cell-number">
                        <div id="dpn-zvc-timer-hours">00</div>
                    </div>
                    <div class="dpn-zvc-timer-cell-string">' . __( 'hours', 'video-conferencing-with-zoom-api' ) . '</div>
                </div>
                <div class="dpn-zvc-timer-cell">
                    <div class="dpn-zvc-timer-cell-number">
                        <div id="dpn-zvc-timer-minutes">00</div>
                    </div>
                    <div class="dpn-zvc-timer-cell-string">' . __( 'minutes', 'video-conferencing-with-zoom-api' ) . '</div>
                </div>
                <div class="dpn-zvc-timer-cell">
                    <div class="dpn-zvc-timer-cell-number">
                        <div id="dpn-zvc-timer-seconds">00</div>
                    </div>
                    <div class="dpn-zvc-timer-cell-string">' . __( 'seconds', 'video-conferencing-with-zoom-api' ) . '</div>
                </div>';

if ( ! empty( $zoom['api']->start_time ) ) {
	if ( ! empty( $zoom['shortcode_post_by_id'] ) ) {
		?>
        <div class="vczapi-show-by-postid-countdown">
            <h3 class="vczapi-show-by-postid-countdown-title"><?php _e( 'Meeting starts in', 'video-conferencing-with-zoom-api' ); ?>
                :</h3>
            <div class="dpn-zvc-timer vczapi-show-by-postid-countdown-timer" id="dpn-zvc-timer"
                 data-date="<?php echo $zoom['api']->start_time; ?>"
                 data-state="<?php echo ! empty( $zoom['api']->state ) ? $zoom['api']->state : false; ?>"
                 data-tz="<?php echo $zoom['api']->timezone; ?>">
				<?php echo $countdown_html; ?>
            </div>
        </div>
		<?php
	} else {
		?>
        <div class="dpn-zvc-sidebar-box">
            <div class="dpn-zvc-timer" id="dpn-zvc-timer" data-date="<?php echo $zoom['api']->start_time; ?>"
                 data-state="<?php echo ! empty( $zoom['api']->state ) ? $zoom['api']->state : false; ?>"
                 data-tz="<?php echo $zoom['api']->timezone; ?>">
				<?php echo $countdown_html; ?>
            </div>
        </div>
		<?php
	}
}