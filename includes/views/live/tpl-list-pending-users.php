<?php
/**
 * View: Pending Approval Users Template
 *
 * @package Video_Conferencing_With_Zoom_Api
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$raw_users = zoom_conference()->listUsers( 1, [ 'status' => 'pending' ] );
$users     = ! empty( $raw_users ) ? json_decode( $raw_users ) : null;

$available_users_url = add_query_arg(
        [
                'post_type' => 'zoom-meetings',
                'page'      => 'zoom-video-conferencing-list-users',
        ],
        admin_url( 'edit.php' )
);
?>
<div class="wrap">
    <h2><?php esc_html_e( 'Pending Approval Users', 'video-conferencing-with-zoom-api' ); ?></h2>

    <p>
        <a href="<?php echo esc_url( $available_users_url ); ?>">
            <?php esc_html_e( 'Check Available Users', 'video-conferencing-with-zoom-api' ); ?>
        </a>
    </p>

    <div class="zvc_listing_table">
        <table id="zvc_users_list_table" class="display" width="100%">
            <thead>
            <tr>
                <th class="zvc-text-left"><?php esc_html_e( 'SN', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'User ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Email', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="zvc-text-left"><?php esc_html_e( 'Created On', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $count = 1;
            if ( ! empty( $users ) && empty( $users->code ) && ! empty( $users->users ) && is_array( $users->users ) ) :
                foreach ( $users->users as $user ) :
                    $created_timestamp = ! empty( $user->created_at ) ? strtotime( $user->created_at ) : false;
                    ?>
                    <tr>
                        <td><?php echo (int) $count++; ?></td>
                        <td><?php echo esc_html( ! empty( $user->id ) ? $user->id : '' ); ?></td>
                        <td><?php echo esc_html( ! empty( $user->email ) ? $user->email : '' ); ?></td>
                        <td>
                            <?php
                            echo $created_timestamp ? esc_html( date_i18n( 'F j, Y, g:i a', $created_timestamp ) ) : esc_html__( 'N/A', 'video-conferencing-with-zoom-api' );
                            ?>
                        </td>
                    </tr>
                <?php
                endforeach;
            endif;
            ?>
            </tbody>
        </table>
    </div>
</div>