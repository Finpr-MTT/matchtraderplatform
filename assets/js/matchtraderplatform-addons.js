(function ($) {
    'use strict';

    $(document).ready(function () {
        function updateSelectedAddons() {
            var chosenAddons = [];
            var chosenAddonsPercentage = 0;

            $('input[type="checkbox"][name="mtt_addons[]"]:checked').each(function () {
                let addonName = $(this).next('label').text().trim(); // Get the label text
                chosenAddons.push(addonName);
                chosenAddonsPercentage += parseFloat($(this).data('value'));
            });

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
                        // Refresh WooCommerce order review and checkout totals
                        $(document.body).trigger('update_checkout');
                    }
                }
            });
        }

        // Event listener for checkbox changes
        $('input[type="checkbox"][name="mtt_addons[]"]').on('change', function () {
            updateSelectedAddons();
        });
    });

})(jQuery);
