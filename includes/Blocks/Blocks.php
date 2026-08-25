<?php

namespace Codemanas\VczApi\Blocks;

use function Composer\Autoload\includeFile;

/**
 * Class Blocks
 *
 * @package Codemanas\VczApi\Blocks
 * @since   3.7.5
 * @updated N/A
 */
class Blocks {

	public static ?Blocks $_instance = null;

	/**
	 * @return Blocks|null
	 */
	public static function get_instance(): ?Blocks {
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
			add_action( 'init', [ $this, 'register_scripts' ] );
			add_action( 'init', [ $this, 'register_blocks' ] );
		}

		add_action( 'wp_ajax_vczapi_get_zoom_hosts', [ $this, 'get_hosts' ] );
		add_action( 'wp_ajax_vczapi_get_live_meetings', [ $this, 'get_live_meetings' ] );
	}

	/**
	 * Register the necessary scripts.
	 *
	 * @since   3.7.5
	 * @updated N/A
	 */
	public function register_scripts(): void {
		$script_asset_path = require_once( ZVC_PLUGIN_DIR_PATH . '/build/index.asset.php' );
		$dependencies      = $script_asset_path['dependencies'];

		wp_register_style(
			'video-conferencing-with-zoom-api-blocks',
			ZVC_PLUGIN_PUBLIC_ASSETS_URL . '/css/style.css',
			false,
			ZVC_PLUGIN_VERSION
		);

		wp_register_style(
			'vczapi-blocks-style',
			plugins_url( '/build/index.css', ZVC_PLUGIN_FILE ),
			[ 'video-conferencing-with-zoom-api-blocks' ],
			$script_asset_path['version']
		);

		wp_register_script(
			'vczapi-blocks',
			plugins_url( '/build/index.js', ZVC_PLUGIN_FILE ),
			$dependencies,
			$script_asset_path['version']
		);

		wp_localize_script(
			'vczapi-blocks',
			'vczapi_blocks',
			[
				'list_meetings_preview'            => ZVC_PLUGIN_IMAGES_PATH . '/block-previews/list-meetings-webinars.png',
				'direct_meeting_preview_image'     => ZVC_PLUGIN_IMAGES_PATH . '/block-previews/direct-meeting.jpg',
				'list_host_meetings_preview_image' => ZVC_PLUGIN_IMAGES_PATH . '/block-previews/list-host-meetings.png',
				'embed_post_preview'               => ZVC_PLUGIN_IMAGES_PATH . '/block-previews/embed_post_preview.png',
				'join_via_browser'                 => ZVC_PLUGIN_IMAGES_PATH . '/block-previews/join-via-browser.png',
				'single_zoom_meeting_page'         => ZVC_PLUGIN_IMAGES_PATH . '/skeleton.png'
			]
		);
	}

	/**
	 * Registering block categories.
	 *
	 * @param array $categories Block categories.
	 * @param mixed $post       Post.
	 *
	 * @return array
	 * @since   3.7.5
	 * @updated N/A
	 */
	public function register_block_categories( $categories, $post ): array {
		return array_merge(
			[
				[
					'slug'  => 'vczapi-blocks',
					'title' => __( 'Zoom', 'video-conferencing-with-zoom-api' ),
				],
			],
			$categories
		);
	}

	/**
	 * Registering blocks.
	 *
	 * @since   3.7.5
	 * @updated N/A
	 */
	public function register_blocks(): void {
		register_block_type( 'vczapi/list-meetings', [
			'title'           => 'List Zoom Meetings',
			'attributes'      => [
				'preview'          => [
					'type'    => 'boolean',
					'default' => false
				],
				'shortcodeType'    => [
					'type'    => 'string',
					'default' => 'meeting'
				],
				'showPastMeeting'  => [
					'type'    => 'boolean',
					'default' => false
				],
				'showFilter'       => [
					'type'    => 'string',
					'default' => 'yes',
				],
				'postsToShow'      => [
					'type'    => 'number',
					'default' => 5
				],
				'orderBy'          => [
					'type'    => 'string',
					'default' => ''
				],
				'selectedCategory' => [
					'type'    => 'array',
					'default' => []
				],
				'selectedAuthor'   => [
					'type'    => 'number',
					'default' => 0
				],
				'displayType'      => [
					'type'    => 'string',
					'default' => ''
				],
				'columns'          => [
					'type'    => 'number',
					'default' => 3
				]
			],
			'category'        => 'vczapi-blocks',
			'icon'            => 'list-view ',
			'description'     => 'List Upcoming or Past Meetings/Webinars',
			'textdomain'      => 'video-conferencing-with-zoom-api',
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_list_meetings' ]
		] );

		register_block_type( 'vczapi/list-host-meetings', [
			'title'           => 'List Zoom Meetings by Host',
			'attributes'      => [
				'host'       => [
					'type' => 'object',
				],
				'shouldShow' => [
					'type'    => 'object',
					'default' => [
						'label' => 'Meeting',
						'value' => 'meeting'
					]
				],
				'preview'    => [
					'type'    => 'boolean',
					'default' => false
				],
			],
			'category'        => 'vczapi-blocks',
			'icon'            => 'list-view',
			'description'     => 'Show Meetings/Webinars by Host',
			'textdomain'      => 'video-conferencing-with-zoom-api',
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_host_meeting_list' ]
		] );

		register_block_type( 'vczapi/show-meeting-post', [
			'title'           => 'Embed Zoom Post',
			'attributes'      => [
				'preview'     => [
					'type'    => 'boolean',
					'default' => false
				],
				'postID'      => [
					'type'    => 'number',
					'default' => 0
				],
				'template'    => [
					'type'    => 'string',
					'default' => 'none'
				],
				'description' => [
					'type'    => 'boolean',
					'default' => true
				],
				'countdown'   => [
					'type'    => 'boolean',
					'default' => true
				],
				'details'     => [
					'type'    => 'boolean',
					'default' => true
				]
			],
			'category'        => 'vczapi-blocks',
			'icon'            => 'embed-post',
			'description'     => 'Show a Meeting Post with Countdown',
			'textdomain'      => 'video-conferencing-with-zoom-api',
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_meeting_post' ]
		] );

		register_block_type( 'vczapi/show-live-meeting', [
			'title'           => 'Direct Meeting or Webinar',
			'attributes'      => [
				'preview'         => [
					'type'    => 'boolean',
					'default' => false
				],
				'shouldShow'      => [
					'type'    => 'object',
					'default' => [
						'label' => 'Meeting',
						'value' => 'meeting'
					]
				],
				'host'            => [
					'type' => 'object',
				],
				'selectedMeeting' => [
					'type' => 'object',
				],
				'link_only'       => [
					'type'    => 'string',
					'default' => 'no'
				]
			],
			'category'        => 'vczapi-blocks',
			'icon'            => 'sticky',
			'description'     => 'Show a Meeting/Webinar details - direct from Zoom',
			'textdomain'      => 'video-conferencing-with-zoom-api',
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_live_meeting' ]
		] );

		register_block_type( 'vczapi/join-via-browser', [
			'title'           => 'Zoom - Join via Browser',
			'attributes'      => [
				'preview'           => [
					'type'    => 'boolean',
					'default' => false
				],
				'shouldShow'        => [
					'type'    => 'object',
					'default' => [
						'label' => 'Meeting',
						'value' => 'meeting'
					]
				],
				'host'              => [
					'type' => 'object',
				],
				'selectedMeeting'   => [
					'type' => 'object',
				],
				'login_required'    => [
					'type'    => 'string',
					'default' => 'no'
				],
				'disable_countdown' => [
					'type'    => 'string',
					'default' => 'no'
				],
				'title'             => [
					'type'    => 'string',
					'default' => ''
				],
				'passcode'          => [
					'type'    => 'string',
					'default' => ''
				],
				'height'            => [
					'type'    => 'number',
					'default' => 500
				]
			],
			'category'        => 'vczapi-blocks',
			'icon'            => 'archive',
			'description'     => 'Show a Meeting/Webinar details - direct from Zoom',
			'textdomain'      => 'video-conferencing-with-zoom-api',
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_join_via_browser' ]
		] );

		register_block_type( 'vczapi/recordings', [
			'title'           => 'Zoom - Show Recordings',
			'attributes'      => [
				'shouldShow'      => [
					'type'    => 'object',
					'default' => [
						'label' => 'Meeting',
						'value' => 'meeting'
					]
				],
				'showBy'          => [
					'type'    => 'string',
					'default' => 'host'
				],
				'host'            => [
					'type' => 'object',
				],
				'selectedMeeting' => [
					'type' => 'object',
				],
				'downloadable'    => [
					'type'    => 'string',
					'default' => 'no'
				]
			],
			'category'        => 'vczapi-blocks',
			'icon'            => 'playlist-video',
			'description'     => 'Show a Meeting/Webinar details - direct from Zoom',
			'textdomain'      => 'video-conferencing-with-zoom-api',
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_recordings' ]
		] );

		register_block_type( 'vczapi/single-zoom-meeting', [
			'title'           => 'Zoom - Single Meeting Page',
			'category'        => 'vczapi-blocks',
			'icon'            => 'dashicons-text-page',
			'description'     => 'Single Zoom Meeting Page',
			'textdomain'      => 'video-conferencing-with-zoom-api',
			'editor_script'   => 'vczapi-blocks',
			'editor_style'    => 'vczapi-blocks-style',
			'render_callback' => [ $this, 'render_single_meeting' ]
		] );
	}

	/**
	 * Render block template from here.
	 *
	 * @return false|string|void
	 */
	public function render_single_meeting() {
		global $post;
		if ( ! empty( $post ) && $post->post_type == 'zoom-meetings' ) {
			$template = vczapi_get_single_or_zoom_template( $post );

			ob_start();
			include $template;

			return ob_get_clean();
		}
	}

	/**
	 * Get a scalar block attribute without display-oriented sanitization.
	 *
	 * @param mixed  $attributes Block attributes.
	 * @param string $key        Attribute key.
	 * @param string $default    Default value.
	 *
	 * @return string
	 */
	private function get_scalar_attribute( mixed $attributes, string $key, string $default = '' ): string {
		if (
			! is_array( $attributes )
			|| ! isset( $attributes[ $key ] )
			|| is_array( $attributes[ $key ] )
			|| is_object( $attributes[ $key ] )
		) {
			return $default;
		}

		return (string) $attributes[ $key ];
	}

	/**
	 * Get a nested scalar block attribute.
	 *
	 * @param mixed  $attributes Block attributes.
	 * @param string $key        Attribute key.
	 * @param string $nested_key Nested key.
	 * @param string $default    Default value.
	 *
	 * @return string
	 */
	private function get_nested_scalar_attribute( mixed $attributes, string $key, string $nested_key = 'value', string $default = '' ): string {
		if (
			! is_array( $attributes )
			|| ! isset( $attributes[ $key ] )
			|| ! is_array( $attributes[ $key ] )
			|| ! isset( $attributes[ $key ][ $nested_key ] )
			|| is_array( $attributes[ $key ][ $nested_key ] )
			|| is_object( $attributes[ $key ][ $nested_key ] )
		) {
			return $default;
		}

		return (string) $attributes[ $key ][ $nested_key ];
	}

	/**
	 * Get a positive integer block attribute.
	 *
	 * @param mixed  $attributes Block attributes.
	 * @param string $key        Attribute key.
	 * @param int    $default    Default value.
	 *
	 * @return int
	 */
	private function get_int_attribute( mixed $attributes, string $key, int $default = 0 ): int {
		if (
			! is_array( $attributes )
			|| ! isset( $attributes[ $key ] )
			|| is_array( $attributes[ $key ] )
			|| is_object( $attributes[ $key ] )
		) {
			return $default;
		}

		return absint( $attributes[ $key ] );
	}

	/**
	 * Get an allowlisted scalar block attribute.
	 *
	 * @param mixed  $attributes     Block attributes.
	 * @param string $key            Attribute key.
	 * @param array  $allowed_values Allowed values.
	 * @param string $default        Default value.
	 *
	 * @return string
	 */
	private function get_allowed_attribute( mixed $attributes, string $key, array $allowed_values, string $default = '' ): string {
		$value = $this->get_scalar_attribute( $attributes, $key, $default );

		return in_array( $value, $allowed_values, true ) ? $value : $default;
	}

	/**
	 * Get an allowlisted nested block attribute.
	 *
	 * @param mixed  $attributes     Block attributes.
	 * @param string $key            Attribute key.
	 * @param array  $allowed_values Allowed values.
	 * @param string $default        Default value.
	 *
	 * @return string
	 */
	private function get_allowed_nested_attribute( mixed $attributes, string $key, array $allowed_values, string $default = '' ): string {
		$value = $this->get_nested_scalar_attribute( $attributes, $key, 'value', $default );

		return in_array( $value, $allowed_values, true ) ? $value : $default;
	}

	/**
	 * Get a numeric identifier from a nested block attribute.
	 *
	 * @param mixed  $attributes Block attributes.
	 * @param string $key        Attribute key.
	 * @param string $default    Default value.
	 *
	 * @return string
	 */
	private function get_nested_numeric_id_attribute( mixed $attributes, string $key, string $default = '' ): string {
		$value = $this->get_nested_scalar_attribute( $attributes, $key, 'value', $default );

		return preg_replace( '/[^0-9]/', '', $value );
	}

	/**
	 * Get a sanitized Zoom host identifier.
	 *
	 * @param mixed  $attributes Block attributes.
	 * @param string $key        Attribute key.
	 * @param string $default    Default value.
	 *
	 * @return string
	 */
	private function get_nested_host_id_attribute( mixed $attributes, string $key, string $default = '' ): string {
		$value = $this->get_nested_scalar_attribute( $attributes, $key, 'value', $default );

		return preg_replace( '/[^A-Za-z0-9_\-@.]/', '', $value );
	}

	/**
	 * Get a Zoom meeting passcode.
	 *
	 * Zoom passcodes are limited to 10 characters and may contain special
	 * characters. Avoid display-oriented sanitizers here.
	 *
	 * @param mixed  $attributes Block attributes.
	 * @param string $key        Attribute key.
	 *
	 * @return string
	 */
	private function get_passcode_attribute( mixed $attributes, string $key ): string {
		$passcode = $this->get_scalar_attribute( $attributes, $key, '' );

		$passcode = str_replace(
			[ "\r", "\n", "\t", ']' ],
			'',
			$passcode
		);

		return function_exists( 'mb_substr' ) ? mb_substr( $passcode, 0, 10 ) : substr( $passcode, 0, 10 );
	}

	/**
	 * Build a safely quoted shortcode attribute fragment.
	 *
	 * This is shortcode-context escaping, not HTML attribute escaping.
	 *
	 * @param string $name  Shortcode attribute name.
	 * @param mixed  $value Shortcode attribute value.
	 *
	 * @return string
	 */
	private function shortcode_attribute( string $name, mixed $value ): string {
		$name  = sanitize_key( $name );
		$value = (string) $value;

		$value = str_replace(
			[ "\r", "\n", "\t", ']' ],
			' ',
			$value
		);

		if ( false === strpos( $value, '"' ) ) {
			return ' ' . $name . '="' . $value . '"';
		}

		if ( false === strpos( $value, "'" ) ) {
			return " " . $name . "='" . $value . "'";
		}

		$value = str_replace( '"', '', $value );

		return ' ' . $name . '="' . $value . '"';
	}

	/**
	 * Get all host helper.
	 *
	 * @since   3.7.5
	 * @updated N/A
	 */
	public function get_hosts() {
		$host_name = filter_input( INPUT_GET, 'host' );
		$host_name = is_scalar( $host_name ) ? (string) $host_name : '';
		$users     = video_conferencing_zoom_api_get_user_transients();

		$hosts = [];
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$first_name = ! empty( $user->first_name ) ? $user->first_name . ' ' : '';
				$last_name  = ! empty( $user->last_name ) ? $user->last_name . ' ' : '';
				$username   = $first_name . $last_name . '(' . $user->email . ')';

				if ( ! empty( $host_name ) ) {
					preg_match( '/' . preg_quote( $host_name, '/' ) . '/', $username, $matches );
					if ( ! empty( $matches ) ) {
						$hosts[] = [ 'label' => $username, 'value' => $user->id ];
					}
				} else {
					$hosts[] = [ 'label' => $username, 'value' => $user->id ];
				}
			}
		}

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
	 * Get all live meetings helper.
	 *
	 * @since   3.7.5
	 * @updated N/A
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

	/**
	 * Render list of meetings.
	 *
	 * @param mixed $attributes Block attributes.
	 *
	 * @return string
	 */
	public function render_list_meetings( $attributes ): string {
		$shortcode_type = $this->get_allowed_attribute( $attributes, 'shortcodeType', [ 'meeting', 'webinar' ], 'meeting' );
		$shortcode      = ( 'webinar' === $shortcode_type ) ? 'zoom_list_webinars' : 'zoom_list_meetings';

		$posts_to_show = $this->get_int_attribute( $attributes, 'postsToShow', 0 );
		if ( ! empty( $posts_to_show ) ) {
			$shortcode .= $this->shortcode_attribute( 'per_page', $posts_to_show );
		}

		$order_by = $this->get_allowed_attribute( $attributes, 'orderBy', [ 'ASC', 'DESC', 'asc', 'desc' ], '' );
		if ( ! empty( $order_by ) ) {
			$shortcode .= $this->shortcode_attribute( 'order', strtoupper( $order_by ) );
		}

		$show_filter = $this->get_allowed_attribute( $attributes, 'showFilter', [ 'yes', 'no' ], 'yes' );
		if ( ! empty( $show_filter ) ) {
			$shortcode .= $this->shortcode_attribute( 'filter', $show_filter );
		}

		if ( isset( $attributes['selectedCategory'] ) && is_array( $attributes['selectedCategory'] ) && ! empty( $attributes['selectedCategory'] ) ) {
			$categories = [];
			foreach ( $attributes['selectedCategory'] as $category ) {
				if (
					! is_array( $category )
					|| empty( $category['value'] )
					|| is_array( $category['value'] )
					|| is_object( $category['value'] )
				) {
					continue;
				}

				$category_value = sanitize_text_field( (string) $category['value'] );
				if ( '' !== $category_value ) {
					$categories[] = $category_value;
				}
			}

			if ( ! empty( $categories ) ) {
				$shortcode .= $this->shortcode_attribute( 'category', implode( ',', $categories ) );
			}
		}

		$selected_author = $this->get_int_attribute( $attributes, 'selectedAuthor', 0 );
		if ( ! empty( $selected_author ) ) {
			$shortcode .= $this->shortcode_attribute( 'author', $selected_author );
		}

		$display_type = $this->get_allowed_attribute( $attributes, 'displayType', [ 'upcoming', 'past' ], '' );
		if ( ! empty( $display_type ) ) {
			$shortcode .= $this->shortcode_attribute( 'type', $display_type );
		}

		$columns = $this->get_int_attribute( $attributes, 'columns', 0 );
		if ( ! empty( $columns ) ) {
			$shortcode .= $this->shortcode_attribute( 'cols', $columns );
		}

		return do_shortcode( '[' . $shortcode . ']' );
	}

	/**
	 * Render just the post.
	 *
	 * @param mixed $attributes Block attributes.
	 *
	 * @return false|string
	 */
	public function render_meeting_post( $attributes ) {
		$shortcode = 'zoom_meeting_post';

		$post_id = $this->get_int_attribute( $attributes, 'postID', 0 );
		if ( ! empty( $post_id ) ) {
			$shortcode .= $this->shortcode_attribute( 'post_id', $post_id );
		}

		$template = $this->get_allowed_attribute( $attributes, 'template', [ 'boxed', 'none' ], 'none' );
		if ( ! empty( $template ) ) {
			$shortcode .= $this->shortcode_attribute( 'template', $template );
		}

		$description = ! empty( $attributes['description'] ) ? 'true' : 'false';
		$shortcode   .= $this->shortcode_attribute( 'description', $description );

		$countdown = ! empty( $attributes['countdown'] ) ? 'true' : 'false';
		$shortcode .= $this->shortcode_attribute( 'countdown', $countdown );

		$details   = ! empty( $attributes['details'] ) ? 'true' : 'false';
		$shortcode .= $this->shortcode_attribute( 'details', $details );

		ob_start();
		echo do_shortcode( '[' . $shortcode . ']' );

		return ob_get_clean();
	}

	/**
	 * Render directly from API.
	 *
	 * @param mixed $attributes Block attributes.
	 *
	 * @return false|string
	 */
	public function render_live_meeting( $attributes ) {
		ob_start();

		$should_show = $this->get_allowed_nested_attribute( $attributes, 'shouldShow', [ 'meeting', 'webinar' ], 'meeting' );
		$shortcode   = ( 'webinar' === $should_show ) ? 'zoom_api_webinar' : 'zoom_api_link';

		$selected_meeting = $this->get_nested_numeric_id_attribute( $attributes, 'selectedMeeting' );
		if ( ! empty( $selected_meeting ) ) {
			$shortcode .= ( 'webinar' === $should_show )
				? $this->shortcode_attribute( 'webinar_id', $selected_meeting )
				: $this->shortcode_attribute( 'meeting_id', $selected_meeting );
		}

		$link_only = $this->get_allowed_attribute( $attributes, 'link_only', [ 'yes', 'no' ], 'no' );
		if ( ! empty( $link_only ) ) {
			$shortcode .= $this->shortcode_attribute( 'link_only', $link_only );
		}

		echo do_shortcode( '[' . $shortcode . ']' );

		return ob_get_clean();
	}

	/**
	 * Render host meeting list.
	 *
	 * @param mixed $attributes Block attributes.
	 *
	 * @return false|string
	 */
	public function render_host_meeting_list( $attributes ) {
		$should_show = $this->get_allowed_nested_attribute( $attributes, 'shouldShow', [ 'meeting', 'webinar' ], 'meeting' );
		$host        = $this->get_nested_host_id_attribute( $attributes, 'host' );
		$shortcode   = ( 'webinar' === $should_show ) ? 'zoom_list_host_webinars' : 'zoom_list_host_meetings';

		ob_start();

		if ( ! empty( $host ) ) {
			echo do_shortcode( '[' . $shortcode . $this->shortcode_attribute( 'host', $host ) . ']' );
		}

		return ob_get_clean();
	}

	/**
	 * Embed join via browser.
	 *
	 * @param mixed $attributes Block attributes.
	 *
	 * @return string
	 */
	public function render_join_via_browser( $attributes ): string {
		$shortcode_args = '';

		$selected_meeting = $this->get_nested_numeric_id_attribute( $attributes, 'selectedMeeting' );
		if ( ! empty( $selected_meeting ) ) {
			$shortcode_args .= $this->shortcode_attribute( 'meeting_id', $selected_meeting );
		}

		$login_required = $this->get_allowed_attribute( $attributes, 'login_required', [ 'yes', 'no' ], 'no' );
		if ( ! empty( $login_required ) ) {
			$shortcode_args .= $this->shortcode_attribute( 'login_required', $login_required );
		}

		$disable_countdown = $this->get_allowed_attribute( $attributes, 'disable_countdown', [ 'yes', 'no' ], 'no' );
		if ( ! empty( $disable_countdown ) ) {
			$shortcode_args .= $this->shortcode_attribute( 'disable_countdown', $disable_countdown );
		}

		$passcode = $this->get_passcode_attribute( $attributes, 'passcode' );
		if ( '' !== $passcode ) {
			$shortcode_args .= $this->shortcode_attribute( 'passcode', $passcode );
		}

		$should_show = $this->get_allowed_nested_attribute( $attributes, 'shouldShow', [ 'meeting', 'webinar' ], 'meeting' );
		if ( 'webinar' === $should_show ) {
			$shortcode_args .= $this->shortcode_attribute( 'webinar', 'yes' );
		}

		ob_start();

		echo do_shortcode( '[zoom_join_via_browser iframe="no"' . $shortcode_args . ']' );

		return ob_get_clean();
	}

	/**
	 * Render Recordings.
	 *
	 * @param mixed $attributes Block attributes.
	 *
	 * @return false|string
	 */
	public function render_recordings( $attributes ): false|string {
		ob_start();

		$shortcode = '';
		$show_by   = $this->get_allowed_attribute( $attributes, 'showBy', [ 'host', 'meeting' ], 'host' );

		if ( 'host' === $show_by ) {
			$host = $this->get_nested_host_id_attribute( $attributes, 'host' );
			if ( ! empty( $host ) ) {
				$shortcode = 'zoom_recordings' . $this->shortcode_attribute( 'host_id', $host );
			}
		} else {
			$selected_meeting = $this->get_nested_numeric_id_attribute( $attributes, 'selectedMeeting' );
			if ( ! empty( $selected_meeting ) ) {
				$shortcode = 'zoom_recordings_by_meeting' . $this->shortcode_attribute( 'meeting_id', $selected_meeting );
			}
		}

		if ( ! empty( $shortcode ) ) {
			$downloadable = $this->get_allowed_attribute( $attributes, 'downloadable', [ 'true', 'false', 'yes', 'no' ], 'no' );
			$maybe        = ( 'true' === $downloadable || 'yes' === $downloadable ) ? 'yes' : 'no';
			$shortcode    .= $this->shortcode_attribute( 'downloadable', $maybe );

			echo do_shortcode( '[' . $shortcode . ']' );
		}

		return ob_get_clean();
	}
}