<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	$shortcode = '';
	if ( isset( $attributes['showBy'] ) && ! empty( $attributes['showBy'] ) ) {
		$shortcode = ( $attributes['showBy'] == 'host' ) ? 'zoom_recordings' : 'zoom_recordings_by_meeting';
		if ( $attributes['showBy'] == 'host' ) {
			if ( isset( $attributes['host']['value'] ) && ! empty( $attributes['host']['value'] ) ) {
				$shortcode .= ' host_id="' . $attributes['host']['value'] . '"';
			}
		} else {
			if ( isset( $attributes['selectedMeeting'] ) && ! empty( $attributes['selectedMeeting'] ) ) {
				$shortcode .= ' meeting_id="' . $attributes['selectedMeeting']['value'] . '"';
			}
		}
	}
	if ( isset( $attributes['downloadable'] ) && ! empty( $attributes['downloadable'] ) ) {
		$maybe     = $attributes['downloadable'] == 'true' ? 'yes' : 'no';
		$shortcode .= ' downloadable="' . $maybe . '"';
	}

	echo do_shortcode( '[' . $shortcode . ']' );
	?>
</div>
