<?php

namespace Codemanas\VczApi\Admin\Foundation\Events;

use Codemanas\VczApi\Admin\Repository\SettingsRepository;
use Codemanas\VczApi\Data\Metastore;
use Codemanas\VczApi\Helpers\Config;

/**
 * Delete Meeting Call
 */
class DeleteMeeting {

	protected string $postType;
	private static ?self $instance = null;

	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	public function __construct() {
		$this->postType = Config::get( 'post_type' );

		add_action( 'before_delete_post', [ $this, 'delete' ] );
	}

	/**
	 * Delete the Custom post type alongside zoom meeting
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function delete( int $post_id ): void {
		if ( get_post_type( $post_id ) !== $this->postType || ! empty( SettingsRepository::getSetting( 'delete_zoom_meeting' ) ) ) {
			return;
		}

		$meeting_id      = Metastore::getPostMeta( $post_id, 'meeting_id' );
		$meeting_details = Metastore::getPostMeta( $post_id, 'meeting_zoom_details' );

		if ( empty( $meeting_id ) ) {
			return;
		}

		do_action( 'vczapi_before_delete_meeting', $meeting_id );

		$is_webinar = is_array( $meeting_details ) && isset( $meeting_details['meeting_type'] ) && $meeting_details['meeting_type'] === "webinar";
		if ( $is_webinar ) {
			zoom_conference_v2()->webinars()->delete( $meeting_id );
		} else {
			zoom_conference_v2()->meetings()->delete( $meeting_id );
		}

		do_action( 'vczapi_after_delete_meeting' );
	}
}