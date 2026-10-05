<?php
defined( 'ABSPATH' ) || exit;

/**
 * Template variable `$zoom` supplied via template loader context $args['zoom'].
 * Fallback to legacy global if third-party extensions include this file directly.
 */
$zoom = $zoom ?? ( $GLOBALS['zoom'] ?? [] );

$api           = $zoom['api'] ?? null;
$has_thumbnail = has_post_thumbnail();
dump($zoom);
?>
<div class="vczapi-show-by-postid">
	<div class="vczapi-show-by-postid-contents vczapi-show-by-postid-flex">
		<?php if ( $has_thumbnail ) : ?>
			<div class="vczapi-show-by-postid-contents-image">
				<?php the_post_thumbnail( 'full', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo $has_thumbnail ? 'vczapi-show-by-postid-contents-sections' : 'vczapi-show-by-postid-contents-sections vczapi-show-by-postid-contents-sections-full'; ?>">
			<div class="vczapi-show-by-postid-contents-sections-description">
				<h2 class="vczapi-show-by-postid-contents-sections-description-topic"><?php the_title(); ?></h2>

				<?php if ( ! empty( $api->start_time ) ) : ?>
					<div class="vczapi-hosted-by-start-time-wrap">
						<span><strong><?php esc_html_e( 'Session date', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
						<span class="sidebar-start-time">
                                <?php echo esc_html( \Codemanas\VczApi\Helpers\Date::dateConverter( $api->start_time, $api->timezone ?? '', 'F j, Y @ g:i a' ) ); ?>
                            </span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $zoom['terms'] ) ) : ?>
					<div class="vczapi-category-wrap">
						<span><strong><?php esc_html_e( 'Category', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
						<span class="sidebar-category"><?php echo esc_html( implode( ', ', (array) $zoom['terms'] ) ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $api->duration ) ) :
					$duration = \Codemanas\VczApi\Helpers\Date::convertMinutesToFormat( $api->duration, false );
					?>
					<div class="vczapi-duration-wrap">
						<span><strong><?php esc_html_e( 'Duration', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
						<span>
                                <?php
                                if ( ! empty( $duration['hr'] ) ) {
	                                printf(
	                                /* translators: 1: Hours count, 2: Minutes count */
		                                esc_html__( '%1$s \%2$s', 'video-conferencing-with-zoom-api' ),
		                                sprintf( _n( '%s hour', '%s hours', $duration['hr'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $duration['hr'] ) ),
		                                sprintf( _n( '%s minute', '%s minutes', $duration['min'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $duration['min'] ) )
	                                );
                                } else {
	                                printf( _n( '%s minute', '%s minutes', $duration['min'], 'video-conferencing-with-zoom-api' ), number_format_i18n( $duration['min'] ) );
                                }
                                ?>
                            </span>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $api->timezone ) ) : ?>
					<div class="vczapi-timezone-wrap">
						<span><strong><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
						<span class="vczapi-single-meeting-timezone"><?php echo esc_html( $api->timezone ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="dpn-zvc-sidebar-content"></div>
		</div>
	</div>

	<?php if ( get_the_content() ) : ?>
		<div class="vczapi-show-by-postid-contents-sections-thecontent">
			<?php the_content(); ?>
		</div>
	<?php endif; ?>
</div>