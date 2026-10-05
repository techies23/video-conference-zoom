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

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\Encryption;
use Codemanas\VczApi\Shortcodes\Support\ZoomResponse;

global $zoom;

$zoom = ZoomResponse::to_array( $zoom );

$meeting_id = $zoom['id'] ?? false;
if ( ! $meeting_id ) {
	return;
}

$attributes         = ZoomResponse::to_array( $zoom['shortcode_attributes'] ?? [] );
$title              = $attributes['title'] ?? '';
$passcode           = $attributes['passcode'] ?? '';
$iframe             = $attributes['iframe'] ?? 'yes';
$iframe_id          = $attributes['id'] ?? 'video-conferncing-embed-iframe';
$iframe_height      = $attributes['height'] ?? '500px';
$disable_countdown  = $attributes['disable_countdown'] ?? 'yes';
$image              = $attributes['image'] ?? '';

$topic      = $zoom['topic'] ?? '';
$timezone   = $zoom['timezone'] ?? '';
$password   = $zoom['password'] ?? '';
$start_time = $zoom['start_time'] ?? '';

$meeting_time_check    = $zoom['meeting_time_check'] ?? 0;
$meeting_timezone_time = $zoom['meeting_timezone_time'] ?? 0;

if ( ! empty( $title ) ) {
	?>
	<h1><?php echo esc_html( $title ); ?></h1>
	<?php
}

$browser_join_link = [
	'join' => Encryption::encrypt( $meeting_id ),
	'type' => 'meeting',
];

if ( ! empty( $passcode ) ) {
	$browser_join_link['pak'] = Encryption::encrypt( $passcode );
}

$post_type_link       = get_post_type_archive_link( 'zoom-meetings' );
$join_via_browser_link = add_query_arg( $browser_join_link, $post_type_link );
$iframe_style          = 'width:100%; height:' . $iframe_height . ';';

$meeting_state = $zoom['zoom_states'][ $meeting_id ]['state'] ?? '';

if ( 'ended' === $meeting_state ) {
	echo '<h3>' . esc_html__( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api' ) . '</h3>';
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
<?php }

if ( 'yes' === $iframe ) {
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
					<div class="vczapi-jvb-countdown-content-description-time"><strong><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( Date::dateConverter( $start_time, $timezone ) ); ?></div>
                <?php } ?>
				<div class="vczapi-jvb-countdown-content-description-timezone"><strong><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $timezone ); ?></div>
				<div class="vczapi-jvb-countdown-content-description-timezone"><strong><?php esc_html_e( 'Password', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo esc_html( $password ); ?></div>
			</div>
			<div class="vczapi-jvb-countdown-content-links">
				<a class="btn btn-join-link btn-join-via-app" href="<?php echo esc_url( $join_via_browser_link ); ?>"><?php esc_html_e( 'Join via Browser', 'video-conferencing-with-zoom-api' ); ?></a>
			</div>
		</div>
	</div>
<?php } ?>