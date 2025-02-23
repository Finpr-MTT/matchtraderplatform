<?php
/**
 * Checkout Form - Multi-Step with Bootstrap
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout hello-theme-checkout container" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<!-- Multi-Step Navigation -->
	<div class="step-progress">
		<div class="step active" data-step="1">
			<div class="step-number">1</div>
			<div class="step-title">Select Account</div>
		</div>
		<div class="step" data-step="2">
			<div class="step-number">2</div>
			<div class="step-title">Billing Details</div>
		</div>
		<div class="step" data-step="3">
			<div class="step-number">3</div>
			<div class="step-title">Make Payment</div>
		</div>
	</div>

	<!-- Step 1: Account Selection -->
	<div class="step-content active" data-step="1">
		<?php do_action('woocommerce_checkout_before_customer_details'); ?>  
		<?php do_action('woocommerce_checkout_before_order_review'); ?>

		<div class="text-center mt-4">
			<button type="button" class="btn btn-primary next-step">Next</button>
		</div>
	</div>

	<!-- Step 2: Billing Details -->
	<div class="step-content" data-step="2">
		<div id="customer_details">
			<div class="container">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="d-flex justify-content-between mt-4">
				<button type="button" class="btn btn-secondary prev-step">Back</button>
				<button type="button" class="btn btn-primary next-step">Next</button>
			</div>
		</div>
	</div>

	<!-- Step 3: Payment -->
	<div class="step-content" data-step="3">
		<?php do_action('woocommerce_checkout_payment'); ?>
		<?php do_action('woocommerce_checkout_order_review'); ?>

		<div class="text-center mt-4">
			<button type="button" class="btn btn-secondary prev-step">Back</button>
			<button type="submit" class="btn btn-success">Place Order</button>
		</div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<style>
    .step-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
    }
    .step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    .step:not(:last-child)::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 2px;
        background: #dee2e6;
        top: 20px;
        left: 50%;
        z-index: -1;
    }
    .step.active:not(:last-child)::before {
        background: #0d6efd;
    }
    .step-number {
        width: 40px;
        height: 40px;
        line-height: 40px;
        border-radius: 50%;
        background: #dee2e6;
        display: inline-block;
        margin-bottom: 0.5rem;
    }
    .step.active .step-number {
        background: #0d6efd;
        color: white;
    }
    .step-content {
        display: none;
    }
    .step-content.active {
        display: block;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const steps = document.querySelectorAll('.step');
        const stepContents = document.querySelectorAll('.step-content');
        const nextButtons = document.querySelectorAll('.next-step');
        const prevButtons = document.querySelectorAll('.prev-step');

        let currentStep = 1;

        function updateProgress() {
            steps.forEach((step, index) => {
                if (index + 1 <= currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });

            stepContents.forEach((content) => {
                if (parseInt(content.getAttribute('data-step')) === currentStep) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        }

        nextButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (currentStep < steps.length) {
                    currentStep++;
                    updateProgress();
                }
            });
        });

        prevButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateProgress();
                }
            });
        });

        // Handle WooCommerce form submission
        document.querySelector('form.checkout').addEventListener('submit', function (e) {
            if (currentStep !== steps.length) {
                e.preventDefault();
                alert('Please complete all steps before submitting the form.');
            }
        });
    });
</script>