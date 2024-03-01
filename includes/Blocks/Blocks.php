<?php

namespace Codemanas\VczApi\Blocks;

use function Composer\Autoload\includeFile;

/**
 * The instance of the Blocks class.
 *
 * @var Blocks|null
 */
class Blocks {

	public static $_instance = null;

	/**
	 * @return Blocks|null
	 */
	public static function get_instance() {
		return is_null( self::$_instance ) ? self::$_instance = new self() : self::$_instance;
	}

	/**
	 * Blocks constructor.
	 */
	public function __construct() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', [ $this, 'register_block_categories' ], 10, 2 );
		} else {
			add_filter( 'block_categories', [ $this, 'register_block_categories' ], 10, 2 );
		}
		if ( function_exists( 'register_block_type' ) ) {
			add_action( 'init', [ $this, 'register_blocks' ] );
			add_action('admin_enqueue_scripts',[$this,'register_shared_assets']);
		}

		add_action( 'wp_ajax_vczapi_get_zoom_hosts', [ $this, 'get_hosts' ] );
		add_action( 'wp_ajax_vczapi_get_live_meetings', [ $this, 'get_live_meetings' ] );
	}

	public function register_shared_assets(  ) {
		wp_enqueue_style('vczapi-blocks__shared-assets', ZVC_PLUGIN_DIR_URL.'assets/shared/style.min.css');
	}

	/**
	 * Registering block categories
	 *
	 * @param $categories
	 * @param $post
	 *
	 * @return array
	 * @since   3.7.5
	 * @updated N/A
	 *
	 */
	public function register_block_categories( $categories, $post ): array {
		return array_merge(
			[
				[
					'slug'  => 'vczapi-blocks',
					'title' => __( 'Zoom', 'video-conferencing-with-zoom-api' ),
					//'icon'  => 'wordpress',
				],
			],
			$categories
		);
	}

	/**
	 * Registers all available blocks for the plugin.
	 *
	 * This method is responsible for registering all the blocks available in the plugin.
	 * It uses the `register_block_type` function provided by WordPress to register each block.
	 * The path of each block's build directory is constructed using the `ZVC_PLUGIN_DIR_PATH` constant.
	 *
	 * @return void
	 */
	public function register_blocks() {
		register_block_type(ZVC_PLUGIN_DIR_PATH . 'build/block/join-via-browser' );
		register_block_type(ZVC_PLUGIN_DIR_PATH . 'build/block/list-host-meetings' );
		register_block_type(ZVC_PLUGIN_DIR_PATH . 'build/block/list-meetings' );
		register_block_type(ZVC_PLUGIN_DIR_PATH . 'build/block/recordings' );
		register_block_type(ZVC_PLUGIN_DIR_PATH . 'build/block/show-live-meeting' );
		register_block_type(ZVC_PLUGIN_DIR_PATH . 'build/block/show-meeting-post' );
		register_block_type(ZVC_PLUGIN_DIR_PATH . 'build/block/single-zoom-meeting' );
	}

	/**
	 * Gets the hosts for the video conferencing session.
	 *
	 * This method retrieves the hosts from the user transients stored in the database.
	 * It filters the hosts based on the host name provided as a query parameter.
	 * If a host name is provided, only hosts whose username includes the host name will be included.
	 * If no host name is provided, all hosts will be included.
	 * If no hosts are found based on the host name, it searches for a user with the specified email address and adds them as a host if found.
	 * The hosts are returned as an array of label-value pairs, where the label is the host's username and the value is the host's ID.
	 *
	 * @return void
	 */
	public function get_hosts() {
		$host_name = filter_input( INPUT_GET, 'host' );
		$users     = video_conferencing_zoom_api_get_user_transients();

		$hosts = [];
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$first_name = ! empty( $user->first_name ) ? $user->first_name . ' ' : '';
				$last_name  = ! empty( $user->last_name ) ? $user->last_name . ' ' : '';
				$username   = $first_name . $last_name . '(' . $user->email . ')';

				if ( ! empty( $host_name ) ) {
					preg_match( "/($host_name)/", $username, $matches );
					if ( ! empty( $matches ) ) {
						$hosts[] = [ 'label' => $username, 'value' => $user->id ];
					}
				} else {
					$hosts[] = [ 'label' => $username, 'value' => $user->id ];
				}
			}
		}

		//If not found host then search for email address
		if ( empty( $hosts ) && ! empty( $host_name ) ) {
			$user = json_decode( zoom_conference()->getUserInfo( $host_name ) );
			if ( ! empty( $user ) && ! isset( $user->code ) ) {
				$first_name = ! empty( $user->first_name ) ? $user->first_name . ' ' : '';
				$last_name  = ! empty( $user->last_name ) ? $user->last_name . ' ' : '';
				$username   = $first_name . $last_name . '(' . $user->email . ')';

				$hosts[] = [ 'label' => $username, 'value' => $user->id ];
			}
		}

		wp_send_json( $hosts );
	}

	/**
	 * Retrieves live meetings or webinars based on host ID and show type.
	 *
	 * This method retrieves live meetings or webinars based on the provided host ID and show type.
	 * It uses the `filter_input` function to get the host ID, show type, and page number from the input.
	 * It sets the `page_size` argument to 300 by default.
	 * If a page number is provided, it adds it to the arguments.
	 * If the host ID is empty, it sends a JSON response with `false`.
	 * It then calls the `listWebinar` or `listMeetings` method from the `zoom_conference` object based on the show type.
	 * If an error occurred while retrieving the meetings or webinars, it sends a JSON response with the error message.
	 * Otherwise, it decodes the retrieved meetings or webinars.
	 * If the show type is 'webinar', it sets the `$meetings_or_webinars` variable to the webinars array if it exists, otherwise an empty array.
	 * If the show type is 'meeting', it sets the `$meetings_or_webinars` variable to the meetings array if it exists, otherwise an empty array.
	 * It then creates an empty array `$data` and an empty array `$formatted_meetings`.
	 * If there are meetings or webinars, it sets the `$data` array with the page size and total records from the decoded meetings or webinars.
	 * It also loops through each meeting or webinar and adds an array with 'label' and 'value' keys to the `$formatted_meetings` array.
	 * Finally, it sets the `$data` array with the `$formatted_meetings` array and sends a JSON response with the `$data` array.
	 *
	 * @return void
	 */
	public function get_live_meetings() {
		$host_id                 = filter_input( INPUT_GET, 'host_id' );
		$show_meeting_or_webinar = filter_input( INPUT_GET, 'show' );
		$args                    = [
			'page_size' => 300,
		];
		$page_number             = filter_input( INPUT_GET, 'page_number' );
		if ( ! empty( $page_number ) ) {
			$args['page_number'] = $page_number;
		}

		if ( empty( $host_id ) ) {
			wp_send_json( false );
		}

		$encoded_meetings_webinar = ( $show_meeting_or_webinar == 'webinar' ) ? zoom_conference()->listWebinar( $host_id, $args ) : zoom_conference()->listMeetings( $host_id, $args );
		if ( is_wp_error( $encoded_meetings_webinar ) ) {
			wp_send_json( $encoded_meetings_webinar->get_error_message() );
		} else {
			$decoded_meetings_webinars = json_decode( $encoded_meetings_webinar );
		}

		if ( $show_meeting_or_webinar == 'webinar' ) {
			$meetings_or_webinars = ! empty( $decoded_meetings_webinars->webinars ) ? $decoded_meetings_webinars->webinars : [];
		} else {
			$meetings_or_webinars = ! empty( $decoded_meetings_webinars->meetings ) ? $decoded_meetings_webinars->meetings : [];
		}

		$data               = [];
		$formatted_meetings = [];
		if ( ! empty( $meetings_or_webinars ) ) {
			$data = [
				'page_size'     => isset( $decoded_meetings_webinars->page_size ) ? $decoded_meetings_webinars->page_size : '',
				'total_records' => isset( $decoded_meetings_webinars->total_records ) ? $decoded_meetings_webinars->total_records : ''
			];
			foreach ( $meetings_or_webinars as $meeting_or_webinar ) {
				$formatted_meetings[] = [
					'label' => $meeting_or_webinar->topic,
					'value' => $meeting_or_webinar->id
				];
			}
			$data['formatted_meetings'] = $formatted_meetings;
		}
		wp_send_json( $data );
	}
}