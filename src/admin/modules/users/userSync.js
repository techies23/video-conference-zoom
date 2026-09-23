export const initUserSync = () => {
    const syncBtn = document.getElementById('vczapi-sync-users');
    const statusEl = document.getElementById('vczapi-sync-status');

    if (!syncBtn || !statusEl) return;

    const {ajaxurl, nonce} = window.vczapi_ajax || {};
    const {i18n} = window.vczapi_user_sync || {};

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
                action: 'vczapi_sync_zoom_users',
                security: nonce,
            });

            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: body.toString(),
            });

            const data = await response.json();

            if (data?.success) {
                statusEl.textContent = i18n.done
                    .replace('{users}', data.data.synced)
                    .replace('{pages}', data.data.pages);
                setResetState();

                // setTimeout(() => window.location.reload(), 2000);
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