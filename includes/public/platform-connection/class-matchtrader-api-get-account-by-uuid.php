<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MatchTrader_Get_Account_By_UUID {
    private $api_url;
    private $api_key;
    private $save_logs;

    public function __construct() {
        // Determine the environment (Sandbox or Live)
        $env = get_option('matchtrader_env', 'sandbox');
        if ($env === 'sandbox') {
            $this->api_url = get_option('matchtrader_sandbox_url', 'https://broker-api-demo.match-trader.com');
            $this->api_key = get_option('matchtrader_sandbox_key', '');
        } else {
            $this->api_url = get_option('matchtrader_live_url', 'https://broker-api.match-trader.com');
            $this->api_key = get_option('matchtrader_live_key', '');
        }

        $this->save_logs = get_option('matchtrader_save_logs', false);

        // Log initialization
        $this->log_message("Initializing MatchTrader_Get_Account_By_UUID with environment: $env");
        add_filter('woocommerce_checkout_fields', [$this, 'prefill_checkout_fields']);
    }

    /**
     * Handle the UUID parameter in URL and fetch account details.
     */
    public function handle_uuid_param() {
        if (!is_checkout() || !isset($_GET['uuid'])) {
            return;
        }

        $uuid = sanitize_text_field($_GET['uuid']);
        $this->log_message("UUID detected in URL: $uuid");

        if (!WC()->session) {
            $this->log_message("WooCommerce session is not available.", 'error');
            return;
        }

        // Check if session data already exists
        $cached_data = WC()->session->get('matchtrader_account_data');
        if ($cached_data && isset($cached_data['uuid']) && $cached_data['uuid'] === $uuid) {
            $this->log_message("Using cached account data for UUID: $uuid");
            return;
        }

        // Fetch data from API
        $account_data = $this->get_account_by_uuid($uuid);
        if ($account_data) {
            WC()->session->set('matchtrader_account_data', $account_data);
            $this->log_message("Stored API response in WooCommerce session.");
        } else {
            $this->log_message("Failed to fetch account details for UUID: $uuid", 'error');
        }
    }

    /**
     * Fetch account details by UUID using WP Remote GET.
     *
     * @param string $uuid
     * @return array|null
     */
    public function get_account_by_uuid($uuid) {
        $endpoint_url = rtrim($this->api_url, '/') . "/v1/accounts/by-uuid/" . ltrim($uuid, '/');

        $this->log_message("Making API request to: $endpoint_url");

        $response = wp_remote_get($endpoint_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ],
            'timeout' => 10
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->log_message("API request failed: $error_message", 'error');
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($this->save_logs) {
            $this->log_message("API Response: " . print_r($data, true));
        }

        return $data;
    }

    /**
     * Prefill WooCommerce checkout fields with API response data.
     *
     * @param array $fields
     * @return array
     */
    public function prefill_checkout_fields($fields) {
        if (!WC()->session) {
            $this->log_message("WooCommerce session not available for pre-filling checkout fields.", 'error');
            return $fields;
        }

        $account_data = WC()->session->get('matchtrader_account_data');

        if (!$account_data || !isset($account_data['personalDetails'])) {
            $this->log_message("No account data found in session for pre-filling checkout fields.");
            return $fields;
        }

        $this->log_message("Prefilling WooCommerce checkout fields with API response.");

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

        return $fields;
    }

    /**
     * Log API responses and steps using WordPress default logger.
     *
     * @param string $message
     * @param string $level (default: 'info', options: 'error', 'warning', 'debug')
     */
    private function log_message($message, $level = 'info') {
        if ($this->save_logs) {
            $logger_data = MatchTrader_Helper::connection_response_logger();
            if ($level === 'error') {
                $logger_data['logger']->error($message, $logger_data['context']);
            } elseif ($level === 'warning') {
                $logger_data['logger']->warning($message, $logger_data['context']);
            } elseif ($level === 'debug') {
                $logger_data['logger']->debug($message, $logger_data['context']);
            } else {
                $logger_data['logger']->info($message, $logger_data['context']);
            }
        }
    }
}

// Initialize the class
new MatchTrader_Get_Account_By_UUID();
