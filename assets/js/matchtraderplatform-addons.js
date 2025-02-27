(function ($) {
    'use strict';

    $(document).ready(function () {
        function updateSelectedAddons() {
            var chosenAddons = {};

            $('input[type="checkbox"][name="mtt_addons[]"]:checked').each(function () {
                let addonName = $(this).next('label').text().trim();
                let addonPrice = parseFloat($(this).data('value'));
                chosenAddons[addonName] = addonPrice;
            });

            $.ajax({
                type: 'POST',
                url: mtt_addons_ajax.ajax_url,
                data: {
                    action: 'update_selected_addons',
                    addons: chosenAddons,
                    nonce: mtt_addons_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
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
