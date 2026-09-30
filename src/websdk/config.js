/**
 * Typed access to the Join-via-Browser configuration that PHP injects.
 *
 * The previous implementation read the `zvc_ajx` global at module scope, which
 * meant the values were captured before the DOM was ready and any missing key
 * threw a TypeError during bundle evaluation.
 */

const globalScope = typeof window !== 'undefined' ? window : {};

/**
 * Shape of the injected configuration.
 *
 * @typedef {Object} JvbConfig
 * @property {string}  meetingNumber      Zoom meeting or webinar number.
 * @property {string}  [passWord]         Meeting passcode, when known.
 * @property {string}  [userName]         Pre-filled display name.
 * @property {string}  [userEmail]        Pre-filled email.
 * @property {string}  [leaveUrl]         Where Zoom sends the user on leave.
 * @property {string}  [lang]             BCP-47 locale for the SDK UI.
 * @property {boolean} [directJoin]       Skip the form and join immediately.
 * @property {string}  [restUrl]          Signature REST route.
 * @property {string}  [restNonce]        X-WP-Nonce for the REST route.
 * @property {string}  [ajaxUrl]          Legacy admin-ajax.php fallback.
 * @property {string}  [bundleUrl]        URL of the lazily loaded SDK bundle.
 * @property {string}  [helperUrl]        URL of the SDK helper page.
 * @property {string}  [registrantToken]  Zoom registrant `tk` when joining a registered meeting.
 * @property {Object}  [initOptions]      Extra ZoomMtg.init() options.
 */

/**
 * Configuration injected by the plugin template.
 *
 * @type {JvbConfig}
 */
export const config = Object.assign(
    {
        meetingNumber: '',
        passWord: '',
        userName: '',
        userEmail: '',
        leaveUrl: '',
        lang: 'en-US',
        directJoin: false,
        restUrl: '',
        restNonce: '',
        ajaxUrl: '',
        bundleUrl: '',
        helperUrl: '',
        registrantToken: '',
        initOptions: {},
    },
    globalScope.vczapiJvb || globalScope.zvc_ajx || {}
);

export default config;
