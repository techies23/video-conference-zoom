<?php

namespace Codemanas\VczApi\Admin\Interfaces;

interface IZoomEvent {
	public function getTypeSpecificFields(): array;

	public function syncWithApi( \WP_Post $post, array $payload, string $zoom_id ): ?object;
}