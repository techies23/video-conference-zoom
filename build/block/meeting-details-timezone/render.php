<?php
/**
 * Render template for Zoom Meeting Details - Timezone Block.
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
$meeting_tz       = $meeting_details['timezone'] ?? 'UTC';
$show_label       = $attributes['showLabel'] ?? true;
$label            = $attributes['label'] ?? __( 'Timezone:', 'video-conferencing-with-zoom-api' );
$timezone_display = $attributes['timezoneDisplay'] ?? 'user';

$wrapper_attributes = get_block_wrapper_attributes( [
	'class'                  => 'vczapi-meeting-detail-item vczapi-meeting-detail-timezone',
	'data-vczapi-meeting-tz' => $meeting_tz,
	'data-vczapi-tz-mode'    => $timezone_display,
] );
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( $show_label && ! empty( $label ) ) : ?>
        <span class="vczapi-meeting-detail-item__label"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
    <span class="vczapi-meeting-detail-item__value"><?php echo esc_html( $meeting_tz ); ?></span>
</div>