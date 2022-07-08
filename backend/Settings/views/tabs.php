<h1><?php _e( 'Zoom Integration Settings', 'video-conferencing-with-zoom-api' ); ?></h1>
<h2 class="nav-tab-wrapper">
	<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'api-connection' ) ) ); ?>" class="nav-tab <?php echo ( 'api-connection' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
		<?php esc_html_e( 'Connect API', 'video-conferencing-with-zoom-api' ); ?>
	</a>
	<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'general' ) ) ); ?>" class="nav-tab <?php echo ( 'general' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
		<?php esc_html_e( 'General', 'video-conferencing-with-zoom-api' ); ?>
	</a>
	<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'support' ) ) ); ?>" class="nav-tab <?php echo ( 'support' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
		<?php esc_html_e( 'Support', 'video-conferencing-with-zoom-api' ); ?>
	</a>
	<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'debug' ) ) ); ?>" class="nav-tab <?php echo ( 'debug' === $active_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
		<?php esc_html_e( 'Logs', 'video-conferencing-with-zoom-api' ); ?>
	</a>
	<?php do_action( 'vczapi_admin_tabs_heading', $active_tab ); ?>
</h2>