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
                const $step = $(this);

                // Add/remove active class for step number
                if (index + 1 <= currentStep) {
                    $step.addClass('active');
                } else {
                    $step.removeClass('active');
                }

                // Add/remove progress-active class for progress bar
                if (index + 1 < currentStep) {
                    $step.addClass('progress-active');
                } else {
                    $step.removeClass('progress-active');
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

        // Handle next button click
        nextButtons.on('click', function() {
            if (currentStep < steps.length) {
                currentStep++;
                updateProgress();
            }
        });

        // Handle previous button click
        prevButtons.on('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateProgress();
            }
        });

        // Handle step number click
        steps.on('click', function() {
            const stepNumber = parseInt($(this).attr('data-step'));

            // Only allow navigation to steps that are before or equal to the current step
            if (stepNumber <= currentStep) {
                currentStep = stepNumber;
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

        // Initialize progress
        updateProgress();
    });
})(jQuery);