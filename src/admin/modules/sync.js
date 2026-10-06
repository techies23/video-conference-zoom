export const initMeetingSync = () => {
    const fetchBtn = document.getElementById('vczapi-sync-fetch');
    const userSelect = document.getElementById('vczapi-sync-user-id');
    const resultsEl = document.querySelector('.vczapi-sync__results');
    const statusEl = document.getElementById('vczapi-sync-status');

    if (!fetchBtn || !userSelect || !resultsEl || !statusEl) return;

    const {ajaxurl, nonce} = window.vczapi_ajax || {};
    const {i18n = {}} = window.vczapi_sync || {};

    let meetings = [];
    let selectedIds = new Set();
    let filters = {query: ''};

    let filterInput;
    let findInput;
    let findBtn;
    let syncSelectedBtn;
    let selectAllCheckbox;
    let tbody;

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

    const setStatus = (message, isError = false) => {
        statusEl.classList.toggle('error', isError);
        statusEl.textContent = message;
    };

    const updateSelection = () => {
        const count = selectedIds.size;
        if (syncSelectedBtn) {
            syncSelectedBtn.textContent = `${i18n.syncSelected} (${count})`;
            syncSelectedBtn.disabled = count === 0;
        }
        if (selectAllCheckbox) {
            const rows = getVisibleRows();
            const checked = rows.filter((row) => row.querySelector('input[data-select]').checked);
            selectAllCheckbox.checked = rows.length > 0 && checked.length === rows.length;
            selectAllCheckbox.indeterminate = checked.length > 0 && checked.length < rows.length;
        }
    };

    const getVisibleRows = () => Array.from(tbody?.rows || []);

    const format = (template, ...args) =>
        template.replace(/%(\d+)\$[ds]/g, (match, index) => args[Number(index) - 1] ?? match);

    const rowMatchesFilter = (row) => {
        if (!filters.query) return true;
        const query = filters.query.toLowerCase();
        const haystack = (row.dataset.meetingId + ' ' + row.dataset.topic).toLowerCase();

        return haystack.includes(query);
    };

    const applyFilter = () => {
        const rows = getVisibleRows();
        let visible = 0;

        rows.forEach((row) => {
            const matches = rowMatchesFilter(row);
            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });

        if (!filters.query) {
            setStatus(`${i18n.totalNotSynced}: ${rows.length}`);
        } else if (visible === 0) {
            setStatus(i18n.searchNoResults);
        } else {
            setStatus(`${visible} / ${rows.length}`);
        }

        updateSelection();
    };

    const getMeetingById = (id) => meetings.find((meeting) => String(meeting.id) === String(id));

    const removeRow = (row) => {
        const id = String(row.dataset.meetingId);
        selectedIds.delete(id);
        row.remove();
        updateSelection();

        if (getVisibleRows().length === 0) {
            resultsEl.innerHTML = '';
        }
    };

    const importMeeting = async (meeting) => {
        const data = await post({type: 'sync', meeting_id: meeting.id});

        if (!data.success) {
            return {ok: false, message: data?.data?.msg || data?.data?.message || i18n.syncError};
        }

        return {ok: true, message: data?.data?.msg || ''};
    };

    const renderMeetings = () => {
        if (!Array.isArray(meetings) || meetings.length === 0) {
            setStatus(i18n.noMeetingsFound);
            resultsEl.innerHTML = '';
            selectedIds.clear();
            return;
        }

        resultsEl.innerHTML = '';

        const toolbar = document.createElement('div');
        toolbar.className = 'vczapi-sync__toolbar';

        filterInput = document.createElement('input');
        filterInput.type = 'search';
        filterInput.className = 'vczapi-sync__filter-input';
        filterInput.placeholder = i18n.filterPlaceholder;

        findInput = document.createElement('input');
        findInput.type = 'text';
        findInput.className = 'vczapi-sync__find-input';
        findInput.placeholder = i18n.findByIdPlaceholder;

        findBtn = document.createElement('button');
        findBtn.type = 'button';
        findBtn.className = 'button vczapi-sync__find-btn';
        findBtn.textContent = i18n.findBtn;

        syncSelectedBtn = document.createElement('button');
        syncSelectedBtn.type = 'button';
        syncSelectedBtn.className = 'button button-primary vczapi-sync__sync-selected';
        syncSelectedBtn.textContent = `${i18n.syncSelected} (0)`;
        syncSelectedBtn.disabled = true;

        const selectAllLabel = document.createElement('label');
        selectAllLabel.className = 'vczapi-sync__select-all';
        selectAllCheckbox = document.createElement('input');
        selectAllCheckbox.type = 'checkbox';
        selectAllLabel.appendChild(selectAllCheckbox);
        selectAllLabel.appendChild(document.createTextNode(' ' + i18n.selectAll));

        toolbar.appendChild(filterInput);
        toolbar.appendChild(findInput);
        toolbar.appendChild(findBtn);
        toolbar.appendChild(syncSelectedBtn);
        toolbar.appendChild(selectAllLabel);

        const table = document.createElement('table');
        table.className = 'widefat striped vczapi-sync__meetings-table';

        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');

        const selectHeader = document.createElement('th');
        selectHeader.style.width = '30px';
        headerRow.appendChild(selectHeader);
        ['id', 'topic', 'startTime'].forEach((key) => {
            const th = document.createElement('th');
            th.textContent = key === 'id' ? 'ID' : i18n[key];
            headerRow.appendChild(th);
        });
        const actionsHeader = document.createElement('th');
        actionsHeader.style.width = '120px';
        headerRow.appendChild(actionsHeader);

        thead.appendChild(headerRow);
        table.appendChild(thead);

        tbody = document.createElement('tbody');
        table.appendChild(tbody);

        resultsEl.appendChild(toolbar);
        resultsEl.appendChild(table);

        meetings.forEach((meeting) => {
            const row = appendMeetingRow(meeting);
            row.dataset.meetingId = String(meeting.id);
        });

        applyFilter();

        filterInput.addEventListener('input', (e) => {
            filters.query = e.target.value.trim();
            applyFilter();
        });

        findBtn.addEventListener('click', handleFind);
        findInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') handleFind();
        });

        selectAllCheckbox.addEventListener('change', () => {
            const rows = getVisibleRows();
            rows.forEach((row) => {
                const checkbox = row.querySelector('input[data-select]');
                checkbox.checked = selectAllCheckbox.checked;
                toggleSelect(checkbox);
            });
            updateSelection();
        });

        syncSelectedBtn.addEventListener('click', handleBulkSync);
    };

    const toggleSelect = (checkbox) => {
        const row = checkbox.closest('tr');
        const id = String(row.dataset.meetingId);

        if (checkbox.checked) {
            selectedIds.add(id);
            row.classList.add('vczapi-sync__row-selected');
        } else {
            selectedIds.delete(id);
            row.classList.remove('vczapi-sync__row-selected');
        }
    };

    const appendMeetingRow = (meeting, options = {}) => {
        const row = document.createElement('tr');
        row.dataset.meetingId = String(meeting.id);
        row.dataset.topic = String(meeting.topic || '');

        const selectCell = document.createElement('td');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.dataset.select = '1';
        checkbox.disabled = Boolean(options.alreadyImported);
        checkbox.addEventListener('change', () => {
            toggleSelect(checkbox);
            updateSelection();
        });
        selectCell.appendChild(checkbox);

        row.appendChild(selectCell);

        row.appendChild(cell(String(meeting.id)));
        row.appendChild(cell(String(meeting.topic || '')));
        row.appendChild(cell(String(meeting.start_time || '')));

        const actionsCell = document.createElement('td');
        const syncBtn = document.createElement('button');
        syncBtn.type = 'button';
        syncBtn.className = 'button button-primary vczapi-sync__meeting-sync';
        syncBtn.textContent = options.alreadyImported ? i18n.alreadyImported : i18n.syncBtn;
        syncBtn.disabled = Boolean(options.alreadyImported);

        if (!options.alreadyImported) {
            syncBtn.addEventListener('click', () => handleSync(meeting, row));
        }

        actionsCell.appendChild(syncBtn);
        row.appendChild(actionsCell);

        if (options.highlight) {
            row.classList.add('vczapi-sync__row-highlight');
        }

        tbody.appendChild(row);

        return row;
    };

    const cell = (value) => {
        const td = document.createElement('td');
        td.textContent = value;
        return td;
    };

    const handleFind = async () => {
        const id = findInput.value.trim();
        if (!id) {
            setStatus(i18n.findByIdPlaceholder, true);
            return;
        }

        findBtn.classList.add('disabled');
        findBtn.textContent = i18n.beforeSync;
        setStatus(i18n.beforeSync);

        try {
            const data = await post({type: 'find', meeting_id: id});

            if (!data.success) {
                setStatus(data?.data?.msg || data?.data?.message || i18n.noMeetingFoundForId, true);
                return;
            }

            const meeting = data.data.meeting;
            const existingId = String(meeting.id);

            if (meetings.some((item) => String(item.id) === existingId)) {
                filters.query = existingId;
                filterInput.value = existingId;
                applyFilter();
                setStatus(`${i18n.totalNotSynced}: ${meetings.length}`);
                return;
            }

            meetings.push(meeting);
            appendMeetingRow(meeting, {
                alreadyImported: Boolean(data.data.already_imported),
                highlight: true,
            });
            applyFilter();
            setStatus(`${i18n.totalNotSynced}: ${meetings.length}`);
        } catch (error) {
            setStatus(i18n.error, true);
        } finally {
            findBtn.classList.remove('disabled');
            findBtn.textContent = i18n.findBtn;
        }
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
            const data = await post({type: 'check', user_id: userId});

            if (!data.success) {
                setStatus(data?.data?.message || data?.data || i18n.error, true);
                return;
            }

            selectedIds.clear();
            meetings = data?.data?.meetings || [];
            renderMeetings();
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
            const result = await importMeeting(meeting);

            if (!result.ok) {
                syncBtn.classList.remove('disabled');
                syncBtn.textContent = i18n.syncBtn;
                row.classList.add('vczapi-sync__row-error');
                setStatus(result.message, true);
                return;
            }

            setStatus(`${i18n.syncCompleted} ${result.message}`);
            removeRow(row);
        } catch (error) {
            syncBtn.classList.remove('disabled');
            syncBtn.textContent = i18n.syncBtn;
            setStatus(i18n.error, true);
        }
    };

    const handleBulkSync = async () => {
        const rows = getVisibleRows().filter((row) => {
            const checkbox = row.querySelector('input[data-select]');
            return checkbox && checkbox.checked && !checkbox.disabled;
        });

        if (rows.length === 0) {
            setStatus(i18n.noSelection, true);
            return;
        }

        const total = rows.length;
        let imported = 0;
        let failed = 0;

        fetchBtn.classList.add('disabled');
        syncSelectedBtn.disabled = true;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const meeting = getMeetingById(row.dataset.meetingId);
            const syncBtn = row.querySelector('.vczapi-sync__meeting-sync');

            setStatus(format(i18n.importingProgress, i + 1, total));

            try {
                const result = await importMeeting(meeting);

                if (!result.ok) {
                    failed++;
                    row.classList.add('vczapi-sync__row-error');
                    if (syncBtn) {
                        syncBtn.classList.remove('disabled');
                        syncBtn.textContent = i18n.syncBtn;
                    }
                    continue;
                }

                imported++;
                removeRow(row);
            } catch (error) {
                failed++;
                row.classList.add('vczapi-sync__row-error');
            }
        }

        fetchBtn.classList.remove('disabled');
        updateSelection();

        const summary = format(i18n.bulkCompleted, imported, failed);
        setStatus(summary);
    };

    fetchBtn.addEventListener('click', handleFetch);
};