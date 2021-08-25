<?php

use Codemanas\Zoom\Core\OAuth;

$oauth = OAuth::get_instance();
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Defining Varaibles
$disable_jvb                        = get_option( 'zoom_api_disable_jvb' );
$vczapi_enable_oauth_individual_use = get_option( 'vczapi_enable_oauth_individual_use' );
$zoom_api_key                       = get_option( 'zoom_api_key' );
$zoom_api_secret                    = get_option( 'zoom_api_secret' );

dump($vczapi_enable_oauth_individual_use);
?>
<div class="zvc-row" style="margin-top:10px;">
    <div class="zvc-position-floater-left" style="width: 70%;margin-right:10px;border-top:1px solid #ccc;">

		<?php $oauth->maybe_connected_to_zoom_html(); ?>

		<?php if ( current_user_can( 'manage_options' ) ): ?>
            <script>
                (function ($) {
                    var vczapiToggleJWT = {
                        init: function () {
                            this.$vczapiForm = $('#vczapi-api-connection-form');
                            if (this.$vczapiForm.length < 1) {
                                return;
                            }
                            this.$enableToggle = this.$vczapiForm.find('#meeting_disable_join_via_browser');
                            this.$clientKeyRow = this.$vczapiForm.find('.vczapi-client-key');
                            this.$clientSecretRow = this.$vczapiForm.find('.vczapi-client-secret');
                            this.$verifyJWTKeyButton = this.$vczapiForm.find('#vzcpia-verify-jwt-key');

                            this.$enableToggle.on('click', this.isChecked.bind(this));
                            this.$verifyJWTKeyButton.on('click', this.verifyJWT.bind(this));
                        },
                        isChecked: function (e) {
                            $target = $(e.target);
                            if ($target.is(':checked')) {
                                this.$clientKeyRow.hide();
                                this.$clientSecretRow.hide();
                            } else {
                                this.$clientKeyRow.show();
                                this.$clientSecretRow.show();
                            }
                        },
                        verifyJWT: function (e) {
                            e.preventDefault();

                            this.clientAPIKey = this.$clientKeyRow.find('#zoom_api_key').val();
                            this.clientSecretKey = this.$clientSecretRow.find('#zoom_api_secret').val();

                            $.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: {
                                    action: 'vczapi_verify_jwt_keys',
                                    api_key: this.clientAPIKey,
                                    secret_key: this.clientSecretKey
                                },
                                context: this,
                                beforeSend: function () {
                                    this.$verifyJWTKeyButton.val('Verifying...');
                                },
                                success: function (response) {
                                    if (response) {
                                        alert(response);
                                    } else {
                                        alert('Something has gone wrong...');
                                    }

                                    this.$verifyJWTKeyButton.val('Verify JWT Keys');
                                },
                                error: function (XMLHttpRequest, textStatus, errorThrown) {
                                    console.log(errorThrown);
                                }
                            });

                        }

                    };
                    $(function () {
                        vczapiToggleJWT.init();
                    });

                })(jQuery);
            </script>
            <form action="edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings" method="POST" id="vczapi-api-connection-form">
				<?php wp_nonce_field( '_zoom_settings_update_nonce_action', '_zoom_settings_nonce' ); ?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th>
                            <label for="vczapi_enable_oauth_individual_use">
								<?php _e( 'Individual Accounts', 'video-conferencing-with-zoom-api' ); ?>
                            </label>
                        </th>
                        <td>
                            <input type="checkbox" id="vczapi_enable_oauth_individual_use" name="vczapi_enable_oauth_individual_use" value="yes" <?php checked( $vczapi_enable_oauth_individual_use, 'yes' ); ?>>
                            <span class="description"><?php _e( 'This option will allow other users logged into this site to create Zoom Meetings using their own Zoom accounts' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="meeting_disable_join_via_browser"><?php _e( 'Disable Join via browser', 'video-conferencing-with-zoom-api' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="meeting_disable_join_via_browser" id="meeting_disable_join_via_browser" <?php ! empty( $disable_jvb ) ? checked( $disable_jvb, 'on' ) : false; ?>>
                            <span class="description">
                        <?php _e( 'Enabling join via browser requires some extra configuration, so please check this box if you want users to be able to join Zoom meetings via your site.', 'video-conferencing-with-zoom-api' ) ?>
                        </span>
                        </td>
                    </tr>
                    <tr class="vczapi-client-key" <?php echo empty( $disable_jvb ) ? 'style="display:table-row"' : 'style="display:none"'; ?>>
                        <th><label><?php _e( 'API Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                        <td>
                            <input type="password" style="width: 400px;" name="zoom_api_key" id="zoom_api_key" value="<?php echo ! empty( $zoom_api_key ) ? esc_html( $zoom_api_key ) : ''; ?>">
                            <a href="javascript:void(0);" class="toggle-api">Show</a></td>
                    </tr>
                    <tr class="vczapi-client-secret" <?php echo empty( $disable_jvb ) ? 'style="display:table-row"' : 'style="display:none"'; ?>>
                        <th><label><?php _e( 'API Secret Key', 'video-conferencing-with-zoom-api' ); ?></label></th>
                        <td>
                            <input type="password" style="width: 400px;" name="zoom_api_secret" id="zoom_api_secret" value="<?php echo ! empty( $zoom_api_secret ) ? esc_html( $zoom_api_secret ) : ''; ?>">
                            <a href="javascript:void(0);" class="toggle-secret">Show</a></td>
                    </tr>
                    <tr class="vczapi-client-secret" <?php echo empty( $disable_jvb ) ? 'style="display:table-row"' : 'style="display:none"'; ?>>
                        <th colspan="2">
                            <h3 style="margin: 0 0 20px;"><?php _e( 'Please follow', 'video-conferencing-with-zoom-api' ) ?>
                                <a target="_blank" href="https://zoom.codemanas.com/integration/"><?php _e( 'this guide', 'video-conferencing-with-zoom-api' ) ?> </a> <?php _e( 'to generate the below API values from your Zoom account', 'video-conferencing-with-zoom-api' ) ?>
                            </h3>
                        </th>
                    </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="save_zoom_api_connection" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'video-conferencing-with-zoom-api' ); ?>">
                    <input type="button" class="button button-primary vczapi-client-secret" value="<?php _e( 'Verify JWT Keys', 'video-conferencing-with-zoom-api' ); ?>" id="vzcpia-verify-jwt-key" <?php echo empty( $disable_jvb ) ? 'style="display:inline-block"' : 'style="display:none"'; ?>>
                </p>
            </form>
		<?php endif; ?>
    </div>
    <div class="zvc-position-floater-right">
        <ul class="zvc-information-sec">
            <li>
                <a target="_blank" href="https://zoom.codemanas.com"><?php _e( 'Documentation', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li>
                <a target="_blank" href="https://www.codemanas.com"><?php _e( 'Contact for additional Support', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li><a target="_blank" href="https://deepenbajracharya.com.np"><?php _e( 'Developer', 'video-conferencing-with-zoom-api' ); ?></a></li>
            <li>
                <a target="_blank" href="<?php echo admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-addons' ); ?>"><?php _e( 'Addons', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
            <li>
                <a target="_blank" href="https://www.facebook.com/groups/zoomwp/"><?php _e( 'Facebook Group', 'video-conferencing-with-zoom-api' ); ?></a>
            </li>
        </ul>
        <div class="zvc-information-sec">
            <h3>WooCommerce Addon</h3>
            <p>Integrate your Zoom Meetings directly to WooCommerce or WooCommerce booking products. Zoom Integration for WooCommerce allows you to automate your zoom meetings directly from your WordPress dashboard by linking zoom meetings to your WooCommerce or WooCommerce Booking products automatically. Users will receive join links in their booking confirmation emails.</p>
            <p><a href="https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/" class="button button-primary">More Details</a>
            </p>
        </div>
        <div class="zvc-information-sec">
            <h3>Need Idle Auto logout ?</h3>
            <p>Protect your WordPress users' sessions from shoulder surfers and snoopers!</p>
            <p>Use the Inactive Logout plugin to automatically terminate idle user sessions, thus protecting the site if the users leave unattended sessions.</p>
            <p>
                <a target="_blank" href="https://wordpress.org/plugins/inactive-logout/"><?php _e( 'Try inactive logout', 'video-conferencing-with-zoom-api' ); ?></a>
        </div>
    </div>
</div>
