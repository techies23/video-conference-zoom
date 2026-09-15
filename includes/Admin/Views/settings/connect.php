<?php
/**
 * Zoom Connect Settings View
 *
 * @package Codemanas\VczApi
 * @since   2.0.0
 * @updated 4.7.0
 */

defined( 'ABSPATH' ) || exit;

$depreciation_link = sprintf(
	'<a href="%s" target="_blank" rel="noreferrer noopener">%s</a>',
	esc_url( 'https://marketplace.zoom.us/docs/guides/build/jwt-app/jwt-faq/#jwt-app-type-deprecation-faq--omit-in-toc-' ),
	esc_html__( 'JWT App Type Depreciation FAQ', 'video-conferencing-with-zoom-api' )
);

$migration_wizard_url = add_query_arg(
	[
		'post_type' => 'zoom-meetings',
		'page'      => 'zoom-video-conferencing-settings',
		'migrate'   => 'now',
	],
	admin_url( 'edit.php' )
);

$migration_wizard_link = sprintf(
	'<a href="%s">%s</a>',
	esc_url( $migration_wizard_url ),
	esc_html__( 'migration wizard', 'video-conferencing-with-zoom-api' )
);

$is_jwt_active = vczapi_is_jwt_active();
?>

<div id="vczapi-overlay" class="vczapi-overlay" style="display: none;"></div>

