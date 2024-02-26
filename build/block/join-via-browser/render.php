<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	$shortcode_args = '';
	if ( isset( $attributes['selectedMeeting'] ) && ! empty( $attributes['selectedMeeting'] ) ) {
		$shortcode_args .= ' meeting_id="' . $attributes['selectedMeeting']['value'] . '"';
	}
	if ( isset( $attributes['login_required'] ) && ! empty( $attributes['login_required'] ) ) {
		$shortcode_args .= ' login_required="' . $attributes['login_required'] . '"';
	}
	if ( isset( $attributes['disable_countdown'] ) && ! empty( $attributes['disable_countdown'] ) ) {
		$shortcode_args .= ' disable_countdown="' . $attributes['disable_countdown'] . '"';
	}
	if ( isset( $attributes['passcode'] ) && ! empty( $attributes['passcode'] ) ) {
		$shortcode_args .= ' passcode="' . $attributes['passcode'] . '"';
	}
	if ( ! empty( $attributes['shouldShow'] ) && ! empty( $attributes['shouldShow']['value'] ) && $attributes['shouldShow']['value'] == "webinar" ) {
		$shortcode_args .= ' webinar="yes"';
	}
	echo do_shortcode( '[zoom_join_via_browser iframe="no" ' . $shortcode_args . ']' );
	?>
</div>
