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

<form id="matchTraderCheckout" name="checkout" method="post" class="checkout woocommerce-checkout hello-theme-checkout container" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

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
		<div class="container">
		<div class="row">
		    <div class="col-sm-7 border px-4"><?php do_action('woocommerce_checkout_before_customer_details'); ?> </div>
		    <div class="col-sm-5">
		    	<?php do_action('woocommerce_checkout_before_order_review'); ?>
		    </div>
		    <div class="text-center mt-4">
				<button type="button" class="btn btn-success next-step">Next</button>
			</div>
		</div>
		</div>
		 
		

		
	</div>

	<!-- Step 2: Billing Details -->
	<div class="step-content" data-step="2">
		<div id="customer_details">
			<div class="container">
				<div class="row justify-content-md-center">
					<div class="col-sm-7 p-4 border">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>
					<div class="d-flex justify-content-between mt-4">
						<button type="button" class="btn btn-secondary prev-step">Back</button>
						<button type="button" class="btn btn-success next-step">Next</button>
					</div>
				</div>

			</div>

			
		</div>
	</div>

	<!-- Step 3: Payment -->
	<div class="step-content" data-step="3">
		<?php do_action('woocommerce_checkout_payment'); ?>
		<?php do_action('woocommerce_checkout_order_review'); ?>

		<div class="text-center mt-4">
			<button type="button" class="btn btn-secondary prev-step">Back</button>
		</div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>