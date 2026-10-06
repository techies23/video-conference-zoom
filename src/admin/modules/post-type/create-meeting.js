export const initCreateMeeting = () => {
    const form = document.getElementById('vczapi-create-meeting-form');
    if (!form) {
        return;
    }

    const {ajaxurl, nonce} = window.vczapi_ajax || {};
    const apiEndpoint = ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php';

    const submitBtn = form.querySelector('.vczapi-create-meeting__submit');
    const spinner = form.querySelector('.vczapi-spinner');
    const feedbackEl = form.querySelector('.vczapi-create-meeting__feedback');

    let isSubmitting = false;
    let isDirty = false;

    const showFeedback = (message) => {
        if (!feedbackEl) {
            return;
        }
        feedbackEl.hidden = false;
        feedbackEl.className = 'vczapi-create-meeting__feedback is-error';
        feedbackEl.textContent = message;
    };

    const hideFeedback = () => {
        if (feedbackEl) {
            feedbackEl.hidden = true;
        }
    };

    const toggleError = (target, container, hasError) => {
        const el = container || target;
        if (hasError) {
            el.classList.add('vczapi-field-error');
        } else {
            el.classList.remove('vczapi-field-error');
        }
    };

    const getLabelText = (input) => {
        const id = input.getAttribute('id');
        if (id) {
            const label = document.querySelector(`label[for="${id}"]`);
            if (label) {
                return label.textContent.replace('*', '').trim();
            }
        }
        return input.getAttribute('name') || id || '';
    };

    const validateField = (input, errors) => {
        let isValid = false;

        // Choices.js fields wrap the native select.
        if (
            input.classList.contains('choices__input') ||
            input.hasAttribute('data-choice') ||
            input.choices ||
            input.closest('.choices')
        ) {
            const outer = input.closest('.choices');
            if (outer) {
                const selected = outer.querySelector(
                    '.choices__list--single .choices__item--selectable, .choices__list--multiple .choices__item'
                );
                const value = selected
                    ? selected.getAttribute('data-value')
                    : '';
                const isPlaceholder = selected
                    ? selected.classList.contains('choices__placeholder')
                    : false;
                isValid = selected !== null && !isPlaceholder && value !== '';
            } else {
                const val = input.choices
                    ? input.choices.getValue(true)
                    : input.value;
                isValid = Array.isArray(val)
                    ? val.length > 0
                    : val !== null &&
                    val !== undefined &&
                    String(val).trim() !== '';
            }
            toggleError(input, outer || input, !isValid);
        } else if (
            input._flatpickr ||
            input.classList.contains('flatpickr-input')
        ) {
            // Flatpickr date/time fields.
            if (input._flatpickr) {
                isValid = input._flatpickr.selectedDates.length > 0;
            } else {
                isValid = input.value.trim() !== '';
            }
            const visible =
                input._flatpickr && input._flatpickr.altInput
                    ? input._flatpickr.altInput
                    : input;
            toggleError(input, visible, !isValid);
        } else if (
            input.getAttribute('type') === 'checkbox' ||
            input.getAttribute('type') === 'radio'
        ) {
            const name = input.getAttribute('name');
            isValid = name
                ? document.querySelector(`[name="${name}"]:checked`) !==
                null
                : input.checked;
            toggleError(input, input, !isValid);
        } else {
            isValid = input.value.trim() !== '';
            toggleError(input, input, !isValid);
        }

        if (!isValid) {
            errors.push({
                id: input.getAttribute('id') || input.getAttribute('name'),
                label:
                    input.getAttribute('data-label') || getLabelText(input),
            });
        }
    };

    const validate = () => {
        const errors = [];
        const requiredInputs = form.querySelectorAll(
            '.vczapi-required-validation, [data-required="true"]'
        );
        requiredInputs.forEach((input) => validateField(input, errors));
        return errors;
    };

    const setLoading = (loading) => {
        isSubmitting = loading;
        submitBtn.disabled = loading;
        spinner.classList.toggle('vczapi-is-loading', loading);
    };

    // The create modal cannot be dismissed: ignore Escape so users are not
    // tempted into abandoning a half-configured meeting.
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    form.addEventListener('input', () => {
        isDirty = true;
    });
    form.addEventListener('change', () => {
        isDirty = true;
    });

    // Warn when leaving the page before the meeting has been saved.
    window.addEventListener('beforeunload', (e) => {
        // Allow navigation without prompt if form is clean or submitting
        if (!isDirty || isSubmitting) {
            return;
        }
        e.preventDefault();
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        if (isSubmitting) {
            return;
        }

        const invalid = validate();
        if (invalid.length > 0) {
            const first = invalid[0];
            const field = form.querySelector(
                `[id="${first.id}"], [name="${first.id}"]`
            );
            showFeedback(
                `Please fill in "${first.label}" before creating the meeting.`
            );
            if (field) {
                field.scrollIntoView({behavior: 'smooth', block: 'center'});
            }
            return;
        }

        const data = new FormData(form);

        // Composite duration selects (hour/minute) map to a single minute value.
        const hours = parseInt(data.get('hour') || '0', 10);
        const minutes = parseInt(data.get('minute') || '0', 10);

        data.delete('hour');
        data.delete('minute');
        data.set('duration', String(hours * 60 + minutes));

        data.set('action', 'vczapi_create_meeting_event');
        data.set('security', nonce || '');

        setLoading(true);
        hideFeedback();

        fetch(apiEndpoint, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
        })
            .then(async (response) => {
                let payload;
                try {
                    payload = await response.json();
                } catch {
                    payload = {
                        success: false,
                        data: {message: 'Invalid server response.'},
                    };
                }
                return {response, payload};
            })
            .then(({response, payload}) => {
                if (!response.ok || !payload.success) {
                    throw new Error(
                        (payload.data && payload.data.message) ||
                        'Unable to create the meeting.'
                    );
                }
                window.location.href = payload.data.edit_url;
            })
            .catch((error) => {
                setLoading(false);
                showFeedback(
                    error.message || 'Something went wrong. Please try again.'
                );
            });
    });
};
