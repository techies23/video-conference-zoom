<?php
/**
 * Render template for Zoom Meeting Details - Duration Block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

use Codemanas\VczApi\Blocks\DetailsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract block context inherited from ancestor container
$context_attributes = [
	'sourceType'            => $block->context['vczapi/sourceType'] ?? 'current',
	'selectedMeetingPostId' => $block->context['vczapi/selectedMeetingPostId'] ?? 0,
	'customMeetingId'       => $block->context['vczapi/customMeetingId'] ?? '',
];

$meeting_details = DetailsHelper::get_meeting_details( $context_attributes );
$duration        = $meeting_details['duration'] ?? 0;
$show_label      = $attributes['showLabel'] ?? true;
$label           = $attributes['label'] ?? __( 'Duration:', 'video-conferencing-with-zoom-api' );

if ( empty( $duration ) ) {
	return;
}

// Format duration string (e.g., "40 minutes" or "1 hour 30 minutes")
if ( $duration >= 60 ) {
	$hours             = floor( $duration / 60 );
	$remaining_minutes = $duration % 60;

	if ( $remaining_minutes > 0 ) {
		/* translators: 1: Hours, 2: Minutes */
		$duration_text = sprintf( _n( '%1$d hour %2$d mins', '%1$d hours %2$d mins', $hours, 'video-conferencing-with-zoom-api' ), $hours, $remaining_minutes );
	} else {
		/* translators: %d: Hours */
		$duration_text = sprintf( _n( '%d hour', '%d hours', $hours, 'video-conferencing-with-zoom-api' ), $hours );
	}
} else {
	/* translators: %d: Minutes */
	$duration_text = sprintf( _n( '%d minute', '%d minutes', $duration, 'video-conferencing-with-zoom-api' ), $duration );
}

$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'vczapi-meeting-detail-item vczapi-meeting-detail-duration',
] );
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( $show_label && ! empty( $label ) ) : ?>
        <span class="vczapi-meeting-detail-item__label"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
    <span class="vczapi-meeting-detail-item__value"><?php echo esc_html( $duration_text ); ?></span>
</div>