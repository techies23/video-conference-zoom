import Choices from 'choices.js';

/**
 * Simple debounce utility to prevent firing rapid API calls on keyup.
 */
const debounce = (fn, delay = 300) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

/**
 * Initializes Choices.js select elements with optional AJAX search API integration.
 *
 * @param {string|NodeList|HTMLElement} selector Target element(s) or CSS selector.
 * @param {Object} customOptions Custom Choices.js options or API configurations.
 * @returns {Choices[]} Array of instantiated Choices instances.
 */
export const initChoices = (selector = '.vczapi-choices', customOptions = {}) => {
    const elements = typeof selector === 'string'
        ? document.querySelectorAll(selector)
        : selector;

    if (!elements || !elements.length) {
        return [];
    }

    const instances = [];

    elements.forEach((element) => {
        // Avoid double initialization
        if (element.dataset.choicesInitialized) {
            return;
        }

        const isSearchable = element.getAttribute('data-searchable') !== 'false';
        const placeholder = element.getAttribute('data-placeholder') || 'Select an option';
        const removeItemButton = element.getAttribute('data-remove-item') === 'true';

        // API Configurations from data attributes or customOptions
        const apiEndpoint = customOptions.apiEndpoint || element.getAttribute('data-api-endpoint');
        const apiAction = customOptions.apiAction || element.getAttribute('data-api-action');
        const minSearchLength = parseInt(element.getAttribute('data-min-search') || '2', 10);

        const defaultOptions = {
            searchEnabled: isSearchable,
            placeholderValue: placeholder,
            removeItemButton: removeItemButton,
            itemSelectText: '',
            shouldSort: false,
            allowHTML: false,
            searchFloor: minSearchLength,
        };

        const instance = new Choices(element, {
            ...defaultOptions,
            ...customOptions,
        });

        // Handle AJAX Remote API Searching if an endpoint or WP Action is configured
        if (apiEndpoint || apiAction) {
            const handleSearch = debounce(async (event) => {
                const query = event.detail.value;

                if (!query || query.length < minSearchLength) {
                    return;
                }

                try {
                    let url = apiEndpoint;

                    // If it's a WordPress AJAX Action
                    if (apiAction) {
                        const baseUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
                        url = `${baseUrl}?action=${encodeURIComponent(apiAction)}&q=${encodeURIComponent(query)}`;
                    } else if (url && !url.includes('?')) {
                        url = `${url}?q=${encodeURIComponent(query)}`;
                    } else {
                        url = `${url}&q=${encodeURIComponent(query)}`;
                    }

                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`API HTTP Error: ${response.status}`);
                    }

                    const data = await response.json();

                    // Transform response data if a custom mapping function is passed, otherwise use default
                    const results = typeof customOptions.transformResults === 'function'
                        ? customOptions.transformResults(data)
                        : (data.results || data.data || data);

                    // Format items into standard Choices.js input objects [{ value, label }]
                    const formattedChoices = results.map((item) => ({
                        value: item.id || item.value,
                        label: item.text || item.label || item.name,
                        selected: false,
                        disabled: false,
                    }));

                    // Replace current choices with fetched results
                    instance.setChoices(formattedChoices, 'value', 'label', true);
                } catch (error) {
                    console.error('Choices.js API Fetch Error:', error);
                }
            }, 300);

            element.addEventListener('search', handleSearch);
        }

        element.dataset.choicesInitialized = 'true';
        instances.push(instance);
    });

    return instances;
};

export default initChoices;