<?php
/**
 * The Template for displaying all single meetings
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/zoom-listing.php.
 *
 * @package    Video Conferencing with Zoom API/Templates
 * @version     3.2.2
 */

$meeting_details = get_post_meta( get_the_id(), '_meeting_fields', true );
?>
<div class="vczapi-list-zoom-meetings--item">
	<?php if ( has_post_thumbnail() ) { ?>
        <div class="vczapi-list-zoom-meetings--item__image">
			<?php the_post_thumbnail(); ?>
        </div><!--Image End-->
	<?php } ?>
    <div class="vczapi-list-zoom-meetings--item__details">
        <h3><?php the_title(); ?></h3>
        <div class="vczapi-list-zoom-meetings--item__details__meta">
            <div class="hosted-by meta">
                <strong><?php _e( 'Hosted By:', 'video-conferencing-with-zoom-api' ); ?></strong> <span><?php echo get_the_author(); ?></span>
            </div>
            <div class="start-date meta">
                <strong><?php _e( 'Start', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                <span><?php echo date( 'F j, Y @ g:i a', strtotime( $meeting_details['start_date'] ) ); ?></span>
            </div>
            <div class="timezone meta">
                <strong><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                <span><?php echo $meeting_details['timezone']; ?></span>
            </div>
        </div>
        <a href="<?php echo esc_url( get_the_permalink() ) ?>" class="btn"><?php _e( 'See More', 'video-conferencing-with-zoom-api' ); ?></a>
    </div><!--Details end-->
</div><!--List item end-->