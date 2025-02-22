(function ($) {
    'use strict';

    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateField = $('#billing_state');
        const states = wc_country_states.states;

        // Check if fields are prefilled (from session)
        const isPrefilled = countryField.attr('data-prefilled') === 'true';

        if (isPrefilled) {
            countryField.prop('disabled', true).css('background', '#f3f3f3');
            stateField.prop('disabled', true).css('background', '#f3f3f3');
        }

        // Function to update state field dynamically
        function updateStateField() {
            const selectedCountry = countryField.val();
            const stateFieldContainer = $('#billing_state_field');

            // Remove existing state field
            $('#billing_state').remove();

            if (states[selectedCountry] && Object.keys(states[selectedCountry]).length > 0) {
                // Create dropdown if states exist
                const stateSelect = $('<select>', {
                    id: 'billing_state',
                    name: 'billing_state',
                    class: 'state_select input-text',
                    required: true,
                });

                stateSelect.append($('<option>', { value: '', text: 'Select State/Region' }));

                $.each(states[selectedCountry], function (code, name) {
                    stateSelect.append($('<option>', { value: code, text: name }));
                });

                stateFieldContainer.append(stateSelect);
            } else {
                // Use input text if no states exist
                stateFieldContainer.append(
                    $('<input>', {
                        type: 'text',
                        id: 'billing_state',
                        name: 'billing_state',
                        class: 'input-text',
                        required: true,
                        placeholder: 'Enter State/Region',
                    })
                );
            }

            // Re-disable state field if prefilled
            if (isPrefilled) {
                $('#billing_state').prop('disabled', true).css('background', '#f3f3f3');
            }
        }

        // Update state field when country changes
        countryField.on('change', function () {
            if (!isPrefilled) {
                updateStateField();
            }
        });

        // Initialize state field
        updateStateField();
    });
})(jQuery);
