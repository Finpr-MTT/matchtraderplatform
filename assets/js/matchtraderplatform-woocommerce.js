(function ($) {
    'use strict';
    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateFieldContainer = $('#billing_state_field');
        const states = wc_country_states.states;
        const prefilledState = $('#billing_state').val();
        
        // Make country field read-only
        countryField.attr('readonly', 'readonly')
                   .addClass('matchtrader-readonly')
                   .css('pointer-events', 'none'); // Prevents dropdown interaction

        function updateStateField(clearState = true) {
            const selectedCountry = countryField.val();
            const currentState = clearState ? '' : prefilledState;
            
            $('#billing_state').remove();
            
            if (stateFieldContainer.find('label[for="billing_state"]').length === 0) {
                stateFieldContainer.append(
                    $('<label>', {
                        for: 'billing_state',
                        text: 'State/Region',
                        class: 'form-label',
                    })
                );
            }

            if (states[selectedCountry] && Object.keys(states[selectedCountry]).length > 0) {
                // Create read-only select dropdown for states
                const stateSelect = $('<select>', {
                    id: 'billing_state',
                    name: 'billing_state',
                    class: 'state_select input-text matchtrader-readonly',
                    required: true,
                    readonly: 'readonly'
                }).css('pointer-events', 'none'); // Prevents dropdown interaction

                stateSelect.append($('<option>', { value: '', text: 'Select State/Region' }));
                
                $.each(states[selectedCountry], function (code, name) {
                    const option = $('<option>', { value: code, text: name });
                    if (code === currentState) {
                        option.attr('selected', 'selected');
                    }
                    stateSelect.append(option);
                });
                stateFieldContainer.append(stateSelect);
            } else {
                // Create read-only text input for states
                const stateInput = $('<input>', {
                    type: 'text',
                    id: 'billing_state',
                    name: 'billing_state',
                    class: 'input-text matchtrader-readonly',
                    required: true,
                    placeholder: 'Enter State/Region',
                    value: currentState,
                    readonly: 'readonly'
                });
                stateFieldContainer.append(stateInput);
            }
        }

        // Since fields are read-only, we can remove the change event handler
        // countryField.on('change', function () {
        //     updateStateField(true);
        // });

        // Initialize the state field with prefilled data
        updateStateField(false);
    });
})(jQuery);