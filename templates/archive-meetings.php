<?php
/**
 * The template for displaying archive of meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/archive-meetings.php.
 *
 * @author Deepen
 * @since 3.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

    <div id="dpn-zvc-primary" class="dpn-zvc-primary container">

		<?php if ( have_posts() ) {
			// Start the Loop.
			while ( have_posts() ) {
				the_post();

				vczapi_get_template_part( 'content', 'meeting' );
			}
		} else {
			echo "<p>" . __( 'No Meetings found.', 'video-conferencing-with-zoom-api' ) . "</p>";
		}
		?>
    </div>

<?php
get_footer();
