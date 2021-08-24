<?php

namespace Codemanas\VczApi;

use Codemanas\VczApi\Shortcodes\Meetings;
use Codemanas\VczApi\Shortcodes\Recordings;
use Codemanas\VczApi\Shortcodes\Webinars;
use DateTimeZone;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shortcodes Controller
 *
 * @since   3.0.0
 * @author  Deepen
 */
class Shortcodes {

	/**
	 * Shortcodes container
	 *
	 * @var array
	 */
	private $shortcodes;

	/**
	 * Zoom_Video_Conferencing_Shorcodes constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );

		$meetings         = Meetings::get_instance();
		$webinars         = Webinars::get_instance();
		$recordings       = Recordings::get_instance();
		$this->shortcodes = array(
			'zoom_api_link'              => array( $meetings, 'show_meeting_by_ID' ),
			'zoom_meeting_post'          => array( $meetings, 'show_meeting_by_postTypeID' ),
			'zoom_list_meetings'         => array( $meetings, 'list_cpt_meetings' ),
			'zoom_list_host_meetings'    => array( $meetings, 'list_live_host_meetings' ),

			//Embed Browser
			'zoom_join_via_browser'      => array( $this, 'join_via_browser' ),

			//Webinars
			'zoom_api_webinar'           => array( $webinars, 'show_webinar_by_ID' ),
			'zoom_list_webinars'         => array( $webinars, 'list_cpt_webinars' ),
			'zoom_list_host_webinars'    => array( $webinars, 'list_live_host_webinars' ),

			//Recordings
			'zoom_recordings'            => array( $recordings, 'recordings_by_user' ),
			'zoom_recordings_by_meeting' => array( $recordings, 'recordings_by_meeting_id' )
		);

		$this->init_shortcodes();
	}

	/**
	 * Init the Shortcode adding function
	 */
	public function init_shortcodes() {
		foreach ( $this->shortcodes as $shortcode => $callback ) {
			add_shortcode( $shortcode, $callback );
		}
	}

