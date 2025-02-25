(function ($) {
    'use strict';

    jQuery(document).ready(function ($) {
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
                    console.log('Updating cart...'); // Debug log
                    $('#matchtrader-update-cart').prop('disabled', true).text('Updating...');
                },
                success: function (response) {
                    if (response.success) {
                        console.log('Cart updated successfully. Refreshing order total...');
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('update_checkout');

                        // Refresh order total
                        updateOrderTotal();
                    } else {
                        console.error('Cart update failed:', response);
                        alert(response.data.message);
                    }
                },
                complete: function () {
                    $('#matchtrader-update-cart').prop('disabled', false).text('Update Cart');
                }
            });
        }

        function updateOrderTotal() {
            console.log('Sending AJAX request to update order total...');
            $.ajax({
                type: 'POST',
                url: wc_checkout_params.ajax_url, // WooCommerce AJAX URL
                data: {
                    action: 'matchtrader_update_order_review'
                },
                beforeSend: function () {
                    console.log('Updating order total...'); // Debug log
                    $('.matchtrader-order-total-value').fadeTo(300, 0.5);
                },
                success: function (response) {
                    console.log('Order total response:', response);
                    if (response && response.success && response.order_total) {
                        $('.matchtrader-order-total-value').html(response.order_total).fadeTo(300, 1);
                    } else {
                        console.error('Invalid response:', response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
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
