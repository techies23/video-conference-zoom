<?php
if ( empty( $attributes ) ) {
	_e( 'Choose Host First', 'video-conferencing-with-zoom-api' );

	return;
}
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	$shortcode = ( $attributes['shouldShow']['value'] == "webinar" ) ? 'zoom_list_host_webinars' : 'zoom_list_host_meetings';
	echo do_shortcode( '[' . $shortcode . ' host="' . $attributes['host']['value'] . '"]' );
	?>
</div>
