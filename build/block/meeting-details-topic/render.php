<?php
/**
 * Render template for Zoom Meeting Details - Topic Block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

use Codemanas\VczApi\Blocks\DetailsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Extract context inherited from the container parent block
$source_type = $block->context['vczapi/sourceType'] ?? 'current';
$post_id     = $block->context['vczapi/selectedMeetingPostId'] ?? 0;
$custom_id   = $block->context['vczapi/customMeetingId'] ?? '';

// 2. Resolve cached meeting details
$meeting_details = DetailsHelper::get_meeting_details( [
	'sourceType'            => $source_type,
	'selectedMeetingPostId' => $post_id,
	'customMeetingId'       => $custom_id,
] );


$topic           = $meeting_details['topic'] ?? '';
$show_label      = $attributes['showLabel'] ?? true;
$label           = $attributes['label'] ?? __( 'Topic:', 'video-conferencing-with-zoom-api' );

if ( empty( $topic ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'vczapi-meeting-detail-item vczapi-meeting-detail-topic',
] );
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( $show_label && ! empty( $label ) ) : ?>
        <span class="vczapi-meeting-detail-item__label"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
    <span class="vczapi-meeting-detail-item__value"><?php echo esc_html( $topic ); ?></span>
</div>