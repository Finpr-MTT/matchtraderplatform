(function($) {
    'use strict';

    jQuery(document).ready(function($) {
        // Function to update the cart
        function updateCart() {
            let selectedAttributes = {};
            $('.matchtrader-radio-group input[type="radio"]:checked').each(function() {
                let attribute = $(this).closest('.matchtrader-radio-group').data('attribute');
                let value = $(this).val();
                selectedAttributes[attribute] = value;
            });

            $.ajax({
                type: 'POST',
                url: matchtraderAjax.ajaxurl,
                data: {
                    action: 'matchtrader_update_cart',
                    security: matchtraderAjax.nonce,
                    variation_attributes: selectedAttributes
                },
                beforeSend: function() {
                    $('#matchtrader-update-cart').prop('disabled', true).text('Updating...');
                },
                success: function(response) {
                    if (response.success) {
                        // Update cart fragments without reloading
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('update_checkout');
                    } else {
                        alert(response.data.message);
                    }
                },
                complete: function() {
                    $('#matchtrader-update-cart').prop('disabled', false).text('Update Cart');
                }
            });
        }

        // Event listener for radio button changes
        $('.matchtrader-radio-group input[type="radio"]').on('change', function() {
            updateCart();
        });

        // Event listener for the "Update Cart" button
        $('#matchtrader-update-cart').on('click', function(e) {
            e.preventDefault();
            updateCart();
        });

        // Function to disable billing country and state fields
        function disableSelect2Fields() {
            $('#billing_country').prop('disabled', true);
            $('#billing_state').prop('disabled', true);
        }

        // Check if Select2 is initialized on page load
        if ($('#billing_country').hasClass('select2-hidden-accessible')) {
            disableSelect2Fields();
        }

        // Listen for Select2 initialization events
        $(document).on('select2:open', function() {
            disableSelect2Fields();
        });
    });

})(jQuery);