<?php

namespace Codemanas\VczApi\Shortcodes;

class Meetings {

	/**
	 * Define post type
	 *
	 * @var string
	 */
	private $post_type = 'zoom-meetings';

	/**
	 * Instance
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
	 * Show Meeting based on ID
	 *
	 * @param $atts
	 *
	 * @return bool|false|string|void
	 * @author Deepen
	 *
	 * @since  3.0.4
	 */
	public function show_meeting_by_ID( $atts ) {
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api' );

		extract( shortcode_atts( array(
			'meeting_id' => '',
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
		$meeting                  = Helpers::fetch_meeting( $meeting_id );
		$GLOBALS['vanity_uri']    = $vanity_uri;
		$GLOBALS['zoom_meetings'] = $meeting;
		if ( ! empty( $meeting ) && ! empty( $meeting->code ) ) {
			?>
            <p class="dpn-error dpn-mtg-not-found"><?php echo $meeting->message; ?></p>
			<?php
		} else {
			if ( ! empty( $link_only ) && $link_only === "yes" ) {
				Helpers::generate_link_only();
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
	 * Show Meeting based on POST ID
	 *
	 * @param $atts
	 *
	 * @return bool|false|string|void
	 * @author Deepen
	 *
	 * @since  3.6.4
	 */
	public function show_meeting_by_postTypeID( $atts ) {
		extract( shortcode_atts( array(
			'post_id' => ''
		), $atts ) );

		ob_start();

		if ( empty( $post_id ) ) {
			echo '<h4 class="no-meeting-id"><strong style="color:red;">' . __( 'ERROR: ', 'video-conferencing-with-zoom-api' ) . '</strong>' . __( 'No post id set in the shortcode', 'video-conferencing-with-zoom-api' ) . '</h4>';

			return false;
		}

		unset( $GLOBALS['zoom'] );

		wp_enqueue_style( 'video-conferencing-with-zoom-api' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-locales' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-moment-timezone' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api' );

		$date_format = get_option( 'zoom_api_date_time_format' );
		if ( $date_format == 'custom' ) {
			$date_format = get_option( 'zoom_api_custom_date_time_format' );
			$date_format = vczapi_convertPHPToMomentFormat( $date_format );
		}

		$zoom_started        = get_option( 'zoom_started_meeting_text' );
		$zoom_going_to_start = get_option( 'zoom_going_tostart_meeting_text' );
		$zoom_ended          = get_option( 'zoom_ended_meeting_text' );
		$translation_array   = apply_filters( 'vczapi_meeting_event_text', array(
			'meeting_started'  => ! empty( $zoom_started ) ? $zoom_started : __( 'Meeting Has Started ! Click below join button to join meeting now !', 'video-conferencing-with-zoom-api' ),
			'meeting_starting' => ! empty( $zoom_going_to_start ) ? $zoom_going_to_start : __( 'Click join button below to join the meeting now !', 'video-conferencing-with-zoom-api' ),
			'meeting_ended'    => ! empty( $zoom_ended ) ? $zoom_ended : __( 'This meeting has been ended by the host.', 'video-conferencing-with-zoom-api' ),
			'date_format'      => $date_format
		) );
		wp_localize_script( 'video-conferencing-with-zoom-api', 'zvc_strings', $translation_array );

		$meeting = new \WP_Query( [ 'p' => $post_id, 'post_type' => $this->post_type ] );
		if ( $meeting->have_posts() ) {
			while ( $meeting->have_posts() ) {
				$meeting->the_post();

				$show_zoom_author_name = get_option( 'zoom_show_author' );
				$GLOBALS['zoom']       = get_post_meta( get_the_id(), '_meeting_fields', true ); //For Backwards Compatibility ( Will be removed someday )
				$meeting_details       = get_post_meta( get_the_id(), '_meeting_zoom_details', true );

				if ( ! empty( $show_zoom_author_name ) ) {
					$meeting_author = vczapi_get_meeting_author( get_the_id(), $meeting_details );
				} else {
					$meeting_author = get_the_author();
				}

				$GLOBALS['zoom']['host_name'] = $meeting_author;
				if ( ! empty( $meeting_details ) ) {
					$GLOBALS['zoom']['api'] = get_post_meta( get_the_id(), '_meeting_zoom_details', true );
				}

				$terms = get_the_terms( get_the_id(), 'zoom-meeting' );
				if ( ! empty( $terms ) ) {
					$set_terms = array();
					foreach ( $terms as $term ) {
						$set_terms[] = $term->name;
					}
					$GLOBALS['zoom']['terms'] = $set_terms;
				}

				//Set flag that this is coming from shortcode instance
				$GLOBALS['zoom']['shortcode'] = true;

				vczapi_get_template_part( 'content', 'single-meeting' );
			}
		} else {
			echo "<p>" . __( 'This post does not exist.', 'video-conferencing-with-zoom-api' ) . "</p>";
		}

		return ob_get_clean();
	}

	/**
	 * List Zoom Meetings by Custom Post Type
	 *
	 * @param $atts
	 *
	 * @return string
	 * @author Deepen
	 * @since  3.0.4
	 */
	public function list_cpt_meetings( $atts ) {
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
			'meta_key'       => '_meeting_field_start_date_utc',
			'order'          => $atts['order'],
			'caller'         => ! empty( $atts['filter'] ) && $atts['filter'] === "yes" ? 'vczapi' : false,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => '_vczapi_meeting_type',
						'value'   => 'meeting',
						'compare' => '='
					),
					array(
						'key'     => '_vczapi_meeting_type',
						'compare' => 'NOT EXISTS'
					),
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
		ob_start();
		vczapi_get_template( 'shortcode-listing.php', true, false );
		$content .= ob_get_clean();

		return $content;
	}

	/**
	 * Lists Live Host Meetings from your Zoom Account
	 *
	 * @param $atts
	 *
	 * @return false|string|void
	 * @author Deepen
	 *
	 * @since  3.0.4
	 */
	public function list_live_host_meetings( $atts ) {
		$atts = shortcode_atts(
			[
				'host' => ''
			],
			$atts
		);

		if ( empty( $atts['host'] ) ) {
			return __( 'Host ID should be given when defining this shortcode.', 'video-conferencing-with-zoom-api' );
		}

		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable' );
		wp_enqueue_style( 'video-conferencing-with-zoom-api-datable-responsive' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-datable-dt-responsive-js' );
		wp_enqueue_script( 'video-conferencing-with-zoom-api-shortcode-js' );

		$meetings         = get_option( 'vczapi_user_meetings_for_' . $atts['host'] );
		$cache_expiration = get_option( 'vczapi_user_meetings_for_' . $atts['host'] . '_expiration' );
		if ( empty( $meetings ) || $cache_expiration < time() ) {
			$encoded_meetings = zoom_conference()->listMeetings( $atts['host'] );
			$decoded_meetings = json_decode( $encoded_meetings );
			if ( isset( $decoded_meetings->meetings ) ) {
				$meetings = $decoded_meetings->meetings;
				update_option( 'vczapi_user_meetings_for_' . $atts['host'], $meetings );
				update_option( 'vczapi_user_meetings_for_' . $atts['host'] . '_expiration', time() + 60 * 5 );
			} else {
				return __( 'Could not retrieve meetings, check Host ID', 'video-conferencing-with-zoom-api' );
			}
		}

		ob_start();
		?>
        <table id="vczapi-show-meetings-list-table" class="vczapi-user-meeting-list">
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
				$meeting->password = ! empty( $meeting->password ) ? $meeting->password : false;
				$meeting_status    = '';
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

				echo '<td>' . $meeting->topic . '</td>';
				echo '<td>' . $meeting_status . '</td>';
				echo '<td>' . vczapi_dateConverter( $meeting->start_time, $meeting->timezone, 'F j, Y, g:i a' ) . '</td>';
				echo '<td>' . $meeting->timezone . '</td>';
				echo '<td><div class="view">
<a href="' . $meeting->join_url . '" rel="permalink" target="_blank">' . __( 'Join via App', 'video-conferencing-with-zoom-api' ) . '</a></div><div class="view">' . vczapi_get_browser_join_shortcode( $meeting->id, $meeting->password, false, ' / ' ) . '</div></td>';
				echo '</tr>';
			}
			?>
            </tbody>
        </table>
		<?php
		return ob_get_clean();
	}
}