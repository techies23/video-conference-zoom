import defaultConfig from './config';

const DEFAULT_SELECTORS = Object.freeze({
    form: '#vczapi-zoom-browser-meeting-join-form',
    joinButton: '#vczapi-zoom-browser-meeting-join-mtg',
    name: '#vczapi-jvb-display-name',
    email: '#vczapi-jvb-email',
    password: '#meeting_password',
    locale: '.meeting-locale',
    messages: '.vczapi-zoom-browser-meeting--info__browser',
    meetingRoot: '#vczapi-zoom-browser-meeting',
    zoomRoot: '#zmmtg-root',
});

export function createZoomBootstrap(
    customConfig = defaultConfig,
    customSelectors = DEFAULT_SELECTORS
) {

    const config = {...customConfig};
    const selectors = {...DEFAULT_SELECTORS, ...customSelectors};
    let sdkRequested = false;

    const showLoader = () => {
        if (document.getElementById('zvc-cover')) return;

        const cover = document.createElement('div');
        cover.id = 'zvc-cover';
        cover.setAttribute('role', 'status');
        cover.setAttribute('aria-live', 'polite');
        cover.setAttribute('aria-label', 'Loading the Zoom Meeting');

        document.body.appendChild(cover);
    };

    const removeLoader = () => {
        document.getElementById('zvc-cover')?.remove();
    };

    const showError = (message) => {
        const container = document.querySelector(selectors.messages);
        if (!container) return;

        container.textContent = message;
        container.classList.add('vczapi-jvb-error');
        container.classList.remove('vczapi-jvb-error--fatal');
    };

    const showFatal = (error) => {
        removeLoader();

        const message = error instanceof Error ? error.message : String(error);
        const root = document.querySelector(selectors.zoomRoot);

        if (root) {
            root.style.display = 'block';
            root.textContent = '';
        }

        const notice = document.createElement('div');
        notice.className = 'vczapi-jvb-fatal';
        notice.setAttribute('role', 'alert');
        notice.textContent = message;

        document.body.appendChild(notice);
    };

    const loadSdk = () => {
        if (window.VczapiMeeting) {
            return Promise.resolve();
        }

        if (sdkRequested) {
            return new Promise((resolve, reject) => {
                window.addEventListener('vczapi:meeting-sdk-ready', resolve, {once: true});
                window.addEventListener(
                    'vczapi:meeting-sdk-error',
                    (event) => reject(event.detail || new Error('The Zoom Meeting SDK failed to load.')),
                    {once: true}
                );
            });
        }

        sdkRequested = true;

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = config.bundleUrl;
            script.async = true;

            script.addEventListener('load', () => {
                if (window.VczapiMeeting) {
                    resolve();
                    return;
                }
                reject(new Error('The Zoom Meeting SDK loaded but did not initialise.'));
            });

            script.addEventListener('error', () => {
                window.dispatchEvent(
                    new window.CustomEvent('vczapi:meeting-sdk-error', {
                        detail: new Error('Could not download the Zoom Meeting SDK. Please check your connection and try again.'),
                    })
                );
                reject(new Error('Could not download the Zoom Meeting SDK.'));
            });

            window.addEventListener('vczapi:meeting-sdk-ready', resolve, {once: true});

            document.head.appendChild(script);
        });
    };

    const startJoin = async (userName, userEmail, passWord, lang) => {
        showLoader();

        const meetingRoot = document.querySelector(selectors.meetingRoot);
        if (meetingRoot) {
            meetingRoot.remove();
        }

        try {
            await loadSdk();

            const client = window.VczapiMeeting;
            if (!client) {
                throw new Error('The Zoom Meeting SDK failed to initialise.');
            }

            client.fields = {userName, userEmail, passWord, lang};
            await client.join({userName, userEmail, passWord});
        } catch (error) {
            showFatal(error);
        }
    };

    const handleJoinClick = () => {
        const name = document.querySelector(selectors.name);
        const email = document.querySelector(selectors.email);
        const password = document.querySelector(selectors.password);
        const locale = document.querySelector(selectors.locale);

        if (name && name.value.trim() === '') {
            showError('Please enter your name to join.');
            return;
        }

        if (email && email.value.trim() === '') {
            showError('Please enter your email to join.');
            return;
        }

        if (password && password.value.trim() === '') {
            showError('Please enter the meeting password to join.');
            return;
        }

        startJoin(
            name ? name.value.trim() : '',
            email ? email.value.trim() : '',
            password ? password.value : config.passWord,
            locale ? locale.value : config.lang
        );
    };

    const init = () => {
        const button = document.querySelector(selectors.joinButton);

        if (!button) {
            return;
        }

        button.addEventListener('click', (event) => {
            event.preventDefault();
            handleJoinClick();
        });

        const form = document.querySelector(selectors.form);
        if (form) {
            form.addEventListener('submit', (event) => event.preventDefault());
        }

        if (config.directJoin) {
            startJoin(config.userName, config.userEmail, config.passWord, config.lang);
        }
    };

    // Return object instance wrapping all functions
    return Object.freeze({
        init,
        onJoinClick: handleJoinClick,
        startJoin,
        loadSdk,
        showError,
        showFatal,
    });
}

// Auto-run bootstrap instance
const bootstrap = createZoomBootstrap();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => bootstrap.init());
} else {
    bootstrap.init();
}

export default bootstrap;