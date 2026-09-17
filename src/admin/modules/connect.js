export const initConnect = () => {
    const {ajaxurl, nonce} = window.vczapi_ajax || {};
    const {i18n} = window.vczapi_connect || {};

    if (!ajaxurl || !i18n) {
        return;
    }

    const form = document.getElementById('vczapi-connect-form');
    if (!form) {
        return;
    }

    const progress = document.getElementById('vczapi-connect-progress');
    const submitBtn = document.getElementById('vczapi-connect-submit');
    const editBtn = document.getElementById('vczapi-connect-edit');

    let submitting = false;

    const setProgress = (message, isError = false) => {
        progress.classList.toggle('vczapi-error', isError);
        progress.textContent = message;
		progress.style.display = 'inline-block';
    };

    const setSubmitting = (isSubmitting) => {
        submitting = isSubmitting;
        submitBtn.classList.toggle('disabled', isSubmitting);
        submitBtn.textContent = isSubmitting ? i18n.connecting : i18n.connect;
        form.classList.toggle('is-connecting', isSubmitting);
    };

    const markConnected = () => {
        form.setAttribute('data-connected', '1');
        form.classList.remove('is-editing');
    };

    const postFormData = async (body) => {
        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type':
                    'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: body.toString(),
        });

        return response.json();
    };

    const buildConnectBody = (formData) => {
        const nonceField = form.querySelector(
            'input[name="vczapi_zoom_connect_nonce"]'
        );
        formData.set('action', 'vczapi_connect_credentials');
        formData.set(
            'vczapi_zoom_connect_nonce',
            nonceField ? nonceField.value : ''
        );

        return new URLSearchParams(formData);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (submitting) {
            return;
        }

        setSubmitting(true);
        setProgress(i18n.saving);

        try {
            const connectBody = buildConnectBody(new FormData(form));
            const connectData = await postFormData(connectBody);

            if (!connectData?.success) {
                setProgress(
                    i18n.errorCode.replace(
                        '{message}',
                        connectData?.data?.message || i18n.error
                    ),
                    true
                );
                setSubmitting(false);

                return;
            }

            setProgress(i18n.syncing);

            const syncBody = new URLSearchParams({
                action: 'vczapi_sync_zoom_users',
                security: nonce,
            });
            const syncData = await postFormData(syncBody);

            markConnected();

            if (syncData?.success) {
                setProgress(
                    i18n.connected
                        .replace('{users}', syncData.data.synced)
                        .replace('{pages}', syncData.data.pages)
                );
            } else {
                setProgress(
                    i18n.syncFailed.replace(
                        '{message}',
                        syncData?.data?.message || i18n.error
                    ),
                    true
                );
            }
        } catch {
            setProgress(i18n.networkError, true);
        }

        setSubmitting(false);
    };

    const handleEdit = (e) => {
        e.preventDefault();

        const isEditing = form.classList.toggle('is-editing');
        if (editBtn) {
            editBtn.textContent = isEditing
                ? i18n.cancelEdit
                : i18n.editCredentials;
        }
    };

    form.addEventListener('submit', handleSubmit);
    if (editBtn) {
        editBtn.addEventListener('click', handleEdit);
    }
};
