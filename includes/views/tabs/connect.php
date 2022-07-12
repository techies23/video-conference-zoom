<div id="zvc-cover" style="display: none;"></div>
<div class="zvc-row">
    <form action="" method="post">
		<?php
		wp_nonce_field( 'verify_vczapi_zoom_connect', 'vczapi_zoom_connect_nonce' );
		?>
        <table class="form-table">
            <tbody>
			<?php if ( isset( $oauth_error_message ) && ! empty( $oauth_error_message ) ) : ?>
                <tr>
                    <th colspan="2">
						<?php echo $oauth_error_message; ?>
                    </th>
                </tr>
			<?php endif; ?>
            <tr>
                <th><label for="vczapi_oauth_account_id"><?php _e( 'Oauth Account ID', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="password" style="width: 400px;"
                           name="vczapi_oauth_account_id"
                           id="vczapi_oauth_account_id" value="<?php echo ! empty( $vczapi_oauth_account_id ) ? esc_html( $vczapi_oauth_account_id ) : ''; ?>">
                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_oauth_account_id">Show</a></td>
            </tr>
            <tr>
                <th><label for="vczapi_oauth_client_id"><?php _e( 'Oauth Client ID', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="password" style="width: 400px;"
                           name="vczapi_oauth_client_id"
                           id="vczapi_oauth_client_id" value="<?php echo ! empty( $vczapi_oauth_client_id ) ? esc_html( $vczapi_oauth_client_id ) : ''; ?>">
                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_oauth_client_id">Show</a></td>
            </tr>
            <tr>
                <th><label for="vczapi_oauth_client_secret"><?php _e( 'Oauth Client Secret', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="password" style="width: 400px;"
                           name="vczapi_oauth_client_secret"
                           id="vczapi_oauth_client_secret"
                           value="<?php echo ! empty( $vczapi_oauth_client_secret ) ? esc_html( $vczapi_oauth_client_secret ) : ''; ?>">
                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_oauth_client_secret">Show</a></td>
            </tr>
            <tr>
            </tr>
            <tr>
                <th><label><?php _e( 'API Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="password" style="width: 400px;" name="zoom_api_key" id="zoom_api_key" value="<?php echo ! empty( $zoom_api_key ) ? esc_html( $zoom_api_key ) : ''; ?>">
                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#zoom_api_key">Show</a></td>
            </tr>
            <tr>
                <th><label><?php _e( 'API Secret Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                <td>
                    <input type="password" style="width: 400px;" name="zoom_api_secret" id="zoom_api_secret" value="<?php echo ! empty( $zoom_api_secret ) ? esc_html( $zoom_api_secret ) : ''; ?>">
                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#zoom_api_secret">Show</a></td>
            </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th>
                        <input type="submit" value="Save" class="button  button-primary">
                    </th>
                </tr>
            </tfoot>
        </table>
    </form>
</div>