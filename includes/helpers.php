<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dump' ) ) {
	/**
	 * @author Deepen
	 * @since  1.0.0
	 */
	function dump( $var ) {
		echo '<pre>';
		var_dump( $var );
		echo '</pre>';
	}
}

if ( ! function_exists( 'zvc_get_timezone_offset_wp' ) ) {
	function zvc_get_timezone_offset_wp() {
		$offset  = get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = abs( ( $offset - (int) $offset ) * 60 );
		$offset  = sprintf( '%+03d:%02d', $hours, $minutes );

		// Calculate seconds from offset
		list( $hours, $minutes ) = explode( ':', $offset );
		$seconds = $hours * 60 * 60 + $minutes * 60;
		$tz      = timezone_name_from_abbr( '', $seconds, 1 );
		if ( $tz === false ) {
			$tz = timezone_name_from_abbr( '', $seconds, 0 );
		}

		return $tz;
	}
}

if ( ! function_exists( 'zvc_get_timezone_options' ) ) {
	/**
	 * @author Deepen
	 * @since  1.0.0
	 */
	function zvc_get_timezone_options() {
		$zones_array = array(
			"Pacific/Midway"                 => "(GMT-11:00) Midway Island, Samoa ",
			"Pacific/Pago_Pago"              => "(GMT-11:00) Pago Pago ",
			"Pacific/Honolulu"               => "(GMT-10:00) Hawaii ",
			"America/Anchorage"              => "(GMT-8:00) Alaska ",
			"America/Vancouver"              => "(GMT-7:00) Vancouver ",
			"America/Los_Angeles"            => "(GMT-7:00) Pacific Time (US and Canada) ",
			"America/Tijuana"                => "(GMT-7:00) Tijuana ",
			"America/Phoenix"                => "(GMT-7:00) Arizona ",
			"America/Edmonton"               => "(GMT-6:00) Edmonton ",
			"America/Denver"                 => "(GMT-6:00) Mountain Time (US and Canada) ",
			"America/Mazatlan"               => "(GMT-6:00) Mazatlan ",
			"America/Regina"                 => "(GMT-6:00) Saskatchewan ",
			"America/Guatemala"              => "(GMT-6:00) Guatemala ",
			"America/El_Salvador"            => "(GMT-6:00) El Salvador ",
			"America/Managua"                => "(GMT-6:00) Managua ",
			"America/Costa_Rica"             => "(GMT-6:00) Costa Rica ",
			"America/Tegucigalpa"            => "(GMT-6:00) Tegucigalpa ",
			"America/Winnipeg"               => "(GMT-5:00) Winnipeg ",
			"America/Chicago"                => "(GMT-5:00) Central Time (US and Canada) ",
			"America/Mexico_City"            => "(GMT-5:00) Mexico City ",
			"America/Panama"                 => "(GMT-5:00) Panama ",
			"America/Bogota"                 => "(GMT-5:00) Bogota ",
			"America/Lima"                   => "(GMT-5:00) Lima ",
			"America/Caracas"                => "(GMT-4:30) Caracas ",
			"America/Montreal"               => "(GMT-4:00) Montreal ",
			"America/New_York"               => "(GMT-4:00) Eastern Time (US and Canada) ",
			"America/Indianapolis"           => "(GMT-4:00) Indiana (East) ",
			"America/Puerto_Rico"            => "(GMT-4:00) Puerto Rico ",
			"America/Santiago"               => "(GMT-4:00) Santiago ",
			"America/Halifax"                => "(GMT-3:00) Halifax ",
			"America/Montevideo"             => "(GMT-3:00) Montevideo ",
			"America/Araguaina"              => "(GMT-3:00) Brasilia ",
			"America/Argentina/Buenos_Aires" => "(GMT-3:00) Buenos Aires, Georgetown ",
			"America/Sao_Paulo"              => "(GMT-3:00) Sao Paulo ",
			"Canada/Atlantic"                => "(GMT-3:00) Atlantic Time (Canada) ",
			"America/St_Johns"               => "(GMT-2:30) Newfoundland and Labrador ",
			"America/Godthab"                => "(GMT-2:00) Greenland ",
			"Atlantic/Cape_Verde"            => "(GMT-1:00) Cape Verde Islands ",
			"Atlantic/Azores"                => "(GMT+0:00) Azores ",
			"UTC"                            => "(GMT+0:00) Universal Time UTC ",
			"Etc/Greenwich"                  => "(GMT+0:00) Greenwich Mean Time ",
			"Atlantic/Reykjavik"             => "(GMT+0:00) Reykjavik ",
			"Africa/Nouakchott"              => "(GMT+0:00) Nouakchott ",
			"Europe/Dublin"                  => "(GMT+1:00) Dublin ",
			"Europe/London"                  => "(GMT+1:00) London ",
			"Europe/Lisbon"                  => "(GMT+1:00) Lisbon ",
			"Africa/Casablanca"              => "(GMT+1:00) Casablanca ",
			"Africa/Bangui"                  => "(GMT+1:00) West Central Africa ",
			"Africa/Algiers"                 => "(GMT+1:00) Algiers ",
			"Africa/Tunis"                   => "(GMT+1:00) Tunis ",
			"Europe/Belgrade"                => "(GMT+2:00) Belgrade, Bratislava, Ljubljana ",
			"CET"                            => "(GMT+2:00) Sarajevo, Skopje, Zagreb ",
			"Europe/Oslo"                    => "(GMT+2:00) Oslo ",
			"Europe/Copenhagen"              => "(GMT+2:00) Copenhagen ",
			"Europe/Brussels"                => "(GMT+2:00) Brussels ",
			"Europe/Berlin"                  => "(GMT+2:00) Amsterdam, Berlin, Rome, Stockholm, Vienna ",
			"Europe/Amsterdam"               => "(GMT+2:00) Amsterdam ",
			"Europe/Rome"                    => "(GMT+2:00) Rome ",
			"Europe/Stockholm"               => "(GMT+2:00) Stockholm ",
			"Europe/Vienna"                  => "(GMT+2:00) Vienna ",
			"Europe/Luxembourg"              => "(GMT+2:00) Luxembourg ",
			"Europe/Paris"                   => "(GMT+2:00) Paris ",
			"Europe/Zurich"                  => "(GMT+2:00) Zurich ",
			"Europe/Madrid"                  => "(GMT+2:00) Madrid ",
			"Africa/Harare"                  => "(GMT+2:00) Harare, Pretoria ",
			"Europe/Warsaw"                  => "(GMT+2:00) Warsaw ",
			"Europe/Prague"                  => "(GMT+2:00) Prague Bratislava ",
			"Europe/Budapest"                => "(GMT+2:00) Budapest ",
			"Africa/Tripoli"                 => "(GMT+2:00) Tripoli ",
			"Africa/Cairo"                   => "(GMT+2:00) Cairo ",
			"Africa/Johannesburg"            => "(GMT+2:00) Johannesburg ",
			"Europe/Helsinki"                => "(GMT+3:00) Helsinki ",
			"Africa/Nairobi"                 => "(GMT+3:00) Nairobi ",
			"Europe/Sofia"                   => "(GMT+3:00) Sofia ",
			"Europe/Istanbul"                => "(GMT+3:00) Istanbul ",
			"Europe/Athens"                  => "(GMT+3:00) Athens ",
			"Europe/Bucharest"               => "(GMT+3:00) Bucharest ",
			"Asia/Nicosia"                   => "(GMT+3:00) Nicosia ",
			"Asia/Beirut"                    => "(GMT+3:00) Beirut ",
			"Asia/Damascus"                  => "(GMT+3:00) Damascus ",
			"Asia/Jerusalem"                 => "(GMT+3:00) Jerusalem ",
			"Asia/Amman"                     => "(GMT+3:00) Amman ",
			"Europe/Moscow"                  => "(GMT+3:00) Moscow ",
			"Asia/Baghdad"                   => "(GMT+3:00) Baghdad ",
			"Asia/Kuwait"                    => "(GMT+3:00) Kuwait ",
			"Asia/Riyadh"                    => "(GMT+3:00) Riyadh ",
			"Asia/Bahrain"                   => "(GMT+3:00) Bahrain ",
			"Asia/Qatar"                     => "(GMT+3:00) Qatar ",
			"Asia/Aden"                      => "(GMT+3:00) Aden ",
			"Africa/Khartoum"                => "(GMT+3:00) Khartoum ",
			"Africa/Djibouti"                => "(GMT+3:00) Djibouti ",
			"Africa/Mogadishu"               => "(GMT+3:00) Mogadishu ",
			"Europe/Kiev"                    => "(GMT+3:00) Kiev ",
			"Asia/Dubai"                     => "(GMT+4:00) Dubai ",
			"Asia/Muscat"                    => "(GMT+4:00) Muscat ",
			"Asia/Tehran"                    => "(GMT+4:30) Tehran ",
			"Asia/Kabul"                     => "(GMT+4:30) Kabul ",
			"Asia/Baku"                      => "(GMT+5:00) Baku, Tbilisi, Yerevan ",
			"Asia/Yekaterinburg"             => "(GMT+5:00) Yekaterinburg ",
			"Asia/Tashkent"                  => "(GMT+5:00) Islamabad, Karachi, Tashkent ",
			"Asia/Calcutta"                  => "(GMT+5:30) India ",
			"Asia/Kolkata"                   => "(GMT+5:30) Mumbai, Kolkata, New Delhi ",
			"Asia/Kathmandu"                 => "(GMT+5:45) Kathmandu ",
			"Asia/Novosibirsk"               => "(GMT+6:00) Novosibirsk ",
			"Asia/Almaty"                    => "(GMT+6:00) Almaty ",
			"Asia/Dacca"                     => "(GMT+6:00) Dacca ",
			"Asia/Dhaka"                     => "(GMT+6:00) Astana, Dhaka ",
			"Asia/Krasnoyarsk"               => "(GMT+7:00) Krasnoyarsk ",
			"Asia/Bangkok"                   => "(GMT+7:00) Bangkok ",
			"Asia/Saigon"                    => "(GMT+7:00) Vietnam ",
			"Asia/Jakarta"                   => "(GMT+7:00) Jakarta ",
			"Asia/Irkutsk"                   => "(GMT+8:00) Irkutsk, Ulaanbaatar ",
			"Asia/Shanghai"                  => "(GMT+8:00) Beijing, Shanghai ",
			"Asia/Hong_Kong"                 => "(GMT+8:00) Hong Kong ",
			"Asia/Taipei"                    => "(GMT+8:00) Taipei ",
			"Asia/Kuala_Lumpur"              => "(GMT+8:00) Kuala Lumpur ",
			"Asia/Singapore"                 => "(GMT+8:00) Singapore ",
			"Australia/Perth"                => "(GMT+8:00) Perth ",
			"Asia/Yakutsk"                   => "(GMT+9:00) Yakutsk ",
			"Asia/Seoul"                     => "(GMT+9:00) Seoul ",
			"Asia/Tokyo"                     => "(GMT+9:00) Osaka, Sapporo, Tokyo ",
			"Australia/Darwin"               => "(GMT+9:30) Darwin ",
			"Australia/Adelaide"             => "(GMT+9:30) Adelaide ",
			"Asia/Vladivostok"               => "(GMT+10:00) Vladivostok ",
			"Pacific/Port_Moresby"           => "(GMT+10:00) Guam, Port Moresby ",
			"Australia/Brisbane"             => "(GMT+10:00) Brisbane ",
			"Australia/Sydney"               => "(GMT+10:00) Canberra, Melbourne, Sydney ",
			"Australia/Hobart"               => "(GMT+10:00) Hobart ",
			"Asia/Magadan"                   => "(GMT+10:00) Magadan ",
			"SST"                            => "(GMT+11:00) Solomon Islands ",
			"Pacific/Noumea"                 => "(GMT+11:00) New Caledonia ",
			"Asia/Kamchatka"                 => "(GMT+12:00) Kamchatka ",
			"Pacific/Fiji"                   => "(GMT+12:00) Fiji Islands, Marshall Islands ",
			"Pacific/Auckland"               => "(GMT+12:00) Auckland, Wellington"
		);

		return apply_filters( 'vczapi_timezone_list', $zones_array );
	}
}

