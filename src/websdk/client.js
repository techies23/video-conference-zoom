import {ZoomMtg} from '@zoom/meetingsdk';
import defaultConfig from './config';
import {fetchSignature} from './signature';

/**
 * Factory that creates a stateful, object-driven Zoom SDK client using closures.
 *
 * @param {Object} [customConfig=defaultConfig] Configuration overrides.
 * @return {Object} Client instance API.
 */
export function createZoomClient(customConfig = defaultConfig) {
    const config = {...customConfig};

    const fields = {
        userName: '',
        userEmail: '',
        passWord: '',
        lang: config.lang,
    };

    /**
     * Remove loading overlay.
     */
    const removeLoader = () => {
        document.getElementById('zvc-cover')?.remove();
    };

    /**
     * Ensure the target `#zmmtg-root` element exists.
     *
     * @return {HTMLElement}
     */
    const ensureRoot = () => {
        let root = document.getElementById('zmmtg-root');

        if (!root) {
            root = document.createElement('div');
            root.id = 'zmmtg-root';
            document.body.appendChild(root);
        }

        return root;
    };

    /**
     * Show fatal error notice.
     *
     * @param {Error|unknown} error
     */
    const showFatal = (error) => {
        const message =
            error instanceof Error ? error.message : 'Something went wrong while joining this meeting.';

        if (document.getElementById('zmmtg-root')?.children.length) {
            console.error('[Video Conferencing with Zoom API]', error);
            return;
        }

        removeLoader();

        const notice = document.createElement('div');
        notice.className = 'vczapi-join-error';
        notice.setAttribute('role', 'alert');
        notice.textContent = message;

        document.body.prepend(notice);
        console.error('[Video Conferencing with Zoom API]', error);
    };

    /**
     * Determine post-leave redirect URL.
     *
     * @return {string}
     */
    const resolveLeaveUrl = () => {
        if (config.leaveUrl) {
            return config.leaveUrl;
        }

        try {
            if (window.location !== window.parent.location) {
                return window.location.href;
            }
        } catch {
            // Cross-origin access threw exception
        }

        return window.location.origin;
    };

    /**
     * Construct parameters for ZoomMtg.init().
     *
     * @return {Object}
     */
    const buildInitOptions = () => {
        const leaveUrl = resolveLeaveUrl();

        return {
            leaveUrl,
            patchJsMedia: true,
            enableHD: true,
            isSupportAV: true,
            helper: config.helperUrl || undefined,
            ...(config.initOptions || {}),
        };
    };

    /**
     * Initialize dependencies and launch meeting.
     *
     * @return {Promise<void>}
     */
    const join = async () => {
        const container = ensureRoot();
        container.style.display = 'block';

        removeLoader();

        try {
            const {signature} = await fetchSignature();

            ZoomMtg.i18n.load(fields.lang || config.lang);

            await ZoomMtg.preLoadWasm();
            await ZoomMtg.prepareWebSDK();

            await ZoomMtg.init(buildInitOptions());

            await ZoomMtg.join({
                signature,
                meetingNumber: config.meetingNumber,
                passWord: config.passWord,
                userName: fields.userName,
                userEmail: fields.userEmail,
                registrantToken: config.registrantToken || '',
            });
        } catch (error) {
            showFatal(error);
            throw error;
        }
    };

    // Return frozen object interface (no `this` needed)
    return Object.freeze({
        fields,
        join,
        buildInitOptions,
        resolveLeaveUrl,
    });
}

// Global binding for backward compatibility
const clientInstance = createZoomClient();

window.VczapiWebSDK = clientInstance;
window.dispatchEvent(new window.CustomEvent('vczapi:meeting-sdk-ready'));

export default clientInstance;