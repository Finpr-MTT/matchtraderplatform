(function ($) {
    'use strict';

    $(document).ready(function () {
        const steps = $('.step');
        const stepContents = $('.step-content');
        const nextButtons = $('.next-step');
        const prevButtons = $('.prev-step');
        const billingFields = $('.mtt-customer-details input, .mtt-customer-details select');

        let currentStep = 1;

        function updateProgress() {
            steps.each(function (index) {
                const $step = $(this);

                if (index + 1 <= currentStep) {
                    $step.addClass('active');
                } else {
                    $step.removeClass('active');
                }

                if (index + 1 < currentStep) {
                    $step.addClass('progress-active');
                } else {
                    $step.removeClass('progress-active');
                }
            });

            stepContents.each(function () {
                if (parseInt($(this).attr('data-step')) === currentStep) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        }

        // Function to validate required fields
        function validateBillingFields() {
            let isValid = true;
            billingFields.each(function () {
                let field = $(this);
                if (field.prop('required') && field.val().trim() === '') {
                    isValid = false;
                    field.addClass('input-error');
                } else {
                    field.removeClass('input-error');
                }
            });

            return isValid;
        }

        // Handle live field validation
        billingFields.on('input change', function () {
            validateBillingFields();
        });

        // Handle next button click with validation
        nextButtons.on('click', function () {
            if (currentStep === 2) { // Validate only on the Billing Details step
                if (!validateBillingFields()) {
                    alert('Please fill in all required billing fields before proceeding.');
                    return;
                }
            }

            if (currentStep < steps.length) {
                currentStep++;
                updateProgress();
            }
        });

        // Handle previous button click
        prevButtons.on('click', function () {
            if (currentStep > 1) {
                currentStep--;
                updateProgress();
            }
        });

        // Handle step number click
        steps.on('click', function () {
            const stepNumber = parseInt($(this).attr('data-step'));

            if (stepNumber <= currentStep) {
                currentStep = stepNumber;
                updateProgress();
            }
        });

        // Handle WooCommerce form submission
        $('form.checkout').on('submit', function (e) {
            if (currentStep !== steps.length) {
                e.preventDefault();
                alert('Please complete all steps before submitting the form.');
            }
        });

        updateProgress();
    });

})(jQuery);