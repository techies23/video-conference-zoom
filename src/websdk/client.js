import { ZoomMtg } from '@zoom/meetingsdk';
import defaultConfig from './config';
import { CLIENT_GLOBAL } from './contract';

/**
 * @typedef {Object} JoinParameters
 * @property {string} signature       Meeting SDK signature.
 * @property {string} sdkKey          Public SDK key the signature was made with.
 * @property {string} meetingNumber   Zoom meeting number.
 * @property {string} passWord        Meeting passcode, when one is required.
 * @property {string} registrantToken Zoom registrant token, when joining a registered meeting.
 * @property {string} userName        Display name.
 * @property {string} userEmail       Email, when the visitor supplied one.
 * @property {string} lang            BCP-47 locale for the SDK interface.
 */

/**
 * Build the ZoomMtg.init() options.
 *
 * @param {Object} config Resolved configuration.
 * @return {Object} Options for ZoomMtg.init().
 */
const buildInitOptions = ( config ) => ( {
	leaveUrl: config.leaveUrl || window.location.origin,
	patchJsMedia: true,
	isSupportAV: true,
	enableHD: false,
	// The helper page must be same-origin: the document is served with COEP
	// require-corp so that SharedArrayBuffer, and therefore the SDK, is
	// available at all.
	helper: config.helperUrl || undefined,
	...( config.initOptions || {} ),
} );

/**
 * Create the SDK client.
 *
 * @param {Object} [customConfig] Configuration overrides.
 * @return {Object} Frozen client API.
 */
export function createZoomClient( customConfig = defaultConfig ) {
	const config = { ...customConfig };
	let initialised = false;

	/**
	 * Join the meeting.
	 *
	 * @param {JoinParameters} parameters Resolved by the signature service.
	 * @return {Promise<void>} Resolves once the SDK has joined.
	 */
	const join = async ( parameters ) => {
		if ( initialised ) {
			return;
		}

		// `sdkKey` is required by ZoomMtg.join() as a separate argument even
		// though the signature already carries it. It is served by the signature
		// endpoint so it never has to be hardcoded into the bundle.
		if ( ! parameters.sdkKey ) {
			throw new Error(
				'The server did not return an SDK key, so this meeting cannot be joined.'
			);
		}

		// i18n.load resolves asynchronously. It used to be called without
		// awaiting, so a non-default locale raced the init sequence and the SDK
		// could fall back to English, or throw inside the SDK's own init.
		await ZoomMtg.i18n.load( parameters.lang || config.lang || 'en-US' );

		ZoomMtg.preLoadWasm();
		ZoomMtg.prepareWebSDK();
		ZoomMtg.init(buildInitOptions(config));

		initialised = true;

		ZoomMtg.join({
			meetingNumber: parameters.meetingNumber,
			userName: parameters.userName || '',
			userEmail: parameters.userEmail || '',
			signature: parameters.signature,
			sdkKey: parameters.sdkKey,
			passWord: parameters.passWord || '',
			registrantToken: parameters.registrantToken || '',
			zak: '',
		});
	};

	return Object.freeze( { join } );
}

const client = createZoomClient();

/**
 * Publish the client.
 *
 * Evaluated synchronously at module scope. `bootstrap.js` injects this bundle
 * and waits for the script's `load` event, which fires only after this file has
 * fully evaluated, so the global is guaranteed to exist by the time it is read.
 */
window[ CLIENT_GLOBAL ] = client;

export default client;
