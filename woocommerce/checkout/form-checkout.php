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
<div class="row">
    <div class="col-md-12">
        <form id="matchTraderCheckout" name="checkout" method="post" class="checkout woocommerce-checkout matchtrader-checkout container" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
            
            <!-- Multi-Step Navigation -->
            <ul id="progressbar">
                <li class="active">1. Select Account</li>
                <li>Billing Details</li>
                <li>Make Payment</li>
            </ul>

            <!-- Step 1: Account Selection -->
            <fieldset>
                <?php do_action('woocommerce_checkout_before_customer_details'); ?>  
                <?php do_action('woocommerce_checkout_before_order_review'); ?>

                <input type="button" name="next" class="next action-button" value="Next"/>
            </fieldset>

            <!-- Step 2: Billing Details -->
            <fieldset>
                <div id="customer_details">
                    <div class="container">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                    </div>

                    <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
			        <input type="button" name="next" class="next action-button" value="Next"/>
                </div>
            </fieldset>

            <!-- Step 3: Payment -->
            <fieldset>
                <?php do_action('woocommerce_checkout_payment'); ?>
                <?php do_action('woocommerce_checkout_order_review'); ?>
                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
            </fieldset>

        </form>
    </div>
</div>
<!-- /.MultiStep Form -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
