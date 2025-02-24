(function ($) {
    'use strict';
    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateFieldContainer = $('#billing_state_field');
        const states = wc_country_states.states;

        // Check session status via Ajax
        $.ajax({
            url: matchtrader_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'check_matchtrader_session',
                nonce: matchtrader_ajax.nonce
            },
            success: function(response) {
                if (response.has_session) {
                    // Make country field read-only if session exists
                    if (response.country) {
                        countryField.select2({
                            disabled: true,
                            minimumResultsForSearch: Infinity
                        });
                        countryField.addClass('matchtrader-readonly');
                    }
                    
                    // Update state field
                    updateStateField(response.state);
                }
            }
        });

        function updateStateField(prefilledState) {
            const selectedCountry = countryField.val();
            
            const existingState = $('#billing_state');
            if (existingState.length && existingState.hasClass('select2-hidden-accessible')) {
                existingState.select2('destroy');
            }
            existingState.remove();
            
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
                // Create select dropdown for states
                const stateSelect = $('<select>', {
                    id: 'billing_state',
                    name: 'billing_state',
                    class: 'state_select input-text',
                    required: true
                });

                stateSelect.append($('<option>', { value: '', text: 'Select State/Region' }));
                
                $.each(states[selectedCountry], function (code, name) {
                    const option = $('<option>', { value: code, text: name });
                    if (code === prefilledState) {
                        option.attr('selected', 'selected');
                    }
                    stateSelect.append(option);
                });
                stateFieldContainer.append(stateSelect);

                // Make state field read-only if prefilled state exists
                if (prefilledState) {
                    stateSelect.select2({
                        disabled: true,
                        minimumResultsForSearch: Infinity
                    });
                    stateSelect.addClass('matchtrader-readonly');
                } else {
                    stateSelect.select2({
                        minimumResultsForSearch: Infinity
                    });
                }
            } else {
                // Create text input for states
                const stateInput = $('<input>', {
                    type: 'text',
                    id: 'billing_state',
                    name: 'billing_state',
                    class: 'input-text',
                    required: true,
                    placeholder: 'Enter State/Region',
                    value: prefilledState
                });

                if (prefilledState) {
                    stateInput.attr('readonly', 'readonly')
                             .addClass('matchtrader-readonly');
                }
                stateFieldContainer.append(stateInput);
            }
        }
    });
})(jQuery);