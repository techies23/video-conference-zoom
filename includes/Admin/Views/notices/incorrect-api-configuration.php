<div id="message" class="notice notice-error">
    <h3><?php esc_html_e( 'Zoom Account Not Connected', 'video-conferencing-with-zoom-api' ); ?></h3>
    <p>
        <?php
        printf(
                esc_html__( 'Your Zoom account is not connected yet. Might be because you have not correctly added your keys. If you need help setting up your API keys, please follow our %s.', 'video-conferencing-with-zoom-api' ),
                '<a href="' . admin_url( '/edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">Settings page</a>',
                '<a target="_blank" href="https://zoomdocs.codemanas.com/setup/">setup guide</a>'
        );
        ?>
    </p>
</div>
