import { initDataTable } from '../components/datatable.js';
import { filesizeConverter, formatRecordingDate, escapeHtml } from "../../utils/helpers";

export const initRecordingsTable = () => {
    const container = document.querySelector('.vczapi-recordings-table');
    if (!container) return;

    const { ajaxurl, nonce } = window.vczapi_ajax || {};
    const { host_id: initialHostId, i18n = {} } = window.vczapi_recordings || {};

    const hostSelect = document.querySelector('select[name="host_id"]');
    const dateForm = document.querySelector('.vczapi-recordings__date-form');
    const dateInput = document.getElementById('vczapi-check-recording-date');

    let meetingModalCount = 0;

    const getSelectedHost = () => hostSelect?.value || initialHostId || '';
    const getSelectedDate = () => dateInput?.value || '';

    // Initialize the DataTable
    const [dtInstance] = initDataTable(container, {
        apiEndpoint: ajaxurl,
        apiAction: 'vczapi_list_recordings',
        nonce: nonce,
        initialParams: {
            host_id: getSelectedHost(),
            date: getSelectedDate()
        },

        // Strict boolean check: Prevents AJAX request if host_id is missing
        beforeFetch: (params) => {
            return Boolean(params && params.host_id);
        },

        columns: [
            { field: 'id', label: 'ID', sortable: false },
            { field: 'topic', label: 'Topic', sortable: true },
            { field: 'duration', label: 'Duration', sortable: false },
            {
                field: 'start_time',
                label: 'Start Time',
                sortable: true,
                render: (value) => formatRecordingDate(value)
            },
            {
                field: 'total_size',
                label: 'Total Size',
                sortable: false,
                render: (value) => filesizeConverter(value, i18n)
            },
            {
                field: 'recording_files',
                label: 'Actions',
                sortable: false,
                render: (files) => {
                    const hasFiles = Array.isArray(files) && files.length > 0;
                    if (!hasFiles) return 'N/A';

                    meetingModalCount++;
                    const modalId = `recording-${meetingModalCount}`;

                    const filesHtml = files.map((file) => `
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
                    `).join('');

                    return `
                        <a href="#TB_inline?width=600&height=550&inlineId=${modalId}" class="thickbox vczapi-recordings__action-link">
                            ${escapeHtml(i18n.viewRecordings)}
                        </a>
                        <div id="${modalId}" class="vczapi-recordings__modal" style="display:none;">
                            ${filesHtml}
                        </div>
                    `;
                }
            }
        ],

        transformResults: (response) => {
            meetingModalCount = 0;

            if (!response.success) {
                return { items: [], total: 0 };
            }

            const meetings = response.data?.items || response.data?.meetings || [];
            return {
                items: meetings,
                total: response.data?.total || meetings.length
            };
        }
    });

    // Handle Thickbox modal re-initialization after table body renders
    if (window.tb_init) {
        const observer = new MutationObserver(() => {
            window.tb_init('a.thickbox');
        });
        const tbody = container.querySelector('.vczapi-dt-body');
        if (tbody) {
            observer.observe(tbody, { childList: true });
        }
    }

    const applyFilters = () => {
        const hostId = getSelectedHost();
        const date = getSelectedDate();

        if (!hostId) {
            dtInstance.clearTable?.(i18n.noHost || 'Please select a host to load recordings.');
            return;
        }

        dtInstance.reloadWithParams?.({
            host_id: hostId,
            date: date
        });
    };

    if (dateForm) {
        dateForm.addEventListener('submit', (e) => {
            e.preventDefault();
            applyFilters();
        });
    }

    if (hostSelect) {
        hostSelect.addEventListener('change', applyFilters);
    }

    if (!getSelectedHost()) {
        dtInstance.clearTable?.(i18n.noHost || 'Please select a host to load recordings.');
    }
};