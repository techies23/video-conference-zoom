<?php
/**
 * The template for displaying archive of meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/archive-meetings.php.
 *
 * @author Deepen
 * @since 3.0.0
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Before the LOOP archive page
 */
do_action( 'vczapi_before_main_archive_content' );
?>
    <div id="vczapi-primary" class="vczapi-primary container">

        <header class="page-header">
            <h1 class="page-title vczapi-archive-page-title"><?php _e( 'Meetings', 'video-conferencing-with-zoom-api' ); ?></h1>
        </header><!-- .page-header -->
		<?php
		/**
		 * BEFORE LOOP HOOK
		 */
		do_action( 'vczapi_before_main_content_post_loop' );

		if ( have_posts() ) {
			?>
            <div class="vczapi-list-zoom-meetings">
                <div class="vczapi-wrap">
					<?php
					// Start the Loop.
					while ( have_posts() ) {
						the_post();

						do_action( 'vczapi_main_content_post_loop' );

						vczapi_get_template_part( 'content', 'meeting' );
					}

					wp_reset_postdata();
					?>
                </div>
            </div>
            <div class="vczapi-pagination">
				<?php echo paginate_links(); ?>
            </div>
			<?php
		} else {
			echo "<p class='vczapi-no-meeting-found'>" . __( 'No Meetings found.', 'video-conferencing-with-zoom-api' ) . "</p>";
		}

		/**
		 * AFTER LOOP HOOK
		 */
		do_action( 'vczapi_after_main_content_post_loop' );
		?>
    </div>
<?php
/**
 * After loop call
 */
do_action( 'vczapi_after_main_archive_content' );

get_footer();
