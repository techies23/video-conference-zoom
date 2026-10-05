<?php

namespace Codemanas\VczApi\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shortcode tag registry.
 *
 * Owns the tag => callback map so that `Shortcodes` stays a bootstrap and the
 * supported public surface (the tag names below) is readable in one place.
 *
 * The Gutenberg blocks (`includes/Blocks/Blocks.php`) and the Elementor widgets
 * (`includes/Elementor/Widgets/`) both render through `do_shortcode()`, so these
 * tag names are a public API and must not be renamed.
 *
 * @package Codemanas\VczApi\Shortcodes
 */
class ShortcodeRegistry {

	/**
	 * @var array<string,callable>
	 */
	private array $shortcodes = [];

	public function __construct() {
		$meetings   = Meetings::get_instance();
		$webinars   = Webinars::get_instance();
		$recordings = Recordings::get_instance();
		$embed      = Embed::get_instance();

		$this->shortcodes = [
			//Meetings
			'zoom_api_link'              => [ $meetings, 'show_meeting_by_ID' ],
			'zoom_meeting_post'          => [ $meetings, 'show_meeting_by_postTypeID' ],
			'zoom_list_meetings'         => [ $meetings, 'list_cpt_meetings' ],
			'zoom_list_host_meetings'    => [ $meetings, 'list_live_host_meetings' ],

			//Embed Browser
			'zoom_join_via_browser'      => [ $embed, 'join_via_browser' ],

			//Webinars
			'zoom_api_webinar'           => [ $webinars, 'show_webinar_by_ID' ],
			'zoom_list_webinars'         => [ $webinars, 'list_cpt_webinars' ],
			'zoom_list_host_webinars'    => [ $webinars, 'list_live_host_webinars' ],

			//Recordings
			'zoom_recordings'            => [ $recordings, 'recordings_by_user' ],
			'zoom_recordings_by_meeting' => [ $recordings, 'recordings_by_meeting_id' ],
		];
	}

	/**
	 * Register every tag with WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( $this->shortcodes as $shortcode => $callback ) {
			add_shortcode( $shortcode, $callback );
		}
	}

	/**
	 * The registered tag => callback map.
	 *
	 * @return array<string,callable>
	 */
	public function all(): array {
		return $this->shortcodes;
	}
}