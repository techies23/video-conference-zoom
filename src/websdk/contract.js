/**
 * The contract between the bootstrap bundle and the SDK client bundle.
 *
 * This lives in its own module because both bundles need it, and `client.js`
 * imports the entire Meeting SDK. Importing the constant from `client.js`
 * directly would pull that dependency graph into the bootstrap bundle and
 * defeat the lazy loading that keeps the small bundle small.
 */

/**
 * Global name the SDK client bundle publishes itself on.
 *
 * The two bundles previously disagreed: `client.js` published
 * `window.VczapiWebSDK` while `bootstrap.js` polled `window.VczapiMeeting`.
 * Nothing ever defined `VczapiMeeting`, so every join failed with "the SDK
 * loaded but did not initialise" despite both bundles loading successfully.
 *
 * @type {string}
 */
export const CLIENT_GLOBAL = 'VczapiWebSDKClient';

export default CLIENT_GLOBAL;
