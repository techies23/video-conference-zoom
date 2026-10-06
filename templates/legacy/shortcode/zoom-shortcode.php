<?php
/**
 * The template for displaying shortcode
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-shortcode.php.
 *
 * @author Deepen.
 * @since 3.0.0
 * @version 3.0.0
 */

global $zoom_meetings;
?>

<div class="dpn-zvc-shortcode-op-wrapper">
	<?php
	/**
	 * Hook: vczoom_meeting_before_shortcode
     * @video_conference_zoom_shortcode_table
	 */
	do_action( 'vczoom_meeting_before_shortcode', $zoom_meetings );
	?>
</div>
