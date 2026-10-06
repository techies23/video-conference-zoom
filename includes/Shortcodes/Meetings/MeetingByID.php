<?php

namespace Codemanas\VczApi\Shortcodes\Meetings;

use Codemanas\VczApi\Shortcodes\Assets;
use Codemanas\VczApi\Shortcodes\Utils\AttributeSanitizer;
use Codemanas\VczApi\Shortcodes\Utils\Support;

class MeetingByID {

	public function render( $atts ): string {
		$atts = shortcode_atts(
			[
				'meeting_id' => '',
				'link_only'  => 'no',
			],
			$atts,
			'zoom_api_link'
		);

		$meeting_id = absint( $atts['meeting_id'] );
		$link_only  = AttributeSanitizer::yes_no( $atts['link_only'] );

		if ( empty( $meeting_id ) ) {
			return Support::no_id_error( __( 'No meeting id set in the shortcode', 'video-conferencing-with-zoom-api' ) );
		}

		Assets::enqueue();

		//Check meeting validation first.
		$meeting = zoom_conference_v2()->meetings()->get( $meeting_id );

		ob_start();
		if ( is_wp_error( $meeting ) ) {
			echo "<p>" . $meeting->get_error_message() . "</p>";
		} else {
			vczapi_get_template( 'shortcode/meeting-by-id.php', true, false, $meeting );
		}

		return (string) ob_get_clean();
	}

	private static ?MeetingByID $instance = null;

	public static function get_instance(): ?MeetingByID {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}