/**
 * Get Users using transients
 *
 * @since  2.1.0
 * @author Deepen
 */
function video_conferencing_zoom_api_get_user_transients() {
	if ( isset( $_GET['page'] ) && $_GET['page'] === "zoom-video-conferencing-list-users" && isset( $_GET['pg'] ) ) {
		$page          = $_GET['pg'];
		$encoded_users = zoom_conference()->listUsers( $page );
		$decoded_users = json_decode( $encoded_users );
		if ( ! empty( $decoded_users->code ) ) {
			$users = false;
		} else {
			$users = $decoded_users->users;
		}
	} else {
		$check_user_cache_expiry = get_option( '_zvc_user_lists_expiry_time' );
		if ( time() > $check_user_cache_expiry ) {
			update_option( '_zvc_user_lists', '' );
		}

		//Check if any transient by name is available
		$check_transient = get_option( '_zvc_user_lists' );
		if ( $check_transient ) {
			$users = $check_transient->users;
		} else {
			$encoded_users = zoom_conference()->listUsers();
			$decoded_users = json_decode( $encoded_users );
			if ( ! empty( $decoded_users->code ) ) {
				$users = false;
			} else {
				$users = $decoded_users->users;
				update_option( '_zvc_user_lists', $decoded_users );
				update_option( '_zvc_user_lists_expiry_time', time() + 108000 );
			}
		}
	}

	return apply_filters( 'vczapi_users_list', $users );
}

