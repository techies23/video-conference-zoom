import defaultConfig, { missingConfig } from './config';

/**
 * Exchanges the join token for a Meeting SDK signature.
 *
 * The previous implementation tried the REST route first and silently fell back
 * to the `admin-ajax` `get_auth` action. That fallback sent the `wp_rest` nonce
 * as `zvc_security`, which is a different nonce action, so it could never have
 * validated — and the endpoint it targeted had its own `check_ajax_referer()`
 * call commented out, making it an open signing oracle.
 *
 * There is no fallback now. The REST route is the only way to get a signature,
 * and it requires the join token.
 */

/**
 * Extract a human readable message from a REST error payload.
 *
 * The endpoint returns both a top-level `message` and a `data.message`, and
 * wraps everything in a WP_Error shape, so check them in order.
 *
 * @param {*}      payload  Parsed response body.
 * @param {string} fallback Used when nothing usable was found.
 * @return {string} Human readable message.
 */
const messageFrom = ( payload, fallback ) => {
	if ( ! payload || typeof payload !== 'object' ) {
		return fallback;
	}

	if ( typeof payload.message === 'string' && payload.message ) {
		return payload.message;
	}

	if (
		payload.data &&
		typeof payload.data.message === 'string' &&
		payload.data.message
	) {
		return payload.data.message;
	}

	return fallback;
};

/**
 * Create the signature service.
 *
 * @param {Object} [customConfig] Configuration overrides.
 * @return {Object} Frozen service API.
 */
export function createSignatureService( customConfig = defaultConfig ) {
	const config = { ...customConfig };

	/**
	 * Request a signature for the join token.
	 *
	 * The response is the authoritative source of the join parameters: the
	 * server resolves the meeting number, the passcode (from the token when the
	 * visitor did not type one) and the registrant token. What the caller sends
	 * for the passcode is treated as a correction the server may accept, not as
	 * the value used.
	 *
	 * @param {Object} [overrides]           Values the visitor supplied.
	 * @param {string} [overrides.userName]  Display name.
	 * @param {string} [overrides.userEmail] Email address.
	 * @param {string} [overrides.passWord]  Passcode.
	 * @param {string} [overrides.lang]      Locale.
	 * @return {Promise<Object>} Join parameters, including `signature`.
	 * @throws {Error} When the request fails or the payload is unusable.
	 */
	const requestJoin = async ( overrides = {} ) => {
		if ( ! config.signatureUrl ) {
			throw new Error(
				'No signature endpoint is configured for this page.'
			);
		}

		if ( ! config.joinToken ) {
			throw new Error( 'This join link is missing its access token.' );
		}

		const headers = { 'Content-Type': 'application/json' };

		if ( config.restNonce ) {
			headers[ 'X-WP-Nonce' ] = config.restNonce;
		}

		let response;

		try {
			response = await fetch( config.signatureUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers,
				body: JSON.stringify( {
					token: config.joinToken,
					userName: overrides.userName || '',
					userEmail: overrides.userEmail || '',
					passWord: overrides.passWord || '',
					lang: overrides.lang || config.lang || '',
				} ),
			} );
		} catch {
			throw new Error(
				'Could not reach the server to authorise this meeting. Please check your connection and try again.'
			);
		}

		let payload = null;

		try {
			payload = await response.json();
		} catch {
			// Non-JSON response: a PHP notice or an HTML error page. Surface the
			// status rather than a parse error, which tells the user nothing.
			throw new Error(
				response.status === 0 || response.status >= 500
					? 'The server could not authorise this meeting. Please try again in a moment.'
					: 'This join link could not be authorised. Please ask the organiser for a new link.'
			);
		}

		if (
			! response.ok ||
			! payload ||
			payload.success !== true ||
			! payload.data
		) {
			throw new Error(
				messageFrom( payload, 'Could not authorise this meeting.' )
			);
		}

		const {
			signature,
			sdkKey,
			meetingNumber,
			passWord,
			registrantToken,
			userName,
			userEmail,
			lang,
		} = payload.data;

		if ( ! signature || ! meetingNumber ) {
			throw new Error(
				'The server returned an incomplete authorisation for this meeting.'
			);
		}

		return {
			signature,
			sdkKey,
			meetingNumber,
			passWord,
			registrantToken,
			userName,
			userEmail,
			lang,
		};
	};

	return Object.freeze( { requestJoin } );
}

const signatureService = createSignatureService();

export const requestJoin = signatureService.requestJoin;

/**
 * Report missing configuration up front, so a broken page fails with a clear
 * message instead of a confusing one after the visitor has clicked Join.
 */
export const assertConfigured = () => {
	const absent = missingConfig( defaultConfig );

	if ( absent.length ) {
		throw new Error(
			`The join page is not configured correctly (missing: ${ absent.join(
				', '
			) }).`
		);
	}
};

export default signatureService;
