(function ($) {
    'use strict';

    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateFieldContainer = $('#billing_state_field');
        const savedCountry = sessionStorage.getItem('billing_country');
        const savedState = sessionStorage.getItem('billing_state');
        const states = wc_country_select_params.countries; // WooCommerce country-state data

        // Restore saved country and state on page load
        if (savedCountry) {
            countryField.val(savedCountry).trigger('change');
        }

        function updateStateField(clearState = true) {
            const selectedCountry = countryField.val();

            // Ensure WooCommerce updates the field dynamically
            setTimeout(() => {
                // Clear previous state field content
                stateFieldContainer.empty();

                // Add label for State/Region
                const stateLabel = $('<label>', {
                    for: 'billing_state',
                    text: 'State/Region',
                    class: 'form-label',
                });
                stateFieldContainer.append(stateLabel);

                if (states[selectedCountry] && Object.keys(states[selectedCountry]).length > 0) {
                    // Create a select dropdown for states
                    const stateSelect = $('<select>', {
                        id: 'billing_state',
                        name: 'billing_state',
                        class: 'state_select input-text',
                        required: true,
                    });

                    // Add a placeholder option
                    stateSelect.append($('<option>', { value: '', text: 'Select State/Region' }));

                    // Add state options
                    $.each(states[selectedCountry], function (code, name) {
                        const option = $('<option>', { value: code, text: name });

                        // Restore selected state if it matches saved value and clearState is false
                        if (!clearState && savedState && savedState === code) {
                            option.prop('selected', true);
                        }

                        stateSelect.append(option);
                    });

                    stateFieldContainer.append(stateSelect);
                } else {
                    // Create a text input for states
                    const stateInput = $('<input>', {
                        type: 'text',
                        id: 'billing_state',
                        name: 'billing_state',
                        class: 'input-text',
                        required: true,
                        placeholder: 'Enter State/Region',
                    });

                    if (!clearState && savedState) {
                        stateInput.val(savedState); // Restore saved value if clearState is false
                    }

                    stateFieldContainer.append(stateInput);
                }

                // Clear state saved in session storage if clearState is true
                if (clearState) {
                    sessionStorage.removeItem('billing_state');
                }

                // Ensure WooCommerce triggers change event for billing state
                $('#billing_state').trigger('change');
            }, 300); // Delay to allow WooCommerce to load states
        }

        // Handle country change event
        countryField.on('change', function () {
            updateStateField(true);
            sessionStorage.setItem('billing_country', countryField.val());
        });

        // Save state value to session storage on change
        stateFieldContainer.on('change', '#billing_state', function () {
            sessionStorage.setItem('billing_state', $(this).val());
        });

        // Ensure the state field updates correctly when WooCommerce reloads checkout
        $(document.body).on('updated_checkout', function () {
            updateStateField(false);
        });

        // Initialize the state field on page load
        updateStateField(false);
    });
})(jQuery);