(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {
        // Function to update the cart
        function updateCart() {
            let selectedAttributes = {};
            $('.matchtrader-radio-group input[type="radio"]:checked').each(function () {
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
                beforeSend: function () {
                    $('#matchtrader-update-cart').prop('disabled', true).text('Updating...');
                },
                success: function (response) {
                    if (response.success) {
                        // Update cart fragments and checkout details
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('update_checkout');

                        // Refresh order total
                        updateOrderTotal();
                    } else {
                        alert(response.data.message);
                    }
                },
                complete: function () {
                    $('#matchtrader-update-cart').prop('disabled', false).text('Update Cart');
                }
            });
        }

        // Function to update order total dynamically
        function updateOrderTotal() {
            $.ajax({
                type: 'POST',
                url: wc_checkout_params.ajax_url, // WooCommerce AJAX URL
                data: {
                    action: 'matchtrader_update_order_review'
                },
                success: function (response) {
                    if (response && response.order_total) {
                        $('.matchtrader-order-total-value').html(response.order_total);
                    }
                }
            });
        }

        // Event listener for radio button changes (update cart & order total)
        $('.matchtrader-radio-group input[type="radio"]').on('change', function () {
            updateCart();
        });

        // Event listener for the "Update Cart" button
        $('#matchtrader-update-cart').on('click', function (e) {
            e.preventDefault();
            updateCart();
        });

    });

})(jQuery);
