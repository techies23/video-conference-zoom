<?php
/**
 * View: Recordings Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$host_id = isset( $_GET['host_id'] ) ? sanitize_text_field( wp_unslash( $_GET['host_id'] ) ) : '';
?>
<div class="wrap">
    <h2><?php esc_html_e( 'Recordings', 'video-conferencing-with-zoom-api' ); ?></h2>

    <p style="padding: 20px;" class="vczapi-notification vczapi-error">
        <strong>
            <?php esc_html_e( 'The maximum range can be a month. If no value is provided for this field, the default will be current date. For example, if you make the API request on June 30, 2020, without providing the “from” parameter, by default the value of ‘from’ field will be “2020-05-30” and the value of the ‘to’ field will be “2020-06-30”.', 'video-conferencing-with-zoom-api' ); ?>
        </strong>
    </p>

    <?php
    video_conferencing_zoom_api_show_like_popup();
    if ( current_user_can( 'manage_options' ) ) {
        zvc_recordings()->get_hosts( $host_id, true );
    }
    ?>

    <div class="zvc_listing_table">
        <table id="zvc_meetings_list_table" class="display" width="100%">
            <thead>
            <tr>
                <th class="zvc-text-left"><?php esc_html_e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Recorded', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ( ! empty( $recordings ) && ! empty( $recordings->meetings ) && is_array( $recordings->meetings ) ) :
                $meeting_count = 0;
                foreach ( $recordings->meetings as $recording ) :
                    $recording_by_uuid = ! empty( $recording->uuid ) ? zoom_conference()->recordingsByMeeting( $recording->uuid ) : null;
                    $recording_by_uuid = ! is_wp_error( $recording_by_uuid ) && is_string( $recording_by_uuid ) ? json_decode( $recording_by_uuid ) : null;

                    $start_timestamp = ! empty( $recording->start_time ) ? strtotime( $recording->start_time ) : false;
                    ?>
                    <tr>
                        <td><?php echo esc_html( ! empty( $recording->id ) ? $recording->id : '' ); ?></td>
                        <td><?php echo esc_html( ! empty( $recording->topic ) ? $recording->topic : '' ); ?></td>
                        <td><?php echo esc_html( ! empty( $recording->duration ) ? $recording->duration : '' ); ?></td>
                        <td>
                            <?php
                            echo $start_timestamp ? esc_html( date_i18n( 'F j, Y, g:i a', $start_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
                            ?>
                        </td>
                        <td><?php echo esc_html( ! empty( $recording->total_size ) ? vczapi_filesize_converter( $recording->total_size ) : '' ); ?></td>
                        <td>
                            <?php if ( ! empty( $recording->recording_files ) && is_array( $recording->recording_files ) ) : ?>
                                <a href="#TB_inline?width=600&height=550&inlineId=recording-<?php echo (int) $meeting_count; ?>" class="thickbox">
                                    <?php esc_html_e( 'View Recordings', 'video-conferencing-with-zoom-api' ); ?>
                                </a>
                                <div id="recording-<?php echo (int) $meeting_count; ?>" style="display:none;">
                                    <?php
                                    if ( ! is_null( $recording_by_uuid ) && ! empty( $recording_by_uuid->recording_files ) && is_array( $recording_by_uuid->recording_files ) ) :
                                        foreach ( $recording_by_uuid->recording_files as $files ) :
                                            ?>
                                            <ul class="zvc-inside-table-wrapper zvc-inside-table-wrapper-<?php echo esc_attr( ! empty( $files->id ) ? $files->id : '' ); ?>">
                                                <li>
                                                    <strong><?php esc_html_e( 'File Type', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                                    <?php echo esc_html( ! empty( $files->file_type ) ? $files->file_type : '' ); ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e( 'File Size', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                                    <?php echo esc_html( ! empty( $files->file_size ) ? vczapi_filesize_converter( $files->file_size ) : '' ); ?>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e( 'Play', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                                    <a href="<?php echo esc_url( ! empty( $files->play_url ) ? $files->play_url : '' ); ?>" target="_blank" rel="noopener noreferrer">
                                                        <?php esc_html_e( 'Play', 'video-conferencing-with-zoom-api' ); ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <strong><?php esc_html_e( 'Download', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                                    <a href="<?php echo esc_url( ! empty( $files->download_url ) ? $files->download_url : '' ); ?>" target="_blank" rel="noopener noreferrer">
                                                        <?php esc_html_e( 'Download', 'video-conferencing-with-zoom-api' ); ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            <?php else : ?>
                                <?php esc_html_e( 'N/A', 'video-conferencing-with-zoom-api' ); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                    $meeting_count ++;
                endforeach;
            endif;
            ?>
            </tbody>
        </table>
    </div>
</div>