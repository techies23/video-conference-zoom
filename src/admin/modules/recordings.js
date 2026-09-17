const escapeHtml = (value) => {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
};

const filesizeConverter = (bytes, i18n) => {
    bytes = parseInt(bytes, 10) || 0;

    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' ' + i18n.formatGigaBytes;
    }

    if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' ' + i18n.formatMegaBytes;
    }

    if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' ' + i18n.formatKiloBytes;
    }

    if (bytes > 1) {
        return bytes + ' ' + i18n.formatBytes;
    }

    if (bytes === 1) {
        return bytes + ' ' + i18n.formatBytes;
    }

    return '0 ' + i18n.formatBytes;
};

const formatRecordingDate = (value) => {
    const date = new Date(value);
    if (isNaN(date.getTime())) {
        return escapeHtml(value || '');
    }

    const datePart = date.toLocaleDateString('en-US', {month: 'long', day: 'numeric', year: 'numeric'});
    const timePart = date.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'});

    return escapeHtml(`${datePart}, ${timePart}`);
};

const renderRows = (tbody, meetings, i18n) => {
    if (!meetings.length) {
        tbody.innerHTML = `
            <tr class="vczapi-recordings__table-row vczapi-recordings__table-row--empty">
                <td class="vczapi-recordings__table-cell vczapi-recordings__table-cell--empty" colspan="6">${escapeHtml(i18n.noRecordings)}</td>
            </tr>
        `;
        return;
    }

    let meetingCount = 0;
    let html = '';

    meetings.forEach((meeting) => {
        const hasFiles = Array.isArray(meeting.recording_files) && meeting.recording_files.length > 0;
        let filesHtml = '';

        if (hasFiles) {
            meeting.recording_files.forEach((file) => {
                filesHtml += `
                    <ul class="vczapi-recordings__file-list vczapi-recordings__file-list--${escapeHtml(file.id)}">
                        <li class="vczapi-recordings__file-item">
                            <strong>${escapeHtml(i18n.fileType)}:</strong> ${escapeHtml(file.file_type)}
                        </li>
                        <li class="vczapi-recordings__file-item">
                            <strong>${escapeHtml(i18n.fileSize)}:</strong> ${filesizeConverter(file.file_size, i18n)}
                        </li>
                        <li class="vczapi-recordings__file-item">
                            <strong>${escapeHtml(i18n.play)}:</strong>
                            <a href="${escapeHtml(file.play_url)}" class="vczapi-recordings__file-link" target="_blank">${escapeHtml(i18n.play)}</a>
                        </li>
                        <li class="vczapi-recordings__file-item">
                            <strong>${escapeHtml(i18n.download)}:</strong>
                            <a href="${escapeHtml(file.download_url)}" class="vczapi-recordings__file-link" target="_blank">${escapeHtml(i18n.download)}</a>
                        </li>
                    </ul>
                `;
            });
        }

        html += `
            <tr class="vczapi-recordings__table-row">
                <td class="vczapi-recordings__table-cell">${escapeHtml(meeting.id)}</td>
                <td class="vczapi-recordings__table-cell">${escapeHtml(meeting.topic)}</td>
                <td class="vczapi-recordings__table-cell">${escapeHtml(meeting.duration)}</td>
                <td class="vczapi-recordings__table-cell">${formatRecordingDate(meeting.start_time)}</td>
                <td class="vczapi-recordings__table-cell">${filesizeConverter(meeting.total_size, i18n)}</td>
                <td class="vczapi-recordings__table-cell">
                    ${hasFiles ? `
                        <a href="#TB_inline?width=600&height=550&inlineId=recording-${meetingCount}" class="thickbox vczapi-recordings__action-link">
                            ${escapeHtml(i18n.viewRecordings)}
                        </a>
                        <div id="recording-${meetingCount}" class="vczapi-recordings__modal" style="display:none;">
                            ${filesHtml}
                        </div>
                    ` : escapeHtml('N/A')}
                </td>
            </tr>
        `;
        meetingCount++;
    });

    tbody.innerHTML = html;
};

export const initRecordingsTable = () => {
    const table = document.getElementById('vczapi_recordings_table');
    if (!table) return;

    const tbody = document.getElementById('vczapi_recordings_table_body');
    const {ajaxurl, nonce} = window.vczapi_ajax || {};
    const {host_id: initialHostId, i18n} = window.vczapi_recordings || {};
    const hostSelect = document.querySelector('select[name="host_id"]');
    const dateForm = document.querySelector('.vczapi-recordings__date-form');
    const dateInput = document.getElementById('vczapi-check-recording-date');

    const setLoading = () => {
        tbody.innerHTML = `
            <tr class="vczapi-recordings__table-row vczapi-recordings__table-row--empty">
                <td class="vczapi-recordings__table-cell vczapi-recordings__table-cell--empty" colspan="6">${escapeHtml(i18n.loading)}</td>
            </tr>
        `;
    };

    const setMessage = (message, type = "") => {
        const rowClass = type === "error"
            ? "vczapi-table__row vczapi-table__row--error vczapi-table__row--empty"
            : "vczapi-table__row vczapi-table__row--empty";

        tbody.innerHTML = `
        <tr class="${rowClass}">
            <td class="vczapi-table__cell vczapi-table__cell--empty" colspan="6">${escapeHtml(message)}</td>
        </tr>
    `;
    };

    const loadRecordings = async (hostId, date) => {
        if (!hostId) {
            setMessage(i18n.noHost);
            return;
        }

        setLoading();

        const query = new URLSearchParams({
            action: 'vczapi_list_recordings',
            security: nonce,
            host_id: hostId,
        });

        if (date) {
            query.set('date', date);
        }

        try {
            const response = await fetch(`${ajaxurl}?${query.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data?.success) {
                renderRows(tbody, data.data.meetings || [], i18n);
            } else {
                setMessage(data?.data?.message || i18n.error, 'error');
            }
        } catch (error) {
            setMessage(i18n.error, 'error');
        }
    };

    const getSelectedHost = () => hostSelect?.value || '';

    if (dateForm) {
        dateForm.addEventListener('submit', (e) => {
            e.preventDefault();
            loadRecordings(getSelectedHost(), dateInput ? dateInput.value : '');
        });
    }

    if (hostSelect) {
        hostSelect.addEventListener('change', () => {
            loadRecordings(getSelectedHost(), dateInput ? dateInput.value : '');
        });
    }

    loadRecordings(initialHostId);
};