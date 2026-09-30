import { ZoomMtg } from '@zoom/meetingsdk';
import config from './config';
import { fetchSignature } from './signature';

/**
 * Zoom Meeting SDK client for the Join-via-Browser page.
 *
 * Loaded on demand: this module statically imports the SDK, so bundling it
 * produces the ~5.5 MB payload that `bootstrap.js` injects when the user
 * commits to joining.
 */
const VczapiMeeting = {
	/** @type {Object<string, string>} */
	fields: {
		userName: '',
		userEmail: '',
		passWord: '',
		lang: config.lang,
	},

	/**
	 * Fetch a signature and hand control to Zoom.
	 *
	 * @return {Promise<void>} Resolves once ZoomMtg.join() has been called.
	 */
	async join() {
		const container = ensureRoot();

		container.style.display = 'block';

		removeLoader();

		try {
			const { signature } = await fetchSignature();

			ZoomMtg.i18n.load( this.fields.lang || config.lang );

			// preLoadWasm pulls the audio/video codecs down before init so the
			// SDK does not stall on them mid-join.
			await ZoomMtg.preLoadWasm();

			// Downloads the component and language packages init() expects.
			await ZoomMtg.prepareWebSDK();

			await ZoomMtg.init( this.buildInitOptions() );

			await ZoomMtg.join( {
				signature,
				meetingNumber: config.meetingNumber,
				passWord: config.passWord,
				userName: this.fields.userName,
				userEmail: this.fields.userEmail,
				registrantToken: config.registrantToken || '',
			} );
		} catch ( error ) {
			showFatal( error );
			throw error;
		}
	},

	/**
	 * Assemble the ZoomMtg.init() argument.
	 *
	 * Any option supplied through the `vczapi_api_join_via_browser_params`
	 * filter wins, so integrations can enable new SDK features without a
	 * plugin update.
	 *
	 * @return {Object} Init options.
	 */
	buildInitOptions() {
		const leaveUrl = this.resolveLeaveUrl();

		return Object.assign(
			{
				leaveUrl,
				patchJsMedia: true,
				enableHD: true,
				isSupportAV: true,
				helper: config.helperUrl || undefined,
			},
			config.initOptions || {},
			{
				// Must always reflect the current join, never a filtered value.
				leaveUrl,
			}
		);
	},

	/**
	 * Work out where Zoom should send the user after they leave.
	 *
	 * @return {string} Leave URL.
	 */
	resolveLeaveUrl() {
		if ( config.leaveUrl ) {
			return config.leaveUrl;
		}

		try {
			if ( window.location !== window.parent.location ) {
				return window.location.href;
			}
		} catch ( error ) {
			// Cross-origin parent access throws; fall through to the current URL.
		}

		return window.location.origin;
	},
};

/**
 * Return the element the SDK renders into, creating it when the template did
 * not provide one.
 *
 * ZoomMtg.init() mounts the meeting UI into `#zmmtg-root`; the previous
 * implementation relied on prepareWebSDK() implicitly creating it during page
 * load, which no longer happens now that the SDK is loaded on demand.
 *
 * @return {HTMLElement} The SDK root element.
 */
function ensureRoot() {
	let root = document.getElementById( 'zmmtg-root' );

	if ( ! root ) {
		root = document.createElement( 'div' );
		root.id = 'zmmtg-root';
		document.body.appendChild( root );
	}

	return root;
}

/**
 * Remove the loading overlay.
 *
 * @return {void}
 */
function removeLoader() {
	const cover = document.getElementById( 'zvc-cover' );

	if ( cover ) {
		cover.remove();
	}
}

/**
 * Surface a join failure instead of leaving a blank page behind.
 *
 * @param {Error} error Failure raised while joining.
 * @return {void}
 */
function showFatal( error ) {
	const message =
		( error && error.message ) || 'Something went wrong while joining this meeting.';

	// Zoom renders its own errors from here on, so bail if its view is up.
	if ( document.getElementById( 'zmmtg-root' )?.children.length ) {		console.error( '[Video Conferencing with Zoom API]', error );
		return;
	}

	removeLoader();

	const notice = document.createElement( 'div' );
	notice.className = 'vczapi-join-error';
	notice.setAttribute( 'role', 'alert' );
	notice.textContent = message;

	document.body.prepend( notice );

	console.error( '[Video Conferencing with Zoom API]', error );
}

window.VczapiMeeting = VczapiMeeting;

window.dispatchEvent( new window.CustomEvent( 'vczapi:meeting-sdk-ready' ) );
