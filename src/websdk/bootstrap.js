import defaultConfig, { missingConfig } from './config';
import { requestJoin, assertConfigured } from './signature';
import { CLIENT_GLOBAL } from './contract';

/**
 * Drives the Join-via-Browser form and hands off to the SDK client.
 *
 * This bundle is deliberately small and imports nothing from the Meeting SDK, so
 * a visitor who never clicks Join never downloads the ~5.7MB SDK payload. The
 * client bundle is fetched on demand by `loadClient()` below.
 *
 * Global name of the SDK client bundle, imported as a constant rather than
 * hard-coded so the two bundles cannot drift apart again.
 */
const SELECTORS = Object.freeze( {
	form: '#vczapi-zoom-browser-meeting-join-form',
	joinButton: '#vczapi-zoom-browser-meeting-join-mtg',
	name: '#vczapi-jvb-display-name',
	email: '#vczapi-jvb-email',
	password: '#meeting_password',
	locale: '.meeting-locale',
	status: '#vczapi-zoom-browser-meeting--status',
	fatal: '#vczapi-zoom-browser-meeting--fatal',
	zoomRoot: '#zmmtg-root',
} );

/**
 * Create the bootstrap.
 *
 * @param {Object} [customConfig]    Configuration overrides.
 * @param {Object} [customSelectors] Selector overrides.
 * @return {Object} Frozen bootstrap API.
 */
