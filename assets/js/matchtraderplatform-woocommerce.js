(function ($) {
    'use strict';

    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateFieldContainer = $('#billing_state_field');
        const states = wc_country_states.states;

        // Set saved country and state on page load
        // Removed localStorage usage

        function updateStateField(clearState = true) {
            const selectedCountry = countryField.val();

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

                stateFieldContainer.append(stateInput);
            }
        }

        // Handle country change event
        countryField.on('change', function () {
            updateStateField(true);
        });

        // Initialize the state field
        updateStateField(false);
    });
})(jQuery);