	/**
	 * Enqueuing Scripts
	 */
	public function enqueue_scripts() {
		$minified = SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'video-conferencing-with-zoom-api' );
		wp_register_style( 'video-conferencing-with-zoom-api-datable', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.css', false, ZVC_PLUGIN_VERSION );
		wp_register_style( 'video-conferencing-with-zoom-api-datable-responsive', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable-responsive/responsive.dataTables.min.css', [ 'video-conferencing-with-zoom-api-datable' ], ZVC_PLUGIN_VERSION );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.js', [ 'jquery' ], ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable-responsive/dataTables.responsive.min.js', [
			'jquery',
			'video-conferencing-with-zoom-api-datable-js'
		], ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-responsive-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable-responsive/responsive.dataTables.min.js', [
			'jquery',
			'video-conferencing-with-zoom-api-datable-js'
		], ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferncing-with-zoom-browser-js', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/join-via-browser' . $minified . '.js', array( 'jquery' ), ZVC_PLUGIN_VERSION, true );
		wp_register_script( 'video-conferencing-with-zoom-api-shortcode-js', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/shortcode' . $minified . '.js', [
			'jquery',
			'video-conferencing-with-zoom-api-datable-js'
		], ZVC_PLUGIN_VERSION, true );
		wp_localize_script( 'video-conferencing-with-zoom-api-shortcode-js', 'vczapi_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
		wp_localize_script( 'video-conferencing-with-zoom-api-datable-js', 'vczapi_dt_i18n', array(
			'emptyTable'     => __( 'No data available in table', 'video-conferencing-with-zoom-api' ),
			'info'           => sprintf( __( 'Showing %s to %s of %s entries', 'video-conferencing-with-zoom-api' ), '_START_', '_END_', '_TOTAL_' ),
			'infoEmpty'      => __( '', 'video-conferencing-with-zoom-api' ),
			'infoFiltered'   => sprintf( __( 'filtered from %s total entries', 'video-conferencing-with-zoom-api' ), '_MAX_' ),
			'lengthMenu'     => sprintf( __( 'Show %s entries', 'video-conferencing-with-zoom-api' ), '_MENU_' ),
			'loadingRecords' => __( 'Loading', 'video-conferencing-with-zoom-api' ),
			'processing'     => __( 'Processing', 'video-conferencing-with-zoom-api' ),
			'search'         => __( 'Search', 'video-conferencing-with-zoom-api' ),
			'zeroRecords'    => __( 'No matching records found', 'video-conferencing-with-zoom-api' ),
			'paginate'       => [
				'first'    => __( 'First', 'video-conferencing-with-zoom-api' ),
				'last'     => __( 'Last', 'video-conferencing-with-zoom-api' ),
				'next'     => __( 'Next', 'video-conferencing-with-zoom-api' ),
				'previous' => __( 'Previous', 'video-conferencing-with-zoom-api' )
			]
		) );
	}

	/**
	 * Join via browser shortcode
	 *
	 * @param $atts
	 * @param $content
	 *
	 * @return mixed|string|void
	 * @deprecated 3.3.1
	 *
	 */
	public function join_via_browser( $atts, $content = null ) {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferncing-with-zoom-browser-js' );

		// Allow addon devs to perform action before window rendering
		do_action( 'vczapi_before_shortcode_content' );

		extract( shortcode_atts( array(
			'meeting_id'        => '',
			'title'             => '',
			'id'                => 'zoom_video_uri',
			'login_required'    => "no",
			'help'              => "yes",
			'height'            => "500px",
			'disable_countdown' => 'yes',
			'passcode'          => '',
			'webinar'           => 'no'
		), $atts ) );

		ob_start();
		echo '<div class="vczapi-join-via-browser-main-wrapper">';
		if ( empty( $meeting_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . __( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . __( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return;
		}

		if ( ! empty( $login_required ) && $login_required === "yes" && ! is_user_logged_in() ) {
			echo '<h3>' . esc_html__( 'Restricted access, please login to continue.', 'video-conferencing-with-zoom-api' ) . '</h3>';

			return;
		}

		if ( ! empty( $webinar ) && $webinar === "yes" ) {
			$meeting = json_decode( zoom_conference()->getWebinarInfo( $meeting_id ) );
		} else {
			$meeting = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
		}
		$meeting = apply_filters( 'vczapi_join_via_browser_shortcode_meetings', $meeting );

		$zoom_states = get_option( 'zoom_api_meeting_options' );
		if ( empty( $zoom_vanity_url ) ) {
			$mobile_zoom_url = 'https://zoom.us/j/' . $meeting_id;
		} else {
			$mobile_zoom_url = trailingslashit( $zoom_vanity_url . '/j' ) . $meeting_id;
		}

		if ( ! empty( $meeting ) && ! empty( $meeting->code ) ) {
			echo $meeting->message;
		} else {
			if ( ! empty( $meeting ) ) {
				if ( ! empty( $meeting->type ) && ( $meeting->type === 9 || $meeting->type === 8 ) && ! empty( $meeting->occurrences ) ) {
					$occurrences  = ( isset( $meeting->occurrences ) && is_array( $meeting->occurrences ) ) ? $meeting->occurrences : '';
					$meeting_time = is_array( $occurrences ) ? $occurrences[0]->start_time : date( 'Y-m-d h:i a', time() );
				} else {
					$meeting_time = date( 'Y-m-d h:i a', strtotime( $meeting->start_time ) );
				}

				try {
					$meeting_timezone_time = vczapi_dateConverter( 'now', $meeting->timezone, false );
					$meeting_time_check    = vczapi_dateConverter( $meeting_time, $meeting->timezone, false );
					if ( ! empty( $title ) ) {
						?>
                        <h1><?php esc_html_e( $title ); ?></h1>
						<?php
					}

					if ( ! empty( $help ) && $help === "yes" ) {
						$app_store_link = vczapi_get_browser_agent_type();
						if ( ! isset( $zoom_states[ $meeting_id ]['state'] ) ) {
							?>
                            <div class="vczapi-zoom-app-notice-wrap zoom-app-notice">
                                <p><?php echo esc_html__( 'Note: If you are having trouble joining the meeting below, enter Meeting ID: ', 'video-conferencing-with-zoom-api' ) . '<strong>' . esc_html( $meeting_id ) . '</strong> ' . esc_html__( 'and join via Zoom App.', 'video-conferencing-with-zoom-api' ); ?></p>
                                <div class="zoom-links">
                                    <ul>
                                        <li>
                                            <a href="<?php echo esc_url( $mobile_zoom_url ); ?>" class="join-link retry-url"><?php _e( 'Join via Zoom App', 'video-conferencing-with-zoom-api' ); ?></a>
                                        </li>
                                        <li>
                                            <a href="<?php echo esc_url( $app_store_link ); ?>" class="download-link"><?php _e( 'Download App from Store', 'video-conferencing-with-zoom-api' ); ?></a>
                                        </li>
                                        <li>
                                            <a href="https://zoom.us/client/latest/zoom.apk" class="download-link"><?php _e( 'Download from Zoom', 'video-conferencing-with-zoom-api' ); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
						<?php }
					}

					if ( isset( $zoom_states[ $meeting_id ]['state'] ) && $zoom_states[ $meeting_id ]['state'] === "ended" ) {
						echo '<h3>' . esc_html__( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api ' ) . '</h3>';
					} elseif ( $meeting_time_check > $meeting_timezone_time && ! empty( $disable_countdown ) && $disable_countdown === "no" ) {
						?>
                        <div class="dpn-zvc-timer zoom-join-via-browser-countdown" id="dpn-zvc-timer" data-date="<?php echo $meeting->start_time; ?>" data-tz="<?php echo $meeting->timezone; ?>">
                            <div class="dpn-zvc-timer-cell">
                                <div class="dpn-zvc-timer-cell-number">
                                    <div id="dpn-zvc-timer-days"></div>
                                </div>
                                <div class="dpn-zvc-timer-cell-string"><?php _e( 'days', 'video-conferencing-with-zoom-api' ); ?></div>
                            </div>
                            <div class="dpn-zvc-timer-cell">
                                <div class="dpn-zvc-timer-cell-number">
                                    <div id="dpn-zvc-timer-hours"></div>
                                </div>
                                <div class="dpn-zvc-timer-cell-string"><?php _e( 'hours', 'video-conferencing-with-zoom-api' ); ?></div>
                            </div>
                            <div class="dpn-zvc-timer-cell">
                                <div class="dpn-zvc-timer-cell-number">
                                    <div id="dpn-zvc-timer-minutes"></div>
                                </div>
                                <div class="dpn-zvc-timer-cell-string"><?php _e( 'minutes', 'video-conferencing-with-zoom-api' ); ?></div>
                            </div>
                            <div class="dpn-zvc-timer-cell">
                                <div class="dpn-zvc-timer-cell-number">
                                    <div id="dpn-zvc-timer-seconds"></div>
                                </div>
                                <div class="dpn-zvc-timer-cell-string"><?php _e( 'seconds', 'video-conferencing-with-zoom-api' ); ?></div>
                            </div>
                        </div>
					<?php } else { ?>
                        <div class="vczapi-jvb-wrapper zoom-window-wrap">
							<?php
							$styling     = ! empty( $height ) ? "height: " . $height : "height: 500px;";
							$iframe_link = get_post_type_archive_link( 'zoom-meetings' );
							$iframe_arrr = array(
								'join' => vczapi_encrypt_decrypt( 'encrypt', $meeting_id ),
								'type' => 'meeting'
							);
							if ( ! empty( $passcode ) ) {
								$iframe_arrr['pak'] = vczapi_encrypt_decrypt( 'encrypt', $passcode );
							}
							$iframe_query_args = add_query_arg( $iframe_arrr, $iframe_link );
							?>
                            <div id="<?php echo ! empty( $id ) ? esc_html( $id ) : 'video-conferncing-embed-iframe'; ?>" class="zoom-iframe-container">
                                <iframe scrolling="no" style="width:100%; <?php echo $styling; ?>" sandbox="allow-forms allow-scripts allow-same-origin allow-popups allow-modals" allowfullscreen="allowfullscreen" allow="encrypted-media; autoplay; microphone; camera" src="<?php echo esc_url( $iframe_query_args ); ?>" frameborder="0"></iframe>
                            </div>
                        </div>
						<?php
					}
				} catch ( \Exception $e ) {
					error_log( $e->getMessage() );
				}
			}
		}

		echo "</div>";

		$content .= ob_get_clean();

		// Allow addon devs to perform filter before window rendering
		$content = apply_filters( 'vczapi_after_shortcode_content', $content );

		return $content;
	}
}

new Shortcodes();