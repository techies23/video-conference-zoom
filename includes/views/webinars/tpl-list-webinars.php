<?php
/**
 * View: Webinar Listings Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\MeetingType;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$get_host_id      = isset( $_GET['host_id'] ) ? sanitize_text_field( wp_unslash( $_GET['host_id'] ) ) : '';
$add_new_url      = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'new'       => 'zoom-video-conferencing-webinars-add',
        ],
        admin_url( 'edit.php' )
);
$default_user_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-webinars',
        ],
        admin_url( 'edit.php' )
);
?>
<div id="zvc-cover" style="display: none;"></div>
<div class="wrap">
    <h2><?php esc_html_e( 'Webinars', 'video-conferencing-with-zoom-api' ); ?></h2>
    <?php video_conferencing_zoom_api_show_like_popup(); ?>
    <div class="message">
        <?php
        $message = self::get_message();
        if ( ! empty( $message ) ) {
            echo wp_kses_post( $message );
        }
        ?>
    </div>

    <div class="select_zvc_user_listings_wrapp">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'video-conferencing-with-zoom-api' ); ?></label>
            <select name="action" id="bulk-action-selector-top">
                <option value="trash"><?php esc_html_e( 'Move to Trash', 'video-conferencing-with-zoom-api' ); ?></option>
            </select>
            <input type="submit" id="bulk_delete_meeting_listings" data-type="webinar" class="button action" value="<?php esc_attr_e( 'Apply', 'video-conferencing-with-zoom-api' ); ?>">
            <a href="<?php echo esc_url( $add_new_url ); ?>" class="button action" title="<?php esc_attr_e( 'Add new meeting', 'video-conferencing-with-zoom-api' ); ?>"><?php esc_html_e( 'Add New Webinar', 'video-conferencing-with-zoom-api' ); ?></a>
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
                                        'page'      => 'zoom-video-conferencing-webinars',
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
                <th class="zvc-text-left"><?php esc_html_e( 'Webinar ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Shortcode', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Status', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Created On', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $webinars ) ) : ?>
                <?php foreach ( $webinars as $webinar ) : ?>
                    <?php
                    $edit_url = add_query_arg(
                            [
                                    'post_type' => 'zoom-meetings',
                                    'new'       => 'zoom-video-conferencing-webinars-add',
                                    'page'      => 'zoom-video-conferencing-webinars',
                                    'edit'      => $webinar->id,
                                    'host_id'   => $webinar->host_id,
                            ],
                            admin_url( 'edit.php' )
                    );
                    $app_url  = ! empty( $webinar->start_url ) ? $webinar->start_url : $webinar->join_url;

                    $zoom_host_url = 'https://zoom.us/wc/' . $webinar->id . '/start';
                    $zoom_host_url = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );

                    $shortcode = sprintf( '[zoom_api_webinar webinar_id="%s" link_only="no"]', esc_attr( $webinar->id ) );
                    $copy_msg  = esc_js( __( 'Copied to clipboard', 'video-conferencing-with-zoom-api' ) );
                    ?>
                    <tr>
                        <td class="zvc-text-center">
                            <input type="checkbox" name="meeting_id_check[]" class="checkthis" value="<?php echo esc_attr( $webinar->id ); ?>"/>
                        </td>
                        <td><?php echo esc_html( $webinar->id ); ?></td>
                        <td>
                            <input class="text" id="meeting-shortcode-<?php echo esc_attr( $webinar->id ); ?>" type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.select(); document.execCommand('copy'); alert('<?php echo $copy_msg; ?>');"/>
                            <p class="description"><?php esc_html_e( 'Click to Copy Shortcode !', 'video-conferencing-with-zoom-api' ); ?></p>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $webinar->topic ); ?></a>
                            <div class="row-actionss">
								<span class="trash">
									<a style="color:red;" href="javascript:void(0);" data-meetingid="<?php echo esc_attr( $webinar->id ); ?>" data-type="webinar" class="submitdelete delete-meeting">
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
                            if ( isset( $webinar->status ) && '' !== $webinar->status ) {
                                switch ( (int) $webinar->status ) {
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
                            if ( MeetingType::is_scheduled_webinar( $webinar->type ) ) {
                                echo esc_html( Date::dateConverter( $webinar->start_time, $webinar->timezone, 'F j, Y, g:i a ( e )' ) );
                            } elseif ( MeetingType::is_recurring_no_fixed_time_webinar( $webinar->type ) ) {
                                esc_html_e( 'This is a recurring meeting with no fixed time.', 'video-conferencing-with-zoom-api' );
                            } elseif ( MeetingType::is_recurring_fixed_time_webinar( $webinar->type ) ) {
                                esc_html_e( 'Recurring Webinar', 'video-conferencing-with-zoom-api' );
                            } else {
                                esc_html_e( 'N/A', 'video-conferencing-with-zoom-api' );
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $created_timestamp = ! empty( $webinar->created_at ) ? strtotime( $webinar->created_at ) : false;
                            echo $created_timestamp ? esc_html( date_i18n( 'F j, Y, g:i a', $created_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>