<?php
/**
 * Render template for Standalone Zoom Meeting Countdown Block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

use Codemanas\VczApi\Blocks\DetailsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pass attributes directly to DetailsHelper (uses static in-memory caching)
$meeting_details = DetailsHelper::get_meeting_details( [
	'sourceType'            => $attributes['sourceType'] ?? 'current',
	'selectedMeetingPostId' => $attributes['selectedMeetingPostId'] ?? 0,
	'customMeetingId'       => $attributes['customMeetingId'] ?? '',
] );

$start_time_raw  = $meeting_details['start_time'] ?? '';
$duration        = (int) ( $meeting_details['duration'] ?? 60 );
$layout_preset   = $attributes['layoutPreset'] ?? 'cards';
$started_text    = $attributes['startedText'] ?? __( 'Meeting has started', 'video-conferencing-with-zoom-api' );
$ended_text      = $attributes['endedText'] ?? __( 'Meeting has ended', 'video-conferencing-with-zoom-api' );
$show_days       = $attributes['showDays'] ?? true;
$show_seconds    = $attributes['showSeconds'] ?? true;
$all_day_started = $attributes['keepStartedAllDay'] ?? false;

if ( empty( $start_time_raw ) ) {
	return;
}

$utc_start_time = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $start_time_raw ) );

$wrapper_attributes = get_block_wrapper_attributes( [
	'class'                     => 'vczapi-meeting-countdown vczapi-meeting-countdown--' . sanitize_html_class( $layout_preset ),
	'data-vczapi-utc-start'      => $utc_start_time,
	'data-vczapi-duration'       => $duration,
	'data-vczapi-started-text'   => esc_attr( $started_text ),
	'data-vczapi-ended-text'     => esc_attr( $ended_text ),
	'data-vczapi-show-days'      => $show_days ? '1' : '0',
	'data-vczapi-show-seconds'   => $show_seconds ? '1' : '0',
	'data-vczapi-all-day-started' => $all_day_started ? '1' : '0',
] );
?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="vczapi-countdown-wrapper">
        <div class="vczapi-countdown-timer">
			<?php if ( $show_days ) : ?>
                <div class="vczapi-countdown-unit vczapi-countdown-unit--days">
                    <span class="vczapi-countdown-value">00</span>
                    <span class="vczapi-countdown-label"><?php esc_html_e( 'DAYS', 'video-conferencing-with-zoom-api' ); ?></span>
                </div>
			<?php endif; ?>
            <div class="vczapi-countdown-unit vczapi-countdown-unit--hours">
                <span class="vczapi-countdown-value">00</span>
                <span class="vczapi-countdown-label"><?php esc_html_e( 'HOURS', 'video-conferencing-with-zoom-api' ); ?></span>
            </div>
            <div class="vczapi-countdown-unit vczapi-countdown-unit--minutes">
                <span class="vczapi-countdown-value">00</span>
                <span class="vczapi-countdown-label"><?php esc_html_e( 'MINUTES', 'video-conferencing-with-zoom-api' ); ?></span>
            </div>
			<?php if ( $show_seconds ) : ?>
                <div class="vczapi-countdown-unit vczapi-countdown-unit--seconds">
                    <span class="vczapi-countdown-value">00</span>
                    <span class="vczapi-countdown-label"><?php esc_html_e( 'SECONDS', 'video-conferencing-with-zoom-api' ); ?></span>
                </div>
			<?php endif; ?>
        </div>
        <div class="vczapi-countdown-status-message" style="display: none;"></div>
    </div>
</div>