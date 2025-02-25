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
	<div class="step-progress mb-4">
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
			<div class="row justify-content-md-center">
			    <div class="col-md-12">
			    	<?php do_action('woocommerce_checkout_before_customer_details'); ?>
			    </div>
			</div>
			<div class="row justify-content-md-center g-3">
			    <div class="col-md-6">
			    	<?php do_action('woocommerce_checkout_before_order_review'); ?>	    	
			    </div>
			    <div class="col-md-6">
			    	<?php do_action('woocommerce_checkout_before_order_review'); ?>	    	
			    </div>			    
			</div>
			<div class="row justify-content-md-center">
				<div class="col-md-12">
			    	<?php do_action('matchtrader_checkout_after_order_review');?>    	
			    </div>
			</div>
			<div class="d-flex justify-content-center my-4">
		    	<div class="card text-white bg-dark p-3 shadow-sm" style="width: 80%;">
		        	<div class="card-body">
			            <?php
			            // Get the last item in cart
			            $cart_items = WC()->cart->get_cart();
			            $last_item = end($cart_items);
			            
			            if ($last_item) {
			                $_product = apply_filters('woocommerce_cart_item_product', $last_item['data'], $last_item, key($cart_items));
			                $product_name = $_product->get_name();
			                ?>
			                <div class="d-flex flex-column justify-content-center align-items-center mb-3">
			                    <div class="woocommerce-product-name text-center">
			                        <strong><?php echo wp_kses_post($product_name); ?></strong>
			                    </div>
			                    <div class="woocommerce-product-price text-center">
			                    	<h3>
				                    	<strong>
				                        <?php 
				                        echo apply_filters(
				                            'woocommerce_cart_item_subtotal',
				                            WC()->cart->get_product_subtotal($_product, $last_item['quantity']),
				                            $last_item,
				                            key($cart_items)
				                        ); 
				                        ?>
				                        </strong>
			                    	</h3>
			                    </div>
			                </div>
		            	<?php } ?>
		           	</div>
		        </div>
			</div>
			<div class="row justify-content-md-center">
			    <div class="col-md-4">
			    	<div class="mt-4">
						<button type="button" class="w-100 btn btn-primary next-step">Next</button>
					</div>	    	
			    </div>
			</div>	    
		</div>
	</div>

	<!-- Step 2: Billing Details -->
	<div class="step-content" data-step="2">		
		<div class="container">
			<div class="row justify-content-md-center">
				<div class="col-md-12 p-4 border">
					<div id="customer_details">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>
					<div class="d-flex justify-content-between mt-4 gap-3">
						<button type="button" class="btn btn-secondary w-50 prev-step">Back</button>
						<button type="button" class="btn btn-primary w-50 next-step">Next</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Step 3: Payment -->
	<div class="step-content" data-step="3">
		<div class="container">
			<div class="row justify-content-md-center">
				<div class="col-md-12 p-4 border">
					<?php do_action('woocommerce_checkout_payment'); ?>
					<?php do_action('woocommerce_checkout_order_review'); ?>

					<div class="text-center mt-4">
						<button type="button" class="btn btn-secondary w-100 prev-step">Back</button>
					</div>
				</div>
			</div>
		</div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>