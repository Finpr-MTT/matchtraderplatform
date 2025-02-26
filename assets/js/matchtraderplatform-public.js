(function ($) {
    'use strict';

    $(document).ready(function () {
        const steps = $('.step');
        const stepContents = $('.step-content');
        const nextButtons = $('.next-step');
        const prevButtons = $('.prev-step');
        const billingFields = $('.woocommerce-billing-fields input, .woocommerce-billing-fields select');

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

            $('.woocommerce-billing-fields .validate-required input, .woocommerce-billing-fields .validate-required select').each(function () {
                let field = $(this);
                let fieldWrapper = field.closest('.form-row');

                // Check if field is empty
                if (field.val().trim() === '') {
                    isValid = false;
                    field.addClass('input-error');
                    fieldWrapper.addClass('woocommerce-invalid'); // Add WooCommerce error class
                    if (!fieldWrapper.find('.error-message').length) {
                        fieldWrapper.append('<span class="error-message">This field is required.</span>');
                    }
                } else {
                    field.removeClass('input-error');
                    fieldWrapper.removeClass('woocommerce-invalid'); // Remove WooCommerce error class
                    fieldWrapper.find('.error-message').remove();
                }

                // Additional email validation
                if (field.attr('id') === 'billing_email' && !isValidEmail(field.val())) {
                    isValid = false;
                    field.addClass('input-error');
                    fieldWrapper.addClass('woocommerce-invalid');
                    if (!fieldWrapper.find('.error-message').length) {
                        fieldWrapper.append('<span class="error-message">Enter a valid email address.</span>');
                    }
                }
            });

            return isValid;
        }

        // Function to validate email format
        function isValidEmail(email) {
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailPattern.test(email);
        }

        // Handle live validation on input change
        billingFields.on('input change', function () {
            validateBillingFields();
        });

        // Handle next button click with validation
        nextButtons.on('click', function () {
            if (currentStep === 2) { // Validate only on the Billing Details step
                if (!validateBillingFields()) {
                    alert('⚠ Please fill in all required billing fields and enter a valid email before proceeding.');
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
                alert('⚠ Please complete all steps before submitting the form.');
            }
        });

        updateProgress();
    });

})(jQuery);
