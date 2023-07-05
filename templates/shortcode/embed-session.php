<?php
/**
 * The template for displaying embedded zoom join
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/embed-session.php
 *
 * @author Deepen Bajracharya
 * @since 3.9.0
 * @version 3.9.0
 */

global $zoom;

$meeting_id = ! empty( $zoom ) && ! empty( $zoom->id ) ? $zoom->id : false;
if ( ! $meeting_id ) {
	return;
}

//SETUP
if ( ! empty( $zoom->shortcode_attributes['title'] ) ) {
	?>
    <h1><?php esc_html_e( $zoom->shortcode_attributes['title'] ); ?></h1>
	<?php
}

$post_type_link    = get_post_type_archive_link( 'zoom-meetings' );
$browser_join_link = array(
	'join' => \Codemanas\VczApi\Helpers\Encryption::encrypt( $meeting_id ),
	'type' => 'meeting'
);
if ( ! empty( $zoom->shortcode_attributes['passcode'] ) ) {
	$browser_join_link['pak'] = \Codemanas\VczApi\Helpers\Encryption::encrypt( $zoom->shortcode_attributes['passcode'] );
}
$join_via_browser_link = add_query_arg( $browser_join_link, $post_type_link );

if ( isset( $zoom->zoom_states[ $meeting_id ]['state'] ) && $zoom->zoom_states[ $meeting_id ]['state'] == "ended" ) {
	echo '<h3>' . esc_html__( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api ' ) . '</h3>';
} else if ( $zoom->meeting_time_check > $zoom->meeting_timezone_time && ! empty( $zoom->shortcode_attributes['disable_countdown'] ) && $zoom->shortcode_attributes['disable_countdown'] == "no" ) {
	?>
    <div class="vczapi-jvb-countdown-wrapper">
        <h3 class="vczapi-jvb-countdown-wrapper-countdown-title"><?php _e( 'Meeting starts in', 'video-conferencing-with-zoom-api' ); ?>:</h3>
        <div class="dpn-zvc-timer zoom-join-via-browser-countdown" id="dpn-zvc-timer" data-date="<?php echo $zoom->start_time; ?>" data-tz="<?php echo $zoom->timezone; ?>">
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-days">00</div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'days', 'video-conferencing-with-zoom-api' ); ?></div>
            </div>
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-hours">00</div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'hours', 'video-conferencing-with-zoom-api' ); ?></div>
            </div>
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-minutes">00</div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'minutes', 'video-conferencing-with-zoom-api' ); ?></div>
            </div>
            <div class="dpn-zvc-timer-cell">
                <div class="dpn-zvc-timer-cell-number">
                    <div id="dpn-zvc-timer-seconds">00</div>
                </div>
                <div class="dpn-zvc-timer-cell-string"><?php _e( 'seconds', 'video-conferencing-with-zoom-api' ); ?></div>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ( $zoom->shortcode_attributes['iframe'] == "yes" ) {
	if ( $zoom->meeting_time_check < $zoom->meeting_timezone_time || ( ! empty( $zoom->shortcode_attributes['disable_countdown'] ) && $zoom->shortcode_attributes['disable_countdown'] == "yes" ) ) {
		?>
        <div class="vczapi-jvb-wrapper zoom-window-wrap">
            <div id="<?php echo ! empty( $zoom->shortcode_attributes['id'] ) ? esc_attr( $zoom->shortcode_attributes['id'] ) : 'video-conferncing-embed-iframe'; ?>" class="zoom-iframe-container">
                <iframe style="width:100%; <?php echo ! empty( $zoom->shortcode_attributes['height'] ) ? "height: " . $zoom->shortcode_attributes['height'] : "height: 500px;"; ?>" sandbox="allow-forms allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-top-navigation" allow="cross-origin-isolated; encrypted-media; autoplay; microphone; camera" src="<?php echo esc_url( $join_via_browser_link ); ?>"></iframe>
            </div>
        </div>
		<?php
	}
} else { ?>
    <div class="vczapi-jvb-countdown-content">
		<?php if ( ! empty( $zoom->shortcode_attributes['image'] ) ) { ?>
            <div class="vczapi-jvb-countdown-content-image">
                <img src="<?php echo esc_url( $zoom->shortcode_attributes['image'] ); ?>" alt="<?php echo $zoom->topic; ?>">
            </div>
		<?php } ?>
        <div class="vczapi-jvb-countdown-content-contents">
            <div class="vczapi-jvb-countdown-content-description">
                <h2 class="vczapi-jvb-countdown-content-description-topic"><?php echo $zoom->topic; ?></h2>
				<?php if ( ! empty( $zoom->start_time ) ) { ?>
                    <div class="vczapi-jvb-countdown-content-description-time"><strong><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo vczapi_dateConverter( $zoom->start_time, $zoom->timezone, true ); ?></div>
				<?php } ?>
                <div class="vczapi-jvb-countdown-content-description-timezone"><strong><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $zoom->timezone; ?></div>
                <div class="vczapi-jvb-countdown-content-description-timezone"><strong><?php _e( 'Password', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $zoom->password; ?></div>
            </div>
            <div class="vczapi-jvb-countdown-content-links">
                <a class="btn btn-join-link btn-join-via-app" href="<?php echo esc_url( $join_via_browser_link ); ?>"><?php _e( 'Join via Browser', 'video-conferencing-with-zoom-api' ); ?></a>
                <!--            <a class="btn btn-join-link btn-join-via-browser" href="--><?php //echo $zoom->join_link; ?><!--">Join via Zoom App</a>-->
            </div>
        </div>
    </div>
<?php } ?>


