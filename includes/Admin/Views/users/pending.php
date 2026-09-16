<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_page = isset( $current_page ) ? (int) $current_page : 1;
$page_count   = isset( $page_count ) ? (int) $page_count : 1;
$users        = isset( $users ) ? $users : array();
$error        = isset( $error ) ? $error : null;

$pagination = paginate_links( array(
	'base'      => add_query_arg( 'pg', '%#%' ),
	'format'    => '',
	'current'   => max( 1, $current_page ),
	'total'     => max( 1, $page_count ),
	'type'      => 'list',
	'prev_text' => '&laquo;',
	'next_text' => '&raquo;',
) );
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( "Pending Approval Users", "video-conferencing-with-zoom-api" ); ?></h1>
    <a href="?post_type=zoom-meetings&page=zoom-video-conferencing-list-users"><?php esc_html_e( 'Check Available Users', 'video-conferencing-with-zoom-api' ); ?></a>
    <hr class="wp-header-end">
    <div class="message">
		<?php
		if ( ! empty( $message ) ) {
			echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
    </div>
	<?php if ( ! empty( $error ) ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
	<?php endif; ?>

    <div class="zvc_listing_table">
        <table id="zvc_wp_pending_users_list_table" class="widefat striped" width="100%">
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
			if ( ! empty( $users ) ) {
				foreach ( $users as $user ) {
					?>
                    <tr>
                        <td><?php echo esc_html( $count ++ ); ?></td>
                        <td><?php echo esc_html( $user->id ); ?></td>
                        <td><?php echo esc_html( $user->email ); ?></td>
                        <td><?php echo ! empty( $user->created_at ) ? esc_html( date( 'F j, Y, g:i a', strtotime( $user->created_at ) ) ) : 'N/A'; ?></td>
                    </tr>
					<?php
				}
			} else {
				?>
                <tr>
                    <td colspan="4"><?php esc_html_e( 'No pending users found.', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>
    </div>
	<?php if ( $pagination ) : ?>
        <div class="tablenav">
            <div class="tablenav-pages">
				<?php echo $pagination; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
	<?php endif; ?>
</div>