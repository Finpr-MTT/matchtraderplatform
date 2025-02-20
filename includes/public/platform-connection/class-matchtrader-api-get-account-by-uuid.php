<?php
/**
 * Plugin functions and definitions for Get Account by Uuid.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure the helper class is included
require_once MATCHTRADERPLUGIN_PATH . 'includes/helper/class-matchtrader-api-helper.php';

class MatchTrader_Get_Account_By_UUID {

    public function __construct() {
        // Hook into WooCommerce Checkout process
        add_action('template_redirect', [$this, 'handle_uuid_param'], 3);
        add_filter('woocommerce_checkout_fields', [$this, 'prefill_checkout_fields']);

        // Hook into order update to save UUID
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_uuid_challenge_id_to_order_meta'], 10, 2);
    }

    /**
     * Handle the UUID parameter in URL and fetch account details.
     */
    public function handle_uuid_param() {
        if (!is_checkout()) {
            return;
        }

        if (isset($_GET['uuid']) && !empty($_GET['uuid'])) {
            $uuid = sanitize_text_field($_GET['uuid']);
            $account_data = $this->get_account_by_uuid($uuid);

            if ($account_data) {
                WC()->session->set('matchtrader_account_data', $account_data);
            }
        }
    }

    /**
     * Fetch account details by UUID using Centralized API Helper.
     *
     * @param string $uuid
     * @return array|null
     */
    private function get_account_by_uuid($uuid) {
        $endpoint = "v1/accounts/by-uuid/{$uuid}";
        return MatchTrader_API_Helper::get_request($endpoint);
    }

    /**
     * Save UUID and Challenge ID to WooCommerce order meta after checkout.
     *
     * @param int $order_id WooCommerce Order ID
     * @param array $data Order data
     */
    public function save_uuid_challenge_id_to_order_meta($order_id, $data) {
        $uuid = '';
        $challenge_id = '';

        // Check if UUID exists in the URL
        if (isset($_GET['uuid']) && !empty($_GET['uuid'])) {
            $uuid = sanitize_text_field($_GET['uuid']);
        }

        // Check if UUID is in WooCommerce session (API response)
        $account_data = WC()->session->get('matchtrader_account_data');
        if ($account_data && isset($account_data['uuid'])) {
            $uuid = sanitize_text_field($account_data['uuid']);
        }

        // Save UUID as order meta if available
        if (!empty($uuid)) {
            update_post_meta($order_id, '_matchtrader_account_uuid', $uuid);
        }

        // Get Challenge ID from the cart
        foreach (WC()->cart->get_cart() as $cart_item) {
            if (isset($cart_item['variation_id']) && !empty($cart_item['variation_id'])) {
                $challenge_id = get_post_meta($cart_item['variation_id'], '_matchtrader_challenge_id', true);
                break; // Save only the first match (assuming 1 product in cart)
            }
        }

        // Save Challenge ID as order meta if available
        if (!empty($challenge_id)) {
            update_post_meta($order_id, '_matchtrader_challenge_id', $challenge_id);
        }
         update_post_meta($order_id, '_matchtrader_connection_completed', 0);
    }

    /**
     * Prefill WooCommerce checkout fields with API response data.
     *
     * @param array $fields
     * @return array
     */
    public function prefill_checkout_fields($fields) {
        $account_data = WC()->session->get('matchtrader_account_data');

        if (!$account_data || !isset($account_data['personalDetails'])) {
            return $fields;
        }

        // Prefill checkout fields from API response
        if (!empty($account_data['personalDetails']['firstname'])) {
            $fields['billing']['billing_first_name']['default'] = sanitize_text_field($account_data['personalDetails']['firstname']);
        }
        if (!empty($account_data['personalDetails']['lastname'])) {
            $fields['billing']['billing_last_name']['default'] = sanitize_text_field($account_data['personalDetails']['lastname']);
        }
        if (!empty($account_data['email'])) {
            $fields['billing']['billing_email']['default'] = sanitize_email($account_data['email']);
        }
        if (!empty($account_data['contactDetails']['phoneNumber'])) {
            $fields['billing']['billing_phone']['default'] = sanitize_text_field($account_data['contactDetails']['phoneNumber']);
        }
        if (!empty($account_data['addressDetails']['country'])) {
            $fields['billing']['billing_country']['default'] = sanitize_text_field($account_data['addressDetails']['country']);
        }
        if (!empty($account_data['addressDetails']['state'])) {
            $fields['billing']['billing_state']['default'] = sanitize_text_field($account_data['addressDetails']['state']);
        }
        if (!empty($account_data['addressDetails']['city'])) {
            $fields['billing']['billing_city']['default'] = sanitize_text_field($account_data['addressDetails']['city']);
        }
        if (!empty($account_data['addressDetails']['postCode'])) {
            $fields['billing']['billing_postcode']['default'] = sanitize_text_field($account_data['addressDetails']['postCode']);
        }
        if (!empty($account_data['addressDetails']['address'])) {
            $fields['billing']['billing_address_1']['default'] = sanitize_text_field($account_data['addressDetails']['address']);
        }

        return $fields;
    }
}

// Initialize the class
new MatchTrader_Get_Account_By_UUID();