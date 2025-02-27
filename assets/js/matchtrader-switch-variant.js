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

        function updateOrderTotal() {
            $.ajax({
                type: 'POST',
                url: wc_checkout_params.ajax_url, // WooCommerce AJAX URL
                data: {
                    action: 'matchtrader_update_order_review'
                },
                beforeSend: function () {
                    $('.matchtrader-order-total-value').fadeTo(300, 0.5);
                },
                success: function (response) {
                    $('.matchtrader-order-total-value').html('<h2>' + response.data.order_total + '</h2>').fadeTo(300, 1);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        // Run check when checkout updates
        $(document.body).on('updated_checkout', function () {
            updateOrderTotal();
        });


        // Trigger the function when the cart is updated
        $(document.body).on('updated_cart_totals', function() {
            updateOrderTotal();
        });

        // Optional: Trigger the function when a coupon is applied or removed
        $(document.body).on('applied_coupon removed_coupon', function() {
            updateOrderTotal();
        });

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
