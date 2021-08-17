<?php
/**
 * @author     Deepen.
 * @created_on 11/20/19
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to check if a user is logged in or not
 *
 * @author Deepen
 * @since  3.0.0
 */
function video_conference_zoom_check_login() {
	global $zoom;
	if ( ! empty( $zoom ) && ! empty( $zoom['site_option_logged_in'] ) ) {
		if ( is_user_logged_in() ) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

/**
 * Function to view featured image on the post
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_featured_image' ) ) {
	function video_conference_zoom_featured_image() {
		vczapi_get_template( 'fragments/image.php', true );
	}
}

/**
 * Function to view main content i.e title and main content
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_main_content' ) ) {
	function video_conference_zoom_main_content() {
		vczapi_get_template( 'fragments/content.php', true );
	}
}


/**
 * Function to add in the counter
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_countdown_timer' ) ) {
	function video_conference_zoom_countdown_timer() {
		vczapi_get_template( 'fragments/countdown-timer.php', true );
	}
}

/**
 * Function to show meeting details
 *
 * @author Deepen
 * @since  3.0.0
 */
if ( ! function_exists( 'video_conference_zoom_meeting_details' ) ) {
	function video_conference_zoom_meeting_details() {
		vczapi_get_template( 'fragments/meeting-details.php', true );
	}
}

/**
 * Control State of the meeting by author from frontend
 */
function video_conference_zoom_meeting_end_author() {
	global $post;
	$meeting = get_post_meta( $post->ID, '_meeting_zoom_details', true );
	$author  = vczapi_check_author( $post->ID );
	if ( ! $author ) {
		return;
	}

	$data = array(
		'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		'zvc_security' => wp_create_nonce( "_nonce_zvc_security" ),
		'lang'         => array(
			'confirm_end' => __( "Are you sure you want to end this meeting ? Users won't be able to join this meeting shown from the shortcode.", "video-conferencing-with-zoom-api" )
		)
	);
	wp_localize_script( 'video-conferencing-with-zoom-api', 'vczapi_state', $data );
	?>
    <div class="dpn-zvc-sidebar-state">
		<?php if ( empty( $meeting->state ) ) { ?>
            <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="post_type" data-state="end" data-postid="<?php echo $post->ID; ?>" data-id="<?php echo $meeting->id ?>"><?php _e( 'End Meeting ?', 'video-conferencing-with-zoom-api' ); ?></a>
		<?php } else { ?>
            <a href="javascript:void(0);" class="vczapi-meeting-state-change" data-type="post_type" data-state="resume" data-postid="<?php echo $post->ID; ?>" data-id="<?php echo $meeting->id ?>"><?php _e( 'Enable Meeting Join ?', 'video-conferencing-with-zoom-api' ); ?></a>
		<?php } ?>
        <p><?php _e( 'You are seeing this because you are the author of this post.', 'video-conferencing-with-zoom-api' ); ?></p>
    </div>
	<?php
}

/**
 * Function to show meeting join links
 *
 * @author Deepen
 * @since  3.0.0
 */
function video_conference_zoom_meeting_join() {
	global $zoom;

	if ( ! vczapi_pro_version_active() && ( $zoom['api']->type === 8 || $zoom['api']->type === 3 ) || empty( $zoom ) ) {
		return;
	}

	if ( empty( $zoom['api']->state ) && video_conference_zoom_check_login() ) {
		$post_id            = get_the_id();
		$meeting_start_date = get_post_meta( $post_id, '_meeting_field_start_date_utc', true );
		$data               = array(
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'start_date' => $meeting_start_date,
			'timezone'   => $zoom['timezone'],
			'post_id'    => $post_id,
			'page'       => 'single-meeting'
		);
		$data               = apply_filters( 'vczapi_single_meeting_localized_data', $data );
		wp_localize_script( 'video-conferencing-with-zoom-api', 'mtg_data', $data );
	} else {
		echo "<p>" . __( 'Please login to join this meeting.', 'video-conferencing-with-zoom-api' ) . "</p>";
	}
}

/**
 * Generate join links
 *
 * @param $zoom_meeting
 *
 * @since  3.0.0
 *
 * @author Deepen
 */
function video_conference_zoom_meeting_join_link( $zoom_meeting ) {
	$disable_app_join = apply_filters( 'vczoom_join_meeting_via_app_disable', false );
	if ( ! empty( $zoom_meeting->join_url ) && ! $disable_app_join ) {
		$join_url = ! empty( $zoom_meeting->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $zoom_meeting->join_url, $zoom_meeting->encrypted_password ) : $zoom_meeting->join_url;
		?>
        <a target="_blank" href="<?php echo esc_url( $join_url ); ?>" class="btn btn-join-link btn-join-via-app"><?php echo apply_filters( 'vczapi_join_meeting_via_app_text', __( 'Join Meeting via Zoom App', 'video-conferencing-with-zoom-api' ) ); ?></a>
		<?php
	}

	if ( wp_doing_ajax() ) {
		$post_id         = filter_input( INPUT_POST, 'post_id' );
		$meeting_details = get_post_meta( $post_id, '_meeting_fields', true );
		if ( ! empty( $zoom_meeting->id ) && ! empty( $post_id ) && empty( $meeting_details['site_option_browser_join'] ) && ! vczapi_check_disable_joinViaBrowser() ) {
			$meeting_id = ! empty( $zoom_meeting->pmi ) ? $zoom_meeting->pmi : $zoom_meeting->id;
			if ( ! empty( $zoom_meeting->password ) ) {
				echo vczapi_get_browser_join_links( $post_id, $meeting_id, $zoom_meeting->password );
			} else {
				echo vczapi_get_browser_join_links( $post_id, $meeting_id );
			}
		}
	}
}

/**
 * Generate join links for webinar
 *
 * @param $zoom_webinars
 *
 * @throws Exception
 * @since  3.4.0
 *
 * @author Deepen
 */
function video_conference_zoom_shortcode_join_link_webinar( $zoom_webinars ) {
	if ( empty( $zoom_webinars ) ) {
		echo "<p>" . __( 'Webinar is not defined. Try updating this Webinar', 'video-conferencing-with-zoom-api' ) . "</p>";

		return;
	}

	$now               = new DateTime( 'now -1 hour', new DateTimeZone( $zoom_webinars->timezone ) );
	$closest_occurence = false;
	if ( ! empty( $zoom_webinars->type ) && $zoom_webinars->type === 9 && ! empty( $zoom_webinars->occurrences ) ) {
		foreach ( $zoom_webinars->occurrences as $occurrence ) {
			if ( $occurrence->status === "available" ) {
				$start_date = new DateTime( $occurrence->start_time, new DateTimeZone( $zoom_webinars->timezone ) );
				if ( $start_date >= $now ) {
					$closest_occurence = $occurrence->start_time;
					break;
				}
			}
		}
	} else if ( empty( $zoom_webinars->occurrences ) ) {
		$zoom_webinars->start_time = false;
	} else if ( ! empty( $zoom_webinars->type ) && $zoom_webinars->type === 6 ) {
		$zoom_webinars->start_time = false;
	}

	$start_time = ! empty( $closest_occurence ) ? $closest_occurence : $zoom_webinars->start_time;
	$start_time = new DateTime( $start_time, new DateTimeZone( $zoom_webinars->timezone ) );
	$start_time->setTimezone( new DateTimeZone( $zoom_webinars->timezone ) );
	if ( $now <= $start_time ) {
		unset( $GLOBALS['webinars'] );

		if ( ! empty( $zoom_webinars->password ) ) {
			$browser_join = vczapi_get_browser_join_shortcode( $zoom_webinars->id, $zoom_webinars->password, true );
		} else {
			$browser_join = vczapi_get_browser_join_shortcode( $zoom_webinars->id, false, true );
		}

		$join_url            = ! empty( $zoom_webinars->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $zoom_webinars->join_url, $zoom_webinars->encrypted_password ) : $zoom_webinars->join_url;
		$GLOBALS['webinars'] = array(
			'join_uri'    => apply_filters( 'vczoom_join_webinar_via_app_shortcode', $join_url, $zoom_webinars ),
			'browser_url' => ! vczapi_check_disable_joinViaBrowser() ? apply_filters( 'vczoom_join_webinar_via_browser_disable', $browser_join ) : false
		);
		vczapi_get_template( 'shortcode/webinar-join-links.php', true, false );
	}
}

/**
 * Generate join links
 *
 * @param $zoom_meetings
 *
 * @throws Exception
 * @since  3.0.0
 *
 * @author Deepen
 */
function video_conference_zoom_shortcode_join_link( $zoom_meetings ) {
	if ( empty( $zoom_meetings ) ) {
		echo "<p>" . __( 'Meeting is not defined. Try updating this meeting', 'video-conferencing-with-zoom-api' ) . "</p>";

		return;
	}

	if ( empty( $zoom_meetings->timezone ) ) {
		$zoom_meetings->timezone = zvc_get_timezone_offset_wp();
	}

	$now               = new DateTime( 'now -1 hour', new DateTimeZone( $zoom_meetings->timezone ) );
	$closest_occurence = false;
	if ( ! empty( $zoom_meetings->type ) && $zoom_meetings->type === 8 && ! empty( $zoom_meetings->occurrences ) ) {
		foreach ( $zoom_meetings->occurrences as $occurrence ) {
			if ( $occurrence->status === "available" ) {
				$start_date = new DateTime( $occurrence->start_time, new DateTimeZone( $zoom_meetings->timezone ) );
				if ( $start_date >= $now ) {
					$closest_occurence = $occurrence->start_time;
					break;
				}
			}
		}
	} else if ( empty( $zoom_meetings->occurrences ) ) {
		$zoom_meetings->start_time = false;
	} else if ( ! empty( $zoom_meetings->type ) && $zoom_meetings->type === 3 ) {
		$zoom_meetings->start_time = false;
	} else if ( ! empty( $zoom_meetings->type ) && $zoom_meetings->type === 4 ) {
		$zoom_meetings->start_time = false;
	}

	$start_time = ! empty( $closest_occurence ) ? $closest_occurence : $zoom_meetings->start_time;
	$start_time = new DateTime( $start_time, new DateTimeZone( $zoom_meetings->timezone ) );
	$start_time->setTimezone( new DateTimeZone( $zoom_meetings->timezone ) );
	if ( $now <= $start_time ) {
		unset( $GLOBALS['meetings'] );

		if ( ! empty( $zoom_meetings->password ) ) {
			$browser_join = vczapi_get_browser_join_shortcode( $zoom_meetings->id, $zoom_meetings->password, true );
		} else {
			$browser_join = vczapi_get_browser_join_shortcode( $zoom_meetings->id, false, true );
		}

		$join_url            = ! empty( $zoom_meetings->encrypted_password ) ? vczapi_get_pwd_embedded_join_link( $zoom_meetings->join_url, $zoom_meetings->encrypted_password ) : $zoom_meetings->join_url;
		$GLOBALS['meetings'] = array(
			'join_uri'    => apply_filters( 'vczoom_join_meeting_via_app_shortcode', $join_url, $zoom_meetings ),
			'browser_url' => ! vczapi_check_disable_joinViaBrowser() ? apply_filters( 'vczoom_join_meeting_via_browser_disable', $browser_join ) : false
		);
		vczapi_get_template( 'shortcode/join-links.php', true, false );
	}
}

if ( ! function_exists( 'video_conference_zoom_shortcode_table' ) ) {
	/**
	 *  * Render Zoom Meeting ShortCode table in frontend
	 *
	 * @param $zoom_meetings
	 *
	 * @throws Exception
	 * @since  3.0.0
	 *
	 * @author Deepen
	 */
	function video_conference_zoom_shortcode_table( $zoom_meetings ) {
		$hide_join_link_nloggedusers = get_option( 'zoom_api_hide_shortcode_join_links' );
		?>
        <table class="vczapi-shortcode-meeting-table">
            <tr class="vczapi-shortcode-meeting-table--row1">
                <td><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php echo $zoom_meetings->id; ?></td>
            </tr>
            <tr class="vczapi-shortcode-meeting-table--row2">
                <td><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></td>
                <td><?php echo $zoom_meetings->topic; ?></td>
            </tr>
            <tr class="vczapi-shortcode-meeting-table--row3">
                <td><?php _e( 'Meeting Status', 'video-conferencing-with-zoom-api' ); ?></td>
                <td>
					<?php
					if ( $zoom_meetings->status === "waiting" ) {
						_e( 'Waiting - Not started', 'video-conferencing-with-zoom-api' );
					} else if ( $zoom_meetings->status === "started" ) {
						_e( 'Meeting is in Progress', 'video-conferencing-with-zoom-api' );
					} else {
						echo $zoom_meetings->status;
					}
					?>
                    <p class="small-description"><?php _e( 'Refresh is needed to change status.', 'video-conferencing-with-zoom-api' ); ?></p>
                </td>
            </tr>
			<?php
			if ( ! empty( $zoom_meetings->type ) && $zoom_meetings->type === 8 ) {
				if ( ! empty( $zoom_meetings->occurrences ) ) {
					?>
                    <tr class="vczapi-shortcode-meeting-table--row4">
                        <td><?php _e( 'Type', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td><?php _e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' ); ?></td>
                    </tr>
                    <tr class="vczapi-shortcode-meeting-table--row4">
                        <td><?php _e( 'Ocurrences', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td><?php echo count( $zoom_meetings->occurrences ); ?></td>
                    </tr>
                    <tr class="vczapi-shortcode-meeting-table--row5">
                        <td><?php _e( 'Next Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td>
							<?php
							$now               = new DateTime( 'now -1 hour', new DateTimeZone( $zoom_meetings->timezone ) );
							$closest_occurence = false;
							if ( ! empty( $zoom_meetings->type ) && $zoom_meetings->type === 8 && ! empty( $zoom_meetings->occurrences ) ) {
								foreach ( $zoom_meetings->occurrences as $occurrence ) {
									if ( $occurrence->status === "available" ) {
										$start_date = new DateTime( $occurrence->start_time, new DateTimeZone( $zoom_meetings->timezone ) );
										if ( $start_date >= $now ) {
											$closest_occurence = $occurrence->start_time;
											break;
										}

										_e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' );
										break;
									}
								}
							}

							if ( $closest_occurence ) {
								echo vczapi_dateConverter( $closest_occurence, $zoom_meetings->timezone, 'F j, Y @ g:i a' );
							} else {
								_e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' );
							}
							?>
                        </td>
                    </tr>
					<?php
				} else {
					?>
                    <tr class="vczapi-shortcode-meeting-table--row6">
                        <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                        <td><?php _e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' ); ?></td>
                    </tr>
					<?php
				}
			} else if ( ! empty( $zoom_meetings->type ) && $zoom_meetings->type === 3 ) {
				?>
                <tr class="vczapi-shortcode-meeting-table--row6">
                    <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php _e( 'This is a meeting with no Fixed Time.', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
				<?php
			} else if ( ! empty( $zoom_meetings->type ) && $zoom_meetings->type === 4 ) {
				?>
                <tr class="vczapi-shortcode-meeting-table--row6">
                    <td><?php _e( 'Type', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php _e( 'Personal Meeting Room', 'video-conferencing-with-zoom-api' ); ?></td>
                </tr>
				<?php
			} else if ( ! empty( $zoom_meetings->start_time ) ) {
				?>
                <tr class="vczapi-shortcode-meeting-table--row6">
                    <td><?php _e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo vczapi_dateConverter( $zoom_meetings->start_time, $zoom_meetings->timezone, 'F j, Y @ g:i a' ); ?></td>
                </tr>
			<?php } ?>
			<?php if ( ! empty( $zoom_meetings->timezone ) ) { ?>
                <tr class="vczapi-shortcode-meeting-table--row7">
                    <td><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo $zoom_meetings->timezone; ?></td>
                </tr>
			<?php } ?>
			<?php if ( ! empty( $zoom_meetings->duration ) ) { ?>
                <tr class="zvc-table-shortcode-duration">
                    <td><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></td>
                    <td><?php echo $zoom_meetings->duration; ?></td>
                </tr>
				<?php
			}

			do_action( 'vczoom_meeting_shortcode_additional_fields', $zoom_meetings );

			if ( ! empty( $hide_join_link_nloggedusers ) ) {
				if ( is_user_logged_in() ) {
					$show_join_links = true;
				} else {
					$show_join_links = false;
				}
			} else {
				$show_join_links = true;
			}

			if ( $show_join_links ) {
				/**
				 * Hook: vczoom_meeting_shortcode_join_links
				 *
				 * @video_conference_zoom_shortcode_join_link - 10
				 *
				 */
				do_action( 'vczoom_meeting_shortcode_join_links', $zoom_meetings );
			}
			?>
        </table>
		<?php
	}
}

if ( ! function_exists( 'video_conference_zoom_output_content_start' ) ) {
	function video_conference_zoom_output_content_start() {
		vczapi_get_template( 'global/wrap-start.php', true );
	}
}

if ( ! function_exists( 'video_conference_zoom_output_content_end' ) ) {
	function video_conference_zoom_output_content_end() {
		vczapi_get_template( 'global/wrap-end.php', true );
	}
}

/**
 * Get a slug identifying the current theme.
 *
 * @return string
 * @since 3.0.2
 */
function video_conference_zoom_get_current_theme_slug() {
	return apply_filters( 'video_conference_zoom_theme_slug_for_templates', get_option( 'template' ) );
}

/**
 * REMOVE WHITESPACES
 *
 * @param $buffer
 *
 * @return string|string[]|null
 */
function vczapi_removeWhitespace( $buffer ) {
	return preg_replace( '/\s+/', ' ', $buffer );
}

/**
 * Before join before host
 *
 * @param $zoom
 */
function video_conference_zoom_before_jbh_html( $zoom ) {
	ob_start( 'vczapi_removeWhitespace' );
	?>
    <!DOCTYPE html><html>
    <head>
        <meta charset="UTF-8">
        <meta name="format-detection" content="telephone=no">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="robots" content="noindex, nofollow">
        <title><?php echo ! empty( $zoom['api']->topic ) ? $zoom['api']->topic : 'Join Meeting'; ?></title>
        <link rel='stylesheet' type="text/css" href="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/bootstrap.css?ver=' . ZVC_PLUGIN_VERSION; ?>" media='all'>
        <link rel='stylesheet' type="text/css" href="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/react-select.css?ver=' . ZVC_PLUGIN_VERSION; ?>" media='all'>
        <link rel='stylesheet' type="text/css" href="<?php echo ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/css/style.min.css?ver=' . ZVC_PLUGIN_VERSION; ?>" media='all'>
        <link rel='stylesheet' type="text/css" href="<?php echo get_stylesheet_uri(); ?>" media='all'>
    </head><body class="join-via-browser-body">
	<?php
	ob_end_flush();
}

/**
 * AFter join before host
 */
function video_conference_zoom_after_jbh_html() {
	do_action( 'vczapi_join_via_browser_footer' );

	ob_start( 'vczapi_removeWhitespace' );
	$localize = array(
		'ajaxurl'       => admin_url( 'admin-ajax.php' ),
		'zvc_security'  => wp_create_nonce( "_nonce_zvc_security" ),
		'redirect_page' => apply_filters( 'vczapi_api_redirect_join_browser', esc_url( home_url( '/' ) ) ),
		'meeting_id'    => base64_encode( vczapi_encrypt_decrypt( 'decrypt', $_GET['join'] ) ),
		'meeting_pwd'   => ! empty( $_GET['pak'] ) ? base64_encode( vczapi_encrypt_decrypt( 'decrypt', $_GET['pak'] ) ) : false,
		'disableInvite' => ( get_option( 'vczapi_disable_invite' ) == 'yes' ) ? true : false
	);

	/**
	 * Additional Data
	 */
	$additional_data = apply_filters( 'vczapi_api_join_via_browser_params', array(
		'meetingInfo'       => [
			'topic',
			'host',
			#'mn',
			#'pwd',
			#'telPwd',
			#'invite',
			#'participant',
			#'dc',
			#'enctype',
			#'report'
		],
		'disableRecord'     => false,
		'disableJoinAudio'  => false,
		'isSupportChat'     => true,
		'isSupportQA'       => true,
		'isSupportBreakout' => true,
		'isSupportCC'       => true,
		'screenShare'       => true
	) );
	$localize        = array_merge( $localize, $additional_data );
	?>
    <script id='video-conferencing-with-zoom-api-browser-js-extra'>
        var zvc_ajx = <?php echo wp_json_encode( $localize ); ?>;
    </script>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/jquery.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>

<?php if ( ! defined( 'VCZAPI_STATIC_CDN' ) ) { ?>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/react.production.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/react-dom.production.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/redux.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/redux-thunk.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/lodash.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/zoom/zoom-meeting.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
<?php } else { ?>
    <script src="<?php echo 'https://source.zoom.us/' . ZVC_ZOOM_WEBSDK_VERSION . '/lib/vendor/react.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo 'https://source.zoom.us/' . ZVC_ZOOM_WEBSDK_VERSION . '/lib/vendor/react-dom.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo 'https://source.zoom.us/' . ZVC_ZOOM_WEBSDK_VERSION . '/lib/vendor/redux.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo 'https://source.zoom.us/' . ZVC_ZOOM_WEBSDK_VERSION . '/lib/vendor/redux-thunk.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo 'https://source.zoom.us/' . ZVC_ZOOM_WEBSDK_VERSION . '/lib/vendor/lodash.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo 'https://source.zoom.us/zoom-meeting-' . ZVC_ZOOM_WEBSDK_VERSION . '.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
<?php } ?>
    <script src="<?php echo ZVC_PLUGIN_VENDOR_ASSETS_URL . '/crypto-js/crypto-js.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
    <script src="<?php echo ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/js/zoom-meeting.min.js?ver=' . ZVC_PLUGIN_VERSION; ?>"></script>
<?php do_action( 'vczapi_join_via_browser_after_script_load' ); ?>
    </body>
    </html>
	<?php
	ob_end_flush();
}

/**
 * Before POST LOOP hook
 */
function video_conference_zoom_before_post_loop() {
	global $zoom_meetings;
	unset( $GLOBALS['zoom'] );
	$post_id               = get_the_id();
	$show_zoom_author_name = get_option( 'zoom_show_author' );
	$GLOBALS['zoom']       = get_post_meta( $post_id, '_meeting_fields', true ); //For Backwards Compatibility ( Will be removed someday )
	$meeting_details       = get_post_meta( $post_id, '_meeting_zoom_details', true );
	$meeting_author        = get_the_author();
	if ( ! empty( $show_zoom_author_name ) ) {
		$meeting_author = vczapi_get_meeting_author( $post_id, $meeting_details, $meeting_author );
	}
	$GLOBALS['zoom']['host_name'] = $meeting_author;

	$GLOBALS['zoom']['api'] = $meeting_details;
	$terms                  = get_the_terms( get_the_id(), 'zoom-meeting' );
	if ( ! empty( $terms ) ) {
		$set_terms = array();
		foreach ( $terms as $term ) {
			$set_terms[] = $term->name;
		}
		$GLOBALS['zoom']['terms'] = $set_terms;
	}

	if ( ! empty( $zoom_meetings ) && ! empty( $zoom_meetings->columns ) ) {
		$columns = 'vczapi-col-4';
		switch ( $zoom_meetings->columns ) {
			case 3:
				$columns = 'vczapi-col-4';
				break;
			case 2:
				$columns = 'vczapi-col-6';
				break;
			case 4:
				$columns = 'vczapi-col-3';
				break;
			case 1:
				$columns = 'vczapi-col-12';
				break;
		}

		$GLOBALS['zoom']['columns'] = $columns;
	}
}