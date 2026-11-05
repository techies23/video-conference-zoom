<?php
/**
 * View: Reports Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zvc_reports = Zoom_Video_Conferencing_Reports::getInstance();
$active_tab  = ! empty( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'zoom_daily_report';

$daily_report_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-reports',
                'tab'       => 'zoom_daily_report',
        ],
        admin_url( 'edit.php' )
);

$account_report_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-reports',
                'tab'       => 'zoom_acount_report',
        ],
        admin_url( 'edit.php' )
);

$form_action_daily = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-reports',
        ],
        admin_url( 'edit.php' )
);

$form_action_account = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-reports',
                'tab'       => 'zoom_acount_report',
        ],
        admin_url( 'edit.php' )
);

video_conferencing_zoom_api_show_like_popup();
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Reports', 'video-conferencing-with-zoom-api' ); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo esc_url( $daily_report_url ); ?>" class="nav-tab <?php echo 'zoom_daily_report' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( '1. Daily Report', 'video-conferencing-with-zoom-api' ); ?>
        </a>
        <a href="<?php echo esc_url( $account_report_url ); ?>" class="nav-tab <?php echo 'zoom_acount_report' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( '2. Account Report', 'video-conferencing-with-zoom-api' ); ?>
        </a>
    </h2>

    <div id="message" class="notice notice-warning">
        <h2><?php esc_html_e( 'ATTENTION: Zoom Account & Setting Prerequisites for Reports Section', 'video-conferencing-with-zoom-api' ); ?></h2>

        <ol>
            <li>
                <?php
                printf(
                /* translators: %s: Link to Zoom Account Types support article */
                        esc_html__( 'Pro, Business, Enterprise, Education, or API Account. Check more %s.', 'video-conferencing-with-zoom-api' ),
                        '<a target="_blank" rel="noopener noreferrer" href="' . esc_url( 'https://support.zoom.us/hc/en-us/articles/201363173-Account-Types' ) . '">' . esc_html__( 'here', 'video-conferencing-with-zoom-api' ) . '</a>'
                );
                ?>
            </li>
            <li><?php esc_html_e( 'Account owner or admin permissions to access Usage Reports for all users.', 'video-conferencing-with-zoom-api' ); ?></li>
            <li>
                <?php
                printf(
                /* translators: %s: Link to Zoom User Activities Roles support article */
                        esc_html__( 'Account Owner or a user is given the User activities reports %s.', 'video-conferencing-with-zoom-api' ),
                        '<a target="_blank" rel="noopener noreferrer" href="' . esc_url( 'https://support.zoom.us/hc/en-us/articles/115001078646' ) . '">' . esc_html__( 'role', 'video-conferencing-with-zoom-api' ) . '</a>'
                );
                ?>
            </li>
        </ol>
    </div>

    <?php if ( 'zoom_daily_report' === $active_tab ) : ?>
        <?php
        $result = $zvc_reports->get_daily_report_html();
        if ( isset( $_POST['zoom_check_month_year'] ) ) {
            if ( ! empty( $result->error->message ) ) {
                ?>
                <div id="message" class="notice notice-error">
                    <p><?php echo esc_html( $result->error->message ); ?></p>
                </div>
                <?php
            }

            if ( ! empty( $result->message ) ) {
                ?>
                <div id="message" class="notice notice-error">
                    <p><?php echo esc_html( $result->message ); ?></p>
                </div>
                <?php
            }
        }
        ?>
        <div class="zoom_dateinput_field">
            <form action="<?php echo esc_url( $form_action_daily ); ?>" class="zvc_daily_reports_check_form" method="POST">
                <label for="reports_date"><?php esc_html_e( 'Enter the date to check:', 'video-conferencing-with-zoom-api' ); ?></label>
                <input name="zoom_month_year" id="reports_date" type="text"/>
                <input type="submit" name="zoom_check_month_year" class="button" value="<?php esc_attr_e( 'Check', 'video-conferencing-with-zoom-api' ); ?>">
            </form>
        </div>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th><span><?php esc_html_e( 'Date', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'Meetings', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'New Users', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'Participants', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'Meeting Minutes', 'video-conferencing-with-zoom-api' ); ?></span></th>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php if ( ! empty( $result->dates ) && is_array( $result->dates ) ) : ?>
                <?php foreach ( $result->dates as $date ) : ?>
                    <?php $date_timestamp = ! empty( $date->date ) ? strtotime( $date->date ) : false; ?>
                    <tr>
                        <td><?php echo $date_timestamp ? esc_html( date_i18n( 'F j, Y', $date_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td>
                            <?php if ( ! empty( $date->meetings ) && (int) $date->meetings > 0 ) : ?>
                                <strong style="color: #4300FF; font-size: 16px;"><?php echo esc_html( $date->meetings ); ?></strong>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( ! empty( $date->new_users ) && (int) $date->new_users > 0 ) : ?>
                                <strong style="color:#00A1B5; font-size: 16px;"><?php echo esc_html( $date->new_users ); ?></strong>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( ! empty( $date->participants ) && (int) $date->participants > 0 ) : ?>
                                <strong style="color:#00AF00; font-size: 16px;"><?php echo esc_html( $date->participants ); ?></strong>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( ! empty( $date->meeting_minutes ) && (int) $date->meeting_minutes > 0 ) : ?>
                                <strong style="color:red; font-size: 16px;"><?php echo esc_html( $date->meeting_minutes ); ?></strong>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e( 'Select a Date to Check...', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ( 'zoom_acount_report' === $active_tab ) : ?>
        <?php
        $result = $zvc_reports->get_account_report_html();
        if ( isset( $_POST['zoom_check_account_info'] ) ) {
            $account_from = ! empty( $_POST['zoom_account_from'] ) ? sanitize_text_field( wp_unslash( $_POST['zoom_account_from'] ) ) : '';
            $account_to   = ! empty( $_POST['zoom_account_to'] ) ? sanitize_text_field( wp_unslash( $_POST['zoom_account_to'] ) ) : '';

            if ( empty( $account_from ) || empty( $account_to ) ) {
                ?>
                <div id="message" class="notice notice-error">
                    <?php if ( ! empty( $result->error->message ) ) : ?>
                        <p><?php echo esc_html( $result->error->message ); ?></p>
                    <?php else : ?>
                        <p><?php echo esc_html( is_string( $result ) ? $result : '' ); ?></p>
                    <?php endif; ?>
                </div>
                <?php
            } else {
                if ( ! empty( $result->message ) ) {
                    ?>
                    <div id="message" class="notice notice-error">
                        <p><?php echo esc_html( $result->message ); ?></p>
                    </div>
                <?php } ?>

                <div id="message" class="notice notice-success">
                    <ul class="zoom_acount_lists">
                        <?php if ( isset( $result->from ) ) : ?>
                            <li>
                                <?php
                                printf(
                                /* translators: 1: Start date, 2: End date */
                                        esc_html__( 'Searching From: %1$s to %2$s', 'video-conferencing-with-zoom-api' ),
                                        esc_html( $result->from ),
                                        esc_html( ! empty( $result->to ) ? $result->to : '' )
                                );
                                ?>
                            </li>
                        <?php endif; ?>
                        <?php if ( isset( $result->total_records ) ) : ?>
                            <li>
                                <?php
                                printf(
                                /* translators: %s: Total records count */
                                        esc_html__( 'Total Records Found: %s', 'video-conferencing-with-zoom-api' ),
                                        esc_html( $result->total_records )
                                );
                                ?>
                            </li>
                        <?php endif; ?>
                        <?php if ( isset( $result->total_meetings ) ) : ?>
                            <li>
                                <?php
                                printf(
                                /* translators: %s: Total meetings count */
                                        esc_html__( 'Total Meetings Held: %s', 'video-conferencing-with-zoom-api' ),
                                        esc_html( $result->total_meetings )
                                );
                                ?>
                            </li>
                        <?php endif; ?>
                        <?php if ( isset( $result->total_participants ) ) : ?>
                            <li>
                                <?php
                                printf(
                                /* translators: %s: Total participants count */
                                        esc_html__( 'Total Participants Involved: %s', 'video-conferencing-with-zoom-api' ),
                                        esc_html( $result->total_participants )
                                );
                                ?>
                            </li>
                        <?php endif; ?>
                        <?php if ( isset( $result->total_meeting_minutes ) ) : ?>
                            <li>
                                <?php
                                printf(
                                /* translators: %s: Total meeting minutes */
                                        esc_html__( 'Total Meeting Minutes Combined: %s', 'video-conferencing-with-zoom-api' ),
                                        esc_html( $result->total_meeting_minutes )
                                );
                                ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php
            }
        }
        ?>
        <div class="zoom_dateinput_field">
            <p>
                <?php esc_html_e( 'Get account report for a specified period.', 'video-conferencing-with-zoom-api' ); ?>
                <a onclick="window.print();" href="javascript:void(0);"><?php esc_html_e( 'Print', 'video-conferencing-with-zoom-api' ); ?></a>
            </p>
            <form action="<?php echo esc_url( $form_action_account ); ?>" class="zvc_accounts_reports_check_form" method="POST">
                <label for="zoom_account_from"><?php esc_html_e( 'From', 'video-conferencing-with-zoom-api' ); ?></label>
                <input name="zoom_account_from" id="zoom_account_from" class="zoom_account_datepicker" type="text"/>
                <label for="zoom_account_to"><?php esc_html_e( 'To', 'video-conferencing-with-zoom-api' ); ?></label>
                <input name="zoom_account_to" id="zoom_account_to" class="zoom_account_datepicker" type="text"/>
                <input type="submit" name="zoom_check_account_info" class="button" value="<?php esc_attr_e( 'Check', 'video-conferencing-with-zoom-api' ); ?>">
            </form>
        </div>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th><span><?php esc_html_e( 'By', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'Meetings Held', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'Total Participants', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'Total Meeting Minutes', 'video-conferencing-with-zoom-api' ); ?></span></th>
                <th><span><?php esc_html_e( 'Last Login Time', 'video-conferencing-with-zoom-api' ); ?></span></th>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php if ( isset( $result->users ) && is_array( $result->users ) ) : ?>
                <?php if ( 0 === count( $result->users ) ) : ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e( 'No Records Found..', 'video-conferencing-with-zoom-api' ); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ( $result->users as $user ) : ?>
                        <?php $login_timestamp = ! empty( $user->last_login_time ) ? strtotime( $user->last_login_time ) : false; ?>
                        <tr>
                            <td><?php echo esc_html( ! empty( $user->email ) ? $user->email : '' ); ?></td>
                            <td><?php echo esc_html( isset( $user->meetings ) ? $user->meetings : 0 ); ?></td>
                            <td><?php echo esc_html( isset( $user->participants ) ? $user->participants : 0 ); ?></td>
                            <td><?php echo esc_html( isset( $user->meeting_minutes ) ? $user->meeting_minutes : 0 ); ?></td>
                            <td><?php echo $login_timestamp ? esc_html( date_i18n( 'F j, Y g:i a', $login_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e( 'Enter a value to Check...', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>