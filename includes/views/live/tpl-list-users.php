<?php
/**
 * View: Users Listings Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

video_conferencing_zoom_api_show_like_popup();

$users = video_conferencing_zoom_api_get_user_transients();

$add_user_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-add-users',
        ],
        admin_url( 'edit.php' )
);

$flush_cache_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-list-users',
                'flush'     => 'true',
        ],
        admin_url( 'edit.php' )
);

$pending_users_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-list-users',
                'status'    => 'pending',
        ],
        admin_url( 'edit.php' )
);
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Users', 'video-conferencing-with-zoom-api' ); ?></h1>
    <a href="<?php echo esc_url( $add_user_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'video-conferencing-with-zoom-api' ); ?></a>
    <hr class="wp-header-end">

    <p>
        <a href="<?php echo esc_url( $flush_cache_url ); ?>"><?php esc_html_e( 'Flush User Cache', 'video-conferencing-with-zoom-api' ); ?></a>
        /
        <a href="<?php echo esc_url( $pending_users_url ); ?>"><?php esc_html_e( 'Check Pending Users', 'video-conferencing-with-zoom-api' ); ?></a>
    </p>

    <div class="message">
        <?php
        $message = self::get_message();
        if ( ! empty( $message ) ) {
            echo wp_kses_post( $message );
        }
        ?>
    </div>

    <p>
        <?php
        $pagination_next = function_exists( 'video_conferencing_zoom_api_pagination_next' ) ? video_conferencing_zoom_api_pagination_next( $users ) : '';
        $pagination_prev = function_exists( 'video_conferencing_zoom_api_pagination_prev' ) ? video_conferencing_zoom_api_pagination_prev( $users ) : '';
        echo wp_kses_post( trim( $pagination_next . ' ' . $pagination_prev ) );
        ?>
    </p>

    <div class="zvc_listing_table">
        <table id="zvc_users_list_table" class="display" width="100%">
            <thead>
            <tr>
                <th class="zvc-text-left"><?php esc_html_e( 'SN', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'User ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Email', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Name', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Created On', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Last Login', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Last Client', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $count = 1;
            if ( ! empty( $users ) && is_array( $users ) ) :
                foreach ( $users as $user ) :
                    $created_timestamp = ! empty( $user->created_at ) ? strtotime( $user->created_at ) : false;
                    $login_timestamp   = ! empty( $user->last_login_time ) ? strtotime( $user->last_login_time ) : false;

                    $first_name = ! empty( $user->first_name ) ? $user->first_name : '';
                    $last_name  = ! empty( $user->last_name ) ? $user->last_name : '';
                    $full_name  = trim( $first_name . ' ' . $last_name );
                    if ( '' === $full_name ) {
                        $full_name = ! empty( $user->email ) ? $user->email : '';
                    }
                    ?>
                    <tr>
                        <td><?php echo (int) $count++; ?></td>
                        <td><?php echo esc_html( ! empty( $user->id ) ? $user->id : '' ); ?></td>
                        <td><?php echo esc_html( ! empty( $user->email ) ? $user->email : '' ); ?></td>
                        <td><?php echo esc_html( $full_name ); ?></td>
                        <td>
                            <?php
                            echo $created_timestamp ? esc_html( date_i18n( 'F j, Y, g:i a', $created_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
                            ?>
                        </td>
                        <td>
                            <?php
                            echo $login_timestamp ? esc_html( date_i18n( 'F j, Y, g:i a', $login_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
                            ?>
                        </td>
                        <td><?php echo esc_html( ! empty( $user->last_client_version ) ? $user->last_client_version : __( 'N/A', 'video-conferencing-with-zoom-api' ) ); ?></td>
                    </tr>
                <?php
                endforeach;
            endif;
            ?>
            </tbody>
        </table>
    </div>

    <div id="zvc_getting_user_info" style="display:none;">
        <div class="zvc_getting_user_info_content"></div>
    </div>
</div>