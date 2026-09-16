export const initUserSync = () => {
    const syncBtn = document.getElementById('vczapi-sync-users');
    const statusEl = document.getElementById('vczapi-sync-status');

    if (!syncBtn || !statusEl) return;

    const {ajaxurl, nonce, i18n} = window.vczapi_ajax || {};

    const setSyncingState = () => {
        syncBtn.classList.add('disabled');
        syncBtn.textContent = i18n.syncing;
        statusEl.classList.remove('error');
        statusEl.textContent = i18n.syncing;
    };

    const setResetState = () => {
        syncBtn.classList.remove('disabled');
        syncBtn.textContent = i18n.syncNow;
    };

    const handleSync = async (e) => {
        e.preventDefault();

        if (syncBtn.classList.contains('disabled')) return;

        setSyncingState();

        try {
            // WordPress admin-ajax expects URL-encoded form data
            const body = new URLSearchParams({
                action: 'vczapiSyncZoomUsers',
                security: nonce,
            });

            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: body.toString(),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();

            if (data?.success) {
                statusEl.textContent = i18n.done
                    .replace('{users}', data.data.synced)
                    .replace('{pages}', data.data.pages);
                setResetState();

                setTimeout(() => window.location.reload(), 2000);
            } else {
                const message = data?.data?.message || i18n.error;
                statusEl.classList.add('error');
                statusEl.textContent = message;
                setResetState();
            }
        } catch (error) {
            statusEl.classList.add('error');
            statusEl.textContent = i18n.error;
            setResetState();
        }
    };

    syncBtn.addEventListener('click', handleSync);
};