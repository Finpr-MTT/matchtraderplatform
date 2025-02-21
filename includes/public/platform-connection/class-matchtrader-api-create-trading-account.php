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

class MatchTrader_Create_Trading_Account {

    public function __construct() {
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 4);
    }

    /**
     * Handle order status change.
     *
     * @param int      $order_id  WooCommerce Order ID.
     * @param string   $old_status  Previous order status.
     * @param string   $new_status  New order status.
     * @param WC_Order $order WooCommerce Order object.
     */
    public function handle_order_status_change($order_id, $old_status, $new_status, $order) {
        if ('completed' !== $new_status || 'completed' === $old_status) {
            return;
        }

        // Prevent duplicate API calls
        if (false !== get_transient('send_api_lock_' . $order_id)) {
            return;
        }
        set_transient('send_api_lock_' . $order_id, true, 3);

        // Check if connection was already completed
        if (get_post_meta($order_id, '_matchtrader_connection_completed', true)) {
            return;
        }

        // Fetch order details
        $uuid = get_post_meta($order_id, '_matchtrader_account_uuid', true);
        $challenge_id = get_post_meta($order_id, '_matchtrader_challenge_id', true);
        $email = $order->get_billing_email();
        $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

        // If UUID exists, create a trading account
        if (!empty($uuid)) {
            $this->create_trading_account($challenge_id, $uuid, $name, $order_id);
            return;
        }

        // Fetch UUID by Email
        $uuid = $this->get_account_uuid_by_email($email);
        if (!empty($uuid)) {
            update_post_meta($order_id, '_matchtrader_account_uuid', $uuid);
            $this->create_trading_account($challenge_id, $uuid, $name, $order_id);
            return;
        }

        // If email is not found, create a new account
        $uuid = $this->create_new_account($order);
        if (!empty($uuid)) {
            update_post_meta($order_id, '_matchtrader_account_uuid', $uuid);
            $this->create_trading_account($challenge_id, $uuid, $name, $order_id);
        }

        // Mark as processed and remove transient lock
        update_post_meta($order_id, '_matchtrader_connection_completed', 1);
        delete_transient('send_api_lock_' . $order_id);
    }

    /**
     * Get UUID by checking email.
     *
     * @param string $email
     * @return string|null
     */
    private function get_account_uuid_by_email($email) {
        $endpoint = "v1/accounts/by-email/" . urlencode($email);
        $response = MatchTrader_API_Helper::get_request($endpoint);

        return $response['uuid'] ?? null;
    }

    /**
     * Create a new MatchTrader account using WooCommerce billing details.
     *
     * @param WC_Order $order WooCommerce Order Object
     * @return string|null
     */
    private function create_new_account($order) {
        $endpoint = "v1/accounts";

        $payload = [
            "email" => $order->get_billing_email(),
            "password" => wp_generate_password(),
            "clientType" => "RETAIL",
            "personalDetails" => [
                "firstname" => $order->get_billing_first_name(),
                "lastname" => $order->get_billing_last_name(),
                "citizenship" => $order->get_billing_country(), // Assuming country as citizenship
                "language" => "en" // Defaulting to English, can be dynamic if needed
            ],
            "contactDetails" => [
                "phoneNumber" => $order->get_billing_phone()
            ],
            "addressDetails" => [
                "country" => $order->get_billing_country(),
                "state" => $order->get_billing_state(),
                "city" => $order->get_billing_city(),
                "postCode" => $order->get_billing_postcode(),
                "address" => $order->get_billing_address_1()
            ],
            "bankingDetails" => [
                "accountName" => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
            ],
            "leadDetails" => [
                "source" => "woocommerce"
            ]
        ];

        // Send API request using the centralized helper
        $response = MatchTrader_API_Helper::post_request($endpoint, $payload);
        $uuid = $response['uuid'] ?? null;

        if (!empty($uuid)) {
            update_post_meta($order->get_id(), '_matchtrader_account_uuid', $uuid);
            $order->add_order_note(__('MatchTrader Account Created: ' . $uuid, 'matchtraderplatform'));
        }

        return $uuid;
    }


    /**
     * Create a Trading Account.
     *
     * @param string $challenge_id
     * @param string $uuid
     * @param string $name
     * @param int $order_id
     */
    private function create_trading_account($challenge_id, $uuid, $name, $order_id) {
        $endpoint = "v1/prop/accounts?instantlyActive=false&phaseStep=1";

        $payload = [
            "challengeId" => $challenge_id,
            "accountUuid" => $uuid,
            "name" => $name
        ];

        $response = MatchTrader_API_Helper::post_request($endpoint, $payload);
        $trading_id = $response['id'] ?? null;

        if (!empty($trading_id)) {
            update_post_meta($order_id, '_matchtrader_trading_account_id', $trading_id);
            $order = wc_get_order($order_id);
            $order->add_order_note(__('MatchTrader Trading Account Created: ' . $trading_id, 'matchtraderplatform'));
        }
    }
}

// Initialize the class
new MatchTrader_Create_Trading_Account();