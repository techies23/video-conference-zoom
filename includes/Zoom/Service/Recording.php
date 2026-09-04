<?php

namespace Codemanas\VczApi\Zoom\Service;

use Codemanas\VczApi\Zoom\Http\Client;
use Codemanas\VczApi\Zoom\Payload\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use WP_Error;

class Recording extends BaseService {

	/** @var Client */
	protected Client $client;

	/**
	 * Inject a client for execution or testing.
	 *
	 * @param Client|null $client
	 */
	public function __construct( Client $client = null ) {
		$this->client = $client ?: new Client();
	}

	/**
	 * List all cloud recordings of a user.
	 *
	 * @param array $params
	 * @return array|WP_Error
	 */
	public function list( array $params = array() ): WP_Error|array {
		$built = PayloadBuilder::build( SchemaManager::RECORDING_LIST, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['query'] = apply_filters( 'vczapi_recordings_list_params', $prepared['query'], $params );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::RECORDING_LIST, $params );
		}

		return $result;
	}

	/**
	 * Get all recording files of a specific meeting.
	 *
	 * @param   int|array|string  $meetingId  ID string, UUID, or array with parameters.
	 *
	 * @return array|WP_Error
	 */
	public function get( int|array|string $meetingId ): WP_Error|array {
		$params = is_array( $meetingId ) ? $meetingId : array( 'meeting_id' => $meetingId );

		$built = PayloadBuilder::build( SchemaManager::RECORDING_GET, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['query'] = apply_filters( 'vczapi_recordings_get_params', $prepared['query'], $params );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::RECORDING_GET, $params );
		}

		return $result;
	}

	/**
	 * Delete/trash all recording files of a meeting.
	 *
	 * @param string|int|array $meetingId ID string, UUID, or array with parameters.
	 * @param string           $action    'trash' or 'delete'.
	 * @return array|WP_Error
	 */
	public function delete( $meetingId, string $action = 'trash' ): WP_Error|array {
		$params           = is_array( $meetingId ) ? $meetingId : array( 'meeting_id' => $meetingId );
		$params['action'] = isset( $params['action'] ) ? $params['action'] : $action;

		$built = PayloadBuilder::build( SchemaManager::RECORDING_DELETE, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}

	/**
	 * Delete/trash a single recording file from a meeting.
	 *
	 * @param string|array $meetingId ID string/UUID or full parameters array.
	 * @param string|null  $fileId    Recording file ID (optional if passed in $meetingId array).
	 * @param string       $action    'trash' or 'delete'.
	 * @return array|WP_Error
	 */
	public function deleteFile( $meetingId, string $fileId = null, string $action = 'trash' ): WP_Error|array {
		$params = is_array( $meetingId ) ? $meetingId : array(
			'meeting_id' => $meetingId,
			'file_id'    => $fileId,
		);

		if ( ! isset( $params['action'] ) ) {
			$params['action'] = $action;
		}

		$built = PayloadBuilder::build( SchemaManager::RECORDING_FILE_DELETE, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}

	/**
	 * Recover/restore meeting recordings from the trash.
	 *
	 * @param   int|array|string  $meetingId  ID string, UUID, or array with parameters.
	 *
	 * @return array|WP_Error
	 */
	public function recover( int|array|string $meetingId ): WP_Error|array {
		$params           = is_array( $meetingId ) ? $meetingId : array( 'meeting_id' => $meetingId );
		$params['action'] = 'recover';

		$built = PayloadBuilder::build( SchemaManager::RECORDING_RECOVER, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared['body'] = apply_filters( 'vczapi_recordings_recover_payload', $prepared['body'], $params );

		$result = $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['body'] );

		if ( ! empty( $prepared['warnings'] ) ) {
			do_action( 'vczapi_payload_warnings', $prepared['warnings'], SchemaManager::RECORDING_RECOVER, $params );
		}

		return $result;
	}
}