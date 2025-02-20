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
require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/helper/class-matchtrader-helper.php';

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

        // Hook into WooCommerce Checkout process
        add_action('template_redirect', [$this, 'handle_uuid_param']);
        add_filter('woocommerce_checkout_fields', [$this, 'prefill_checkout_fields']);
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
     * Fetch account details by UUID using WP Remote GET.
     *
     * @param string $uuid
     * @return array|null
     */
    private function get_account_by_uuid($uuid) {
        $endpoint_path = "v1/accounts/by-uuid/{$uuid}";
        $endpoint_url = rtrim($this->api_url, '/') . '/' . ltrim($endpoint_path, '/');

        $response = wp_remote_get($endpoint_url, [
            'headers' => [
                'Authorization' => $this->api_key,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ],
            'timeout' => 10
        ]);

        if (is_wp_error($response)) {
            $this->log_api_error($response->get_error_message());
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($this->save_logs) {
            $this->log_api_response($data);
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

    /**
     * Log API responses using MatchTrader_Helper::connection_response_logger()
     *
     * @param array $data
     */
    private function log_api_response($data) {
        if ($this->save_logs) {
            $logger_data = MatchTrader_Helper::connection_response_logger();
            $logger_data['logger']->info('API Response: ' . wp_json_encode($data), $logger_data['context']);
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
new MatchTrader_Get_Account_By_UUID();