<div class="vczapi-connect">
    <div class="vczapi-connect__main">
        <form action="" method="post" class="vczapi-connect__form">
			<?php wp_nonce_field( 'verify_vczapi_zoom_connect', 'vczapi_zoom_connect_nonce' ); ?>

            <div class="vczapi-admin-accordion">

				<?php if ( apply_filters( 'vczapi_show_jwt_keys', $is_jwt_active ) ) : ?>
                    <!-- Legacy JWT Implementation -->
                    <div id="vczapi-s2sOauth-jwt-credentials" class="vczapi-admin-accordion__item">
                        <input type="checkbox" id="vczapi-accordion-jwt" class="vczapi-admin-accordion__toggle">
                        <label for="vczapi-accordion-jwt" class="vczapi-admin-accordion__header">
                            <span class="vczapi-admin-accordion__title"><?php esc_html_e( 'JWT Credentials ( Legacy )', 'video-conferencing-with-zoom-api' ); ?></span>
                            <span class="dashicons dashicons-arrow-down-alt2 vczapi-admin-accordion__icon"></span>
                        </label>
                        <div class="vczapi-admin-accordion__content">
                            <p class="description">
								<?php
								printf(
								/* translators: 1: Deprecation link, 2: Migration wizard link */
									esc_html__( 'Zoom is deprecating their JWT app. Please see %1$s for details. Until the deadline all your current settings will work; however, to ensure a smooth transition to the new Server-to-Server OAuth system + SDK (required for Join via Browser), we recommend migrating as soon as possible. Run the %2$s now to complete the process.', 'video-conferencing-with-zoom-api' ),
									$depreciation_link,
									$migration_wizard_link
								);
								?>
                            </p>
                            <table class="form-table vczapi-connect__form-table">
                                <tbody>
                                <tr>
                                    <th>
                                        <label for="zoom_api_key"><?php esc_html_e( 'JWT API Key', 'video-conferencing-with-zoom-api' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" class="regular-text vczapi-connect__input" name="zoom_api_key" id="zoom_api_key" value="<?php echo ! empty( $zoom_api_key ) ? esc_attr( $zoom_api_key ) : ''; ?>">
                                        <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#zoom_api_key"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="zoom_api_secret"><?php esc_html_e( 'JWT API Secret Key', 'video-conferencing-with-zoom-api' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" class="regular-text vczapi-connect__input" name="zoom_api_secret" id="zoom_api_secret" value="<?php echo ! empty( $zoom_api_secret ) ? esc_attr( $zoom_api_secret ) : ''; ?>">
                                        <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#zoom_api_secret"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End Legacy JWT Implementation -->
				<?php endif; ?>

                <!-- OAuth Credentials -->
                <div id="vczapi-s2sOauth-credentials" class="vczapi-admin-accordion__item">
                    <input type="checkbox" id="vczapi-accordion-oauth" class="vczapi-admin-accordion__toggle" checked>
                    <label for="vczapi-accordion-oauth" class="vczapi-admin-accordion__header">
                        <span class="vczapi-admin-accordion__title"><?php esc_html_e( 'Server to Server OAuth Credentials', 'video-conferencing-with-zoom-api' ); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2 vczapi-admin-accordion__icon"></span>
                    </label>
                    <div class="vczapi-admin-accordion__content">
                        <p class="description">
							<?php
							$oauth_docs_link = '<a href="https://zoomdocs.codemanas.com/setup/#generating-api-credentials" target="_blank" rel="noreferrer noopener">' . esc_html__( 'setup guide', 'video-conferencing-with-zoom-api' ) . '</a>';
							$sdk_app_link    = '<a href="#vczapi-s2sOauth-app-sdk-credentials" class="vczapi-go-to-open-accordion">' . esc_html__( 'SDK App Credentials', 'video-conferencing-with-zoom-api' ) . '</a>';

							printf(
							/* translators: 1: Documentation link, 2: SDK credentials accordion link */
								esc_html__( 'Please see %1$s on how to generate credentials. Additionally, for "Join via Browser" functionality to work, please also configure %2$s.', 'video-conferencing-with-zoom-api' ),
								$oauth_docs_link,
								$sdk_app_link
							);
							?>
                        </p>
                        <table class="form-table vczapi-connect__form-table">
                            <tbody>
							<?php if ( ! empty( $oauth_error_message ) ) : ?>
                                <tr>
                                    <td colspan="2" class="vczapi-connect__error-notice">
										<?php echo wp_kses_post( $oauth_error_message ); ?>
                                    </td>
                                </tr>
							<?php endif; ?>
                            <tr>
                                <th>
                                    <label for="vczapi_oauth_account_id"><?php esc_html_e( 'OAuth Account ID', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" class="regular-text vczapi-connect__input" name="vczapi_oauth_account_id" id="vczapi_oauth_account_id" value="<?php echo ! empty( $vczapi_oauth_account_id ) ? esc_attr( $vczapi_oauth_account_id ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_oauth_account_id"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vczapi_oauth_client_id"><?php esc_html_e( 'OAuth Client ID', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" class="regular-text vczapi-connect__input" name="vczapi_oauth_client_id" id="vczapi_oauth_client_id" value="<?php echo ! empty( $vczapi_oauth_client_id ) ? esc_attr( $vczapi_oauth_client_id ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_oauth_client_id"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vczapi_oauth_client_secret"><?php esc_html_e( 'OAuth Client Secret', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" class="regular-text vczapi-connect__input" name="vczapi_oauth_client_secret" id="vczapi_oauth_client_secret" value="<?php echo ! empty( $vczapi_oauth_client_secret ) ? esc_attr( $vczapi_oauth_client_secret ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_oauth_client_secret"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                                </td>
                            </tr>
							<?php if ( $is_jwt_active ) : ?>
                                <tr>
                                    <th>
                                        <label for="vczapi-delete-jwt-keys"><?php esc_html_e( 'Delete JWT Keys', 'video-conferencing-with-zoom-api' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="vczapi-delete-jwt-keys" name="vczapi-delete-jwt-keys" value="on"/>
                                        <span class="description"><?php esc_html_e( 'Check this box to delete legacy JWT keys after saving and verifying Server-to-Server OAuth credentials.', 'video-conferencing-with-zoom-api' ); ?></span>
                                    </td>
                                </tr>
							<?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End OAuth Credentials -->

                <!-- App SDK Credentials -->
                <div id="vczapi-s2sOauth-app-sdk-credentials" class="vczapi-admin-accordion__item">
                    <input type="checkbox" id="vczapi-accordion-sdk" class="vczapi-admin-accordion__toggle" checked>
                    <label for="vczapi-accordion-sdk" class="vczapi-admin-accordion__header">
                        <span class="vczapi-admin-accordion__title"><?php esc_html_e( 'Meeting SDK App Credentials', 'video-conferencing-with-zoom-api' ); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2 vczapi-admin-accordion__icon"></span>
                    </label>
                    <div class="vczapi-admin-accordion__content">
                        <p class="description">
							<?php
							$sdk_docs_link = '<a href="https://zoomdocs.codemanas.com/setup/#setup-app-sdk-credentials" target="_blank" rel="noreferrer noopener">' . esc_html__( 'see the documentation', 'video-conferencing-with-zoom-api' ) . '</a>';
							printf(
							/* translators: %s: Documentation link */
								esc_html__( 'SDK App Credentials are required for "Join via Browser" to work. Please %s on how to generate your App SDK keys.', 'video-conferencing-with-zoom-api' ),
								$sdk_docs_link
							);
							?>
                        </p>
                        <table class="form-table vczapi-connect__form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="vczapi_sdk_key"><?php esc_html_e( 'Client ID', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" class="regular-text vczapi-connect__input" name="vczapi_sdk_key" id="vczapi_sdk_key" value="<?php echo ! empty( $vczapi_sdk_key ) ? esc_attr( $vczapi_sdk_key ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_sdk_key"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vczapi_sdk_secret_key"><?php esc_html_e( 'Client Secret', 'video-conferencing-with-zoom-api' ); ?></label>
                                </th>
                                <td>
                                    <input type="password" class="regular-text vczapi-connect__input" name="vczapi_sdk_secret_key" id="vczapi_sdk_secret_key" value="<?php echo ! empty( $vczapi_sdk_secret_key ) ? esc_attr( $vczapi_sdk_secret_key ) : ''; ?>">
                                    <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0" data-element="#vczapi_sdk_secret_key"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p class="description">
							<?php esc_html_e( 'If credentials have been correctly added, you will see a screen displaying "Meeting has not started". This requires Server-to-Server OAuth Credentials to be properly configured first.', 'video-conferencing-with-zoom-api' ); ?>
                        </p>
                    </div>
                </div>
                <!-- End App SDK Credentials -->

            </div>

            <!-- Save Actions -->
            <div class="vczapi-connect__actions">
				<?php submit_button( __( 'Save Changes', 'video-conferencing-with-zoom-api' ), 'primary', 'submit', false ); ?>

				<?php
				$zoom_users = video_conferencing_zoom_api_get_user_transients();
				if ( ! empty( $zoom_users[0]->pmi ) ) :
					$join_link = \Codemanas\VczApi\Helpers\Links::getJoinViaBrowserJoinLinks(
						[
							'link_only'   => true,
							'direct_join' => 1,
						],
						$zoom_users[0]->pmi
					);

					$thickbox_link = add_query_arg(
						[
							'TB_iframe' => 'true',
							'width'     => '900',
							'height'    => '700',
						],
						$join_link
					);
					add_thickbox();
					?>
                    <a href="<?php echo esc_url( $thickbox_link ); ?>"
                       onclick="alert('<?php echo esc_js( __( 'If you receive a signature timeout message when trying to join this meeting, your Meeting SDK credentials are invalid.', 'video-conferencing-with-zoom-api' ) ); ?>')"
                       class="thickbox button button-secondary vczapi-connect__btn-verify">
						<?php esc_html_e( 'Verify SDK Credentials', 'video-conferencing-with-zoom-api' ); ?>
                    </a>
				<?php endif; ?>
            </div>
            <!-- End Save Actions -->
        </form>
    </div>

    <div class="vczapi-connect__sidebar">
		<?php require_once ZVC_PLUGIN_VIEWS_PATH . '/additional-info.php'; ?>
    </div>
</div>