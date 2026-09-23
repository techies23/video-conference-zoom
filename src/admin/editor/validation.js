(function (wp) {
    const { registerPlugin } = wp.plugins;
    const { useSelect, useDispatch } = wp.data;
    const { useEffect, useRef } = wp.element;

    const VCZAPIMetaboxValidator = () => {
        const { isSaving, isPublishing } = useSelect((select) => {
            const editor = select('core/editor');
            return {
                isSaving: editor.isSavingPost(),
                isPublishing: editor.isPublishingPost(),
            };
        }, []);

        const { lockPostSaving, unlockPostSaving } = useDispatch('core/editor');
        const { createNotice, removeNotice } = useDispatch('core/notices');
        const isLockedRef = useRef(false);

        /**
         * Validates fields dynamically with Choices.js and Flatpickr compatibility
         */
        const validateMetaboxFields = () => {
            const errors = [];
            const requiredInputs = document.querySelectorAll('.vczapi-required-validation, [data-required="true"]');

            requiredInputs.forEach((input) => {
                let isValid = false;
                const inputType = input.getAttribute('type');

                // 1. CHOICES.JS INTEGRATION
                if (input.classList.contains('choices__input') || input.hasAttribute('data-choice') || input.choices || input.closest('.choices')) {
                    const choicesOuterContainer = input.closest('.choices');

                    if (choicesOuterContainer) {
                        // Track valid selection by checking for rendering inside choices__list--single or choices__list--multiple
                        const selectedItem = choicesOuterContainer.querySelector(
                            '.choices__list--single .choices__item--selectable, .choices__list--multiple .choices__item'
                        );

                        // Also verify the item isn't a placeholder
                        const value = selectedItem ? selectedItem.getAttribute('data-value') : '';
                        const isPlaceholder = selectedItem ? selectedItem.classList.contains('choices__placeholder') : false;

                        isValid = selectedItem !== null && !isPlaceholder && value !== '';
                    } else {
                        // Fallback for raw input value checking
                        const val = input.choices ? input.choices.getValue(true) : input.value;
                        isValid = Array.isArray(val) ? val.length > 0 : (val !== null && val !== undefined && String(val).trim() !== '');
                    }

                    toggleErrorClass(input, choicesOuterContainer, !isValid);
                }

                // 2. FLATPICKR INTEGRATION
                else if (input._flatpickr || input.classList.contains('flatpickr-input')) {
                    if (input._flatpickr) {
                        isValid = input._flatpickr.selectedDates.length > 0;
                    } else {
                        isValid = input.value ? input.value.trim() !== '' : false;
                    }

                    // Flatpickr creates an altInput element if altInput: true is enabled
                    const visibleFlatpickrInput = input._flatpickr && input._flatpickr.altInput
                        ? input._flatpickr.altInput
                        : input;

                    toggleErrorClass(input, visibleFlatpickrInput, !isValid);
                }
                // 3. STANDARD CHECKBOXES / RADIOS
                else if (inputType === 'checkbox' || inputType === 'radio') {
                    const name = input.getAttribute('name');
                    isValid = name ? document.querySelector(`[name="${name}"]:checked`) !== null : input.checked;
                    toggleErrorClass(input, input, !isValid);
                }
                // 4. STANDARD TEXT INPUTS & SELECTS
                else {
                    const val = input.value ? input.value.trim() : '';
                    isValid = val !== '';
                    toggleErrorClass(input, input, !isValid);
                }

                const fieldId = input.getAttribute('id') || input.getAttribute('name');
                const fieldLabel = input.getAttribute('data-label') || getLabelText(input) || fieldId;

                if (!isValid) {
                    errors.push({ id: fieldId, label: fieldLabel });
                } else {
                    removeNotice(`vczapi-err-${fieldId}`);
                }
            });

            return errors;
        };

        /**
         * Helper to apply/remove error class on visible UI wrappers or raw inputs
         */
        const toggleErrorClass = (targetInput, visualContainer, hasError) => {
            const container = visualContainer || targetInput;
            if (hasError) {
                container.classList.add('vczapi-field-error');
            } else {
                container.classList.remove('vczapi-field-error');
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
            return '';
        };

        const ensureMetaboxIsVisible = () => {
            const metaboxContainer = document.getElementById('vczapi-admin-meeting-fields-meta');
            if (metaboxContainer) {
                if (metaboxContainer.classList.contains('closed')) {
                    metaboxContainer.classList.remove('closed');
                }
                metaboxContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        // Run validation when saving/publishing starts
        useEffect(() => {
            if ((isSaving || isPublishing) && !isLockedRef.current) {
                const invalidFields = validateMetaboxFields();

                if (invalidFields.length > 0) {
                    isLockedRef.current = true;

                    // Lock post saving in Gutenberg
                    lockPostSaving('vczapi_metabox_validation');

                    // Create error notices
                    invalidFields.forEach((field) => {
                        createNotice(
                            'error',
                            `Validation Error: ${field.label} is required.`,
                            { id: `vczapi-err-${field.id}`, isDismissible: true }
                        );
                    });

                    ensureMetaboxIsVisible();
                }
            }
        }, [isSaving, isPublishing]);

        // Real-time unlock listener setup
        useEffect(() => {
            const handleInput = (e) => {
                const target = e.target;
                if (!target) return;

                // Check if the event was raised inside Choices.js or Flatpickr
                const isValidationTarget =
                    target.matches('.vczapi-required-validation, [data-required="true"]') ||
                    target.closest('.choices') ||
                    target.classList.contains('flatpickr-input');

                if (isValidationTarget) {
                    const invalidFields = validateMetaboxFields();
                    if (invalidFields.length === 0 && isLockedRef.current) {
                        unlockPostSaving('vczapi_metabox_validation');
                        isLockedRef.current = false;
                    }
                }
            };

            document.addEventListener('input', handleInput);
            document.addEventListener('change', handleInput);

            return () => {
                document.removeEventListener('input', handleInput);
                document.removeEventListener('change', handleInput);
            };
        }, []);

        return null;
    };

    // Register as a Gutenberg Plugin Component
    registerPlugin('vczapi-metabox-validator', {
        render: VCZAPIMetaboxValidator,
    });
})(window.wp);