<?php
$depreciationLink = '<a href="' . esc_url( 'https://marketplace.zoom.us/docs/guides/build/jwt-app/jwt-faq/#jwt-app-type-deprecation-faq--omit-in-toc-' ) . '"
target="_blank" rel="noreferrer noopener">' . __( 'JWT App Type Depreciation FAQ', 'video-conferencing-with-zoom-api' ) . '</a>';

$migration_wizard_url  = esc_url( add_query_arg(
	[
		'post_type' => 'zoom-meetings',
		'page'      => 'zoom-video-conferencing-settings',
		'migrate'   => 'now',
	],
	admin_url( 'edit.php' )
) );
$migration_wizard_link = '<a href="' . $migration_wizard_url . '">migration wizard</a>';

$is_jwt_active = vczapi_is_jwt_active();

?>
    <div id="zvc-cover" style="display: none;"></div>
    <div class="zvc-row">
        <div class="zvc-position-floater-left vczapi-connect-box"
             style="width: 70%;margin-right:10px;border-top:1px solid #ccc;">
            <form action="" method="post">
				<?php
				wp_nonce_field( 'verify_vczapi_zoom_connect', 'vczapi_zoom_connect_nonce' );
				?>
				<?php if ( apply_filters( 'vczapi_show_jwt_keys', ( $is_jwt_active ) ) ): ?>
                    <!-- Legacy JWT Implementation -->
                    <div id="vczapi-s2sOauth-jwt-credentials" class="vczapi-admin-accordion expanded">
                        <div class="vczapi-admin-accordion--header">
                            <div class="vczapi-admin-accordion--header-title">
                                <h3><?php _e( 'JWT Credentials ( Legacy )', 'video-conferencing-with-zoom-api' ); ?></h3>
                            </div>
                            <div class="vczapi-admin-accordion--header-trigger">
                                <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                            </div>
                        </div>
                        <div class="vczapi-admin-accordion--content" class="show">
							<?php
							printf( __( 'Zoom is deprecating their JWT app from June of 2023, please see %s for more details, Until the deadline all your current settings will work, however to ensure a smooth transition to the new Server to Server OAuth system + New App SDK (required for Join Via Browser) - we recommend that you migrate as soon as possible. Run the %s now to complete the migration process in 2 easy steps ', 'video-conferencing-with-zoom-api' ), $depreciationLink, $migration_wizard_link );
							?>
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th>
                                        <label><?php _e( 'JWT API Key', 'video-conferencing-with-zogit coom-api' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" style="width: 400px;" name="zoom_api_key"
                                               id="zoom_api_key"
                                               value="<?php echo ! empty( $zoom_api_key ) ? esc_html( $zoom_api_key ) : ''; ?>">
                                        <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                           data-element="#zoom_api_key">Show</a></td>
                                </tr>
                                <tr>
                                    <th>
                                        <label><?php _e( 'JWT API Secret Key', 'video-conferencing-with-zoom-api' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" style="width: 400px;" name="zoom_api_secret"
                                               id="zoom_api_secret"
                                               value="<?php echo ! empty( $zoom_api_secret ) ? esc_html( $zoom_api_secret ) : ''; ?>">
                                        <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                           data-element="#zoom_api_secret">Show</a></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End Legacy JWT Implementation -->
				<?php endif; ?>

                <!-- OAuth Credentials -->
                <div id="vczapi-s2sOauth-credentials" class="vczapi-admin-accordion expanded">
                    <div class="vczapi-admin-accordion--header">
                        <div class="vczapi-admin-accordion--header-title">
                            <h3><?php _e( 'Server to Server Oauth Credentials', 'video-conferencing-with-zoom-api' ); ?></h3>
                        </div>
                        <div class="vczapi-admin-accordion--header-trigger">
                            <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                        </div>
                    </div>
                    <div class="vczapi-admin-accordion--content" >
                        <p class="description">
							<?php
							$oauth_documentation_link = '<a href="https://zoomdocs.codemanas.com/setup/#generating-api-credentials" target="_blank" rel="noreferrer noopener">setup guide</a>';
							$sdk_app_link             = '<a href="#vczapi-s2sOauth-app-sdk-credentials" class="vczapi-go-to-open-accordion" onclick="javascript:void(0);">SDK App Credentials</a>';
							printf( __( 'Please see %s on how to generate credentials, additionally for Join via Browser to work please also add %s', 'video-conferencing-with-zoom-api' ), $oauth_documentation_link, $sdk_app_link );
							?>
                        </p>
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
                                <th>
                                    <label for="vczapi_oauth_account_id"><?php _e( 'Oauth Account ID', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" style="width: 400px;"
                                           name="vczapi_oauth_account_id"
                                           id="vczapi_oauth_account_id"
                                           value="<?php echo ! empty( $vczapi_oauth_account_id ) ? esc_html( $vczapi_oauth_account_id ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                       data-element="#vczapi_oauth_account_id">Show</a></td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vczapi_oauth_client_id"><?php _e( 'Oauth Client ID', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" style="width: 400px;"
                                           name="vczapi_oauth_client_id"
                                           id="vczapi_oauth_client_id"
                                           value="<?php echo ! empty( $vczapi_oauth_client_id ) ? esc_html( $vczapi_oauth_client_id ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                       data-element="#vczapi_oauth_client_id">Show</a></td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vczapi_oauth_client_secret"><?php _e( 'Oauth Client Secret', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" style="width: 400px;"
                                           name="vczapi_oauth_client_secret"
                                           id="vczapi_oauth_client_secret"
                                           value="<?php echo ! empty( $vczapi_oauth_client_secret ) ? esc_html( $vczapi_oauth_client_secret ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                       data-element="#vczapi_oauth_client_secret">Show</a></td>
                            </tr>
							<?php if ( $is_jwt_active ): ?>
                                <tr>
                                    <th><?php _e( 'Delete JWT Keys', 'video-conferencing-with-zoom-api' ); ?></th>
                                    <td><input type="checkbox" id="vczapi-delete-jwt-keys"
                                               name="vczapi-delete-jwt-keys"/>
                                        <span class="description"><?php _e( 'Check this box to delete JWT (legacy keys) after saving and verifying Server-to-Server Oauth Keys', 'video-conferencing-with-zoom-api' ); ?></span>
                                    </td>
                                </tr>
							<?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End Oauth Credentials -->

                <!-- App SDK Credentials -->
                <div id="vczapi-s2sOauth-app-sdk-credentials" class="vczapi-admin-accordion expanded">
                    <div class="vczapi-admin-accordion--header">
                        <div class="vczapi-admin-accordion--header-title">
                            <h3><?php _e( 'Meeting SDK App Credentials', 'video-conferencing-with-zoom-api' ); ?></h3>
                        </div>
                        <div class="vczapi-admin-accordion--header-trigger">
                            <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                        </div>
                    </div>
                    <div class="vczapi-admin-accordion--content">
						<?php
						$appSDK_documentation_link = '<a href="https://zoomdocs.codemanas.com/setup/#setup-app-sdk-credentials" target="_blank" rel="noreferrer noopener">see the documentation</a>';
						echo '<p class="description">' . sprintf( __( 'SDK App Credentials are required for Join Via Browser to work, %s on how to generate you App SDK keys', 'video-conferencing-with-zoom-api' ), $appSDK_documentation_link ) . '</p>'; ?>
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="vczapi_sdk_key"><?php _e( 'Client ID', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" style="width: 400px;"
                                           name="vczapi_sdk_key"
                                           id="vczapi_sdk_key"
                                           value="<?php echo ! empty( $vczapi_sdk_key ) ? esc_html( $vczapi_sdk_key ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                       data-element="#vczapi_sdk_key">Show</a></td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vczapi_sdk_secret_key"><?php _e( 'Client Secret', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" style="width: 400px;"
                                           name="vczapi_sdk_secret_key"
                                           id="vczapi_sdk_secret_key"
                                           value="<?php echo ! empty( $vczapi_sdk_secret_key ) ? esc_html( $vczapi_sdk_secret_key ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                       data-element="#vczapi_sdk_secret_key">Show</a></td>
                            </tr>
                            </tbody>
                        </table>
                        <span class="description">If credentials have been correctly added you will go to a screen where it
                            says "Meeting has not started". This also requires for you to correctly setup Server to Server OAuth Credentials first.</span>
                    </div>
                </div>
                <!-- End App SDK Credentials -->

                <!-- Save Actions -->
                <div class="vczapi-save-actions">
                    <table class="form-table">
                        <tfoot>
                        <tr>
                            <th>
                                <input type="submit" value="Save" class="button  button-primary">
                                <?php
                                /*
                                <a href="javascript:void(0);"
                                   class="button button-primary check-api-connection"><?php esc_html_e( 'Verify oAuth Credentials', 'video-conferencing-with-zoom-api' ); ?></a>
                                */ ?>
								<?php
								$zoom_users = video_conferencing_zoom_api_get_user_transients();
								if ( ! empty( $zoom_users ) ) {
									$user      = $zoom_users[0];
									$join_link = Codemanas\VczApi\Helpers\Links::getJoinViaBrowserJoinLinks( [ 'link_only' => true, 'direct_join' => 1 ], $user->pmi );
									$join_link = add_query_arg(
										[
											'TB_iframe' => true,
											'width'     => '900',
											'height'    => '700'
										], $join_link );
									add_thickbox();
									?>
                                    <a href="<?php echo $join_link ?>" onclick="alert('If you receive signature timeout message when trying to join this meeting then your meeting SDK credentials are incorrect.')"
                                       class="thickbox button"><?php esc_html_e( 'Verify SDK Credentials', 'video-conferencing-with-zoom-api' ); ?></a>
									<?php
								}
								?>

                            </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- End Save Actions -->
            </form>
        </div>
        <div class="zvc-position-floater-right">
			<?php require_once ZVC_PLUGIN_VIEWS_PATH . '/additional-info.php'; ?>
        </div>
    </div>
<?php
include_once ZVC_PLUGIN_VIEWS_PATH . '/migration.php';