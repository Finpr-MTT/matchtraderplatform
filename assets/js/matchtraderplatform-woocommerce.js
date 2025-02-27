(function ($) {
    'use strict';

    $(document).ready(function () {
        const countryField = $('#billing_country');
        const stateFieldContainer = $('#billing_state_field');
        const states = wc_country_states.states;

        // Get prefilled state from WooCommerce session
        const prefilledState = $('#billing_state').val(); // Get existing value

        function updateStateField(clearState = true) {
            const selectedCountry = countryField.val();
            const currentState = clearState ? '' : prefilledState; // Keep prefilled state if not clearing

            // Remove existing state field before adding a new one
            $('#billing_state').remove();

            // Add label for State/Region (if not already added)
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

                    // If the prefilled state matches, select it
                    if (code === currentState) {
                        option.attr('selected', 'selected');
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
                    value: currentState, // Keep prefilled state
                });

                stateFieldContainer.append(stateInput);
            }
        }

         function checkOrderTotal() {
            let orderTotal = $('.order-total .woocommerce-Price-amount').text().replace(/[^0-9.]/g, '');
            orderTotal = parseFloat(orderTotal);

            if (orderTotal === 0 || isNaN(orderTotal)) {
                $('.mtt-choose-payment-method h4').hide();
            } else {
                $('.mtt-choose-payment-method h4').show();
            }
        }

        // Run check when WooCommerce updates checkout/cart
        $(document.body).on('updated_wc_div updated_cart_totals wc_fragments_refreshed updated_checkout', function() {
            checkOrderTotal();
        });

        // Run check on page load
        checkOrderTotal();

        // Handle country change event (state field update)
        $('#billing_country').on('change', function () {
            updateStateField(true);
        });

        // Initialize the state field (keep prefilled data if available)
        updateStateField(false);
    });
})(jQuery);