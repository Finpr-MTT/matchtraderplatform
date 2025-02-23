(function($) {
    'use strict';

    $(document).ready(function() {
        const steps = $('.step');
        const stepContents = $('.step-content');
        const nextButtons = $('.next-step');
        const prevButtons = $('.prev-step');

        let currentStep = 1;

        function updateProgress() {
            steps.each(function(index) {
                if (index + 1 <= currentStep) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });

            stepContents.each(function() {
                if (parseInt($(this).attr('data-step')) === currentStep) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        }

        nextButtons.on('click', function() {
            if (currentStep < steps.length) {
                currentStep++;
                updateProgress();
            }
        });

        prevButtons.on('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateProgress();
            }
        });

        // Handle WooCommerce form submission
        $('form.checkout').on('submit', function(e) {
            if (currentStep !== steps.length) {
                e.preventDefault();
                alert('Please complete all steps before submitting the form.');
            }
        });
    });
})(jQuery);