/**
 * Flushing the cache
 */
function video_conferencing_zoom_api_delete_user_cache() {
	update_option( '_zvc_user_lists', '' );
	update_option( '_zvc_user_lists_expiry_time', '' );
}

/**
 * Pagination next for Zoom API
 *
 * @param        $type
 * @param string $page_type
 *
 * @return string
 */
function video_conferencing_zoom_api_pagination_next( $type, $page_type = 'zoom-video-conferencing-list-users' ) {
	if ( ! empty( $type ) && count( $type ) >= 100 ) {
		if ( isset( $_GET['pg'] ) ) {
			$page = absint( $_GET['pg'] ) + 1;

			return '<strong>Show more records:</strong> <a href="?post_type=zoom-meetings&page=zoom-video-conferencing-list-users&flush=true&pg=' . $page . '">Next Page</a>';
		} else {
			return '<strong>Show more records:</strong> <a href="?post_type=zoom-meetings&page=' . $page_type . '&flush=true&pg=2">Next Page</a>';
		}
	}
}

/**
 * Pagination for prev
 *
 * @param        $type
 * @param string $page_type
 *
 * @return string
 */
function video_conferencing_zoom_api_pagination_prev( $type, $page_type = 'zoom-video-conferencing-list-users' ) {
	if ( isset( $_GET['pg'] ) && $_GET['pg'] != 1 ) {
		$page = absint( $_GET['pg'] ) - 1;

		return '<a href="?post_type=zoom-meetings&page=' . $page_type . '&flush=true&pg=' . $page . '">Previous Page</a>';
	}
}

