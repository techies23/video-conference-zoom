<?php

namespace Codemanas\VczApi\Admin\Foundation\Events;

use Codemanas\VczApi\Admin\Foundation\Utils\MeetingFormHandler;
use Codemanas\VczApi\Admin\Foundation\Utils\MeetingValidator;
use Codemanas\VczApi\Admin\Service\PostTypeSyncService;
use Codemanas\VczApi\Helpers\Config;
use WP_Error;
use WP_Post;
use WP_REST_Request;

class UpdateMeeting {

	protected string $postType;
	private static ?self $instance = null;

	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	public function __construct() {
		$this->postType = Config::get( 'post_type' );

		add_action( "save_post_{$this->postType}", [ $this, 'save' ], 10, 2 );
		add_filter( "rest_pre_insert_{$this->postType}", [ $this, 'preInsert' ], 10, 2 );
	}

	/**
	 * Save Request when post type is saved. Gather values and sync with meta fields and API.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function save( int $post_id, WP_Post $post ): void {
		if ( ! $this->isSaveRequestValid( $post_id ) ) {
			return;
		}

		$fields = MeetingFormHandler::gatherFromPost();
		( new PostTypeSyncService() )->sync( $post_id, $post, $fields, (int) $fields['type'] );
	}

	/**
	 * Validate data before insert.
	 *
	 * @param $prepared_post
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function preInsert( $prepared_post, WP_REST_Request $request ): mixed {
		if ( str_contains( $request->get_route(), '/autosaves' ) ) {
			return $prepared_post;
		}

		$meta           = (array) $request->get_param( 'meta' );
		$meeting_fields = $meta['vczapi_meeting_fields'] ?? null;

		if ( ! is_array( $meeting_fields ) || empty( $meeting_fields ) ) {
			return $prepared_post;
		}

		$errors = MeetingValidator::validate( MeetingValidator::normalize( $meeting_fields ) );
		if ( ! empty( $errors ) ) {
			return new WP_Error(
				'vczapi_meeting_validation',
				__( 'Zoom meeting details could not be saved. Please fix the highlighted fields and try again.', 'video-conferencing-with-zoom-api' ),
				[ 'errors' => $errors ]
			);
		}

		return $prepared_post;
	}

	/**
	 * Check capability.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	private function isSaveRequestValid( int $post_id ): bool {
		return current_user_can( 'edit_post', $post_id ) && ! wp_is_post_autosave( $post_id ) && ! wp_is_post_revision( $post_id );
	}
}