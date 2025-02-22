(function ($) {
    'use strict';

    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateFieldContainer = $('#billing_state_field');
        const states = wc_country_select_params.countries; // WooCommerce country-state data

        function updateStateField() {
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

                        // Prefill with WooCommerce session data
                        if (wc_checkout_params && wc_checkout_params.billing_state && wc_checkout_params.billing_state === code) {
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

                    // Prefill with WooCommerce session data
                    if (wc_checkout_params && wc_checkout_params.billing_state) {
                        stateInput.val(wc_checkout_params.billing_state);
                    }

                    stateFieldContainer.append(stateInput);
                }

                // Ensure WooCommerce triggers change event for billing state
                $('#billing_state').trigger('change');
            }, 300); // Delay to allow WooCommerce to load states
        }

        // Handle country change event
        countryField.on('change', function () {
            updateStateField();
        });

        // Ensure the state field updates correctly when WooCommerce reloads checkout
        $(document.body).on('updated_checkout', function () {
            updateStateField();
        });

        // Initialize the state field on page load
        updateStateField();
    });
})(jQuery);
