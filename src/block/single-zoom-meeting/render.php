<?php
global $post;
if ( ! empty( $post ) && $post->post_type == 'zoom-meetings' ) {
	$template = vczapi_get_single_or_zoom_template( $post );

	ob_start();
	include $template;

	return ob_get_clean();
}
