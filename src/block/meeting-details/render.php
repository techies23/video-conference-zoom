<?php
/**
 * Render template for Zoom - Meeting Details Container Block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prepare block wrapper attributes (handles classnames, inline styles, alignments, padding, etc.)
$wrapper_attributes = get_block_wrapper_attributes( [
	'class' => 'vczapi-meeting-details-container',
] );
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php echo $content; ?>
</div>