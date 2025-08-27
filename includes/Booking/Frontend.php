<?php

namespace Codemanas\VczApi\Booking;

use Codemanas\VczApi\Helpers\Date;

class Frontend {
    private static ?Frontend $_instance = null;
    private string $nonce_action = 'verify-vczapi-meeting-booker-nonce';
    private string $nonce_name = 'vczapi-meeting-booker-nonce';
    /**
     * Uses WordPress AUTH_KEY for encryption
     */
    private string $encryption_key;

    private function __construct() {
        $this->encryption_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'vczapi_secure_key_2025';
        add_shortcode( 'vczapi_book_meeting', [ $this, 'vczapi_book_meeting' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_ajax_vczapi-meeting-booker-new-booking', [ $this, 'handle_ajax_form_submission' ] );
        add_action( 'wp_ajax_nopriv_vczapi-meeting-booker-new-booking', [ $this, 'handle_ajax_form_submission' ] );
    }

    public static function get_instance(): ?Frontend {
        return ( self::$_instance == null ) ? self::$_instance = new self() : self::$_instance;
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function encrypt( $data ): string {
        $iv        = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
        $encrypted = openssl_encrypt( $data, 'aes-256-cbc', $this->encryption_key, 0, $iv );

        return base64_encode( $iv . $encrypted );
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function decrypt( $data ): string {
        $data      = base64_decode( $data );
        $ivSize    = openssl_cipher_iv_length( 'aes-256-cbc' );
        $iv        = substr( $data, 0, $ivSize );
        $encrypted = substr( $data, $ivSize );

        return openssl_decrypt( $encrypted, 'aes-256-cbc', $this->encryption_key, 0, $iv );
    }

    /**
     * @return void
     */
    public function enqueue_scripts(): void {
        wp_register_script( 'vczapi-meeting-booker', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/booking.js', false, ZVC_PLUGIN_VERSION, true );
        wp_localize_script( 'vczapi-meeting-booker', 'vczapiMeetingBookerParams', array(
                'ajaxURL' => admin_url( 'admin-ajax.php' ),
        ) );
        wp_enqueue_script( 'vczapi-meeting-booker' );
    }

    /**
     * Generates a booking form for scheduling a meeting.
     *
     * @param  array  $atts  An associative array of attributes which may include:
     *                    - 'type' (string): The type of meeting to book. Default is 'meeting'.
     *                    - 'show_timezone' (bool): Whether to show a timezone dropdown. Default is true.
     *
     * @return string The generated HTML for the meeting booking form.
     */
    public function vczapi_book_meeting( array $atts ): string {
        $args              = shortcode_atts( array(
                'type'          => 'meeting',
                'show_timezone' => true,
                'host'          => ''
        ), $atts );
        $timezones         = Date::timezone_list();
        $wp_timezone       = wp_timezone_string();
        $loadingSVG        = apply_filters( 'vczapi_meeting_booker_loadingSVG', $this->get_loading_svg() );
        $host_id           = $this->findHostByEmail( $args['host'] );
        $encrypted_host_id = $this->encrypt( $host_id );


        ob_start(); ?>
        <div class="vczapi-meeting-booker">
            <div class="vczapi-meeting-booker__loader">
                <?php echo $loadingSVG; ?>
            </div>
            <form class="vczapi-meeting-booker__form" method="post" action="">
                <?php wp_nonce_field( $this->nonce_action, $this->nonce_name ); ?>
                <?php //this is a honey pot ?>
                <input type="hidden" name="zoom_action" value=""/>
                <input type="hidden" name="vczapi-meeting-booker__host-id" value="<?php echo esc_attr( $encrypted_host_id ); ?>"/>

                <label>
                    <input type="text" required name="vczapi-meeting-booker__date-input" class="vczapi-meeting-booker__date-input"/>
                </label>
                <?php if ( $args['show_timezone'] !== 'false' ) : ?>
                    <label>
                        <select name="vczapi-meeting-booker__timezone">
                            <?php foreach ( $timezones as $timezone ) { ?>
                                <option value="<?php echo $timezone; ?>" <?php selected( $wp_timezone, $timezone ); ?>><?php echo $timezone; ?></option>
                            <?php } ?>
                        </select>
                    </label>
                <?php endif; ?>
                <label><input type="text" required name="vczapi-meeting-booker__name" class="vczapi-meeting-booker__name" placeholder="<?php
                    esc_attr_e( 'Full Name', 'video-conferencing-with-zoom-api' );
                    ?>" value="<?php echo esc_attr( is_user_logged_in() ? $this->get_user_full_name() : '' ); ?>"/></label>
                <label>
                    <input type="email" required name="vczapi-meeting-booker__email" class="vczapi-meeting-booker__email" placeholder="<?php
                    esc_attr_e( 'Email', 'video-conferencing-with-zoom-api' ); ?>" value="<?php echo esc_attr( is_user_logged_in() ? $this->get_user_email() : '' ); ?>"/>
                </label>
                <label>
                    <input type="text" name="vczapi-meeting-booker__phone" class="vczapi-meeting-booker__phone" placeholder="<?php esc_attr_e( 'Phone', 'video-conferencing-with-zoom-api' ); ?>"/>
                </label>
                <input type="submit" class="vczapi-meeting-booker__submit-button" value="Book Meeting"/>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Generates and returns an SVG string representation of a loading animation.
     *
     * @return string The SVG string representing a loading animation.
     */
    private function get_loading_svg(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="50" height="50" style="shape-rendering: auto; display: block; background: transparent;" ><g><circle stroke-dasharray="164.93361431346415 56.97787143782138" r="35" stroke-width="10" stroke="#3798ff" fill="none" cy="50" cx="50">
  <animateTransform keyTimes="0;1" values="0 50 50;360 50 50" dur="1s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
</circle><g></g></g><!-- [ldio] generated by https://loading.io --></svg>';
    }

    /**
     * Retrieves and returns the full name of the current user.
     * If both the first and last names are available, it returns them concatenated.
     * If only the first name is available, it returns the first name.
     * If neither the first name nor last name is set, it returns the user's display name.
     *
     * @return string The full name, first name, or display name of the current user.
     */
    private function get_user_full_name(): string {
        $user = wp_get_current_user();
        if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
            return $user->first_name . ' ' . $user->last_name;
        } elseif ( ! empty( $user->first_name ) ) {
            return $user->first_name;
        }

        return $user->display_name;
    }

    private function get_user_email(): string {
        $user = wp_get_current_user();

        return $user->user_email;
    }

    public function handle_ajax_form_submission() {
        if ( ! wp_verify_nonce( $_POST[ $this->nonce_name ] ?? '', $this->nonce_action ) ) {
            wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
        }

        $honeypot = sanitize_text_field( $_POST['zoom_action'] ) ?? '';
        if ( ! empty( $honeypot ) ) {
            wp_send_json_error( [ 'message' => 'Something went wrong' ] );
        }

        $date              = sanitize_text_field( $_POST['vczapi-meeting-booker__date-input'] ?? '' );
        $timezone          = sanitize_text_field( $_POST['vczapi-meeting-booker__timezone'] ?? '' );
        $name              = sanitize_text_field( $_POST['vczapi-meeting-booker__name'] ?? '' );
        $encrypted_host_id = sanitize_text_field( $_POST['vczapi-meeting-booker__host-id'] ?? '' );
        $email             = sanitize_email( $_POST['vczapi-meeting-booker__email'] ?? '' );
        $phone             = sanitize_text_field( $_POST['vczapi-meeting-booker__phone'] ?? '' );
        $host_id           = $this->decrypt( $encrypted_host_id );

        if ( empty( $date ) || empty( $name ) ) {
            wp_send_json_error( [ 'message' => 'Required fields are missing' ], 400 );
        }
        $post_id = CPT::get_instance()->add_entry( $name, $date, $timezone, $host_id, $email, $phone );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( [ 'message' => $post_id->get_error_message() ], 500 );
        }

        wp_send_json_success( [
                'message' => 'Booking created successfully',
                'id'      => $post_id
        ] );
    }

    private function findHostByEmail( string $email ) {
        $users  = video_conferencing_zoom_api_get_user_transients();
        $needle = strtolower( trim( $email ) );
        foreach ( $users as $user ) {
            if ( isset( $user->email ) && strtolower( trim( $user->email ) ) === $needle ) {
                return $user->id;
            }
        }

        return null;
    }


}