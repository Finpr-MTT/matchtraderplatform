(function ($) {
    'use strict';

    $(document).ready(function() {
        // When the checkout form is updated, ensure country/state are refreshed
        $(document.body).on('updated_checkout', function() {
            $('select#billing_country').trigger('change');
            $('select#billing_state').trigger('change');
        });
    });

})(jQuery);
