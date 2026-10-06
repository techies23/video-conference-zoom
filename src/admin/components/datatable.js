/**
 * Simple debounce utility to prevent firing rapid API calls on input/search.
 */
const debounce = (fn, delay = 300) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

/**
 * Creates and initializes a DataTable instance on a given DOM element.
 *
 * @param {HTMLElement} element Target container element.
 * @param {Object} options Configuration options.
 * @returns {Object} Public API object for controlling the table instance.
 */
const createDataTableInstance = (element, options = {}) => {
    // Merged Configuration
    const config = {
        apiEndpoint: element.getAttribute('data-api-endpoint') || options.apiEndpoint,
        apiAction: element.getAttribute('data-api-action') || options.apiAction,
        perPage: parseInt(element.getAttribute('data-per-page') || options.perPage || 10, 10),
        columns: options.columns || [],
        transformResults: options.transformResults || null,
        method: options.method || 'GET',
        headers: options.headers || {},
        nonce: options.nonce || null,
        beforeFetch: options.beforeFetch || null,
        ...options
    };

    // Extra dynamic params passed via initialParams or reloadWithParams
    let extraParams = {...options.initialParams};

    // Encapsulated State
    const state = {
        page: 1,
        search: '',
        sortBy: options.defaultSortBy || '',
        sortOrder: options.defaultSortOrder || 'asc',
        totalItems: 0,
        totalPages: 1,
        data: []
    };

    // DOM Elements Reference Holder
    let tbody, headerRow, searchInput, paginationContainer, infoContainer;

    /**
     * Builds the core HTML skeleton.
     */
    const initDOM = () => {
        element.classList.add('vczapi-datatable-container');
        element.innerHTML = `
            <div class="vczapi-dt-toolbar">
                <div class="vczapi-dt-search">
                    <input type="search" class="vczapi-dt-search-input" placeholder="Search..." />
                </div>
            </div>
            <div class="vczapi-dt-table-wrapper">
                <table class="vczapi-dt-table">
                    <thead>
                        <tr class="vczapi-dt-header-row"></tr>
                    </thead>
                    <tbody class="vczapi-dt-body">
                        <tr><td class="vczapi-dt-loading" colspan="100%">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="vczapi-dt-footer">
                <span class="vczapi-dt-info"></span>
                <div class="vczapi-dt-pagination"></div>
            </div>
        `;

        tbody = element.querySelector('.vczapi-dt-body');
        headerRow = element.querySelector('.vczapi-dt-header-row');
        searchInput = element.querySelector('.vczapi-dt-search-input');
        paginationContainer = element.querySelector('.vczapi-dt-pagination');
        infoContainer = element.querySelector('.vczapi-dt-info');

        renderHeaders();
    };

    /**
     * Renders header columns and binds sort events.
     */
    const renderHeaders = () => {
        headerRow.innerHTML = '';
        config.columns.forEach((col) => {
            const th = document.createElement('th');
            th.textContent = col.label || col.field;

            if (col.sortable !== false) {
                th.classList.add('sortable');
                th.dataset.field = col.field;
                th.addEventListener('click', () => handleSort(col.field));
            }

            headerRow.appendChild(th);
        });
    };

    /**
     * Handles column sorting toggles.
     */
    const handleSort = (field) => {
        if (state.sortBy === field) {
            state.sortOrder = state.sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            state.sortBy = field;
            state.sortOrder = 'asc';
        }

        Array.from(headerRow.children).forEach((th) => {
            th.classList.remove('sort-asc', 'sort-desc');
            if (th.dataset.field === field) {
                th.classList.add(`sort-${state.sortOrder}`);
            }
        });

        fetchData();
    };

    /**
     * Binds search and pagination event listeners.
     */
    const bindEvents = () => {
        const handleSearch = debounce((e) => {
            state.search = e.target.value.trim();
            state.page = 1;
            fetchData();
        }, 400);

        searchInput.addEventListener('input', handleSearch);

        paginationContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-page]');
            if (btn && !btn.disabled) {
                state.page = parseInt(btn.dataset.page, 10);
                fetchData();
            }
        });
    };

    /**
     * Renders loading UI.
     */
    const renderLoading = () => {
        tbody.innerHTML = `<tr><td colspan="100%" class="vczapi-dt-loading">Loading data...</td></tr>`;
    };

    /**
     * Resets table body to a clean message state without making a request.
     */
    const clearTable = (message = 'No records found.') => {
        state.data = [];
        state.totalItems = 0;
        state.totalPages = 1;
        tbody.innerHTML = `<tr><td colspan="100%" class="vczapi-dt-empty">${message}</td></tr>`;
        infoContainer.textContent = 'Showing 0 to 0 of 0 entries';
        paginationContainer.innerHTML = '';
    };

    /**
     * Renders pagination buttons.
     */
    const renderPagination = () => {
        const {page, totalPages} = state;
        let html = `<button data-page="${page - 1}" ${page === 1 ? 'disabled' : ''}>Prev</button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= page - 1 && i <= page + 1)) {
                html += `<button data-page="${i}" class="${i === page ? 'active' : ''}">${i}</button>`;
            } else if (i === page - 2 || i === page + 2) {
                html += `<span class="vczapi-dt-dots">...</span>`;
            }
        }

        html += `<button data-page="${page + 1}" ${page === totalPages ? 'disabled' : ''}>Next</button>`;
        paginationContainer.innerHTML = html;
    };

    /**
     * Renders data rows and status text.
     */
    const render = () => {
        if (!state.data.length) {
            clearTable('No records found.');
            return;
        }

        tbody.innerHTML = state.data.map((row) => `
            <tr>
                ${config.columns.map((col) => {
            const value = row[col.field] ?? '';
            const formatted = typeof col.render === 'function' ? col.render(value, row) : value;
            return `<td>${formatted}</td>`;
        }).join('')}
            </tr>
        `).join('');

        const start = (state.page - 1) * config.perPage + 1;
        const end = Math.min(state.page * config.perPage, state.totalItems);
        infoContainer.textContent = `Showing ${start} to ${end} of ${state.totalItems} entries`;

        renderPagination();
    };

    /**
     * Performs AJAX remote request.
     */
    const fetchData = async () => {
        const {apiEndpoint, apiAction, transformResults, nonce, method, headers} = config;
        const {page, search, sortBy, sortOrder} = state;
        const perPage = config.perPage;

        // Construct request payload object
        const requestParams = {
            page: page,
            per_page: perPage,
            search: search,
            sort_by: sortBy,
            sort_order: sortOrder,
            ...extraParams
        };

        // Prevent making the HTTP request if beforeFetch returns explicit false
        if (typeof config.beforeFetch === 'function' && config.beforeFetch(requestParams) === false) {
            return;
        }

        renderLoading();

        try {
            let url = apiEndpoint;

            if (apiAction) {
                const baseUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
                url = `${baseUrl}?action=${encodeURIComponent(apiAction)}`;
            }

            const queryParams = new URLSearchParams();

            // Append all merged parameters into query string
            Object.entries(requestParams).forEach(([key, val]) => {
                if (val !== undefined && val !== null) {
                    queryParams.append(key, val);
                }
            });

            if (nonce) {
                queryParams.append('_ajax_nonce', nonce);
            }

            const separator = url.includes('?') ? '&' : '?';
            const requestUrl = `${url}${separator}${queryParams.toString()}`;

            const response = await fetch(requestUrl, {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    ...headers
                }
            });

            // if (!response.ok) {
            //     throw new Error(`DataTable HTTP Error: ${response.status}`);
            // }

            const data = await response.json();

            const payload = typeof transformResults === 'function'
                ? transformResults(data)
                : {
                    items: data.items || data.results || data.data || [],
                    total: data.total || data.totalItems || (data.data ? data.data.length : 0)
                };

            state.data = payload.items || [];
            state.totalItems = payload.total || 0;
            state.totalPages = Math.ceil(state.totalItems / perPage) || 1;

            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="100%" class="vczapi-dt-error">${data.data.message}</td></tr>`;
                return;
            }

            render();
        } catch (error) {
            console.error('DataTable Fetch Error:', error);
            tbody.innerHTML = `<tr><td colspan="100%" class="vczapi-dt-error">Failed to load data.</td></tr>`;
        }
    };

    // Initialize
    initDOM();
    bindEvents();
    fetchData();

    // Return public instance controller
    return {
        reload: fetchData,
        reloadWithParams: (newParams = {}) => {
            extraParams = {...extraParams, ...newParams};
            state.page = 1;
            fetchData();
        },
        clearTable,
        getState: () => ({...state}),
        getElement: () => element
    };
};

/**
 * Initializes DataTable elements with optional AJAX search/sort API integration.
 *
 * @param {string|NodeList|HTMLElement} selector Target element(s) or CSS selector.
 * @param {Object} customOptions Custom options, column specs, or API configurations.
 * @returns {Object[]} Array of public DataTable instances.
 */
export const initDataTable = (selector = '.vczapi-datatable', customOptions = {}) => {
    const elements = typeof selector === 'string'
        ? document.querySelectorAll(selector)
        : (selector instanceof NodeList ? selector : [selector]);

    if (!elements || !elements.length) {
        return [];
    }

    const instances = [];

    elements.forEach((element) => {
        if (element.dataset.datatableInitialized) {
            return;
        }

        const instance = createDataTableInstance(element, customOptions);
        element.dataset.datatableInitialized = 'true';
        instances.push(instance);
    });

    return instances;
};

export default initDataTable;