<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	$shortcode = 'zoom_meeting_post';
	if ( isset( $attributes['postID'] ) && ! empty( $attributes['postID'] ) ) {
		$shortcode .= ' post_id="' . $attributes['postID'] . '"';
	}

	if ( isset( $attributes['template'] ) && ! empty( $attributes['template'] ) ) {
		$shortcode .= ' template="' . $attributes['template'] . '"';
	}

	$description = $attributes['description'] ? "true" : "false";
	$shortcode   .= ' description="' . $description . '"';

	$countdown = $attributes['countdown'] ? "true" : "false";
	$shortcode .= ' countdown="' . $countdown . '"';

	$details   = $attributes['details'] ? "true" : "false";
	$shortcode .= ' details="' . $details . '"';
	echo do_shortcode( '[' . $shortcode . ']' );
	?>
</div>
