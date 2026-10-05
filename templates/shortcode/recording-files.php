<?php
/**
 * The Template for displaying the files of a single recording.
 *
 * Rendered into a modal by the `get_recording` AJAX endpoint.
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/shortcode/recording-files.php.
 *
 * @package     Video Conferencing with Zoom API/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$recording    = ! empty( $args['recording'] ) && is_array( $args['recording'] ) ? $args['recording'] : [];
$downloadable = ! empty( $args['downloadable'] ) && 'yes' === $args['downloadable'];
$show_chat    = (bool) apply_filters( 'vczapi_show_recording_chat_file', false );
$show_pwd     = (bool) apply_filters( 'vczapi_recordings_show_password', false );
?>
<div class="vczapi-modal-content">
	<div class="vczapi-modal-body">
		<span class="vczapi-modal-close">&times;</span>
		<?php
		if ( ! empty( $recording['recording_files'] ) ) {
			foreach ( $recording['recording_files'] as $files ) {
				if ( ! $show_chat && ! empty( $files['recording_type'] ) && 'chat_file' === $files['recording_type'] ) {
					continue;
				}

				$file_type    = ! empty( $files['file_type'] ) ? $files['file_type'] : '';
				$file_id      = ! empty( $files['id'] ) ? $files['id'] : '';
				$file_size    = ! empty( $files['file_size'] ) ? $files['file_size'] : 0;
				$play_url     = ! empty( $files['play_url'] ) ? $files['play_url'] : '';
				$download_url = ! empty( $files['download_url'] ) ? $files['download_url'] : '';
				?>
				<ul class="vczapi-modal-list vczapi-modal-list__<?php echo esc_attr( strtolower( $file_type ) ); ?> vczapi-modal-list-<?php echo esc_attr( $file_id ); ?>">
					<li><strong><?php esc_html_e( 'File Type', 'video-conferencing-with-zoom-api' ); ?>: </strong> <?php echo esc_html( $file_type ); ?></li>
					<li><strong><?php esc_html_e( 'File Size', 'video-conferencing-with-zoom-api' ); ?>: </strong> <?php echo esc_html( vczapi_filesize_converter( $file_size ) ); ?></li>
					<?php if ( $show_pwd && ! empty( $recording['password'] ) ) { ?>
						<li><strong><?php esc_html_e( 'Password:', 'video-conferencing-with-zoom-api' ); ?></strong> <?php echo esc_html( $recording['password'] ); ?></li>
					<?php } ?>
					<li><strong><?php esc_html_e( 'Play', 'video-conferencing-with-zoom-api' ); ?>: </strong>
						<a href="<?php echo esc_url( $play_url ); ?>"
						   target="_blank"
						   rel="noopener noreferrer"
						   class="vczapi-recording__play-link"
						><?php esc_html_e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a></li>
					<?php if ( $downloadable ) { ?>
						<li><strong><?php esc_html_e( 'Download', 'video-conferencing-with-zoom-api' ); ?>: </strong>
							<a href="<?php echo esc_url( $download_url ); ?>"
							   target="_blank"
							   rel="noopener noreferrer"
							   class="vczapi-recording__download-link"
							><?php esc_html_e( 'Download', 'video-conferencing-with-zoom-api' ); ?></a>
						</li>
					<?php } ?>
				</ul>
				<?php
			}
		} else {
			echo esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
		}
		?>
	</div>
</div>