/**
 * Initializes Flatpickr on meeting form date/time fields.
 *
 * @param {string|HTMLElement} selector Target input selector or element container.
 */
export const initFlatpicker = (selector = '.vczapi-datetimepicker') => {
    const dateInputs = document.querySelectorAll(selector);

    if (!dateInputs.length) {
        return;
    }

    dateInputs.forEach((input) => {
        const enableTime = input.getAttribute('data-enable-time') === 'true';
        const dateFormat = input.getAttribute('data-date-format') || 'Y-m-d h:i K';
        const now = new Date();
        now.setHours(now.getHours() + 1);
        flatpickr(input, {
            enableTime: enableTime,
            dateFormat: dateFormat,
            defaultDate: input.value || now,
            minuteIncrement: 15,
            // Additional Flatpickr options
        });
    });
};

export default initFlatpicker;