<?php

namespace Codemanas\VczApi\Shortcodes;

class Webinars {

	/**
	 * Define post type
	 *
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	private static ?Webinars $_instance = null;

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
	 * Get a scalar value.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function get_scalar_value( $value, string $default = '' ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return $default;
		}

		return (string) $value;
	}

	/**
	 * Normalize a yes/no value.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function sanitize_yes_no_attribute( $value, string $default = 'no' ): string {
		$value = strtolower( $this->get_scalar_value( $value, $default ) );

		return in_array( $value, [ 'yes', 'no' ], true ) ? $value : $default;
	}

	/**
	 * Sanitize a Zoom numeric webinar ID.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_zoom_numeric_id( $value ): string {
		return preg_replace( '/[^0-9]/', '', $this->get_scalar_value( $value ) );
	}

	/**
	 * Sanitize a Zoom host identifier.
	 *
	 * Zoom host IDs are expected to be alphanumeric API identifiers or email-like
	 * identifiers. This intentionally strips shortcode syntax.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_zoom_host_id( $value ): string {
		return preg_replace( '/[^A-Za-z0-9_\-@.]/', '', $this->get_scalar_value( $value ) );
	}

	/**
	 * Normalize shortcode order value.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function sanitize_order_attribute( $value, string $default = 'DESC' ): string {
		$value = strtoupper( $this->get_scalar_value( $value, $default ) );

		return in_array( $value, [ 'ASC', 'DESC' ], true ) ? $value : $default;
	}

	/**
	 * Normalize webinar list type.
	 *
	 * @param mixed  $value   Value to normalize.
	 * @param string $default Default value.
	 *
	 * @return string
	 */
	private function sanitize_list_type_attribute( $value, string $default = '' ): string {
		$value = strtolower( $this->get_scalar_value( $value, $default ) );

		return in_array( $value, [ 'upcoming', 'past' ], true ) ? $value : $default;
	}

	/**
	 * Sanitize a comma-separated category slug list.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return array
	 */
	private function sanitize_category_slugs( $value ): array {
		$value = $this->get_scalar_value( $value );

		if ( '' === $value ) {
			return [];
		}

		$categories = array_map( 'trim', explode( ',', $value ) );
		$categories = array_map( 'sanitize_title', $categories );
		$categories = array_filter( $categories );

		return array_values( array_unique( $categories ) );
	}

	/**
	 * Normalize attributes for webinar list shortcodes.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return array
	 */
	private function sanitize_list_shortcode_atts( array $atts ): array {
		$atts['author']       = ! empty( $atts['author'] ) ? absint( $atts['author'] ) : '';
		$atts['per_page']     = ! empty( $atts['per_page'] ) ? absint( $atts['per_page'] ) : 5;
		$atts['category']     = implode( ',', $this->sanitize_category_slugs( $atts['category'] ?? '' ) );
		$atts['order']        = $this->sanitize_order_attribute( $atts['order'] ?? 'DESC', 'DESC' );
		$atts['type']         = $this->sanitize_list_type_attribute( $atts['type'] ?? '', '' );
		$atts['filter']       = $this->sanitize_yes_no_attribute( $atts['filter'] ?? 'yes', 'yes' );
		$atts['show_on_past'] = $this->sanitize_yes_no_attribute( $atts['show_on_past'] ?? 'yes', 'yes' );
		$atts['cols']         = ! empty( $atts['cols'] ) ? absint( $atts['cols'] ) : 3;

		if ( empty( $atts['per_page'] ) ) {
			$atts['per_page'] = 5;
		}

		if ( empty( $atts['cols'] ) ) {
			$atts['cols'] = 3;
		}

		return $atts;
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

		$atts = shortcode_atts(
			[
				'webinar_id' => '',
				'link_only'  => 'no',
			],
			$atts,
			'zoom_api_webinar'
		);

		$webinar_id = $this->sanitize_zoom_numeric_id( $atts['webinar_id'] );
		$link_only  = $this->sanitize_yes_no_attribute( $atts['link_only'], 'no' );

		unset( $GLOBALS['vanity_uri'] );
		unset( $GLOBALS['zoom_webinars'] );

		ob_start();
		if ( empty( $webinar_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . esc_html__( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . esc_html__( 'No webinar id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return false;
		}

		$vanity_uri               = get_option( 'zoom_vanity_url' );
		$webinar                  = Helpers::fetch_webinar( $webinar_id );
		$GLOBALS['vanity_uri']    = $vanity_uri;
		$GLOBALS['zoom_webinars'] = $webinar;
		if ( ! empty( $webinar ) && ! empty( $webinar->code ) ) {
			?>
            <p class="dpn-error dpn-mtg-not-found"><?php echo esc_html( $webinar->message ); ?></p>
			<?php
		} else {
			if ( ! empty( $link_only ) && $link_only === "yes" ) {
				Helpers::generate_link_only();
			} else {
				if ( $webinar ) {
					//Get Template
					vczapi_get_template( 'shortcode/zoom-webinar.php', true, false );
				} else {
					printf( esc_html__( 'Please try again ! Some error occured while trying to fetch webinar with id:  %d', 'video-conferencing-with-zoom-api' ), absint( $webinar_id ) );
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
			$atts,
			'zoom_list_host_webinars'
		);

		$host = $this->sanitize_zoom_host_id( $atts['host'] );

		if ( empty( $host ) ) {
			return esc_html__( 'Host ID should be given when defining this shortcode.', 'video-conferencing-with-zoom-api' );
		}

		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$webinars         = get_option( '_vczapi_user_webinars_for_' . $host );
		$cache_expiration = get_option( '_vczapi_user_webinars_for_' . $host . '_expiration' );
		if ( empty( $webinars ) || $cache_expiration < time() ) {
			$encoded_meetings = zoom_conference()->listWebinar( $host );
			$decoded_meetings = json_decode( $encoded_meetings );
			if ( isset( $decoded_meetings->webinars ) ) {
				$webinars = $decoded_meetings->webinars;
				update_option( '_vczapi_user_webinars_for_' . $host, $webinars );
				update_option( '_vczapi_user_webinars_for_' . $host . '_expiration', time() + 60 * 5 );
			} else {
				if ( ! empty( $decoded_meetings ) && ! empty( $decoded_meetings->code ) ) {
					return '<strong>' . esc_html__( 'Zoom API Error:', 'video-conferencing-with-zoom-api' ) . '</strong>' . esc_html( $decoded_meetings->message );
				} else {
					return esc_html__( 'Could not retrieve webinars, check Host ID', 'video-conferencing-with-zoom-api' );
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

		$atts = $this->sanitize_list_shortcode_atts( $atts );

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
			$category                = $this->sanitize_category_slugs( $atts['category'] );
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