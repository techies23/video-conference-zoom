<?php

namespace Codemanas\VczApi\Admin\Interface;

interface IZoomEvent {
	public function getTypeSpecificFields(): array;

	public function syncWithApi( \WP_Post $post, array $payload, string $zoom_id ): ?array;
}