<?php

namespace Codemanas\VczApi\Admin\Foundation\PostType;

use Codemanas\VczApi\Data\Metastore;
use WP_Query;

/**
 * One-time, idempotent migration of legacy `_meeting_fields` postmeta into the
 * canonical `vczapi_meeting_fields` key so existing meetings hydrate correctly
 * in both the block editor and classic metabox.
 *
 * Processed in small batches on admin page loads to avoid long requests.
 *
 * @package Codemanas\VczApi\Admin\Foundation\PostType
 */
class ZoomMetaBackfill {

	private const OPTION = 'vczapi_meeting_meta_backfill';
	private const BATCH_SIZE = 50;

	private string $postType;

	public function __construct( string $postType ) {
		$this->postType = $postType;
	}

	public function maybeBackfill(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_option( self::OPTION ) ) {
			return;
		}

		$this->processBatch();

		if ( $this->hasNoMoreLegacyRows() ) {
			update_option( self::OPTION, 1 );
		}
	}

	private function processBatch(): void {
		$ids = $this->getLegacyPostIds();
		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $post_id ) {
			$legacy = get_post_meta( $post_id, '_meeting_fields', true );
			if ( ! is_array( $legacy ) || empty( $legacy ) ) {
				continue;
			}

			$mapped = Metastore::meetingFieldCompat( $legacy, (int) $post_id );
			Metastore::setPostMeta( $post_id, 'meeting_fields', $mapped );

			$type = (int) ( $mapped['type'] ?? 1 );
			Metastore::setPostMeta( $post_id, 'meeting_type', ( $type === 2 ) ? 'webinar' : 'meeting' );
		}
	}

	/**
	 * @return int[]
	 */
	private function getLegacyPostIds(): array {
		$query = new WP_Query( [
			'post_type'      => $this->postType,
			'post_status'    => 'any',
			'posts_per_page' => self::BATCH_SIZE,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => '_meeting_fields',
					'compare' => 'EXISTS',
				],
				[
					'key'     => 'vczapi_meeting_fields',
					'compare' => 'NOT EXISTS',
				],
			],
		] );

		return array_map( 'intval', (array) $query->posts );
	}

	/**
	 * Verify no legacy-only rows remain so the migration flag can be set.
	 *
	 * @return bool
	 */
	private function hasNoMoreLegacyRows(): bool {
		$remaining = new WP_Query( [
			'post_type'      => $this->postType,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => '_meeting_fields',
					'compare' => 'EXISTS',
				],
				[
					'key'     => 'vczapi_meeting_fields',
					'compare' => 'NOT EXISTS',
				],
			],
		] );

		return empty( $remaining->posts );
	}
}