export function createZoomBootstrap(
	customConfig = defaultConfig,
	customSelectors = SELECTORS
) {
	const config = { ...customConfig };
	const selectors = { ...SELECTORS, ...customSelectors };
	let clientPromise = null;
	let joining = false;

	/**
	 * Show a message in the status area.
	 *
	 * @param {string}  message   Text to display.
	 * @param {boolean} [isError] Whether to render as an error.
	 */
	const show = ( message, isError = false ) => {
		const node = document.querySelector( selectors.status );

		if ( ! node ) {
			return;
		}

		node.textContent = message;
		node.classList.toggle( 'vczapi-jvb__notice--error', isError );
		node.classList.toggle( 'vczapi-jvb__notice--info', ! isError );
		node.hidden = false;
	};

	/**
	 * Hide the status area.
	 */
	const clear = () => {
		const node = document.querySelector( selectors.status );

		if ( node ) {
			node.textContent = '';
			node.hidden = true;
		}
	};

	/**
	 * Show an unrecoverable error.
	 *
	 * Errors used to be written into an element that the template only rendered
	 * when the site was *not* over HTTPS, so on any working HTTPS site every
	 * failure was silent.
	 *
	 * @param {Error|unknown} error The failure.
	 */
	const showFatal = ( error ) => {
		const message =
			error instanceof Error ? error.message : String( error );
		const node = document.querySelector( selectors.fatal );

		if ( node ) {
			node.textContent = message;
			node.hidden = false;
		}

		// eslint-disable-next-line no-console
		console.error( '[Video Conferencing with Zoom API]', error );

		setBusy( false );
	};

	/**
	 * Toggle the busy state of the submit button.
	 *
	 * @param {boolean} busy Whether a join is in flight.
	 */
	const setBusy = ( busy ) => {
		const button = document.querySelector( selectors.joinButton );

		if ( button ) {
			button.disabled = busy;
			button.classList.toggle( 'is-busy', busy );
		}
	};

	/**
	 * Ensure the SDK's mount point exists and is visible.
	 *
	 * @return {HTMLElement} The visible SDK mount point.
	 */
	const ensureRoot = () => {
		let root = document.querySelector( selectors.zoomRoot );

		if ( ! root ) {
			root = document.createElement( 'div' );
			root.id = 'zmmtg-root';
			document.body.appendChild( root );
		}

		// The SDK renders into its own root, so make sure exactly one exists and
		// is visible before init() is called.
		root.style.display = 'block';

		return root;
	};

	/**
	 * Load the SDK client bundle, at most once.
	 *
	 * @return {Promise<Object>} Resolves with the SDK client.
	 */
	const loadClient = () => {
		if ( window[ CLIENT_GLOBAL ] ) {
			return Promise.resolve( window[ CLIENT_GLOBAL ] );
		}

		if ( clientPromise ) {
			return clientPromise;
		}

		clientPromise = new Promise( ( resolve, reject ) => {
			const script = document.createElement( 'script' );
			script.src = config.clientUrl;
			script.async = true;

			// `load` fires only after the bundle has fully evaluated, which is
			// when it publishes window[CLIENT_GLOBAL]. Checking here rather than
			// trusting the event is what makes the contract safe.
			script.addEventListener( 'load', () => {
				if ( window[ CLIENT_GLOBAL ] ) {
					resolve( window[ CLIENT_GLOBAL ] );
					return;
				}

				reject(
					new Error(
						'The Zoom Meeting SDK loaded but did not initialise. Please try again.'
					)
				);
			} );

			script.addEventListener( 'error', () => {
				reject(
					new Error(
						'Could not download the Zoom Meeting SDK. Please check your connection and try again.'
					)
				);
			} );

			document.head.appendChild( script );
		} );

		// A failed load must not poison every later attempt.
		clientPromise = clientPromise.catch( ( error ) => {
			clientPromise = null;
			throw error;
		} );

		return clientPromise;
	};

	/**
	 * Read the visitor's input.
	 *
	 * @return {{userName: string, userEmail: string, passWord: string, lang: string}} Values.
	 */
	const readForm = () => {
		const value = ( selector ) => {
			const node = document.querySelector( selector );

			return node ? String( node.value || '' ).trim() : '';
		};

		return {
			userName: value( selectors.name ),
			userEmail: value( selectors.email ),
			passWord: value( selectors.password ),
			lang: value( selectors.locale ) || config.lang,
		};
	};

	/**
	 * Validate the form before doing any network work.
	 *
	 * @param {{userName: string, passWord: string}} values Form values.
	 * @return {string} Error message, or an empty string when valid.
	 */
	const validate = ( values ) => {
		if ( ! values.userName ) {
			return 'Please enter your name to join.';
		}

		// Only required when the visitor has to type it. The endpoint still
		// supplies the token's passcode, so this is not a hard gate.
		if ( config.hasPassword && ! values.passWord ) {
			return 'Please enter the meeting password to join.';
		}

		return '';
	};

	/**
	 * Run the join.
	 *
	 * @param {Object} [overrides] Values to use instead of reading the form.
	 * @return {Promise<void>} Resolves when the join has been attempted.
	 */
	const join = async ( overrides ) => {
		if ( joining ) {
			return;
		}

		joining = true;
		setBusy( true );
		clear();
		show( 'Loading the Zoom Meeting SDK…' );

		try {
			const client = await loadClient();

			ensureRoot();
			show( 'Connecting to the meeting…' );

			const parameters = await requestJoin( overrides || readForm() );

			show( 'Joining the meeting…' );
			await client.join( parameters );
		} catch ( error ) {
			showFatal( error );
		} finally {
			joining = false;
		}
	};

	/**
	 * Handle form submission.
	 *
	 * Bound to `submit` rather than to the button's `click`, so pressing Enter
	 * in a text field also submits.
	 *
	 * @param {Event} event Submit event.
	 */
	const onSubmit = ( event ) => {
		event.preventDefault();

		const values = readForm();
		const problem = validate( values );

		if ( problem ) {
			show( problem, true );
			return;
		}

		join( values );
	};

	/**
	 * Wire up the form and start a direct join if configured.
	 */
	const init = () => {
		try {
			assertConfigured();
		} catch ( error ) {
			showFatal( error );
			return;
		}

		const absent = missingConfig( config );

		if ( absent.length ) {
			showFatal(
				new Error(
					`The join page is not configured correctly (missing: ${ absent.join(
						', '
					) }).`
				)
			);
			return;
		}

		const form = document.querySelector( selectors.form );

		if ( form ) {
			form.addEventListener( 'submit', onSubmit );
		}

		if ( config.directJoin ) {
			// Direct join has no form to read, so the server-side token is the
			// only source of the passcode and display name.
			join( {
				userName: config.userName || '',
				userEmail: config.userEmail || '',
				passWord: '',
				lang: config.lang,
			} );
		}
	};

	return Object.freeze( {
		init,
		join,
		loadClient,
		onSubmit,
		show,
		showFatal,
		clear,
	} );
}

const bootstrap = createZoomBootstrap();

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', () => bootstrap.init() );
} else {
	bootstrap.init();
}

export default bootstrap;
