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

        $checkout_mode = get_option('matchtrader_enable_mtt_checkout', 'default');

        if ($checkout_mode !== 'none') {
            remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 ); 
            add_filter('woocommerce_locate_template', [$this, 'matchtrader_override_templates'], 10, 3);            
            add_action('wp_ajax_apply_coupon_action', [$this, 'apply_coupon_action']);
            add_action('wp_ajax_nopriv_apply_coupon_action', [$this, 'apply_coupon_action']);
        }

        if (get_option('matchtrader_enable_mtt_checkout', 'default') === 'default') {
            add_action('woocommerce_review_order_before_payment', [$this, 'add_coupon_form_before_payment']);
            remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 5 ); 
        }

        if (get_option('matchtrader_enable_mtt_checkout', 'default') === 'multi-step') {
            add_filter('woocommerce_checkout_fields', [$this, 'restructure_checkout_fields']);
            add_action('matchtrader_checkout_after_order_review', [$this, 'add_coupon_form_before_payment']);
            add_action('wp', [$this, 'matchtrader_remove_default_order_review_checkout']);
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
        // Get the selected checkout mode
        $checkout_mode = get_option('matchtrader_enable_mtt_checkout', 'default');
        
        // Define override templates based on the selected mode
        $override_templates = [];

        if ($checkout_mode === 'multi-step') {
            $override_templates = [
                'checkout/form-checkout.php' => 'checkout/multistep/form-checkout.php',
                'checkout/form-billing.php'  => 'checkout/multistep/form-billing.php',
                'checkout/form-pay.php'      => 'checkout/multistep/form-pay.php',
            ];
        } else {
            $override_templates = [
                'checkout/form-checkout.php' => 'checkout/default/form-checkout.php',
                'checkout/form-billing.php'  => 'checkout/default/form-billing.php',
                'checkout/form-pay.php'      => 'checkout/default/form-pay.php',
            ];
        }

        // Check if the template should be overridden
        if (array_key_exists($template_name, $override_templates)) {
            $plugin_template = MATCHTRADERPLUGIN_PATH . 'woocommerce/' . $override_templates[$template_name];
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }

        return $template;
    }


    /**
     * Customize and prefill WooCommerce checkout fields
     *
     * @param array $fields
     * @return array
     */
    public function restructure_checkout_fields($fields) {
        // Remove shipping fields
        unset($fields['shipping']);

        // Unset all billing fields to replace them with custom fields
        unset($fields['billing']);

        // Get session data
        $account_data = WC()->session->get('matchtrader_account_data');
        $is_prefilled = !empty($account_data); // Check if session data exists

         // Helper function to set custom attributes
        function get_custom_attributes($field_name, $is_prefilled) {
            return $is_prefilled ? ['readonly' => 'readonly', 'class' => 'matchtrader-readonly'] : [];
        }

        // Get country and state from session
        $country = (!empty($account_data['addressDetails']['country'])) ? sanitize_text_field($account_data['addressDetails']['country']) : '';
        $state   = (!empty($account_data['addressDetails']['state'])) ? sanitize_text_field($account_data['addressDetails']['state']) : '';

        // Get available states for the country
        $states = WC()->countries->get_states($country);
        $has_states = !empty($states); // True if country has predefined states

        // Add customized billing fields with WooCommerce classes
        $fields['billing'] = [
            'billing_first_name' => [
                'label' => __('First Name', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-first'],
                'input_class' => ['input-text'],
                'placeholder' => __('First Name', 'matchtraderplatform'),
                'default' => $account_data['personalDetails']['firstname'] ?? '',
                'custom_attributes' => get_custom_attributes('billing_first_name', $is_prefilled),
            ],
            'billing_last_name' => [
                'label' => __('Last Name', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-last'],
                'input_class' => ['input-text'],
                'placeholder' => __('Last Name', 'matchtraderplatform'),
                'clear' => true,
                'default' => $account_data['personalDetails']['lastname'] ?? '',
                'custom_attributes' => get_custom_attributes('billing_last_name', $is_prefilled),
            ],
            'billing_email' => [
                'label' => __('Email', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-first'],
                'input_class' => ['input-text'],
                'placeholder' => __('Email', 'matchtraderplatform'),
                'default' => $account_data['email'] ?? '',
                'custom_attributes' => get_custom_attributes('billing_email', $is_prefilled),
            ],
            'billing_phone' => [
                'label' => __('Phone Number', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-last'],
                'input_class' => ['input-text'],
                'placeholder' => __('Phone Number', 'matchtraderplatform'),
                'clear' => true,
                'default' => $account_data['contactDetails']['phoneNumber'] ?? '',
                'custom_attributes' => get_custom_attributes('billing_phone', $is_prefilled),
            ],
            'billing_address_1' => [
                'label' => __('Address', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-wide'],
                'input_class' => ['input-text'],
                'placeholder' => __('Address', 'matchtraderplatform'),
                'default' => $account_data['addressDetails']['address'] ?? '',
                'custom_attributes' => get_custom_attributes('billing_address_1', $is_prefilled),
            ],
            'billing_country' => [
                'label' => __('Country', 'matchtraderplatform'),
                'required' => true,
                'type' => 'select',
                'class' => ['form-row-first', 'update_totals_on_change'],
                'input_class' => ['input-text'],
                'options' => WC()->countries->get_countries(),
                'default' => $country,
                'custom_attributes' => get_custom_attributes('billing_country', $is_prefilled),
            ],
            'billing_state' => [
                'label' => __('State/Region', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-last'],
                'input_class' => ['input-text'],
                'placeholder' => __('State/Region', 'matchtraderplatform'),
                'clear' => true,
                'type' => $has_states ? 'select' : 'text',
                'options' => $has_states ? ['' => __('Select State', 'matchtraderplatform')] + $states : [],
                'default' => $state,
                'custom_attributes' => get_custom_attributes('billing_state', $is_prefilled),
            ],
            
            'billing_city' => [
                'label' => __('City', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-first'],
                'input_class' => ['input-text'],
                'placeholder' => __('City', 'matchtraderplatform'),
                'default' => $account_data['addressDetails']['city'] ?? '',
                'custom_attributes' => get_custom_attributes('billing_city', $is_prefilled),
            ],
            'billing_postcode' => [
                'label' => __('Postal Code', 'matchtraderplatform'),
                'required' => true,
                'class' => ['form-row-last'],
                'input_class' => ['input-text'],
                'placeholder' => __('Postal Code', 'matchtraderplatform'),
                'clear' => true,
                'default' => $account_data['addressDetails']['postCode'] ?? '',
                'custom_attributes' => get_custom_attributes('billing_postcode', $is_prefilled),
            ],
        ];

        return $fields;
    }

    /**
     * Adjust WooCommerce Checkout Layout by Removing Default Sections
     */
    public function matchtrader_remove_default_order_review_checkout() {
        remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);

        add_action('woocommerce_checkout_before_order_review', 'woocommerce_order_review', 10);
        add_action('woocommerce_review_order_before_payment', 'woocommerce_order_review', 10);
        add_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    }

    /**
     * Redirect to Checkout After Adding Product (For Single Product Checkout)
     * 
     * @return string Checkout URL
     */
    public function redirect_to_checkout() {
        return wc_get_checkout_url();
    }

    /**
     * apply_coupon_action
     */
    public function apply_coupon_action()
    {
        if (!isset($_POST['coupon_code'])) {
            wp_send_json_error('Coupon code not provided.');
        }

        $coupon_code = sanitize_text_field($_POST['coupon_code']);
        WC()->cart->add_discount($coupon_code);

        if (wc_notice_count('error') > 0) {
            $errors = wc_get_notices('error');
            wc_clear_notices();
            wp_send_json_error(join(', ', wp_list_pluck($errors, 'notice')));
        }

        wp_send_json_success();
    }
    
    /**
     * add_coupon_form_before_payment
     */
    public function add_coupon_form_before_payment()
    {
        echo '<div class="matchtrader-coupon-form p-1">
            <label class="mb-2" for="coupon_code_field">If you have a coupon code, please apply it below.</label>    
            <div class="input-group mb-3">                
                <input type="text" id="coupon_code_field" class="form-control" placeholder="Coupon Code" aria-label="Apply Coupon Code" aria-describedby="apply_coupon_button">
                <button class="btn btn-success" type="button" id="apply_coupon_button">Apply Coupon</button>
            </div>        
        </div>';
    }
}

// Initialize the class instance
new MatchTrader_Public_WooCommerce();