(function ($) {
    'use strict';

    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateField = $('#billing_state');
        const stateFieldContainer = $('#billing_state_field');
        const states = wc_country_select_params.countries; // WooCommerce country-state data

        function updateStateField() {
            const selectedCountry = countryField.val();

            // Ensure WooCommerce updates the field dynamically
            setTimeout(() => {
                // Remove any existing state field content
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

                        // Restore selected state from WooCommerce session data
                        if (stateField.val() && stateField.val() === code) {
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

                    if (stateField.val()) {
                        stateInput.val(stateField.val()); // Restore value from WC session
                    }

                    stateFieldContainer.append(stateInput);
                }

                // Ensure WooCommerce triggers change event for billing state
                $('#billing_state').trigger('change');
            }, 500); // Delay to allow WooCommerce to update country field first
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