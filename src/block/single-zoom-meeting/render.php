<?php
global $post;
?>
<div <?php echo get_block_wrapper_attributes() ?>>
	<?php
	if ( ! empty( $post ) && $post->post_type == 'zoom-meetings' ) {
		$template = vczapi_get_single_or_zoom_template( $post );
		ob_start();
		include $template;
		echo ob_get_clean();
	}
	?>
</div>