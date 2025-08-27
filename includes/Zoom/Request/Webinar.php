<?php

namespace Codemanas\VczApi\Zoom\Request;

use Codemanas\VczApi\Zoom\PayloadBuilder;
use Codemanas\VczApi\Zoom\Schema\Webinar as WebinarSchema;
use Codemanas\VczApi\Zoom\SchemaRegistry;
use WP_Error;

class Webinar
{
    private bool $useLegacyApi;

    /** @var \Zoom_Video_Conferencing_Api|null */
    private $legacyApi;

    /** @var object|null Future modern client (to be implemented) */
    private $modernClient;

    private SchemaRegistry $schemas;
    private PayloadBuilder $builder;

    public function __construct(
        bool $useLegacyApi,
        $legacyApi,
        $modernClient,
        SchemaRegistry $schemas,
        PayloadBuilder $builder
    ) {
        $this->useLegacyApi  = $useLegacyApi;
        $this->legacyApi     = $legacyApi;
        $this->modernClient  = $modernClient;
        $this->schemas       = $schemas;
        $this->builder       = $builder;
    }

    /**
     * Create a Zoom webinar.
     * Legacy: extract userId for path; pass rest of payload to createAWebinar($userId, $data)
     */
    public function createWebinar(array $input)
    {
        $payload = $this->builder->build('webinar',WebinarSchema::WEBINAR_CREATE, $input);
        if ($payload instanceof WP_Error) {
            return $payload;
        }

        if ($this->useLegacyApi) {
            if (!$this->legacyApi || !method_exists($this->legacyApi, 'createAWebinar')) {
                return new WP_Error('legacy_api_unavailable', 'Legacy API client is not available.');
            }

            $userId = $payload['userId'] ?? null;
            if (empty($userId)) {
                return new WP_Error('missing_userId', 'userId is required for creating a webinar');
            }
            unset($payload['userId']);

            return $this->legacyApi->createAWebinar($userId, $payload);
        }

        return new WP_Error('not_implemented', 'Modern client for createWebinar is not implemented yet.');
    }

    /**
     * Update a Zoom webinar.
     * Legacy: extract webinar_id for path; pass payload to updateWebinar($webinarId, $data)
     *
     * @param string|int $webinarId
     */
    public function updateWebinar($webinarId, array $input)
    {
        $input = ['webinar_id' => (string)$webinarId] + $input;

        $payload = $this->builder->build('webinar',WebinarSchema::WEBINAR_UPDATE, $input);
        if ($payload instanceof WP_Error) {
            return $payload;
        }

        if ($this->useLegacyApi) {
            if (!$this->legacyApi || !method_exists($this->legacyApi, 'updateWebinar')) {
                return new WP_Error('legacy_api_unavailable', 'Legacy API client is not available.');
            }

            $id = (string)($payload['webinar_id'] ?? '');
            if ($id === '') {
                return new WP_Error('missing_webinar_id', 'webinar_id is required for updating a webinar');
            }
            unset($payload['webinar_id']);

            return $this->legacyApi->updateWebinar($id, $payload);
        }

        return new WP_Error('not_implemented', 'Modern client for updateWebinar is not implemented yet.');
    }
}