function video_conferencing_zoom_api_status() {
	if ( isset( $_GET['vczapi_dismiss_again'] ) && $_GET['vczapi_dismiss_again'] == 1 ) {
		set_transient( '_vczapi_dismiss_notice_api_error', 1, 60 * 60 * 24 * 30 );
	}

	if ( ! get_transient( '_vczapi_dismiss_notice_api_error' ) ) {
		?>
        <div class="zoom-status-notice notice notice-warning is-dismissible">
            <h3><?php _e( 'ZOOM SERVICES STATUS', 'video-conferencing-with-zoom-api' ); ?></h3>
            <p>Experiencing issues with the join via Browser ? This is because Zoom webSDK part is under maintenance, due to which 403 error is
                showing when you try to join the meeting i.e in console of the browser. Check
                <a href="https://devforum.zoom.us/t/in-progress-web-sdk-web-client-from-browser-403-forbidden/10782/107">in this thread</a> as well as
                official <a href="https://marketplace.zoom.us/docs/sdk/native-sdks/web">SDK page</a> for more details. This message will be removed in
                the next update after the webSDK fix. <a href="<?php echo add_query_arg( 'vczapi_dismiss_again', 1 ) ?>" class="is-dismissible">Don't
                    show again !</a></p>

        </div>
		<?php
	}
}

/**
 * @author Deepen
 * @since  3.0.0
 */
