<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

use Codemanas\VczApi\Data\Metastore;
use WP_Post;

/**
 * Exposes zoom-meetings meta to the WP REST API so the block editor can
 * read/write meeting fields natively (via useEntityProp / core-data).
 *
 * Only the user-editable keys are registered as REST meta. Server-managed
 * outcome data (Zoom IDs, join/start URLs, raw Zoom response) is exposed as
 * read-only REST fields so it can never be overwritten through the editor.
 *
 * @package Codemanas\VczApi\Admin\Foundation\PostType
 */
class ZoomMetaRegistration {

	private string $postType;

	public function __construct( string $postType ) {
		$this->postType = $postType;
	}

	public function register(): void {
		register_post_meta( $this->postType, 'vczapi_meeting_fields', [
			'type'              => 'object',
			'description'       => __( 'Zoom meeting configuration fields.', 'video-conferencing-with-zoom-api' ),
			'single'            => true,
			'show_in_rest'      => [
				'schema' => [
					'type'       => 'object',
					'properties' => $this->getMeetingFieldsSchemaProperties(),
				],
			],
			'sanitize_callback' => [ $this, 'sanitizeMeetingFields' ],
		] );

		register_post_meta( $this->postType, 'vczapi_meeting_type', [
			'type'         => 'string',
			'description'  => __( 'The meeting event type (meeting|webinar).', 'video-conferencing-with-zoom-api' ),
			'single'       => true,
			'show_in_rest' => true,
		] );

		// Read-only outcome data (server managed only). Not registered as meta so
		// it can never be echoed back / overwritten by the block editor.
		$this->registerReadOnlyFields();
	}

	private function registerReadOnlyFields(): void {
		$fields = [
			'vczapi_meeting_id'         => [ 'type' => 'string', 'description' => 'Zoom event ID.' ],
			'vczapi_meeting_join_url'   => [ 'type' => 'string', 'description' => 'Zoom join URL.' ],
			'vczapi_meeting_start_url'  => [ 'type' => 'string', 'description' => 'Zoom start URL.' ],
			'vczapi_meeting_start_date_utc' => [ 'type' => 'string', 'description' => 'Meeting start time in UTC.' ],
			'vczapi_meeting_zoom_details'   => [ 'type' => 'object', 'description' => 'Raw Zoom API response.' ],
		];

		foreach ( $fields as $field => $schema ) {
			register_rest_field( $this->postType, $field, [
				'get_callback' => function ( array $post ) use ( $field ) {
					$meta_key = substr( $field, strlen( 'vczapi_' ) );

					return Metastore::getPostMeta( (int) $post['id'], $meta_key );
				},
				'schema'       => $schema + [
						'context' => [ 'view', 'edit' ],
					],
			] );
		}
	}

	/**
	 * Sanitize the nested meeting fields object before persistence.
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function sanitizeMeetingFields( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		return MeetingValidator::normalize( $value );
	}

	/**
	 * REST schema properties for the vczapi_meeting_fields object.
	 *
	 * @return array
	 */
	private function getMeetingFieldsSchemaProperties(): array {
		$boolean = [ 'type' => [ 'string', 'null' ] ];

		return [
			'topic'                        => [ 'type' => 'string' ],
			'user_id'                      => [ 'type' => 'string' ],
			'agenda'                       => [ 'type' => 'string' ],
			'type'                         => [ 'type' => 'integer' ],
			'start_time'                   => [ 'type' => 'string', 'format' => 'date-time' ],
			'timezone'                     => [ 'type' => 'string' ],
			'duration'                     => [ 'type' => 'integer' ],
			'password'                     => [ 'type' => 'string', 'maxLength' => 10 ],
			'disable_waiting_room'         => $boolean,
			'waiting_room'                 => $boolean,
			'meeting_authentication'       => $boolean,
			'host_video'                   => $boolean,
			'auto_recording'               => [ 'type' => 'string' ],
			'alternative_hosts'            => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
			'join_before_host'             => $boolean,
			'jbh_time'                     => [ 'type' => 'integer' ],
			'participant_video'            => $boolean,
			'mute_upon_entry'              => $boolean,
			'site_option_logged_in'        => $boolean,
			'site_option_browser_join'     => $boolean,
			'site_option_enable_debug_log' => $boolean,
			'panelists_video'              => $boolean,
			'practice_session'             => $boolean,
			'hd_video'                     => $boolean,
			'allow_multiple_devices'       => $boolean,
		];
	}
}