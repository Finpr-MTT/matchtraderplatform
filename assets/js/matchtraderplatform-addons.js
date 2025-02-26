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

        // Restore checked checkboxes on page load
        function restoreCheckedAddons() {
            $.ajax({
                type: 'POST',
                url: mtt_addons_ajax.ajax_url,
                data: {
                    action: 'get_selected_addons',
                    nonce: mtt_addons_ajax.nonce
                },
                success: function (response) {
                    if (response.success && response.addons) {
                        $('input[type="checkbox"][name="mtt_addons[]"]').each(function () {
                            let addonName = $(this).next('label').text().trim();
                            if (response.addons.includes(addonName)) {
                                $(this).prop('checked', true);
                            }
                        });
                    }
                }
            });
        }

        // Event listener for checkbox changes
        $('input[type="checkbox"][name="mtt_addons[]"]').on('change', function () {
            updateSelectedAddons();
        });

        // Restore checkboxes on page load
        restoreCheckedAddons();
    });

})(jQuery);
