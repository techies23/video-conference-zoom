<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="zvc-cover" style="display: none;"></div>
<div class="zvc-row">
    <div class="zvc-position-floater-left vczapi-connect-box"
         style="width: 70%;margin-right:10px;border-top:1px solid #ccc;">
        <form action="" method="post">
            <?php wp_nonce_field( 'verify_vczapi_zoom_connect', 'vczapi_zoom_connect_nonce' ); ?>

            <!-- OAuth Credentials -->
            <div id="vczapi-s2sOauth-credentials" class="vczapi-admin-accordion expanded">
                <div class="vczapi-admin-accordion--header">
                    <div class="vczapi-admin-accordion--header-title">
                        <h3><?php esc_html_e( 'Server to Server Oauth Credentials', 'video-conferencing-with-zoom-api' ); ?></h3>
                    </div>
                    <div class="vczapi-admin-accordion--header-trigger">
                        <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                    </div>
                </div>
                <div class="vczapi-admin-accordion--content">
                    <p class="description">
                        <?php
                        printf(
                        /* translators: 1: Setup guide link, 2: SDK App Credentials link */
                                esc_html__( 'Please see %1$s on how to generate credentials, additionally for Join via Browser to work please also add %2$s', 'video-conferencing-with-zoom-api' ),
                                '<a href="' . esc_url( 'https://zoomdocs.codemanas.com/setup/#generating-api-credentials' ) . '" target="_blank" rel="noreferrer noopener">' . esc_html__( 'setup guide', 'video-conferencing-with-zoom-api' ) . '</a>',
                                '<a href="#vczapi-s2sOauth-app-sdk-credentials" class="vczapi-go-to-open-accordion" onclick="javascript:void(0);">' . esc_html__( 'SDK App Credentials', 'video-conferencing-with-zoom-api' ) . '</a>'
                        );
                        ?>
                    </p>
                    <table class="form-table">
                        <tbody>
                        <?php if ( ! empty( $oauth_error_message ) ) : ?>
                            <tr>
                                <th colspan="2">
                                    <?php echo wp_kses_post( $oauth_error_message ); ?>
                                </th>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th>
                                <label for="vczapi_oauth_account_id"><?php esc_html_e( 'Oauth Account ID', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="password" style="width: 400px;"
                                       name="vczapi_oauth_account_id"
                                       id="vczapi_oauth_account_id"
                                       value="<?php echo ! empty( $vczapi_oauth_account_id ) ? esc_attr( $vczapi_oauth_account_id ) : ''; ?>">
                                <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                   data-element="#vczapi_oauth_account_id"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="vczapi_oauth_client_id"><?php esc_html_e( 'Oauth Client ID', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="password" style="width: 400px;"
                                       name="vczapi_oauth_client_id"
                                       id="vczapi_oauth_client_id"
                                       value="<?php echo ! empty( $vczapi_oauth_client_id ) ? esc_attr( $vczapi_oauth_client_id ) : ''; ?>">
                                <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                   data-element="#vczapi_oauth_client_id"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="vczapi_oauth_client_secret"><?php esc_html_e( 'Oauth Client Secret', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="password" style="width: 400px;"
                                       name="vczapi_oauth_client_secret"
                                       id="vczapi_oauth_client_secret"
                                       value="<?php echo ! empty( $vczapi_oauth_client_secret ) ? esc_attr( $vczapi_oauth_client_secret ) : ''; ?>">
                                <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                   data-element="#vczapi_oauth_client_secret"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Oauth Credentials -->

            <!-- App SDK Credentials -->
            <div id="vczapi-s2sOauth-app-sdk-credentials" class="vczapi-admin-accordion expanded">
                <div class="vczapi-admin-accordion--header">
                    <div class="vczapi-admin-accordion--header-title">
                        <h3><?php esc_html_e( 'Meeting SDK App Credentials', 'video-conferencing-with-zoom-api' ); ?></h3>
                    </div>
                    <div class="vczapi-admin-accordion--header-trigger">
                        <a href="#"><span class="dashicons dashicons-arrow-up-alt2"></span></a>
                    </div>
                </div>
                <div class="vczapi-admin-accordion--content">
                    <p class="description">
                        <?php
                        printf(
                        /* translators: %s: Documentation link */
                                esc_html__( 'SDK App Credentials are required for Join Via Browser to work, %s on how to generate you App SDK keys', 'video-conferencing-with-zoom-api' ),
                                '<a href="' . esc_url( 'https://zoomdocs.codemanas.com/setup/#setup-app-sdk-credentials' ) . '" target="_blank" rel="noreferrer noopener">' . esc_html__( 'see the documentation', 'video-conferencing-with-zoom-api' ) . '</a>'
                        );
                        ?>
                    </p>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="vczapi_sdk_key"><?php esc_html_e( 'Client ID', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="password" style="width: 400px;"
                                       name="vczapi_sdk_key"
                                       id="vczapi_sdk_key"
                                       value="<?php echo ! empty( $vczapi_sdk_key ) ? esc_attr( $vczapi_sdk_key ) : ''; ?>">
                                <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                   data-element="#vczapi_sdk_key"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="vczapi_sdk_secret_key"><?php esc_html_e( 'Client Secret', 'video-conferencing-with-zoom-api' ); ?></label>
                            </th>
                            <td>
                                <input type="password" style="width: 400px;"
                                       name="vczapi_sdk_secret_key"
                                       id="vczapi_sdk_secret_key"
                                       value="<?php echo ! empty( $vczapi_sdk_secret_key ) ? esc_attr( $vczapi_sdk_secret_key ) : ''; ?>">
                                <a href="javascript:void(0);" class="vczapi-toggle-trigger" data-visible="0"
                                   data-element="#vczapi_sdk_secret_key"><?php esc_html_e( 'Show', 'video-conferencing-with-zoom-api' ); ?></a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <span class="description"><?php esc_html_e( 'If credentials have been correctly added you will go to a screen where it says "Meeting has not started". This also requires for you to correctly setup Server to Server OAuth Credentials first.', 'video-conferencing-with-zoom-api' ); ?></span>
                </div>
            </div>
            <!-- End App SDK Credentials -->

            <!-- Save Actions -->
            <div class="vczapi-save-actions">
                <table class="form-table">
                    <tfoot>
                    <tr>
                        <th>
                            <input type="submit" value="<?php esc_attr_e( 'Save', 'video-conferencing-with-zoom-api' ); ?>" class="button button-primary">
                            <?php
                            $zoom_users = video_conferencing_zoom_api_get_user_transients();
                            if ( ! empty( $zoom_users ) ) {
                                $user      = $zoom_users[0];
                                $join_link = Codemanas\VczApi\Helpers\Links::getJoinViaBrowserJoinLinks( [
                                        'link_only'   => true,
                                        'direct_join' => 1,
                                ], $user->pmi );
                                $join_link = add_query_arg(
                                        [
                                                'TB_iframe' => true,
                                                'width'     => '900',
                                                'height'    => '700',
                                        ],
                                        $join_link
                                );
                                add_thickbox();
                                ?>
                                <a href="<?php echo esc_url( $join_link ); ?>"
                                   onclick="alert('<?php echo esc_js( __( 'If you receive signature timeout message when trying to join this meeting then your meeting SDK credentials are incorrect.', 'video-conferencing-with-zoom-api' ) ); ?>')"
                                   class="thickbox button"><?php esc_html_e( 'Verify SDK Credentials', 'video-conferencing-with-zoom-api' ); ?></a>
                                <?php
                            }
                            ?>
                        </th>
                    </tr>
                    <tfoot>
                </table>
            </div>
            <!-- End Save Actions -->
        </form>
    </div>
    <div class="zvc-position-floater-right">
        <?php require_once ZVC_PLUGIN_VIEWS_PATH . '/additional-info.php'; ?>
    </div>
</div>