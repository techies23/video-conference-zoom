export const initMeetingSync = () => {
    const fetchBtn = document.getElementById('vczapi-sync-fetch');
    const userSelect = document.getElementById('vczapi-sync-user-id');
    const resultsEl = document.querySelector('.vczapi-sync__results');
    const statusEl = document.getElementById('vczapi-sync-status');

    if (!fetchBtn || !userSelect || !resultsEl || !statusEl) return;

    const { ajaxurl, nonce } = window.vczapi_ajax || {};
    const { i18n = {} } = window.vczapi_sync || {};

    const post = async (params) => {
        const body = new URLSearchParams({
            action: 'vczapi_sync_user',
            security: nonce,
            ...params,
        });

        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: body.toString(),
        });

        return response.json();
    };

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const setStatus = (message, isError = false) => {
        statusEl.classList.toggle('error', isError);
        statusEl.textContent = message;
    };

    const renderMeetings = (meetings) => {
        resultsEl.innerHTML = '';

        if (!Array.isArray(meetings) || meetings.length === 0) {
            setStatus(i18n.noMeetingsFound);
            return;
        }

        setStatus(`${i18n.totalNotSynced}: ${meetings.length}`);

        const table = document.createElement('table');
        table.className = 'widefat striped vczapi-sync__meetings-table';

        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th>ID</th>
                <th>${escapeHtml(i18n.topic)}</th>
                <th>${escapeHtml(i18n.startTime)}</th>
                <th></th>
            </tr>
        `;
        table.appendChild(thead);

        const tbody = document.createElement('tbody');

        meetings.forEach((meeting) => {
            const row = document.createElement('tr');
            row.dataset.meetingId = meeting.id;

            const syncBtn = document.createElement('button');
            syncBtn.type = 'button';
            syncBtn.className = 'button button-primary vczapi-sync__meeting-sync';
            syncBtn.textContent = i18n.syncBtn;
            syncBtn.addEventListener('click', () => handleSync(meeting, row));

            row.innerHTML = `
                <td>${escapeHtml(meeting.id)}</td>
                <td>${escapeHtml(meeting.topic)}</td>
                <td>${escapeHtml(meeting.start_time || '')}</td>
            `;

            const actionsCell = document.createElement('td');
            actionsCell.appendChild(syncBtn);
            row.appendChild(actionsCell);

            tbody.appendChild(row);
        });

        table.appendChild(tbody);
        resultsEl.appendChild(table);
    };

    const handleFetch = async () => {
        const userId = userSelect.value;

        if (!userId) {
            setStatus(i18n.selectUser, true);
            return;
        }

        fetchBtn.classList.add('disabled');
        fetchBtn.textContent = i18n.beforeSync;
        setStatus(i18n.beforeSync);

        try {
            const data = await post({ type: 'check', user_id: userId });

            if (!data.success) {
                setStatus(data?.data?.message || data?.data || i18n.error, true);
                return;
            }

            renderMeetings(data?.data?.meetings || []);
        } catch (error) {
            setStatus(i18n.error, true);
        } finally {
            fetchBtn.classList.remove('disabled');
            fetchBtn.textContent = i18n.fetchMeetings;
        }
    };

    const handleSync = async (meeting, row) => {
        const syncBtn = row.querySelector('.vczapi-sync__meeting-sync');

        syncBtn.classList.add('disabled');
        syncBtn.textContent = i18n.syncStart;
        setStatus(i18n.syncStart);

        try {
            const data = await post({ type: 'sync', meeting_id: meeting.id });

            if (!data.success) {
                const message = data?.data?.msg || data?.data?.message || i18n.syncError;
                setStatus(message, true);
                return;
            }

            setStatus(`${i18n.syncCompleted} ${data?.data?.msg || ''}`);
            row.remove();

            if (resultsEl.querySelectorAll('tbody tr').length === 0) {
                resultsEl.innerHTML = '';
            }
        } catch (error) {
            setStatus(i18n.error, true);
        }
    };

    fetchBtn.addEventListener('click', handleFetch);
};