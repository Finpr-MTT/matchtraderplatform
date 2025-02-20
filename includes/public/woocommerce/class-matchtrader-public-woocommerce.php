<?php
/**
 * Plugin functions and definitions for Public.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MatchTrader_Public_WooCommerce {

    /**
     * Constructor to initialize the public WooCommerce modifications.
     */
    public function __construct() {
        // Skip Cart Page
        if (get_option('matchtrader_skip_cart_page', false)) {
            add_filter('woocommerce_add_to_cart_redirect', [$this, 'skip_cart_page']);
            add_filter('wc_add_to_cart_message_html', '__return_false');
            add_filter('woocommerce_enable_order_notes_field', '__return_false');
        }

        // Disable Shop Page
        if (get_option('matchtrader_disable_shop_page', false)) {
            add_action('template_redirect', [$this, 'disable_shop_page']);
            add_action('template_redirect', [$this, 'disable_cart_page']);
        }

        if (get_option('matchtrader_enable_mtt_checkout', false)) {
            add_filter('woocommerce_locate_template', [$this, 'matchtrader_override_templates'], 10, 3);
        }

        // Disable Product Page
        if (get_option('matchtrader_disable_product_page', false)) {
            add_action('template_redirect', [$this, 'disable_product_page']);
        }

        // Single Product Checkout Mode
        if (get_option('matchtrader_checkout_mode', 'single') === 'single') {
            add_filter('woocommerce_add_cart_item_data', [$this, 'empty_cart_before_add_product'], 10, 2);
            add_filter('woocommerce_add_to_cart_redirect', [$this, 'redirect_to_checkout']);
        }


    }

    /**
     * Skip Cart Page and Redirect to Checkout
     * 
     * @return string Checkout URL
     */
    public function skip_cart_page() {
        return wc_get_checkout_url();
    }

    /**
     * Disable the WooCommerce Shop Page
     */
    public function disable_shop_page() {
        if (is_shop() || is_product_category() || is_product_tag()) {
            wp_redirect(home_url()); 
            exit;
        }
    }

    /**
     * Disable the WooCommerce Cart
     */
    public function disable_cart_page() {
        if ( is_page( 'cart' ) || ( isset( $_GET['cancel_order'] ) && $_GET['cancel_order'] === 'true' ) ) {
            wp_redirect(home_url()); 
            exit;
        }
    }

    /**
     * Disable the WooCommerce Product Page
     */
    public function disable_product_page() {
        if (is_product()) {
            wp_redirect(home_url()); 
            exit;
        }
    }

    /**
     * Empty Cart Before Adding New Product (For Single Product Checkout)
     * 
     * @param array $cart_item_data Cart item data
     * @param int $product_id Product ID being added
     * @return array Modified cart item data
     */
    public function empty_cart_before_add_product($cart_item_data, $product_id) {
        WC()->cart->empty_cart();
        return $cart_item_data;
    }

    public function matchtrader_override_templates($template, $template_name, $template_path) {
        $override_templates = [        
            'checkout/form-checkout.php',
            'checkout/form-billing.php',
            'checkout/form-pay.php',
        ];

        if (in_array($template_name, $override_templates)) {
            $plugin_template = MATCHTRADERPLUGIN_PATH . 'woocommerce/' . $template_name;
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }


    /**
     * Redirect to Checkout After Adding Product (For Single Product Checkout)
     * 
     * @return string Checkout URL
     */
    public function redirect_to_checkout() {
        return wc_get_checkout_url();
    }
}

// Initialize the class instance
new MatchTrader_Public_WooCommerce();