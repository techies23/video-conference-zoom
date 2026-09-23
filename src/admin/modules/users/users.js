import { initDataTable } from '../../components/datatable.js';

export const initUserList = () => {
    const dtEl = document.querySelector('.vczapi-users-table');

    if (!dtEl) return;

    const { ajaxurl, nonce } = window.vczapi_ajax || {};

    initDataTable(dtEl, {
        apiEndpoint: ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php',
        apiAction: 'vczapi_get_user_list',
        columns: [
            { field: 'id', label: 'ID', sortable: false },
            { field: 'first_name', label: 'First Name', sortable: true },
            { field: 'last_name', label: 'Last Name', sortable: false },
            { field: 'email', label: 'Email', sortable: true },
            {
                field: 'status',
                label: 'Status',
                render: (value) => `<span class="badge ${value}">${value}</span>`
            },
        ],
        transformResults: (response) => ({
            items: response.data?.users || [],
            total: response.data?.total_count || 0
        })
    });
};