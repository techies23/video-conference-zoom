<?php

use Codemanas\VczApi\Data\Logger;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args       = isset( $args ) && is_array( $args ) ? $args : [];
$debug_log  = ! empty( $args['debug_log'] );
$logs       = ! empty( $args['logs'] ) && is_array( $args['logs'] ) ? $args['logs'] : [];
$viewed_log = ! empty( $args['viewed_log'] );
?>

<div class="vczapi-log-viewer">
    <section class="vczapi-log-viewer__main">
        <?php if ( ! $debug_log ) : ?>
            <p class="vczapi-log-viewer__notice vczapi-log-viewer__notice--warning">
                <?php
                printf(
                        esc_html__( 'Please check Enable Logs option from %s to enable new logs. Currently, new logs are not being recorded.', 'video-conferencing-with-zoom-api' ),
                        '<a href="' . esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) ) . '"><strong>' . esc_html__( 'API SETTINGS', 'video-conferencing-with-zoom-api' ) . '</strong></a>'
                );
                ?>
            </p>
        <?php endif; ?>

        <?php if ( ! empty( $logs ) ) : ?>
            <div class="vczapi-log-viewer__header">
                <div class="vczapi-log-viewer__title-wrapper">
                    <h2 class="vczapi-log-viewer__title">
                        <?php echo esc_html( $viewed_log ); ?>
                        <?php if ( ! empty( $viewed_log ) ) : ?>
                            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'handle' => sanitize_title( $viewed_log ) ], admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ), 'remove_log' ) ); ?>" class="vczapi-log-viewer__action-button button">
                                <?php esc_html_e( 'Delete log', 'video-conferencing-with-zoom-api' ); ?>
                            </a>
                        <?php endif; ?>
                    </h2>
                </div>

                <div class="vczapi-log-viewer__filter">
                    <form action="<?php echo esc_url( admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings&tab=debug' ) ); ?>" method="post" class="vczapi-log-viewer__filter-form">
                        <?php
                        $log_files = Logger::get_log_files();
                        if ( ! empty( $log_files ) ) :
                            $date_format = get_option( 'date_format' );
                            ?>
                            <select name="log_file" class="vczapi-log-viewer__select">
                                <?php foreach ( $log_files as $k => $log_file ) :
                                    $timestamp = filemtime( ZVC_LOG_DIR . $log_file );
                                    $log_file_date = wp_date( $date_format, $timestamp );
                                    ?>
                                    <option value="<?php echo esc_attr( $log_file ); ?>" <?php selected( sanitize_title( $viewed_log ), $k ); ?>>
                                        <?php echo esc_html( $log_file_date ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="vczapi-log-viewer__submit button" value="<?php esc_attr_e( 'View', 'video-conferencing-with-zoom-api' ); ?>">
                                <?php esc_html_e( 'View', 'video-conferencing-with-zoom-api' ); ?>
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="vczapi-log-viewer__content">
                <pre class="vczapi-log-viewer__terminal"><strong>===START OF LOG===</strong><br><?php echo esc_html( file_get_contents( ZVC_LOG_DIR . $viewed_log ) ); ?><strong>===END OF LOG===</strong></pre>
            </div>
        <?php else : ?>
            <p class="vczapi-log-viewer__empty">
                <?php esc_html_e( 'There aren\'t any new logs to view at the moment.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        <?php endif; ?>
    </section>
</div>