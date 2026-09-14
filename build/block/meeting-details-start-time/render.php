<?php
/**
 * Render template for Zoom Meeting Details - Start Time Block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

use Codemanas\VczApi\Blocks\DetailsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context_attributes = [
	'sourceType'            => $block->context['vczapi/sourceType'] ?? 'current',
	'selectedMeetingPostId' => $block->context['vczapi/selectedMeetingPostId'] ?? 0,
	'customMeetingId'       => $block->context['vczapi/customMeetingId'] ?? '',
];

$meeting_details  = DetailsHelper::get_meeting_details( $context_attributes );
$start_time_raw   = $meeting_details['start_time'] ?? '';
$meeting_tz       = $meeting_details['timezone'] ?? 'UTC';
$show_label       = $attributes['showLabel'] ?? true;
$label            = $attributes['label'] ?? __( 'Start Time:', 'video-conferencing-with-zoom-api' );
$timezone_display = $attributes['timezoneDisplay'] ?? 'user';
$date_style       = $attributes['dateStyle'] ?? 'full';

if ( empty( $start_time_raw ) ) {
	return;
}

// Format UTC ISO 8601 string for client JS parsing
$utc_time = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $start_time_raw ) );

// Simple PHP fallback for noscript/crawler engines
$formatted_fallback = date_i18n( 'l, F j, Y g:i A', strtotime( $start_time_raw ) );

$wrapper_attributes = get_block_wrapper_attributes( [
	'class'                 => 'vczapi-meeting-detail-item vczapi-meeting-detail-start-time',
	'data-vczapi-utc'        => $utc_time,
	'data-vczapi-meeting-tz' => $meeting_tz,
	'data-vczapi-tz-mode'    => $timezone_display,
	'data-vczapi-style'      => $date_style,
] );
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( $show_label && ! empty( $label ) ) : ?>
        <span class="vczapi-meeting-detail-item__label"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
    <span class="vczapi-meeting-detail-item__value"><?php echo esc_html( $formatted_fallback ); ?></span>
</div>