function video_conferencing_zoom_api_show_like_popup() {
	if ( isset( $_GET['vczapi_dismiss'] ) && $_GET['vczapi_dismiss'] == 1 ) {
		set_transient( '_vczapi_dismiss_notice', 1, 60 * 60 * 24 * 30 );
	}

	if ( ! get_transient( '_vczapi_dismiss_notice' ) ) {
		?>
        <div id="message" class="notice notice-warning is-dismissible">
            <h3><?php esc_html_e( 'Like this plugin ?', 'video-conferencing-with-zoom-api' ); ?></h3>
            <p>
				<?php
				printf( esc_html__( 'Please consider giving a %s if you found this useful at wordpress.org or ', 'video-conferencing-with-zoom-api' ), '<a href="https://wordpress.org/support/plugin/video-conferencing-with-zoom-api/reviews/#new-post">5 star thumbs up</a>' );
				printf( esc_html__( 'check %s for shortcode references.', 'video-conferencing-with-zoom-api' ), '<a href="' . admin_url( 'edit.php?post_type=zoom-meetings&page=zoom-video-conferencing-settings' ) . '">settings</a>.' );
				?>
                <a href="<?php echo add_query_arg( 'vczapi_dismiss', 1 ) ?>" class="is-dismissible">I already rated you ! Don't show again !</a>
            </p>
        </div>
		<?php
	}
}

function video_conferencing_zoom_api_new_api_notice() {
	if ( isset( $_GET['vczapi_dismiss_api'] ) && $_GET['vczapi_dismiss_api'] == 1 ) {
		set_transient( '_vczapi_dismiss_notice_api', 1, 60 * 60 * 24 * 30 );
	}

	if ( ! get_transient( '_vczapi_dismiss_notice_api' ) ) {
		?>
        <div id="message" class="notice notice-warning is-dismissible">
            <h3><?php esc_html_e( 'New Zoom Changes Notice !!', 'video-conferencing-with-zoom-api' ); ?></h3>
            <p>With new Zoom update, join links require for password. To fix on old meetings please update the meetings to allow direct join. Please
                update your old meetings and that should do the trick in making compatible with new password change. Please report to me if you have
                any issues !!!!</p>
            <p>
                <a href="javascript:void(0);" class="zvc-dismiss-message"><?php _e( "I understand ! Don't show this again !", "video-conferencing-with-zoom-api" ); ?></a>
            </p>
        </div>
		<?php
	}
}

/**
 * @author Deepen
 * @since  3.0.0
 */
function video_conferencing_zoom_api_show_api_notice() {
	$notice = get_option( 'zoom_api_notice' );
	if ( empty( $notice ) ) {
		?>
        <div id="message" class="notice notice-success"><p style="font-size:16px;">
                <strong><?php _e( "Do not get confused here !!", "video-conferencing-with-zoom-api" ); ?></strong>
            <p>
                <strong><?php _e( "Please read !!! These below meetings are directly from your zoom.us account via API connection. Meetings added from here won't show up on your Post Type list. This will only create meeting in your zoom.us account !", "video-conferencing-with-zoom-api" ); ?></strong>
                <a href="javascript:void(0);" class="zvc-dismiss-message"><?php _e( "I understand ! Don't show this again !", "video-conferencing-with-zoom-api" ); ?></a>
            </p></div>
		<?php
	}
}

/**
 * Get the template
 *
 * @param $template_name
 * @param bool $load
 * @param bool $require_once
 *
 * @return bool|string
 */
