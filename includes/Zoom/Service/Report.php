<?php


namespace Codemanas\VczApi\Zoom\Service;

use Codemanas\VczApi\Zoom\Http\Client;
use Codemanas\VczApi\Zoom\Payload\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\SchemaManager;
use WP_Error;

class Report extends BaseService {

	/** @var Client */
	protected Client $client;

	public function __construct( ?Client $client = null ) {
		$this->client = $client ?: new Client();
	}

	public function getDailyReport( array $params = array() ): WP_Error|array {
		$built = PayloadBuilder::build( SchemaManager::REPORT_DAILY, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}

	public function getUserReport( string $userId, string $from, string $to, array $params = array() ): WP_Error|array {
		$params['user_id'] = $userId;
		$params['from']    = $from;
		$params['to']      = $to;

		$built = PayloadBuilder::build( SchemaManager::REPORT_USER_MEETINGS, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}

	public function getMeetingParticipants( string $meetingId, array $params = array() ): WP_Error|array {
		$params['meeting_id'] = $meetingId;

		$built = PayloadBuilder::build( SchemaManager::REPORT_MEETING_PARTICIPANTS, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}

	public function getMeetingDetails( string $meetingId ): WP_Error|array {
		$params = array( 'meeting_id' => $meetingId );

		$built = PayloadBuilder::build( SchemaManager::REPORT_MEETING_DETAILS, $params );
		if ( is_wp_error( $built ) ) {
			return $built;
		}

		$prepared = $this->prepareFromBuilt( $built );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		return $this->client->request( $prepared['method'], $prepared['endpoint'], $prepared['query'] );
	}
}