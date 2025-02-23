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

<!-- MultiStep Form -->
<div id="matchTraderCheckout" class="row">
    <div class="col-md-12">
        <form name="checkout" method="post" class="checkout woocommerce-checkout hello-theme-checkout container" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
            
            <!-- Multi-Step Navigation -->
            <ul id="progressbar" class="nav nav-pills nav-justified checkout-steps">
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
            <fieldset class="checkout-step-content step-1 active">
                <h2 class="fs-title">Select Account</h2>
                <?php do_action('woocommerce_checkout_before_customer_details'); ?>  
                <?php do_action('woocommerce_checkout_before_order_review'); ?>

                <input type="button" name="next" class="next action-button" value="Next"/>
            </fieldset>

            <!-- Step 2: Billing Details -->
            <fieldset class="checkout-step-content step-2">
                <h2 class="fs-title">Billing Details</h2>
                <div id="customer_details">
                    <div class="container">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                    </div>

                    <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
			        <input type="button" name="next" class="next action-button" value="Next"/>
                </div>
            </fieldset>

            <!-- Step 3: Payment -->
            <fieldset class="checkout-step-content step-3">
                <h2 class="fs-title">Make Payment</h2>
                <?php do_action('woocommerce_checkout_payment'); ?>
                <?php do_action('woocommerce_checkout_order_review'); ?>

                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
            </fieldset>

        </form>
    </div>
</div>
<!-- /.MultiStep Form -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
