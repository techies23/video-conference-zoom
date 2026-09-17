<?php

namespace Codemanas\VczApi\Admin\Interface;

interface IZoomEvent {

	/**
	 * Extract event-type specific fields from a normalized fields array.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function getTypeSpecificFields( array $fields ): array;

	public function syncWithApi( \WP_Post $post, array $payload, string $zoom_id ): ?array;
}