import defaultConfig from './config';

/**
 * Creates an object-driven Signature Service API using closure-based encapsulation.
 *
 * @param {Object} [customConfig=defaultConfig] Configuration overrides.
 * @return {Object} Signature service instance API.
 */
export function createSignatureService(customConfig = defaultConfig) {
    const config = {...customConfig};

    /**
     * Resolve the legacy WordPress admin-ajax endpoint URL.
     *
     * @return {string}
     */
    const resolveAjaxUrl = () => {
        if (config.ajaxUrl) {
            return config.ajaxUrl;
        }

        if (typeof window.vczapiJvb === 'object' && window.vczapiJvb.ajaxUrl) {
            return window.vczapiJvb.ajaxUrl;
        }

        return '';
    };

    /**
     * Issue a signature request to the WordPress REST API route.
     *
     * @param {string} url Route URL.
     * @param {Object} body JSON Payload to send.
     * @param {string} [nonce] Optional X-WP-Nonce value.
     * @return {Promise<Object>} Parsed JSON response.
     */
    const postRest = async (url, body, nonce) => {
        if (!url) {
            throw new Error('No REST route configured for the signature request.');
        }

        const headers = {'Content-Type': 'application/json'};

        if (nonce) {
            headers['X-WP-Nonce'] = nonce;
        }

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers,
            body: JSON.stringify(body),
        });

        return response.json();
    };

    /**
     * Fall back to the legacy admin-ajax handler using FormData.
     *
     * @param {string} meetingNumber Zoom meeting number.
     * @return {Promise<Object>} Parsed JSON response.
     */
    const postAdminAjax = async (meetingNumber) => {
        const url = resolveAjaxUrl();

        if (!url) {
            throw new Error('No signature endpoint is configured.');
        }

        const body = new window.FormData();
        body.append('action', 'get_auth');
        body.append('meeting_id', meetingNumber);

        if (config.restNonce) {
            body.append('zvc_security', config.restNonce);
        }

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            body,
        });

        return response.json();
    };

    /**
     * Request a Meeting SDK signature from the REST route with admin-ajax fallback.
     *
     * @return {Promise<{signature: string}>} Resolves with the SDK signature.
     * @throws {Error} When request fails or signature is missing.
     */
    const fetchSignature = async () => {
        const payload = {
            meetingNumber: config.meetingNumber,
        };

        const result = await postRest(config.restUrl, payload, config.restNonce).catch(() =>
            postAdminAjax(config.meetingNumber)
        );

        if (!result || !result.success || !result.data || !result.data.sig) {
            const message = (result && result.data && result.data.message) || 'Could not authorise this meeting.';
            throw new Error(message);
        }

        return {signature: result.data.sig};
    };

    // Return frozen object interface wrapping functional closures
    return Object.freeze({
        fetchSignature,
        postRest,
        postAdminAjax,
        resolveAjaxUrl,
    });
}

// Default instance export for direct consumption
const signatureService = createSignatureService();

export const fetchSignature = signatureService.fetchSignature;
export default fetchSignature;