function vczapi_get_template( $template_name, $load = false, $require_once = true ) {
	if ( empty( $template_name ) ) {
		return false;
	}

	$located = false;
	if ( file_exists( STYLESHEETPATH . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name ) ) {
		$located = STYLESHEETPATH . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name;
	} elseif ( file_exists( TEMPLATEPATH . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name ) ) {
		$located = TEMPLATEPATH . '/' . ZVC_PLUGIN_SLUG . '/' . $template_name;
	} elseif ( file_exists( ZVC_PLUGIN_DIR_PATH . 'templates/' . $template_name ) ) {
		$located = ZVC_PLUGIN_DIR_PATH . 'templates/' . $template_name;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'vczapi_get_template', $located, $template_name );
	if ( $load && ! empty( $located ) && file_exists( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Get Template Parts
 *
 * @param $slug
 * @param string $name
 *
 * @since  3.0.0
 * @author Deepen
 */
function vczapi_get_template_part( $slug, $name = '' ) {
	$template = false;
	if ( $name ) {
		$template = locate_template( array(
			"{$slug}-{$name}.php",
			ZVC_PLUGIN_SLUG . '/' . "{$slug}-{$name}.php",
		) );

		if ( ! $template ) {
			$fallback = ZVC_PLUGIN_DIR_PATH . "templates/{$slug}-{$name}.php";
			$template = file_exists( $fallback ) ? $fallback : '';
		}
	}

	if ( ! $template ) {
		$template = locate_template( array(
			"{$slug}-{$name}.php",
			ZVC_PLUGIN_SLUG . '/' . "{$slug}-{$name}.php",
		) );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'vcz_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * @author Deepen
 * @since  3.0.0
 */
function vczapi_check_author( $post_id ) {
	$post_author_id = get_post_field( 'post_author', $post_id );
	$current_user   = get_current_user_id();
	if ( (int) $post_author_id === $current_user ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Calculate Time based on Timezone
 *
 * @param $start_time
 * @param $tz
 * @param string $format
 * @param bool $defaults
 *
 * @return DateTime|string
 * @throws Exception
 * @author Deepen
 * @since  1.0.0
 */
function vczapi_dateConverter( $start_time, $tz, $format = 'F j, Y, g:i a ( T )', $defaults = true ) {
	$timezone = ! empty( $tz ) ? $tz : "America/Los_Angeles";
	$tz       = new DateTimeZone( $timezone );
	$date     = new DateTime( $start_time );
	$date->setTimezone( $tz );
	if ( ! $format ) {
		return $date;
	}

	if ( !$defaults ) {
		return $date->format( $format );
	}

	$locale      = get_locale();
	$date_format = get_option( 'zoom_api_date_time_format' );
	if ( $defaults && ! empty( $locale ) && ! empty( $date_format ) ) {
		setlocale( LC_TIME, $locale );
		$start_timestamp = $date->getTimestamp() + $date->getOffset();
		switch ( $date_format ) {
			case 'L LT':
			case 'l LT':
				return strftime( '%D, %R', $start_timestamp );
				break;
			case 'llll':
				return strftime( '%a, %b %e, %G %R', $start_timestamp );
				break;
			case 'lll':
				return strftime( '%b %e, %G %R', $start_timestamp );
				break;
			case 'LLLL':
				return strftime( '%A %b %e, %G %R', $start_timestamp );
				break;
			default:
				return $date->format( $format );
				break;
		}
	} else {
		return $date->format( $format );
	}
}

/**
 * Encrypts URL
 *
 * @param $action
 * @param $string
 *
 * @return bool|string
 */
function vczapi_encrypt_decrypt( $action, $string ) {
	$output = false;

	$encrypt_method = "AES-256-CBC";
	$secret_key     = 'DPEN_X3!3#23121';
	$secret_iv      = '1231232133213221';

	// hash
	$key = hash( 'sha256', $secret_key );

	// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

	if ( $action == 'encrypt' ) {
		$output = openssl_encrypt( $string, $encrypt_method, $key, 0, $iv );
		$output = base64_encode( $output );
	} else if ( $action == 'decrypt' ) {
		$output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
	}

	return $output;
}

if ( ! function_exists( 'vczapi_get_browser_agent_type' ) ) {
	function vczapi_get_browser_agent_type() {
		//Detect special conditions devices
		$iPod    = stripos( $_SERVER['HTTP_USER_AGENT'], "iPod" );
		$iPhone  = stripos( $_SERVER['HTTP_USER_AGENT'], "iPhone" );
		$iPad    = stripos( $_SERVER['HTTP_USER_AGENT'], "iPad" );
		$Android = stripos( $_SERVER['HTTP_USER_AGENT'], "Android" );

		//do something with this information
		if ( $iPod || $iPhone || $iPad ) {
			$app_store_link = 'https://apps.apple.com/app/zoom-cloud-meetings/id546505307';
		} else if ( $Android ) {
			$app_store_link = 'https://play.google.com/store/apps/details?id=us.zoom.videomeetings';
		} else {
			$app_store_link = 'https://zoom.us/support/download';
		}

		return $app_store_link;
	}
}

/**
 * Get Browser join links
 *
 * @param $post_id
 * @param $meeting_id
 * @param bool $password
 *
 * @return string
 */
function vczapi_get_browser_join_links( $post_id, $meeting_id, $password = false ) {
	$link                     = get_permalink( $post_id );
	$encrypt_pwd              = vczapi_encrypt_decrypt( 'encrypt', $password );
	$encrypt_meeting_id       = vczapi_encrypt_decrypt( 'encrypt', $meeting_id );
	$embed_password_join_link = get_option( 'zoom_api_embed_pwd_join_link' );
	if ( ! empty( $password ) && empty( $embed_password_join_link ) ) {
		$query = add_query_arg( array( 'pak' => $encrypt_pwd, 'join' => $encrypt_meeting_id, 'type' => 'meeting' ), $link );

		return '<a target="_blank" rel="nofollow" href="' . esc_url( $query ) . '" class="btn btn-join-link btn-join-via-browser">' . apply_filters( 'vczoom_join_meeting_via_app_text', __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ) ) . '</a>';
	} else {
		$query = add_query_arg( array( 'join' => $encrypt_meeting_id, 'type' => 'meeting' ), $link );

		return '<a target="_blank" rel="nofollow" href="' . esc_url( $query ) . '" class="btn btn-join-link btn-join-via-browser">' . apply_filters( 'vczoom_join_meeting_via_app_text', __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ) ) . '</a>';
	}
}

/**
 * Join via Shortcode
 *
 * @param $meeting_id
 * @param bool $password
 * @param $link_only
 *
 * @return string
 */
function vczapi_get_browser_join_shortcode( $meeting_id, $password = false, $link_only = false ) {
	$link                     = get_post_type_archive_link( 'zoom-meetings' );
	$encrypt_meeting_id       = vczapi_encrypt_decrypt( 'encrypt', $meeting_id );
	$embed_password_join_link = get_option( 'zoom_api_embed_pwd_join_link' );
	if ( ! empty( $password ) && empty( $embed_password_join_link ) ) {
		$encrypt_pwd = vczapi_encrypt_decrypt( 'encrypt', $password );
		$query       = add_query_arg( array( 'pak' => $encrypt_pwd, 'join' => $encrypt_meeting_id, 'type' => 'meeting' ), $link );
		$result      = '<a target="_blank" rel="nofollow" href="' . esc_url( $query ) . '" class="btn btn-join-link btn-join-via-browser">' . apply_filters( 'vczoom_join_meeting_via_app_text', __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ) ) . '</a>';
		$link        = esc_url( $query );
	} else {
		$query  = add_query_arg( array( 'join' => $encrypt_meeting_id, 'type' => 'meeting' ), $link );
		$result = '<a target="_blank" rel="nofollow" href="' . esc_url( $query ) . '" class="btn btn-join-link btn-join-via-browser">' . apply_filters( 'vczoom_join_meeting_via_app_text', __( 'Join via Web Browser', 'video-conferencing-with-zoom-api' ) ) . '</a>';
		$link   = esc_url( $query );
	}

	if ( $link_only ) {
		return $link;
	} else {
		return $result;
	}
}

/**
 * Get Join link with Password Embedded
 *
 * @param $join_url
 * @param $encrpyted_pwd
 *
 * @return string
 */
function vczapi_get_pwd_embedded_join_link( $join_url, $encrpyted_pwd ) {
	if ( ! empty( $encrpyted_pwd ) ) {
		$explode_pwd              = array_map( 'trim', explode( '?pwd', $join_url ) );
		$embed_password_join_link = get_option( 'zoom_api_embed_pwd_join_link' );
		$password_exists          = count( $explode_pwd ) > 1 ? true : false;
		if ( $password_exists ) {
			if ( ! empty( $embed_password_join_link ) ) {
				$join_url = $explode_pwd[0];
			}
		} else {
			$join_url = add_query_arg( array( 'pwd' => $encrpyted_pwd ), $join_url );
		}
	}

	return $join_url;
}
