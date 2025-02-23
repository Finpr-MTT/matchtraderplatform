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
	<ul class="nav nav-pills nav-justified checkout-steps">
        <li class="nav-item">
            <a class="nav-link active" data-step="1" href="#">1. Select Account</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-step="2" href="#">2. Billing Details</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-step="3" href="#">3. Make Payment</a>
        </li>
    </ul>

	<!-- Step 1: Account Selection -->
	<div class="checkout-step-content step-1 active">
		<?php do_action('woocommerce_checkout_before_customer_details'); ?>  
		<?php do_action('woocommerce_checkout_before_order_review'); ?>

		<div class="text-center mt-4">
			<button type="button" class="btn btn-primary next-step" data-next="2">Next</button>
		</div>
	</div>

	<!-- Step 2: Billing Details -->
	<div class="checkout-step-content step-2">
		<div id="customer_details">
			<div class="container">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="d-flex justify-content-between mt-4">
				<button type="button" class="btn btn-secondary prev-step" data-prev="1">Back</button>
				<button type="button" class="btn btn-primary next-step" data-next="3">Next</button>
			</div>
		</div>
	</div>

	<!-- Step 3: Payment -->
	<div class="checkout-step-content step-3">
		<?php do_action('woocommerce_checkout_payment'); ?>
		<?php do_action('woocommerce_checkout_order_review'); ?>

		<div class="text-center mt-4">
			<button type="button" class="btn btn-secondary prev-step" data-prev="2">Back</button>
		</div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
