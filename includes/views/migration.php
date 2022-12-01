<?php
$settings_url = esc_url( add_query_arg(
	[
		'post_type' => 'zoom-meetings',
		'page'      => 'zoom-video-conferencing-settings',
		'tab'       => 'api-settings',
	],
	admin_url( 'edit.php' )
) );
?>
<div class="vczapi-migrate-to-s2sOauth--overlay">
    <div class="vczapi-migrate-to-s2sOauth">
        <h3><?php _e( 'Migration Wizard', 'video-conferencing-with-zoom-api' ) ?></h3>
        <p><?php _e( 'Migrate from JWT to Server to Server Oauth in 2 easy steps', 'video-conferencing-with-zoom-api' ); ?></p>
        <div class="vczapi-migrate-to-s2sOauth--message error-message"></div>
        <div class="step step-1 active">
            <form id="vczapi-s2sOauthCredentials-wizard-form" class="vczapi-migration-form" method="post" action="">
				<?php
				wp_nonce_field( 'verify_s2sOauth_wizard_nonce', 's2sOauth_wizard_nonce' );
				$oauth_documentation_link = '<a href="https://zoomdocs.codemanas.com/setup/#generating-api-credentials" target="_blank" rel="noreferrer noopener">Here</a>';
				printf( __( 'See the documentation on how to generate Server to Server Oauth Credentials %s', 'video-conferencing-with-zoom-api' ), $oauth_documentation_link );
				?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="vczapi_oauth_account_id"><?php _e( 'Oauth Account ID', 'video-conferencing-with-zoom-api' ); ?></label></th>
                        <td>
                            <input type="password" style="width: 400px;"
                                   name="vczapi_wizard_oauth_account_id"
                                   id="vczapi_wizard_oauth_account_id" value="<?php echo ! empty( $vczapi_oauth_account_id ) ? esc_html( $vczapi_oauth_account_id ) : ''; ?>">
                            <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_wizard_oauth_account_id">Show</a></td>
                    </tr>
                    <tr>
                        <th><label for="vczapi_oauth_client_id"><?php _e( 'Oauth Client ID', 'video-conferencing-with-zoom-api' ); ?></label></th>
                        <td>
                            <input type="password" style="width: 400px;"
                                   name="vczapi_wizard_oauth_client_id"
                                   id="vczapi_wizard_oauth_client_id" value="<?php echo ! empty( $vczapi_oauth_client_id ) ? esc_html( $vczapi_oauth_client_id ) : ''; ?>">
                            <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_wizard_oauth_client_id">Show</a></td>
                    </tr>
                    <tr>
                        <th><label for="vczapi_wizard_oauth_client_secret"><?php _e( 'Oauth Client Secret', 'video-conferencing-with-zoom-api' ); ?></label></th>
                        <td>
                            <input type="password" style="width: 400px;"
                                   name="vczapi_wizard_oauth_client_secret"
                                   id="vczapi_wizard_oauth_client_secret"
                                   value="<?php echo ! empty( $vczapi_oauth_client_secret ) ? esc_html( $vczapi_oauth_client_secret ) : ''; ?>">
                            <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_wizard_oauth_client_secret">Show</a></td>
                    </tr>
                    <?php if ( vczapi_is_jwt_active() ): ?>
                        <tr>
                            <th><?php _e( 'Delete JWT Keys', 'video-conferencing-with-zoom-api' ); ?></th>
                            <td><input type="checkbox" id="vczapi_wizard_delete_jwt_keys" name="vczapi_wizard_delete_jwt_keys"/>
                                <span class="description"><?php _e('Check this box to delete JWT (legacy keys) after saving and verifying Server-to-Server Oauth Keys','video-conferencing-with-zoom-api'); ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="2">
                            <span class="spinner is-active"></span>
                            <button type="submit" class="button button-primary">
								<?php _e( 'Check and Save Oauth Credentials' ); ?>
                            </button>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </form>
        </div>
        <div class="step step-2">
			<?php
			$appSDK_documentation_link = '<a href="https://zoomdocs.codemanas.com/setup/#generating-app-sdk-credentials" target="_blank" rel="noreferrer noopener">see the documentation</a>';
			?>
            <p class="description"><?php printf( __( 'App SDK credentails are required to enable the join via broswer feature, to do so first generate your app keys, %s' ), $appSDK_documentation_link ); ?></p>
            <form id="vczapi-s2soauth-app-sdk-form" class="vczapi-migration-form" action="" method="post">
				<?php
				wp_nonce_field( 'verify_s2sOauth_wizard_nonce', 's2sOauth_wizard_nonce' );
				?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><label for="vczapi_wizard_sdk_key"><?php _e( 'SDK key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                        <td>
                            <input type="password" style="width: 400px;"
                                   name="vczapi_wizard_sdk_key"
                                   id="vczapi_wizard_sdk_key"
                                   value="<?php echo ! empty( $vczapi_sdk_key ) ? esc_html( $vczapi_sdk_key ) : ''; ?>">
                            <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_wizard_sdk_key">Show</a></td>
                    </tr>
                    <tr>
                        <th><label for="vczapi_wizard_sdk_secret_key"><?php _e( 'SDK Secret key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                        <td>
                            <input type="password" style="width: 400px;"
                                   name="vczapi_wizard_sdk_secret_key"
                                   id="vczapi_wizard_sdk_secret_key"
                                   value="<?php echo ! empty( $vczapi_sdk_secret_key ) ? esc_html( $vczapi_sdk_secret_key ) : ''; ?>">
                            <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_wizard_sdk_secret_key">Show</a></td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="2">
                            <span class="spinner is-active"></span>
                            <button type="submit" class="button button-primary">
								<?php _e( 'Save App SDK Keys', 'video-conferencing-with-zoom-api' ); ?>
                            </button>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            </form>
        </div>
        <div class="step step-3">
            <h4><?php _e( 'That\'s it, we\'re all done, thank you for continuing to choose Video Conferencing with Zoom API', 'video-conferencing-with-zoom-api' ); ?></h4>
            <a href="<?php echo $settings_url; ?>" class="button button-primary"><?php _e( 'Finish', 'video-conferencing-with-zoom-api' ); ?></a>
        </div>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <button class="button button-primary next-step" disabled data-step="2" data-final_step="3" value="">
						<?php _e( 'Next Step', 'video-conferencing-with-zoom-api' ); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </th>
            </tr>
            </tbody>
        </table>
    </div>
</div>