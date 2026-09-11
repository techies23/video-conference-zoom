<?php
/**
 * Render template for Zoom Meeting Details - Hosted By Block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

use Codemanas\VczApi\Blocks\DetailsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$source_type      = $block->context['vczapi/sourceType'] ?? 'current';
$host_type        = $attributes['hostType'] ?? 'post_author';
$custom_host_name = $attributes['customHostName'] ?? '';
$show_label       = $attributes['showLabel'] ?? true;
$label            = $attributes['label'] ?? __( 'Hosted By:', 'video-conferencing-with-zoom-api' );

$host_name = '';

// If container uses custom source or block is set to custom
if ( 'custom' === $source_type || 'custom' === $host_type ) {
	$host_name = $custom_host_name;
} else {
	$context_attributes = [
		'sourceType'            => $source_type,
		'selectedMeetingPostId' => $block->context['vczapi/selectedMeetingPostId'] ?? 0,
		'customMeetingId'       => $block->context['vczapi/customMeetingId'] ?? '',
	];

	$meeting_details = DetailsHelper::get_meeting_details( $context_attributes );
	$post_id         = $meeting_details['post_id'] ?? get_the_ID();

	if ( $post_id ) {
		$author_id  = get_post_field( 'post_author', $post_id );
		$first_name = get_the_author_meta( 'first_name', $author_id );
		$last_name  = get_the_author_meta( 'last_name', $author_id );

		$full_name = trim( $first_name . ' ' . $last_name );
		$host_name = ! empty( $full_name ) ? $full_name : get_the_author_meta( 'display_name', $author_id );
	}
}

if ( empty( $host_name ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'vczapi-meeting-detail-item vczapi-meeting-detail-hosted-by',
] );
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if ( $show_label && ! empty( $label ) ) : ?>
        <span class="vczapi-meeting-detail-item__label"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
    <span class="vczapi-meeting-detail-item__value"><?php echo esc_html( $host_name ); ?></span>
</div>