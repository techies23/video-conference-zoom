<?php
/**
 * View: Meetings Listings Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if any transient by name is available.
$users       = video_conferencing_zoom_api_get_user_transients();
$get_host_id = isset( $_GET['host_id'] ) ? sanitize_text_field( wp_unslash( $_GET['host_id'] ) ) : '';

if ( ! empty( $get_host_id ) ) {
    $encoded_meetings = zoom_conference()->listMeetings( $get_host_id );
    $decoded_meetings = json_decode( $encoded_meetings );
    $meetings         = ! empty( $decoded_meetings->meetings ) ? $decoded_meetings->meetings : [];
    $meeting_states   = get_option( 'zoom_api_meeting_options' );
}

$add_new_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-add-meeting',
                'host_id'   => $get_host_id,
        ],
        admin_url( 'edit.php' )
);

$default_user_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing',
        ],
        admin_url( 'edit.php' )
);
?>
<div id="zvc-cover" style="display: none;"></div>
<div class="wrap">
    <h2><?php esc_html_e( 'Meetings', 'video-conferencing-with-zoom-api' ); ?></h2>
    <!-- For Errors while deleting this user -->
    <div id="message" style="display:none;" class="notice notice-error show_on_meeting_delete_error"><p></p></div>

    <?php
    video_conferencing_zoom_api_show_like_popup();
    video_conferencing_zoom_api_show_api_notice();
    ?>

    <?php if ( ! empty( $error ) ) : ?>
        <div id="message" class="notice notice-error"><p><?php echo wp_kses_post( $error ); ?></p></div>
    <?php else : ?>
        <div class="select_zvc_user_listings_wrapp">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'video-conferencing-with-zoom-api' ); ?></label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="trash"><?php esc_html_e( 'Move to Trash', 'video-conferencing-with-zoom-api' ); ?></option>
                </select>
                <input type="submit" id="bulk_delete_meeting_listings" data-type="meeting" class="button action" value="<?php esc_attr_e( 'Apply', 'video-conferencing-with-zoom-api' ); ?>">
                <a href="<?php echo esc_url( $add_new_url ); ?>" class="button action" title="<?php esc_attr_e( 'Add new meeting', 'video-conferencing-with-zoom-api' ); ?>"><?php esc_html_e( 'Add New Meeting', 'video-conferencing-with-zoom-api' ); ?></a>
            </div>
            <div class="alignright">
                <select onchange="location = this.value;" class="zvc-hacking-select">
                    <option value="<?php echo esc_url( $default_user_url ); ?>"><?php esc_html_e( 'Select a User', 'video-conferencing-with-zoom-api' ); ?></option>
                    <?php if ( ! empty( $users ) ) : ?>
                        <?php foreach ( $users as $user ) : ?>
                            <?php
                            $user_url = add_query_arg(
                                    [
                                            'post_type' => 'zoom-meetings',
                                            'page'      => 'zoom-video-conferencing',
                                            'host_id'   => $user->id,
                                    ],
                                    admin_url( 'edit.php' )
                            );
                            ?>
                            <option value="<?php echo esc_url( $user_url ); ?>" <?php selected( $get_host_id, $user->id ); ?>>
                                <?php echo esc_html( sprintf( '%s ( %s )', $user->first_name, $user->email ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="clear"></div>
        </div>

        <div class="zvc_listing_table">
            <table id="zvc_meetings_list_table" class="display" width="100%">
                <thead>
                <tr>
                    <th class="zvc-text-center"><input type="checkbox" id="checkall"/></th>
                    <th class="zvc-text-left"><?php esc_html_e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php esc_html_e( 'Shortcode', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php esc_html_e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php esc_html_e( 'Status', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php esc_html_e( 'Meeting State', 'video-conferencing-with-zoom-api' ); ?></th>
                    <th class="zvc-text-left"><?php esc_html_e( 'Created On', 'video-conferencing-with-zoom-api' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if ( ! empty( $meetings ) ) : ?>
                    <?php foreach ( $meetings as $meeting ) : ?>
                        <?php
                        $edit_url = add_query_arg(
                                [
                                        'post_type' => 'zoom-meetings',
                                        'page'      => 'zoom-video-conferencing-add-meeting',
                                        'edit'      => $meeting->id,
                                        'host_id'   => $meeting->host_id,
                                ],
                                admin_url( 'edit.php' )
                        );
                        $app_url  = ! empty( $meeting->start_url ) ? $meeting->start_url : $meeting->join_url;

                        $zoom_host_url = 'https://zoom.us/wc/' . $meeting->id . '/start';
                        $zoom_host_url = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );

                        $shortcode = sprintf( '[zoom_api_link meeting_id="%s" link_only="no"]', esc_attr( $meeting->id ) );
                        $copy_msg  = esc_js( __( 'Copied to clipboard', 'video-conferencing-with-zoom-api' ) );
                        ?>
                        <tr>
                            <td class="zvc-text-center">
                                <input type="checkbox" name="meeting_id_check[]" class="checkthis" value="<?php echo esc_attr( $meeting->id ); ?>"/>
                            </td>
                            <td><?php echo esc_html( $meeting->id ); ?></td>
                            <td>
                                <input class="text" id="meeting-shortcode-<?php echo esc_attr( $meeting->id ); ?>" type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.select(); document.execCommand('copy'); alert('<?php echo $copy_msg; ?>');"/>
                                <p class="description"><?php esc_html_e( 'Click to Copy Shortcode !', 'video-conferencing-with-zoom-api' ); ?></p>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $meeting->topic ); ?></a>
                                <div class="row-actionss">
									<span class="trash">
										<a style="color:red;" href="javascript:void(0);" data-meetingid="<?php echo esc_attr( $meeting->id ); ?>" data-type="meeting" class="submitdelete delete-meeting">
											<?php esc_html_e( 'Trash', 'video-conferencing-with-zoom-api' ); ?>
										</a> |
									</span>
                                    <span class="view">
										<a href="<?php echo esc_url( $app_url ); ?>" rel="permalink" target="_blank">
											<?php esc_html_e( 'Start via App', 'video-conferencing-with-zoom-api' ); ?>
										</a>
									</span>
                                    <span class="view"> |
										<a class="start-meeting-btn reload-meeting-started-button" target="_blank" href="<?php echo esc_url( $zoom_host_url ); ?>">
											<?php esc_html_e( 'Start via Browser', 'video-conferencing-with-zoom-api' ); ?>
										</a>
									</span>
                                </div>
                            </td>
                            <td>
                                <?php
                                if ( isset( $meeting->status ) && '' !== $meeting->status ) {
                                    switch ( (int) $meeting->status ) {
                                        case 0:
                                            echo '<img src="' . esc_url( ZVC_PLUGIN_IMAGES_PATH . '/2.png' ) . '" style="width:14px;" title="' . esc_attr__( 'Not Started', 'video-conferencing-with-zoom-api' ) . '" alt="' . esc_attr__( 'Not Started', 'video-conferencing-with-zoom-api' ) . '">';
                                            break;
                                        case 1:
                                            echo '<img src="' . esc_url( ZVC_PLUGIN_IMAGES_PATH . '/3.png' ) . '" style="width:14px;" title="' . esc_attr__( 'Completed', 'video-conferencing-with-zoom-api' ) . '" alt="' . esc_attr__( 'Completed', 'video-conferencing-with-zoom-api' ) . '">';
                                            break;
                                        case 2:
                                            echo '<img src="' . esc_url( ZVC_PLUGIN_IMAGES_PATH . '/1.png' ) . '" style="width:14px;" title="' . esc_attr__( 'Currently Live', 'video-conferencing-with-zoom-api' ) . '" alt="' . esc_attr__( 'Live', 'video-conferencing-with-zoom-api' ) . '">';
                                            break;
                                        default:
                                            esc_html_e( 'N/A', 'video-conferencing-with-zoom-api' );
                                            break;
                                    }
                                } else {
                                    esc_html_e( 'N/A', 'video-conferencing-with-zoom-api' );
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ( MeetingType::is_scheduled_meeting( $meeting->type ) ) {
                                    echo esc_html( Date::dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a ( e )' ) );
                                } elseif ( MeetingType::is_recurring_no_fixed_time_meeting( $meeting->type ) ) {
                                    esc_html_e( 'This is a recurring meeting with no fixed time.', 'video-conferencing-with-zoom-api' );
                                } elseif ( MeetingType::is_recurring_fixed_time_meeting( $meeting->type ) ) {
                                    esc_html_e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' );
                                } else {
                                    esc_html_e( 'N/A', 'video-conferencing-with-zoom-api' );
                                }
                                ?>
                            </td>
                            <td style="width: 120px;">
                                <?php if ( ! isset( $meeting_states[ $meeting->id ]['state'] ) ) : ?>
                                    <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="shortcode" data-state="end" data-id="<?php echo esc_attr( $meeting->id ); ?>">
                                        <?php esc_html_e( 'Disable Join', 'video-conferencing-with-zoom-api' ); ?>
                                    </a>
                                    <div class="vczapi-admin-info-tooltip">
                                        <span class="dashicons dashicons-info"></span>
                                        <span class="vczapi-admin-info-tooltip--text"><?php esc_html_e( 'Ending this will disable users to join this meeting. Applies to any shortcode output only.', 'video-conferencing-with-zoom-api' ); ?></span>
                                    </div>
                                <?php else : ?>
                                    <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="shortcode" data-state="resume" data-id="<?php echo esc_attr( $meeting->id ); ?>">
                                        <?php esc_html_e( 'Enable Join', 'video-conferencing-with-zoom-api' ); ?>
                                    </a>
                                    <div class="vczapi-admin-info-tooltip">
                                        <span class="dashicons dashicons-info"></span>
                                        <span class="vczapi-admin-info-tooltip--text"><?php esc_html_e( 'Resuming this will enable users to join this meeting. Applies to any shortcode output only.', 'video-conferencing-with-zoom-api' ); ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $created_timestamp = ! empty( $meeting->created_at ) ? strtotime( $meeting->created_at ) : false;
                                echo $created_timestamp ? esc_html( date_i18n( 'F j, Y, g:i a', $created_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>