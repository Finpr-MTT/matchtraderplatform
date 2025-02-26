(function ($) {
    'use strict';

    $(document).ready(function () {
        // Function to update selected addons
        function updateSelectedAddons() {
            var chosenAddons = [];
            var chosenAddonsPercentage = 0;

            $('input[type="checkbox"][name="mtt_addons[]"]:checked').each(function () {
                chosenAddons.push($(this).val());
                chosenAddonsPercentage += parseFloat($(this).data('value'));
            });

            // AJAX request to update session with selected add-ons
            $.ajax({
                type: 'POST',
                url: mtt_addons_ajax.ajax_url,
                data: {
                    action: 'update_selected_addons',
                    addons: chosenAddons,
                    addons_percentage: chosenAddonsPercentage,
                    nonce: mtt_addons_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Trigger an update in WooCommerce checkout totals
                        $(document.body).trigger('update_checkout');
                    }
                }
            });
        }

        // Initialize selected addons on page load
        updateSelectedAddons();

        // Event listener for checkbox changes
        $('input[type="checkbox"][name="mtt_addons[]"]').on('change', function () {
            updateSelectedAddons();
        });
    });

})(jQuery);
