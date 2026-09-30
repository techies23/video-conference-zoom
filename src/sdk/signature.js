import config from './config';

/**
 * Requests a Meeting SDK signature from the plugin REST route.
 *
 * Falls back to the legacy admin-ajax `get_auth` action so cached pages and
 * hosts that block the REST namespace keep working during the deprecation
 * window for the old handler.
 *
 * @return {Promise<{signature: string}>} Resolves with the SDK signature.
 * @throws {Error} When the request fails or the response has no signature.
 */
export async function fetchSignature() {
	const payload = {
		meetingNumber: config.meetingNumber,
	};

	const result = await postRest( config.restUrl, payload, config.restNonce ).catch( () =>
		postAdminAjax( config.meetingNumber )
	);

	if ( ! result || ! result.success || ! result.data || ! result.data.sig ) {
		const message = ( result && result.data && result.data.message ) || 'Could not authorise this meeting.';
		throw new Error( message );
	}

	return { signature: result.data.sig };
}

/**
 * Ask the REST route for a signature.
 *
 * @param {string} url     Route URL.
 * @param {Object} body    Payload to send.
 * @param {string} [nonce] Optional X-WP-Nonce value.
 * @return {Promise<Object>} Parsed JSON response.
 */
async function postRest( url, body, nonce ) {
	if ( ! url ) {
		throw new Error( 'No REST route configured for the signature request.' );
	}

	const headers = { 'Content-Type': 'application/json' };

	if ( nonce ) {
		headers[ 'X-WP-Nonce' ] = nonce;
	}

	const response = await fetch( url, {
		method: 'POST',
		credentials: 'same-origin',
		headers,
		body: JSON.stringify( body ),
	} );

	return response.json();
}

/**
 * Fall back to the legacy admin-ajax handler.
 *
 * The handler reads its input with filter_input(INPUT_POST), so the payload has
 * to be form encoded rather than JSON.
 *
 * @param {string} meetingNumber Zoom meeting number.
 * @return {Promise<Object>} Parsed JSON response.
 */
async function postAdminAjax( meetingNumber ) {
	const url = ajaxUrl();

	if ( ! url ) {
		throw new Error( 'No signature endpoint is configured.' );
	}

	const body = new window.FormData();
	body.append( 'action', 'get_auth' );
	body.append( 'meeting_id', meetingNumber );

	if ( config.restNonce ) {
		body.append( 'zvc_security', config.restNonce );
	}

	const response = await fetch( url, {
		method: 'POST',
		credentials: 'same-origin',
		body,
	} );

	return response.json();
}

/**
 * Resolve the admin-ajax endpoint.
 *
 * @return {string} Admin-ajax URL.
 */
function ajaxUrl() {
	if ( config.ajaxUrl ) {
		return config.ajaxUrl;
	}

	if ( typeof window.vczapiJvb === 'object' && window.vczapiJvb.ajaxUrl ) {
		return window.vczapiJvb.ajaxUrl;
	}

	return '';
}

export default fetchSignature;
