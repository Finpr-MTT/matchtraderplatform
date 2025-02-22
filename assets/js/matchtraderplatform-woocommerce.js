(function ($) {
    'use strict';

    $(document).ready(function() {
        // Listen for country field change
        $(document).on('change', '#billing_country', function() {
            let country = $(this).val();
            let stateField = $('#billing_state_field');
            let stateSelect = $('#billing_state');

            // Fetch states from localized WooCommerce data
            let states = matchtrader_checkout_params.countries[country] ? matchtrader_checkout_params.countries[country].states : null;

            if (states && Object.keys(states).length) {
                // If states exist, replace text input with a dropdown
                let options = '<option value="">' + matchtrader_checkout_params.i18n_select_state_text + '</option>';
                $.each(states, function (key, value) {
                    options += '<option value="' + key + '">' + value + '</option>';
                });
                stateSelect.replaceWith('<select name="billing_state" id="billing_state" class="state_select">' + options + '</select>');
            } else {
                // If no states exist, use a text input
                stateSelect.replaceWith('<input type="text" name="billing_state" id="billing_state" class="input-text">');
            }
        });
    });

})(jQuery);
