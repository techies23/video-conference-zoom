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

$shortcode_attributes = ! empty( $zoom->shortcode_attributes ) && is_array( $zoom->shortcode_attributes ) ? $zoom->shortcode_attributes : [];

$title             = ! empty( $shortcode_attributes['title'] ) ? $shortcode_attributes['title'] : '';
$passcode          = ! empty( $shortcode_attributes['passcode'] ) ? $shortcode_attributes['passcode'] : '';
$iframe            = ! empty( $shortcode_attributes['iframe'] ) ? $shortcode_attributes['iframe'] : 'yes';
$iframe_id         = ! empty( $shortcode_attributes['id'] ) ? $shortcode_attributes['id'] : 'video-conferncing-embed-iframe';
$iframe_height     = ! empty( $shortcode_attributes['height'] ) ? $shortcode_attributes['height'] : '500px';
$disable_countdown = ! empty( $shortcode_attributes['disable_countdown'] ) ? $shortcode_attributes['disable_countdown'] : 'yes';
$image             = ! empty( $shortcode_attributes['image'] ) ? $shortcode_attributes['image'] : '';

$topic      = ! empty( $zoom->topic ) ? $zoom->topic : '';
$timezone   = ! empty( $zoom->timezone ) ? $zoom->timezone : '';
$password   = ! empty( $zoom->password ) ? $zoom->password : '';
$start_time = ! empty( $zoom->start_time ) ? $zoom->start_time : '';

$meeting_time_check    = ! empty( $zoom->meeting_time_check ) ? $zoom->meeting_time_check : 0;
$meeting_timezone_time = ! empty( $zoom->meeting_timezone_time ) ? $zoom->meeting_timezone_time : 0;

if ( ! empty( $title ) ) {
	?>
	<h1><?php echo esc_html( $title ); ?></h1>
	<?php
}

$post_type_link    = get_post_type_archive_link( 'zoom-meetings' );
$browser_join_link = [
	'join' => \Codemanas\VczApi\Helpers\Encryption::encrypt( $meeting_id ),
	'type' => 'meeting',
];

if ( ! empty( $passcode ) ) {
	$browser_join_link['pak'] = \Codemanas\VczApi\Helpers\Encryption::encrypt( $passcode );
}

$join_via_browser_link = add_query_arg( $browser_join_link, $post_type_link );
$iframe_style          = 'width:100%; height:' . $iframe_height . ';';

if ( isset( $zoom->zoom_states[ $meeting_id ]['state'] ) && $zoom->zoom_states[ $meeting_id ]['state'] == 'ended' ) {
	echo '<h3>' . esc_html__( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api ' ) . '</h3>';
} elseif ( $meeting_time_check > $meeting_timezone_time && 'no' === $disable_countdown ) {
	?>
	<div class="vczapi-jvb-countdown-wrapper">
		<h3 class="vczapi-jvb-countdown-wrapper-countdown-title"><?php esc_html_e( 'Meeting starts in', 'video-conferencing-with-zoom-api' ); ?>:</h3>
		<div class="dpn-zvc-timer zoom-join-via-browser-countdown" id="dpn-zvc-timer" data-date="<?php echo esc_attr( $start_time ); ?>" data-tz="<?php echo esc_attr( $timezone ); ?>">
			<div class="dpn-zvc-timer-cell">
				<div class="dpn-zvc-timer-cell-number">
					<div id="dpn-zvc-timer-days">00</div>
				</div>
				<div class="dpn-zvc-timer-cell-string"><?php esc_html_e( 'days', 'video-conferencing-with-zoom-api' ); ?></div>
			</div>
			<div class="dpn-zvc-timer-cell">
				<div class="dpn-zvc-timer-cell-number">
					<div id="dpn-zvc-timer-hours">00</div>
				</div>
				<div class="dpn-zvc-timer-cell-string"><?php esc_html_e( 'hours', 'video-conferencing-with-zoom-api' ); ?></div>
			</div>
			<div class="dpn-zvc-timer-cell">
				<div class="dpn-zvc-timer-cell-number">
					<div id="dpn-zvc-timer-minutes">00</div>
				</div>
				<div class="dpn-zvc-timer-cell-string"><?php esc_html_e( 'minutes', 'video-conferencing-with-zoom-api' ); ?></div>
			</div>
			<div class="dpn-zvc-timer-cell">
				<div class="dpn-zvc-timer-cell-number">
					<div id="dpn-zvc-timer-seconds">00</div>
				</div>
				<div class="dpn-zvc-timer-cell-string"><?php esc_html_e( 'seconds', 'video-conferencing-with-zoom-api' ); ?></div>
			</div>
		</div>
	</div>
<?php } ?>

<?php if ( 'yes' === $iframe ) {
	if ( $meeting_time_check < $meeting_timezone_time || 'yes' === $disable_countdown ) {
		?>
		<div class="vczapi-jvb-wrapper zoom-window-wrap">
			<div id="<?php echo esc_attr( $iframe_id ); ?>" class="zoom-iframe-container">
				<iframe style="<?php echo esc_attr( $iframe_style ); ?>" allow="microphone; camera" src="<?php echo esc_url( $join_via_browser_link ); ?>"></iframe>
			</div>
		</div>
		<?php
	}
} else { ?>
	<div class="vczapi-jvb-countdown-content">
		<?php if ( ! empty( $image ) ) { ?>
			<div class="vczapi-jvb-countdown-content-image">
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $topic ); ?>">
			</div>
		<?php } ?>
		<div class="vczapi-jvb-countdown-content-contents">
			<div class="vczapi-jvb-countdown-content-description">
				<h2 class="vczapi-jvb-countdown-content-description-topic"><?php echo esc_html( $topic ); ?></h2>
				<?php if ( ! empty( $start_time ) ) { ?>
					<div class="vczapi-jvb-countdown-content-description-time"><strong><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( \Codemanas\VczApi\Helpers\Date::dateConverter( $start_time, $timezone ); ?>
                    </div>
				<?php } ?>
				<div class="vczapi-jvb-countdown-content-description-timezone"><strong><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $timezone ); ?></div>
				<div class="vczapi-jvb-countdown-content-description-timezone"><strong><?php esc_html_e( 'Password', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $password ); ?></div>
			</div>
			<div class="vczapi-jvb-countdown-content-links">
				<a class="btn btn-join-link btn-join-via-app" href="<?php echo esc_url( $join_via_browser_link ); ?>"><?php esc_html_e( 'Join via Browser', 'video-conferencing-with-zoom-api' ); ?></a>
				<!--            <a class="btn btn-join-link btn-join-via-browser" href="--><?php //echo $zoom->join_link; ?><!--">Join via Zoom App</a>-->
			</div>
		</div>
	</div>
<?php } ?>
