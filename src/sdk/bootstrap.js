import config from './config';

/**
 * Join-via-Browser bootstrap.
 *
 * Deliberately contains no import of `@zoom/meetingsdk`: the SDK bundle is
 * roughly 5.5 MB, so it is only fetched once the visitor actually commits to
 * joining. The form page therefore ships a few kilobytes instead.
 */
const SELECTORS = {
	form: '#vczapi-zoom-browser-meeting-join-form',
	joinButton: '#vczapi-zoom-browser-meeting-join-mtg',
	name: '#vczapi-jvb-display-name',
	email: '#vczapi-jvb-email',
	password: '#meeting_password',
	locale: '.meeting-locale',
	messages: '.vczapi-zoom-browser-meeting--info__browser',
	meetingRoot: '#vczapi-zoom-browser-meeting',
	zoomRoot: '#zmmtg-root',
};

const bootstrap = {
	/** @type {boolean} */
	sdkRequested: false,

	/**
	 * Wire up the join form.
	 *
	 * @return {void}
	 */
	init() {
		const button = document.querySelector( SELECTORS.joinButton );

		if ( ! button ) {
			return;
		}

		button.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			this.onJoinClick();
		} );

		const form = document.querySelector( SELECTORS.form );

		if ( form ) {
			form.addEventListener( 'submit', ( event ) => event.preventDefault() );
		}

		if ( config.directJoin ) {
			this.startJoin( config.userName, config.userEmail, config.passWord, config.lang );
		}
	},

	/**
	 * Validate the form, then start the join flow.
	 *
	 * @return {void}
	 */
	onJoinClick() {
		const name = document.querySelector( SELECTORS.name );
		const email = document.querySelector( SELECTORS.email );
		const password = document.querySelector( SELECTORS.password );
		const locale = document.querySelector( SELECTORS.locale );

		if ( name && name.value.trim() === '' ) {
			this.showError( 'Please enter your name to join.' );
			return;
		}

		if ( email && email.value.trim() === '' ) {
			this.showError( 'Please enter your email to join.' );
			return;
		}

		if ( password && password.value.trim() === '' ) {
			this.showError( 'Please enter the meeting password to join.' );
			return;
		}

		this.startJoin(
			name ? name.value.trim() : '',
			email ? email.value.trim() : '',
			password ? password.value : config.passWord,
			locale ? locale.value : config.lang
		);
	},

	/**
	 * Remove the form, load the SDK, and hand over to the client.
	 *
	 * @param {string} userName  Display name.
	 * @param {string} userEmail Email address.
	 * @param {string} passWord  Meeting passcode.
	 * @param {string} lang      BCP-47 locale.
	 * @return {void}
	 */
	startJoin( userName, userEmail, passWord, lang ) {
		showLoader();

		const meetingRoot = document.querySelector( SELECTORS.meetingRoot );

		if ( meetingRoot ) {
			meetingRoot.remove();
		}

		this.loadSdk()
			.then( () => {
				const client = window.VczapiMeeting;

				if ( ! client ) {
					throw new Error( 'The Zoom Meeting SDK failed to initialise.' );
				}

				client.fields = { userName, userEmail, passWord, lang };
				client.join( { userName, userEmail, passWord } ).catch( ( error ) => this.showFatal( error ) );
			} )
			.catch( ( error ) => this.showFatal( error ) );
	},

	/**
	 * Inject the SDK bundle exactly once and resolve when it is ready.
	 *
	 * @return {Promise<void>} Resolves once `window.VczapiMeeting` exists.
	 */
	loadSdk() {
		if ( window.VczapiMeeting ) {
			return Promise.resolve();
		}

		if ( this.sdkRequested ) {
			return new Promise( ( resolve, reject ) => {
				window.addEventListener( 'vczapi:meeting-sdk-ready', resolve, { once: true } );
				window.addEventListener(
					'vczapi:meeting-sdk-error',
					( event ) => reject( event.detail || new Error( 'The Zoom Meeting SDK failed to load.' ) ),
					{ once: true }
				);
			} );
		}

		this.sdkRequested = true;

		return new Promise( ( resolve, reject ) => {
			const script = document.createElement( 'script' );

			script.src = config.bundleUrl;
			script.async = true;

			script.addEventListener( 'load', () => {
				if ( window.VczapiMeeting ) {
					resolve();
					return;
				}

				reject( new Error( 'The Zoom Meeting SDK loaded but did not initialise.' ) );
			} );

			script.addEventListener( 'error', () => {
				window.dispatchEvent(
					new window.CustomEvent( 'vczapi:meeting-sdk-error', {
						detail: new Error( 'Could not download the Zoom Meeting SDK. Please check your connection and try again.' ),
					} )
				);
				reject( new Error( 'Could not download the Zoom Meeting SDK.' ) );
			} );

			window.addEventListener( 'vczapi:meeting-sdk-ready', resolve, { once: true } );

			document.head.appendChild( script );
		} );
	},

	/**
	 * Show a field-level validation message.
	 *
	 * @param {string} message Message text.
	 * @return {void}
	 */
	showError( message ) {
		const container = document.querySelector( SELECTORS.messages );

		if ( ! container ) {
			return;
		}

		container.textContent = message;
		container.classList.add( 'vczapi-jvb-error' );
		container.classList.remove( 'vczapi-jvb-error--fatal' );
	},

	/**
	 * Show an unrecoverable error, replacing the page body.
	 *
	 * @param {Error|string} error Failure reason.
	 * @return {void}
	 */
	showFatal( error ) {
		removeLoader();

		const message = error && error.message ? error.message : String( error );

		const root = document.querySelector( SELECTORS.zoomRoot );

		if ( root ) {
			root.style.display = 'block';
			root.textContent = '';
		}

		const notice = document.createElement( 'div' );
		notice.className = 'vczapi-jvb-fatal';
		notice.setAttribute( 'role', 'alert' );
		notice.textContent = message;

		document.body.appendChild( notice );
	},
};

/**
 * Insert the loading overlay.
 *
 * @return {void}
 */
function showLoader() {
	if ( document.getElementById( 'zvc-cover' ) ) {
		return;
	}

	const cover = document.createElement( 'div' );
	cover.id = 'zvc-cover';
	cover.setAttribute( 'role', 'status' );
	cover.setAttribute( 'aria-live', 'polite' );
	cover.setAttribute( 'aria-label', 'Loading the Zoom Meeting' );

	document.body.appendChild( cover );
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

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', () => bootstrap.init() );
} else {
	bootstrap.init();
}

export default bootstrap;
