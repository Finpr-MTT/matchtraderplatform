<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure the helper class is included
require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/helper/class-matchtrader-helper.php';

class MatchTrader_Create_Trading_Account {
    private $api_url;
    private $api_key;
    private $save_logs;

    public function __construct() {
        // Initialize API settings on class instantiation
        $this->initialize_api_settings();

        // Hook into WooCommerce order status change
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 4);
    }

    /**
     * Initialize API settings
     */
    private function initialize_api_settings() {
        $env = get_option('matchtrader_env', 'sandbox');

        if ($env === 'live') {
            $this->api_url = get_option('matchtrader_live_url', 'https://broker-api.match-trader.com');
            $this->api_key = get_option('matchtrader_live_key', '');
        } else {
            $this->api_url = get_option('matchtrader_sandbox_url', 'https://broker-api-demo.match-trader.com');
            $this->api_key = get_option('matchtrader_sandbox_key', '');
        }

        $this->save_logs = get_option('matchtrader_save_logs', false);
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
        $endpoint = rtrim($this->api_url, '/') . '/v1/accounts/by-email/' . urlencode($email);
        
        $response = wp_remote_get($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ],
            'timeout' => 10
        ]);

        if (is_wp_error($response)) {
            $this->log_api_error($response->get_error_message());
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['uuid'] ?? null;
    }

    /**
     * Create a new account if email does not exist.
     *
     * @param WC_Order $order
     * @return string|null
     */
    private function create_new_account($order) {
        $endpoint = rtrim($this->api_url, '/') . '/v1/accounts';

        $payload = [
            "email" => $order->get_billing_email(),
            "password" => wp_generate_password(),
            "clientType" => "RETAIL",
            "createAsDepositedAccount" => false,
            "personalDetails" => [
                "firstname" => $order->get_billing_first_name(),
                "lastname" => $order->get_billing_last_name(),
            ],
            "contactDetails" => [
                "phoneNumber" => $order->get_billing_phone()
            ]
        ];

        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json'
            ],
            'body'    => json_encode($payload),
            'timeout' => 10
        ]);

        if (is_wp_error($response)) {
            $this->log_api_error($response->get_error_message());
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['uuid'] ?? null;
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
        $endpoint = rtrim($this->api_url, '/') . '/v1/prop/accounts?instantlyActive=false&phaseStep=1';

        $payload = [
            "challengeId" => $challenge_id,
            "accountUuid" => $uuid,
            "name" => $name
        ];

        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json'
            ],
            'body'    => json_encode($payload),
            'timeout' => 10
        ]);

        if (is_wp_error($response)) {
            $this->log_api_error($response->get_error_message());
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $trading_id = $body['id'] ?? null;

        if (!empty($trading_id)) {
            update_post_meta($order_id, '_matchtrader_trading_account_id', $trading_id);
        }
    }

    /**
     * Log API errors using MatchTrader_Helper::connection_response_logger()
     *
     * @param string $error_message
     */
    private function log_api_error($error_message) {
        if ($this->save_logs) {
            $logger_data = MatchTrader_Helper::connection_response_logger();
            $logger_data['logger']->error('API Error: ' . $error_message, $logger_data['context']);
        }
    }
}

// Initialize the class
new MatchTrader_Create_Trading_Account();
