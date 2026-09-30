/**
 * Build smoke test for the Zoom Meeting SDK bundle.
 *
 * The bundle statically imports @zoom/meetingsdk, whose published UMD build
 * externalises react, redux and redux-thunk. Declaring those as webpack
 * `externals` made the emitted script dereference bare globals and die with
 * "ReferenceError: React is not defined" before any plugin code ran, which is
 * how the whole Join-via-Browser page broke.
 *
 * The bundle is evaluated inside jsdom against a real DOM. Anything the SDK
 * touches beyond that (WebRTC, wasm, workers) is a browser-only path that
 * cannot run here, so the assertions are scoped to what is verifiable in Node:
 * the bundle must load, must not depend on missing globals, and must expose
 * window.VczapiMeeting.
 *
 * Usage: node tests/js/sdk-bundle.smoke.js [path-to-bundle]
 */

const path = require( 'path' );
const { pathToFileURL } = require( 'url' );
const { JSDOM } = require( 'jsdom' );
const { IDBFactory, IDBKeyRange } = require( 'fake-indexeddb' );

const BUNDLE_ARG = process.argv[ 2 ] || 'dist/vendor/zoom/websdk/zoom-meeting.bundle.js';
const BUNDLE = path.resolve( process.cwd(), BUNDLE_ARG );

const PLUGIN_URL = 'https://example.test/wp-content/plugins/video-conferencing-with-zoom-api';

const dom = new JSDOM(
	`<!DOCTYPE html><html lang="en-US"><head></head><body>
		<div id="zmmtg-root"></div>
	</body></html>`,
	{
		url: `${ PLUGIN_URL }/zoom-meetings/?type=meeting&join=abc`,
		// The SDK's own prebuilt webpack runtime derives its publicPath from
		// document.currentScript.src, so the bundle has to be loaded as an
		// external script. Inline scripts leave currentScript.src empty and the
		// runtime throws "Automatic publicPath is not supported in this browser".
		runScripts: 'dangerously',
		resources: 'usable',
		pretendToBeVisual: true,
	}
);

const { window } = dom;

// jsdom implements neither the User Timing API that the SDK's telemetry
// wrappers call during init, nor IndexedDB which its storage layer opens.
if ( typeof window.performance?.mark !== 'function' ) {
	const marks = [];
	window.performance = Object.assign( window.performance || {}, {
		mark: ( name ) => marks.push( { name, startTime: marks.length } ),
		measure: () => undefined,
		getEntries: () => [],
		getEntriesByName: () => [],
		clearMarks: () => marks.splice( 0, marks.length ),
		clearMeasures: () => undefined,
	} );
}

if ( typeof window.indexedDB === 'undefined' ) {
	window.indexedDB = new IDBFactory();
	window.IDBKeyRange = IDBKeyRange;
}

// Keep node's process reachable for the SDK's bundled `util` shim without
// letting it shadow the real global the test harness needs.
window.process = { env: {}, pid: 0, nextTick: ( fn ) => fn(), browser: true, platform: 'browser' };
window.vczapiJvb = {
	meetingNumber: '1234567890',
	leaveUrl: `${ PLUGIN_URL }/thanks`,
	helperUrl: `${ PLUGIN_URL }/dist/vendor/zoom/websdk/helper.html`,
	lang: 'en-US',
};

let readyDispatched = false;
window.addEventListener( 'vczapi:meeting-sdk-ready', () => {
	readyDispatched = true;
} );

let failure = null;

window.addEventListener( 'error', ( event ) => {
	failure = event.error || new Error( event.message );
} );

const script = window.document.createElement( 'script' );
script.src = pathToFileURL( BUNDLE ).href;
script.addEventListener( 'error', () => {
	failure = failure || new Error( 'Could not load the bundle from disk.' );
} );
window.document.head.appendChild( script );

// jsdom fetches and evaluates external scripts asynchronously, so the event
// loop has to stay free while the bundle runs.
function waitForBundle( timeoutMs = 20000 ) {
	return new Promise( ( resolve ) => {
		const started = Date.now();

		( function poll() {
			if ( window.VczapiMeeting || failure || Date.now() - started > timeoutMs ) {
				resolve();
				return;
			}
			setTimeout( poll, 25 );
		} )();
	} );
}

const MISSING_GLOBALS = [ 'React', 'Redux', 'ReduxThunk' ];

async function report() {
	await waitForBundle();

	// Errors thrown inside the jsdom realm are not `instanceof` the host
	// ReferenceError, so match on the name.
	const missingGlobal =
		failure?.name === 'ReferenceError' &&
		MISSING_GLOBALS.some( ( name ) => failure.message.includes( `${ name } is not defined` ) );

	const results = [
		[
			'bundle has no missing-global ReferenceError',
			! missingGlobal,
			failure ? `${ failure.name }: ${ failure.message }` : '',
		],
		[
			'window.VczapiMeeting is exposed',
			typeof window.VczapiMeeting === 'object' && window.VczapiMeeting !== null,
			'',
		],
		[ 'join() is callable', typeof window.VczapiMeeting?.join === 'function', '' ],
		[ 'ready event dispatched', readyDispatched, '' ],
	];

	if ( typeof window.VczapiMeeting?.buildInitOptions === 'function' ) {
		const options = window.VczapiMeeting.buildInitOptions();
		results.push( [ 'init() enables patchJsMedia', options.patchJsMedia === true, '' ] );
		results.push( [ 'init() passes the SDK helper page', !! options.helper, '' ] );
		results.push( [ 'init() uses the configured leaveUrl', options.leaveUrl === `${ PLUGIN_URL }/thanks`, '' ] );
	}

	// If the SDK reached a browser-only code path, the client assertions above
	// are meaningless, so report why rather than silently passing.
	if ( failure ) {
		console.log( `NOTE  evaluation stopped early: ${ failure.name }: ${ failure.message }` );
	}

	let failed = 0;

	for ( const [ label, ok, detail ] of results ) {
		if ( ! ok ) {
			failed++;
		}
		console.log( `${ ok ? 'PASS' : 'FAIL' }  ${ label }${ ok || ! detail ? '' : ` -> ${ detail }` }` );
	}

	process.exit( failed === 0 ? 0 : 1 );
}

report();
