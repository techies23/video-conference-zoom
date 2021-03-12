<?php
/**
 * The template for displaying product content in the single-meeting.php template
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/single-meetings.php.
 *
 * @author Deepen.
 * @created_on 11/19/19
 * @updated 3.7.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook: vczoom_before_single_meeting.
 */
do_action( 'vczoom_before_single_meeting' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.

	return;
}

/**
 *  Hook: vczoom_before_content
 */
do_action( 'vczoom_before_content' );
?>

    <div class="vczapi-wrap dpn-zvc-single-content-wrapper dpn-zvc-single-content-wrapper-<?php echo get_the_id(); ?>" id="dpn-zvc-single-content-wrapper-<?php echo get_the_id(); ?>">
        <div class="vczapi-col-8">
			<?php
			/**
			 *  Hook: vczoom_single_content_left
			 *
			 * @video_conference_zoom_featured_image - 10
			 * @video_conference_zoom_main_content - 20
			 */
			do_action( 'vczoom_single_content_left' );
			?>
        </div>
        <div class="vczapi-col-4">
            <div class="dpn-zvc-sidebar-wrapper">
				<?php
				/**
				 *  Hook: vczoom_single_content_right
				 *
				 * @video_conference_zoom_countdown_timer - 10
				 * @video_conference_zoom_meeting_details - 20
				 * @video_conference_zoom_meeting_join - 30
				 *
				 */
				do_action( 'vczoom_single_content_right' );
				?>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

<?php
/**
 *  Hook: vczoom_after_content
 */
do_action( 'vczoom_after_content' );

/**
 * Hook: video_conference_zoom_before_single_meeting.
 */
do_action( 'video_conference_zoom_after_single_meeting' );
?>