<?php
/**
 * Shortcodes Controller
 *
 * @since   3.0.0
 * @author  Deepen
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Zoom_Video_Conferencing_Shorcodes {

	/**
	 * Define post type
	 *
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	/**
	 * Meeting list
	 *
	 * @var string
	 */
	public static $meetings_list_number = '0';

	/**
	 * Zoom_Video_Conferencing_Shorcodes constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );
		add_shortcode( 'zoom_api_link', array( $this, 'render_main' ) );
		add_shortcode( 'zoom_api_webinar', array( $this, 'show_webinar' ) );
		add_shortcode( 'zoom_list_meetings', array( $this, 'show_meetings' ) );
		add_shortcode( 'zoom_list_host_webinars', array( $this, 'show_host_webinars' ) );
		add_shortcode( 'zoom_list_host_meetings', array( $this, 'show_host_meetings' ) );
		add_shortcode( 'zoom_join_via_browser', array( $this, 'join_via_browser' ) );
	}

	/**
	 * Enqueuing Scripts
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'video-conferencing-with-zoom-api' );
		wp_register_script( 'video-conferncing-with-zoom-browser-js', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/join-browser.min.js', array( 'jquery' ), '3.2.4', true );
		wp_register_style( 'video-conferencing-with-zoom-api-datable', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.css', false, '3.0.0' );
		wp_register_script( 'video-conferencing-with-zoom-api-datable-js', ZVC_PLUGIN_VENDOR_ASSETS_URL . '/datatable/jquery.dataTables.min.js', [ 'jquery' ], '3.0.0', true );
		wp_register_script( 'video-conferencing-with-zoom-api-shortcode-js', ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/shortcode.js', [
			'jquery',
			'video-conferencing-with-zoom-api-datable-js'
		], '1.0.0', true );
	}

	/**
	 * Shows a list of Host Webinars
	 *
	 * @param $atts
	 *
	 * @return false|string|void
	 * @throws Exception
	 */
	public function show_host_webinars( $atts ) {
		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$atts = shortcode_atts(
			[ 'host' => '' ],
			$atts
		);

		if ( empty( $atts['host'] ) ) {
			return __( 'Host ID should be given when defining this shortcode.', 'video-conferencing-with-zoom-api' );
		}

		$webinars         = get_option( '_vczapi_user_webinars_for_' . $atts['host'] );
		$cache_expiration = get_option( '_vczapi_user_webinars_for_' . $atts['host'] . '_expiration' );
		if ( empty( $webinars ) || $cache_expiration < time() ) {
			$encoded_meetings = zoom_conference()->listWebinar( $atts['host'] );
			$decoded_meetings = json_decode( $encoded_meetings );
			if ( isset( $decoded_meetings->webinars ) ) {
				$webinars = $decoded_meetings->webinars;
				update_option( '_vczapi_user_webinars_for_' . $atts['host'], $webinars );
				update_option( '_vczapi_user_webinars_for_' . $atts['host'] . '_expiration', time() + 60 * 30 );
			} else {
				if ( ! empty( $decoded_meetings ) && ! empty( $decoded_meetings->code ) ) {
					return '<strong>Zoom API Error:</strong>' . $decoded_meetings->message;
				} else {
					return __( 'Could not retrieve meetings, check Host ID', 'video-conferencing-with-zoom-api' );
				}
			}
		}

		ob_start();
		?>
        <table class="vczapi-user-meeting-list">
            <thead>
            <tr>
                <th><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                <th><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                <th><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
                <th><?php _e( 'Actions', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			if ( ! empty( $webinars ) ) {
				foreach ( $webinars as $webinar ) {
					$pass = ! empty( $webinar->password ) ? $webinar->password : false;
					?>
                    <tr>
                        <td><?php echo $webinar->topic; ?></td>
                        <td><?php echo vczapi_dateConverter( $webinar->start_time, $webinar->timezone ); ?></td>
                        <td><?php echo $webinar->timezone; ?></td>
                        <td><a href="<?php echo $webinar->join_url; ?>"><?php _e( 'Join via App', 'video-conferencing-with-zoom-api' ); ?></a> /
                            <a href="<?php echo vczapi_get_browser_join_shortcode( $webinar->id, $pass, true ); ?>"><?php _e( 'Join via Browser', 'video-conferencing-with-zoom-api' ); ?></a>
                        </td>
                    </tr>
					<?php
				}
			}
			?>
            </tbody>
        </table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Show Host Meetings list
	 *
	 * @param $atts
	 *
	 * @return false|string|void
	 * @throws Exception
	 */
	public function show_host_meetings( $atts ) {
		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$atts = shortcode_atts(
			[ 'host' => '' ],
			$atts
		);

		if ( empty( $atts['host'] ) ) {
			return __( 'Host ID should be given when defining this shortcode.', 'video-conferencing-with-zoom-api' );
		}

		$meetings = get_transient( 'vczapi_user_meetings_for_' . $atts['host'] );
		if ( empty( $meetings ) ) {
			$encoded_meetings = zoom_conference()->listMeetings( $atts['host'] );
			$decoded_meetings = json_decode( $encoded_meetings );
			if ( isset( $decoded_meetings->meetings ) ) {
				$meetings = $decoded_meetings->meetings;
				set_transient( 'vczapi_user_meetings_for_' . $atts['host'], $meetings, 1800 );
			} else {
				return __( 'Could not retrieve meetings, check Host ID', 'video-conferencing-with-zoom-api' );
			}
		}

		ob_start();
		?>
        <table class="vczapi-user-meeting-list">
            <thead>
            <tr>
                <th><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                <th><?php _e( 'Meeting Status', 'video-conferencing-with-zoom-api' ); ?></th>
                <th><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                <th><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
                <th><?php _e( 'Actions', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ( $meetings as $meeting ) {
				$zoom_host_url             = 'https://zoom.us' . '/wc/' . $meeting->id . '/start';
				$zoom_host_url             = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );
				$start_meeting_via_browser = '<a class="start-meeting-btn reload-meeting-started-button" target="_blank" href="' . esc_url( $zoom_host_url ) . '" class="join-link">' . __( 'Start via Browser', 'video-conferencing-with-zoom-api' ) . '</a>';

				$meeting_status = '';
				if ( ! empty( $meeting->status ) ) {
					switch ( $meeting->status ) {
						case 0;
							$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/2.png" style="width:14px;" title="Not Started" alt="Not Started">';
							break;
						case 1;
							$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/3.png" style="width:14px;" title="Completed" alt="Completed">';
							break;
						case 2;
							$meeting_status = '<img src="' . ZVC_PLUGIN_IMAGES_PATH . '/1.png" style="width:14px;" title="Currently Live" alt="Live">';
							break;
						default;
							break;
					}
				} else {
					$meeting_status = "N/A";
				}

				$start_url = ! empty( $meeting->start_url ) ? $meeting->start_url : $meeting->join_url;
				echo '<td>' . $meeting->topic . '</td>';
				echo '<td>' . $meeting_status . '</td>';
				echo '<td>' . vczapi_dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a' ) . '</td>';
				echo '<td>' . $meeting->timezone . '</td>';
				echo '<td><div class="view">
<a href="' . $start_url . '" rel="permalink" target="_blank">' . __( 'Start via App', 'video-conferencing-with-zoom-api' ) . '</a><span class="sep"> /</span></div>
                                    <div class="view">' . $start_meeting_via_browser . '</div></td>';
				echo '</tr>';
			}
			?>
            </tbody>
        </table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render output for shortcode
	 *
	 * @param      $atts
	 * @param null $content
	 *
	 * @return string
	 * @author Deepen
	 * @since  3.0.0
	 */
	function render_main( $atts, $content = null ) {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api' );

		extract( shortcode_atts( array(
			'meeting_id' => 'javascript:void(0);',
			'link_only'  => 'no',
		), $atts ) );

		unset( $GLOBALS['vanity_uri'] );
		unset( $GLOBALS['zoom_meetings'] );

		ob_start();

		if ( empty( $meeting_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . __( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . __( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return false;
		}

		$zoom_states = get_option( 'zoom_api_meeting_options' );
		if ( isset( $zoom_states[ $meeting_id ]['state'] ) && $zoom_states[ $meeting_id ]['state'] === "ended" ) {
			echo '<h3>' . esc_html__( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api ' ) . '</h3>';

			return;
		}

		$vanity_uri               = get_option( 'zoom_vanity_url' );
		$meeting                  = $this->fetch_meeting( $meeting_id );
		$GLOBALS['vanity_uri']    = $vanity_uri;
		$GLOBALS['zoom_meetings'] = $meeting;
		if ( ! empty( $meeting ) && ! empty( $meeting->code ) ) {
			?>
            <p class="dpn-error dpn-mtg-not-found"><?php echo $meeting->message; ?></p>
			<?php
		} else {
			if ( ! empty( $link_only ) && $link_only === "yes" ) {
				$this->generate_link_only();
			} else {
				if ( $meeting ) {
					//Get Template
					vczapi_get_template( 'shortcode/zoom-shortcode.php', true, false );
				} else {
					printf( __( 'Please try again ! Some error occured while trying to fetch meeting with id:  %d', 'video-conferencing-with-zoom-api' ), $meeting_id );
				}
			}
		}

		return ob_get_clean();
	}

	/**
	 * Show webinar details
	 *
	 * @param $atts
	 * @param null $content
	 *
	 * @return bool|false|string|void
	 * @author Deepen Bajracharya
	 *
	 * @since 3.4.0
	 */
	public function show_webinar( $atts, $content = null ) {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api' );

		extract( shortcode_atts( array(
			'webinar_id' => 'javascript:void(0);',
			'link_only'  => 'no',
		), $atts ) );

		unset( $GLOBALS['vanity_uri'] );
		unset( $GLOBALS['zoom_webinars'] );

		ob_start();
		if ( empty( $webinar_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . __( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . __( 'No webinar id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return false;
		}

		$vanity_uri               = get_option( 'zoom_vanity_url' );
		$webinar                  = $this->fetch_webinar( $webinar_id );
		$GLOBALS['vanity_uri']    = $vanity_uri;
		$GLOBALS['zoom_webinars'] = $webinar;
		if ( ! empty( $webinar ) && ! empty( $webinar->code ) ) {
			?>
            <p class="dpn-error dpn-mtg-not-found"><?php echo $webinar->message; ?></p>
			<?php
		} else {
			if ( ! empty( $link_only ) && $link_only === "yes" ) {
				$this->generate_link_only();
			} else {
				if ( $webinar ) {
					//Get Template
					vczapi_get_template( 'shortcode/zoom-webinar.php', true, false );
				} else {
					printf( __( 'Please try again ! Some error occured while trying to fetch webinar with id:  %d', 'video-conferencing-with-zoom-api' ), $webinar_id );
				}
			}
		}

		return ob_get_clean();
	}

	/**
	 * Show All Meetings
	 *
	 * @param $atts
	 *
	 * @return string
	 * @since  3.0.0
	 */
	public function show_meetings( $atts ) {
		self::$meetings_list_number ++;
		$atts = shortcode_atts(
			array(
				'per_page' => 5,
				'category' => '',
				'order'    => 'DESC',
				'type'     => ''
			),
			$atts, 'zoom_list_meetings'
		);
		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		$query_args = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => $atts['per_page'],
			'post_status'    => 'publish',
			'paged'          => $paged,
			'orderby'        => 'meta_value',
			'meta_key'       => '_meeting_field_start_date',
			'order'          => $atts['order']
		);

		if ( ! empty( $atts['type'] ) ) {
			$timezone = zvc_get_timezone_offset_wp();
			if ( ! empty( $timezone ) ) {
				date_default_timezone_set( $timezone );
			} else {
				date_default_timezone_set( 'UTC' );
			}
			$type                     = ( $atts['type'] === "upcoming" ) ? '>=' : '<=';
			$query_args['meta_query'] = array(
				array(
					'key'     => '_meeting_field_start_date',
					'value'   => date( "Y-m-d H:i" ),
					'compare' => $type
				),
			);
		}

		if ( ! empty( $atts['category'] ) ) {
			$category                = array_map( 'trim', explode( ',', $atts['category'] ) );
			$query_args['tax_query'] = [
				[
					'taxonomy' => 'zoom-meeting',
					'field'    => 'slug',
					'terms'    => $category,
					'operator' => 'IN'
				]
			];
		}

		$query         = apply_filters( 'vczapi_meeting_list_query_args', $query_args );
		$zoom_meetings = new \WP_Query( $query );
		$content       = '';

		unset( $GLOBALS['zoom_meetings'] );
		$GLOBALS['zoom_meetings'] = $zoom_meetings;
		if ( $zoom_meetings->have_posts() ):
			ob_start();
			vczapi_get_template( 'shortcode-listing.php', true );
			$content .= ob_get_clean();
		else:
			_e( "No meetings found.", "video-conferencing-with-zoom-api" );
		endif;

		return $content;
	}

	/**
	 * Join via browser shortcode
	 *
	 * This shows join via browser using an IFRAME. I personally would not recommend this because this is not so convenient and flawless.
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
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferncing-with-zoom-browser-js' );

		// Allow addon devs to perform action before window rendering
		do_action( 'vczapi_before_shortcode_content' );

		extract( shortcode_atts( array(
			'meeting_id'        => 'javascript:void(0);',
			'title'             => '',
			'id'                => 'zoom_video_uri',
			'login_required'    => "no",
			'help'              => "yes",
			'height'            => "500px",
			'disable_countdown' => 'yes'
		), $atts ) );

		ob_start();
		if ( empty( $meeting_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . __( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . __( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return;
		}

		if ( ! empty( $login_required ) && $login_required === "yes" && ! is_user_logged_in() ) {
			echo '<h3>' . esc_html__( 'Restricted access, please login to continue.', 'video-conferencing-with-zoom-api' ) . '</h3>';

			return;
		}

		$zoom_vanity_url = get_option( 'zoom_vanity_url' );
		$meeting         = $this->fetch_meeting( $meeting_id );
		$zoom_states     = get_option( 'zoom_api_meeting_options' );
		$user_id         = get_current_user_id();
		$visitor_name    = false;
		//Check if user is logged in
		if ( $user_id ) {
			$user_data    = get_userdata( $user_id );
			$visitor_name = ! empty( $user_data->display_name ) ? $user_data->display_name : $user_data->first_name . ' ' . $user_data->last_name;
		}

		//If user is not logged in then send it to post request here
		if ( isset( $_POST['_wpnonce'] ) && isset( $_POST['user_meeting_name_val'] ) ) {
			$retrieved_nonce = $_POST['_wpnonce'];
			if ( wp_verify_nonce( $retrieved_nonce, 'user_meeting_name' ) ) {
				$visitor_name = sanitize_text_field( filter_input( INPUT_POST, 'user_meeting_name_val' ) );
			}
		}

		if ( empty( $zoom_vanity_url ) ) {
			$browser_url     = 'https://zoom.us/wc/' . $meeting_id;
			$mobile_zoom_url = 'https://zoom.us/j/' . $meeting_id;
		} else {
			$browser_url     = trailingslashit( $zoom_vanity_url . '/wc/join/' ) . $meeting_id;
			$mobile_zoom_url = trailingslashit( $zoom_vanity_url . '/j' ) . $meeting_id;
		}

		if ( ! empty( $visitor_name ) ) {
			$zoom_display_name = base64_encode( $visitor_name );
		} else {
			$zoom_display_name = 'visitorVVXSd23==';
		}

		$browser_url   .= '/join' . "?prefer=1&un=" . esc_attr( $zoom_display_name );
		$zoom_host_url = 'https://zoom.us/wc/' . $meeting_id . '/start';
		$zoom_host_url = apply_filters( 'video_conferencing_zoom_join_url_host', $zoom_host_url );

		$browser_url = apply_filters( 'video_conferencing_zoom_join_url', $browser_url );

		if ( ! empty( $meeting ) && ! empty( $meeting->code ) ) {
			echo $meeting->message;
		} else {
			if ( ! empty( $meeting ) ) {
				$meeting_time = date( 'Y-m-d h:i a', strtotime( $meeting->start_time ) );
				try {
					$meeting_timezone_time = vczapi_dateConverter( 'now', $meeting->timezone );
					$meeting_time_check    = vczapi_dateConverter( $meeting_time, $meeting->timezone );

					if ( ! empty( $title ) ) {
						?>
                        <h1><?php esc_html_e( $title ); ?></h1>
						<?php
					}

					if ( ! empty( $help ) && $help === "yes" ) {
						$app_store_link = vczapi_get_browser_agent_type();
						if ( ! isset( $zoom_states[ $meeting_id ]['state'] ) ) {
							?>
                            <div class="zoom-app-notice">
                                <p><?php echo esc_html__( 'Note: If you are having trouble joining the meeting below, enter Meeting ID: ', 'video-conferencing-with-zoom-api' ) . '<strong>' . esc_html( $meeting_id ) . '</strong> ' . esc_html__( 'and join via Zoom App.', 'video-conferencing-with-zoom-api' ); ?></p>
                                <span class="zoom-links">
                            <ul>
                                <li><a href="<?php echo esc_url( $mobile_zoom_url ); ?>" class="join-link retry-url"><?php _e( 'Join via Zoom App', 'video-conferencing-with-zoom-api' ); ?></a></li>
                                <li><a href="<?php echo esc_url( $app_store_link ); ?>" class="download-link"><?php _e( 'Download App from Store', 'video-conferencing-with-zoom-api' ); ?></a></li>
                                <li><a href="https://zoom.us/client/latest/zoom.apk" class="download-link"><?php _e( 'Download from Zoom', 'video-conferencing-with-zoom-api' ); ?></a></li>
                            </ul>
                        </span>
                            </div>
						<?php }
					}

					if ( isset( $zoom_states[ $meeting_id ]['state'] ) && $zoom_states[ $meeting_id ]['state'] === "ended" ) {
						echo '<h3>' . esc_html__( 'This meeting has been ended by host.', 'video-conferencing-with-zoom-api ' ) . '</h3>';
					} elseif ( $meeting_time_check > $meeting_timezone_time && ! empty( $disable_countdown ) && $disable_countdown === "no" ) {
						?>
                        <div class="dpn-zvc-timer zoom-join-via-browser-countdown" id="dpn-zvc-timer" data-date="<?php echo $meeting_time; ?>" data-tz="<?php echo $meeting->timezone; ?>">
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
						<?php
					} else {
						if ( ! $visitor_name ):
							?>
                            <form method="post" class="zoom-meeting-step1">
                                <h4><?php _e( 'Enter your name to join the meeting' ) ?></h4>
                                <input class="join-meeting-field" type="text" value="<?php esc_attr_e( $visitor_name ) ?>" name="user_meeting_name_val" placeholder="<?php _e( 'Your name', 'video-conferencing-with-zoom-api' ) ?>"/><br/>
								<?php wp_nonce_field( 'user_meeting_name' ); ?>
                                <input class="join-meeting-btn" type="submit" name="join_meeting" value="<?php _e( 'Join Meeting', 'video-conferencing-with-zoom-api' ) ?>"/>
                            </form>
						<?php else: ?>
                            <div class="zoom-window-wrap">
								<?php
								if ( ! current_user_can( 'administrator' ) ) {
									?>
                                    <a class="button incompatiblity-notice-btn" target="_blank" href="<?php echo esc_html( 'https://zoom.us/wc/' . $meeting_id . '/join' ); ?>" class="join-link"><?php esc_html_e( 'JOIN MEETING VIA ALTERNATIVE WAY', 'video-conferencing-with-zoom-api' ); ?></a>
									<?php
								}

								$host_users = video_conferencing_zoom_api_get_user_transients();
								foreach ( $host_users as $host ) {
									if ( isset( $user_data->user_email ) && $user_data->user_email == $host->email ) {
										?>
                                        <h3><?php _e( 'If the meeting is not started yet you have to click the below button as a HOST to start the meeting, once started you can join from the window below.', 'video-conferencing-with-zoom-api' ); ?></h3>
                                        <a class="button start-meeting-btn" target="_blank" href="<?php echo esc_url( $zoom_host_url ); ?>" class="join-link"><?php _e( 'START MEETING AS HOST', 'video-conferencing-with-zoom-api' ); ?></a>
										<?php
									}
								}

								if ( current_user_can( 'administrator' ) ) {
									if ( ! is_ssl() ) {
										?>
                                        <h4 class="ssl-alert">
                                            <strong style="color:red;"><?php _e( 'ALERT: ', 'video-conferencing-with-zoom-api' ); ?></strong><?php _e( 'Audio and Video for Zoom meeting will not work on a non HTTPS site, please install a valid SSL certificate on your site to allow participants use audio and video during Zoom meeting: ', 'video-conferencing-with-zoom-api' ); ?>
                                        </h4>
										<?php
									}
								}

								$styling = ! empty( $height ) ? "height: " . $height : "height: 500px;";
								?>
                                <div id="<?php echo ! empty( $id ) ? esc_html( $id ) : 'video-conferncing-embed-iframe'; ?>" class="zoom-iframe-container">
                                    <iframe scrolling="no" style="width:100%; <?php echo $styling; ?>" sandbox="allow-forms allow-scripts allow-same-origin" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen" allow="encrypted-media; autoplay; microphone; camera" src="<?php echo esc_url( $browser_url ); ?>" frameborder="0"></iframe>
                                </div>
                            </div>
						<?php
						endif;
					}
				} catch ( Exception $e ) {
					error_log( $e->getMessage() );
				}
			}
		}


		$content .= ob_get_clean();

		// Allow addon devs to perform filter before window rendering
		$content = apply_filters( 'vczapi_after_shortcode_content', $content );

		return $content;
	}

	/**
	 * Pagination
	 *
	 * @param $query
	 */
	public static function pagination( $query ) {
		$big = 999999999999999;
		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, $paged ),
			'total'   => $query->max_num_pages
		) );
	}

	/**
	 * Output only singel link
	 *
	 * @since  3.0.4
	 * @author Deepen
	 */
	public function generate_link_only() {
		//Get Template
		vczapi_get_template( 'shortcode/zoom-single-link.php', true, false );
	}

	/**
	 * Get Meeting INFO
	 *
	 * @param $meeting_id
	 *
	 * @return bool|mixed|null
	 */
	private function fetch_meeting( $meeting_id ) {
		$meeting = json_decode( zoom_conference()->getMeetingInfo( $meeting_id ) );
		if ( ! empty( $meeting->error ) ) {
			return false;
		}

		return $meeting;
	}

	/**
	 * Get a webinar detail
	 *
	 * @param $webinar_id
	 *
	 * @return bool|mixed|null
	 */
	private function fetch_webinar( $webinar_id ) {
		$webinar = json_decode( zoom_conference()->getWebinarInfo( $webinar_id ) );

		return $webinar;
	}
}

new Zoom_Video_Conferencing_Shorcodes();