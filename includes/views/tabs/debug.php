<?php

use Codemanas\VczApi\Data\Logger;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="zvc-row">
    <section class="zvc-position-floater-left">
		<?php
		if ( empty( $debug_log ) ) {
			echo "<p class='vczapi-admin-enable-debug-log-msg'>Please check <strong><i>Enable Logs</i></strong> option from <a href='" . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . "'>API SETTINGS</a> page to enable new logs. Currently, new logs are not being recorded.</p>";
		}

		if ( ! empty( $logs ) ) : ?>
            <div class="alignleft">
                <h2>
					<?php echo esc_html( $viewed_log ); ?>
					<?php if ( ! empty( $viewed_log ) ) : ?>
                        <a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'handle' => sanitize_title( $viewed_log ) ), admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ), 'remove_log' ) ); ?>" class="button"><?php esc_html_e( 'Delete log', 'video-conferencing-with-zoom-api' ); ?></a>
					<?php endif; ?>
                </h2>
            </div>
            <div class="alignright">
                <form action="<?php echo esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ); ?>" method="post">
					<?php
					$log_files = Logger::get_log_files();
					if ( ! empty( $log_files ) ) {
						?>
                        <select name="log_file">
							<?php
							$date_format = get_option( 'date_format' );
							foreach ( $log_files as $k => $log_file ) {
								$timestamp     = filemtime( ZVC_LOG_DIR . $log_file );
								$log_file_date = wp_date( $date_format, $timestamp );
								?>
                                <option value="<?php echo esc_html( $log_file ); ?>" <?php selected( sanitize_title( $viewed_log ), $k ); ?>><?php echo esc_html( $log_file_date ); ?></option>
							<?php } ?>
                        </select>
                        <button type="submit" class="button" value="<?php esc_attr_e( 'View', 'video-conferencing-with-zoom-api' ); ?>"><?php esc_html_e( 'View', 'video-conferencing-with-zoom-api' ); ?></button>
						<?php
					}
					?>
                </form>
            </div>
            <div class="clear"></div>
            <div id="log-viewer">
                <pre><strong>===START OF LOG===</strong><br><?php echo esc_html( file_get_contents( ZVC_LOG_DIR . $viewed_log ) ); ?><strong>===END OF LOG===</strong></pre>
            </div>
		<?php else : ?>
            <p><?php esc_html_e( 'There aren\'t any new logs to view at the moment.', 'video-conferencing-with-zoom-api' ); ?></p>
		<?php endif; ?>
    </section>
</div>
