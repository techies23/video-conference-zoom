<?php

if ( empty( $attributes ) ) {
	?>
    <div <?php echo get_block_wrapper_attributes(); ?>>
		<?php echo __( "Meeting has not been selected, please select meeting first" ); ?>
    </div>
	<?php
}
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	/**
	 * Shortcode functionality for rendering specific content.
	 *
	 * @param array $atts An array of attributes passed to the shortcode.
	 *
	 * @return string The HTML content to be rendered.
	 */
	$shortcode = ( $attributes['shouldShow']['value'] == 'webinar' ) ? 'zoom_api_webinar' : 'zoom_api_link';
	if ( isset( $attributes['selectedMeeting'] ) && ! empty( 'selectedMeeting' ) ) {
		$shortcode .= ( $attributes['shouldShow']['value'] == 'webinar' )
			?
			' webinar_id="' . $attributes['selectedMeeting']['value'] . '"'
			:
			' meeting_id="' . $attributes['selectedMeeting']['value'] . '"';
	}
	if ( isset( $attributes['link_only'] ) && ! empty( 'link_only' ) ) {
		$shortcode .= ' link_only="' . $attributes['link_only'] . '"';
	}
	echo do_shortcode( '[' . $shortcode . ']' );
	?>
</div>
