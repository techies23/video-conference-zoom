/**
 * Typed access to the Join-via-Browser configuration published by PHP.
 *
 * PHP prints `window.vczapiJvb` as an inline script immediately *before* the
 * bootstrap bundle (see \Codemanas\VczApi\Browser\Assets::enqueue()), so reading
 * it at module scope is safe and needs no readiness polling.
 *
 * What is deliberately absent: the meeting password. It used to be published as
 * `passWord` and as a base64 `meeting_pwd` in the `zvc_ajx` global, which put the
 * host's passcode in page source for every visitor. The password is now released
 * only by the signature endpoint, and only to a caller that presented the join
 * token.
 */

/**
 * Shape of the injected configuration.
 *
 * @typedef {Object} JvbConfig
 * @property {string}  joinToken    Signed, expiring, meeting-bound join token.
 * @property {string}  signatureUrl REST route that exchanges the token for a signature.
 * @property {string}  restNonce    X-WP-Nonce for the REST route.
 * @property {string}  clientUrl    URL of the SDK client bundle, fetched on demand.
 * @property {string}  helperUrl    URL of the SDK cross-origin helper page.
 * @property {string}  leaveUrl     Where Zoom sends the user on leave.
 * @property {string}  lang         BCP-47 locale for the SDK interface.
 * @property {boolean} directJoin   Skip the form and join immediately.
 * @property {string}  userName     Pre-filled display name.
 * @property {string}  userEmail    Pre-filled email.
 * @property {boolean} hasPassword  Whether the host set a passcode.
 * @property {string}  sdkVersion   Version of the bundled Meeting SDK.
 */

const DEFAULTS = {
	joinToken: '',
	signatureUrl: '',
	restNonce: '',
	clientUrl: '',
	helperUrl: '',
	leaveUrl: '',
	lang: 'en-US',
	directJoin: false,
	userName: '',
	userEmail: '',
	hasPassword: false,
	sdkVersion: '',
};

/**
 * Resolve the configuration injected by PHP.
 *
 * @return {Object} The injected values, or an empty object when absent.
 */
const readInjectedConfig = () => {
	const injected = typeof window !== 'undefined' ? window.vczapiJvb : null;

	if ( ! injected || typeof injected !== 'object' ) {
		return {};
	}

	// Guard against `__proto__` reaching Object.assign via a polluted global.
	return { ...injected };
};

export const config = Object.freeze( { ...DEFAULTS, ...readInjectedConfig() } );

/**
 * Whether the configuration is complete enough to attempt a join.
 *
 * @param {JvbConfig} [candidate] Configuration to check.
 * @return {string[]} Names of the required fields that are absent.
 */
export const missingConfig = ( candidate = config ) => {
	const required = [ 'joinToken', 'signatureUrl', 'clientUrl' ];
	const absent = required.filter( ( key ) => ! candidate[ key ] );

	if ( ! candidate.helperUrl ) {
		absent.push( 'helperUrl' );
	}

	return absent;
};

export default config;
