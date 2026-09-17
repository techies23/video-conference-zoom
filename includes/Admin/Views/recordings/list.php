<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args         = ! empty( $args ) && is_array( $args ) ? $args : [];
$defaultUsers = ! empty( $args['default_users'] ) ? $args['default_users'] : [];

?>
<div class="wrap vczapi-recordings">
    <h2 class="vczapi-recordings__title"><?php _e( "Recordings", "video-conferencing-with-zoom-api" ); ?></h2>

    <p class="vczapi-notification vczapi-error vczapi-recordings__notification">
        <strong><?php _e( "The maximum range can be a month. If no value is provided for this field, the default will be current date. For example, if you make the API request on June 30, 2020, without providing the “from” parameter, by default the value of ‘from’ field will be “2020-05-30” and the value of the ‘to’ field will be “2020-06-30”.", "video-conferencing-with-zoom-api" ); ?></strong>
    </p>

    <?php if ( current_user_can( 'manage_options' ) ) { ?>
        <div class="vczapi-recordings__filter-wrapper">
            <div class="vczapi-recordings__filter-item vczapi-recordings__filter-item--left">
                <form action="" class="vczapi-datepicker-admin vczapi-recordings__date-form" method="POST">
                    <label class="vczapi-recordings__date-label"><?php _e( 'Enter the date to check:', 'video-conferencing-with-zoom-api' ); ?></label>
                    <input name="date" id="vczapi-check-recording-date" class="vczapi-recordings__date-input"/>
                    <input type="submit" name="check-recordings" class="vczapi-recordings__submit-btn" value="<?php _e( 'Check', 'video-conferencing-with-zoom-api' ); ?>">
                </form>
            </div>

            <div class="vczapi-recordings__filter-item vczapi-recordings__filter-item--right">
                <?php
                \Codemanas\VczApi\Helpers\FormHelper::fields(
                        'host_id',
                        [
                                "type"              => "select",
                                "input_class"       => [ "vczapi-choices", "vczapi-recordings__host-select" ],
                                "options"           => $defaultUsers,
                                'custom_attributes' => [
                                        'data-api-action'  => 'vczapi_get_user_by_query',
                                        'data-placeholder' => __( 'Search host by name or email...', 'video-conferencing-with-zoom-api' ),
                                        'data-searchable'  => 'true',
                                ],
                        ],
                );
                ?>
            </div>
        </div>
    <?php } ?>

    <div class="vczapi-recordings__table-wrapper">
        <table id="vczapi_recordings_table" class="display vczapi-recordings__table" width="100%">
            <thead class="vczapi-recordings__table-head">
            <tr class="vczapi-recordings__table-row vczapi-recordings__table-row--header">
                <th class="vczapi-recordings__table-cell vczapi-recordings__table-cell--head vczapi-recordings__table-cell--left"><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-recordings__table-cell vczapi-recordings__table-cell--head vczapi-recordings__table-cell--left"><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-recordings__table-cell vczapi-recordings__table-cell--head vczapi-recordings__table-cell--left"><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-recordings__table-cell vczapi-recordings__table-cell--head vczapi-recordings__table-cell--left"><?php _e( 'Recorded', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-recordings__table-cell vczapi-recordings__table-cell--head vczapi-recordings__table-cell--left"><?php _e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-recordings__table-cell vczapi-recordings__table-cell--head vczapi-recordings__table-cell--left"><?php _e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody class="vczapi-recordings__table-body">
            <?php
            if ( ! empty( $recordings ) && ! empty( $recordings->meetings ) ) {
                $meeting_count = 0;
                foreach ( $recordings->meetings as $recording ) {
                    $recording_by_uuid = zoom_conference()->recordingsByMeeting( $recording->uuid );
                    $recording_by_uuid = ! is_wp_error( $recording_by_uuid ) && is_string( $recording_by_uuid ) ? json_decode( $recording_by_uuid ) : null;
                    ?>
                    <tr class="vczapi-recordings__table-row">
                        <td class="vczapi-recordings__table-cell"><?php echo $recording->id; ?></td>
                        <td class="vczapi-recordings__table-cell"><?php echo $recording->topic; ?></td>
                        <td class="vczapi-recordings__table-cell"><?php echo $recording->duration; ?></td>
                        <td class="vczapi-recordings__table-cell"><?php echo date( 'F j, Y, g:i a', strtotime( $recording->start_time ) ); ?></td>
                        <td class="vczapi-recordings__table-cell"><?php echo vczapi_filesize_converter( $recording->total_size ); ?></td>
                        <td class="vczapi-recordings__table-cell">
                            <?php if ( ! empty( $recording->recording_files ) ) { ?>
                                <a href="#TB_inline?width=600&height=550&inlineId=recording-<?php echo $meeting_count; ?>" class="thickbox vczapi-recordings__action-link">
                                    <?php _e( 'View Recordings', 'video-conferencing-with-zoom-api' ); ?>
                                </a>
                                <div id="recording-<?php echo $meeting_count; ?>" class="vczapi-recordings__modal" style="display:none;">
                                    <?php
                                    if ( ! is_null( $recording_by_uuid ) ) {
                                        foreach ( $recording_by_uuid->recording_files as $files ) { ?>
                                            <ul class="vczapi-recordings__file-list vczapi-recordings__file-list--<?php echo $files->id; ?>">
                                                <li class="vczapi-recordings__file-item">
                                                    <strong><?php _e( 'File Type', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo $files->file_type; ?>
                                                </li>
                                                <li class="vczapi-recordings__file-item">
                                                    <strong><?php _e( 'File Size', 'video-conferencing-with-zoom-api' ); ?>:</strong> <?php echo vczapi_filesize_converter( $files->file_size ); ?>
                                                </li>
                                                <li class="vczapi-recordings__file-item">
                                                    <strong><?php _e( 'Play', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                                    <a href="<?php echo $files->play_url; ?>" class="vczapi-recordings__file-link" target="_blank"><?php _e( 'Play', 'video-conferencing-with-zoom-api' ); ?></a>
                                                </li>
                                                <li class="vczapi-recordings__file-item">
                                                    <strong><?php _e( 'Download', 'video-conferencing-with-zoom-api' ); ?>:</strong>
                                                    <a href="<?php echo $files->download_url; ?>" class="vczapi-recordings__file-link" target="_blank"><?php _e( 'Download', 'video-conferencing-with-zoom-api' ); ?></a>
                                                </li>
                                            </ul>
                                        <?php }
                                    }
                                    ?>
                                </div>
                            <?php } else {
                                echo "N/A";
                            } ?>
                        </td>
                    </tr>
                    <?php
                    $meeting_count ++;
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>