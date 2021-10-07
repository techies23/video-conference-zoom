<?php

namespace Codemanas\VczApi\Shortcodes;

class Webinars {

	/**
	 * Define post type
	 *
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	/**
	 * Instance
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * Create only one instance so that it may not Repeat
	 *
	 * @since 2.0.0
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Show Webinar based on Webinar ID
	 *
	 * @param $atts
	 *
	 * @return bool|false|string
	 * @author Deepen
	 *
	 * @since  3.0.4
	 */
	public function show_webinar_by_ID( $atts ) {
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
		$webinar                  = Helpers::fetch_webinar( $webinar_id );
		$GLOBALS['vanity_uri']    = $vanity_uri;
		$GLOBALS['zoom_webinars'] = $webinar;
		if ( ! empty( $webinar ) && ! empty( $webinar->code ) ) {
			?>
            <p class="dpn-error dpn-mtg-not-found"><?php echo $webinar->message; ?></p>
			<?php
		} else {
			if ( ! empty( $link_only ) && $link_only === "yes" ) {
				Helpers::generate_link_only();
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
	 * Show List of live webinars from your zoom account
	 *
	 * @param $atts
	 *
	 * @return false|string|void
	 * @author Deepen
	 *
	 * @since  3.0.4
	 */
	public function list_live_host_webinars( $atts ) {
		$atts = shortcode_atts(
			[
				'host' => ''
			],
			$atts
		);

		if ( empty( $atts['host'] ) ) {
			return __( 'Host ID should be given when defining this shortcode.', 'video-conferencing-with-zoom-api' );
		}

		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$webinars         = get_option( '_vczapi_user_webinars_for_' . $atts['host'] );
		$cache_expiration = get_option( '_vczapi_user_webinars_for_' . $atts['host'] . '_expiration' );
		if ( empty( $webinars ) || $cache_expiration < time() ) {
			$encoded_meetings = zoom_conference()->listWebinar( $atts['host'] );
			$decoded_meetings = json_decode( $encoded_meetings );
			if ( isset( $decoded_meetings->webinars ) ) {
				$webinars = $decoded_meetings->webinars;
				update_option( '_vczapi_user_webinars_for_' . $atts['host'], $webinars );
				update_option( '_vczapi_user_webinars_for_' . $atts['host'] . '_expiration', time() + 60 * 5 );
			} else {
				if ( ! empty( $decoded_meetings ) && ! empty( $decoded_meetings->code ) ) {
					return '<strong>Zoom API Error:</strong>' . $decoded_meetings->message;
				} else {
					return __( 'Could not retrieve webinars, check Host ID', 'video-conferencing-with-zoom-api' );
				}
			}
		}

		ob_start();
		vczapi_get_template( 'shortcode/list-webinars-host.php', true, false, $webinars );

		return ob_get_clean();
	}

	/**
	 * List webinars based on Custom Post Types
	 *
	 * @param $atts
	 *
	 * @return string
	 * @since  3.6.0
	 *
	 * @author Deepen Bajracharya
	 */
	public function list_cpt_webinars( $atts ) {
		$atts = shortcode_atts(
			array(
				'author'       => '',
				'per_page'     => 5,
				'category'     => '',
				'order'        => 'DESC',
				'type'         => '',
				'filter'       => 'yes',
				'show_on_past' => 'yes',
				'cols'         => 3
			),
			$atts, 'zoom_list_webinars'
		);

		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );
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
			'meta_key'       => '_meeting_field_start_date_utc',
			'order'          => $atts['order'],
			'caller'         => ! empty( $atts['filter'] ) && $atts['filter'] === "yes" ? 'vczapi' : false,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => '_vczapi_meeting_type',
						'value'   => 'webinar',
						'compare' => '='
					)
				)
			)
		);

		if ( ! empty( $atts['author'] ) ) {
			$query_args['author'] = absint( $atts['author'] );
		}

		if ( ! empty( $atts['type'] ) && ! empty( $query_args['meta_query'] ) ) {
			//NOTE !!!! When using this filter please correctly send minutes or hours otherwise it will output error
			$threshold_limit = apply_filters( 'vczapi_list_cpt_meetings_threshold', '30 minutes' );
			if ( $atts['show_on_past'] === "yes" && ! empty( $threshold_limit ) ) {
				$threshold = ( $atts['type'] === "upcoming" ) ? vczapi_dateConverter( 'now -' . $threshold_limit, 'UTC', 'Y-m-d H:i:s', false ) : vczapi_dateConverter( 'now +' . $threshold_limit, 'UTC', 'Y-m-d H:i:s', false );
			} else {
				$threshold = vczapi_dateConverter( 'now', 'UTC', 'Y-m-d H:i:s', false );
			}

			$type       = ( $atts['type'] === "upcoming" ) ? '>=' : '<=';
			$meta_query = array(
				'key'     => '_meeting_field_start_date_utc',
				'value'   => $threshold,
				'compare' => $type,
				'type'    => 'DATETIME'
			);
			array_push( $query_args['meta_query'], $meta_query );
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
		$GLOBALS['zoom_meetings']          = $zoom_meetings;
		$GLOBALS['zoom_meetings']->columns = ! empty( $atts['cols'] ) ? absint( $atts['cols'] ) : 3;
		//since list webinars shortcode is different from list meeting shortcode $atts['meeting_type'] needs to be defined explicitly here
		//to be used in shortcode-listing.php otherwise it will cause issues.
		//@todo: consider using singular webinar instead of webinars - must change code in list_meeting_ajax_handler function
		$atts['meeting_type'] = 'webinars';
		ob_start();
		vczapi_get_template( 'shortcode-listing.php', true, false, $atts );
		$content .= ob_get_clean();

		return $content;
	}
}