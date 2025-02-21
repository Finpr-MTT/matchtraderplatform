<?php
/**
 * Plugin functions and definitions for Helper.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MatchTrader_API_Helper {
    private static $api_url;
    private static $api_key;
    private static $save_logs;

    /**
     * Initialize API settings.
     */
    public static function initialize_api_settings() {
        $env = get_option('matchtrader_env', 'sandbox');

        if ($env === 'live') {
            self::$api_url = get_option('matchtrader_live_url', 'https://broker-api.match-trader.com');
            self::$api_key = get_option('matchtrader_live_key', '');
        } else {
            self::$api_url = get_option('matchtrader_sandbox_url', 'https://broker-api-demo.match-trader.com');
            self::$api_key = get_option('matchtrader_sandbox_key', '');
        }

        self::$save_logs = get_option('matchtrader_save_logs', false);
    }

    /**
     * Perform a GET request to the MatchTrader API.
     *
     * @param string $endpoint
     * @return array|null
     */
    public static function get_request($endpoint) {
        self::initialize_api_settings();

        $url = rtrim(self::$api_url, '/') . '/' . ltrim($endpoint, '/');
        
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$api_key,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json'
            ],
            'timeout' => 10
        ]);

        return self::handle_api_response($response, 'GET', $url);
    }

    /**
     * Perform a POST request to the MatchTrader API.
     *
     * @param string $endpoint
     * @param array $payload
     * @return array|null
     */
    public static function post_request($endpoint, $payload) {
        self::initialize_api_settings();

        $url = rtrim(self::$api_url, '/') . '/' . ltrim($endpoint, '/');
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$api_key,
                'Content-Type'  => 'application/json'
            ],
            'body'    => json_encode($payload),
            'timeout' => 10
        ]);

        return self::handle_api_response($response, 'POST', $url, $payload);
    }

    /**
     * Handle API response based on status code.
     *
     * @param array|WP_Error $response
     * @param string $method
     * @param string $url
     * @param array|null $payload
     * @return array|null
     */
    private static function handle_api_response($response, $method, $url, $payload = null) {
        if (is_wp_error($response)) {
            self::log_api_error($method . " Request Failed: " . $response->get_error_message());
            return null;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        switch ($status_code) {
            case 200:
                self::log_api_success("$method Request to $url successful", $body);
                return $body;
            case 204:
                self::log_api_success("$method Request to $url successful but no content");
                return null;
            case 400:
                self::log_api_error("400 Bad Request - Invalid request to $url", $body);
                return null;
            case 401:
                self::log_api_error("401 Unauthorized - Invalid API key for $url", $body);
                return null;
            case 403:
                self::log_api_error("403 Forbidden - No permission for $url", $body);
                return null;
            case 422:
                self::log_api_error("422 Unprocessable Entity - Request correct but cannot be processed for $url", $body);
                return null;
            case 500:
                self::log_api_error("500 Internal Server Error - MatchTrader API failed at $url", $body);
                return null;
            default:
                self::log_api_error("$status_code Unexpected API response from $url", $body);
                return null;
        }
    }

    /**
     * Log API Success using MatchTrader_Helper::connection_response_logger()
     *
     * @param string $message
     * @param array|null $data
     */
    private static function log_api_success($message, $data = null) {
        if (self::$save_logs) {
            $logger_data = MatchTrader_Helper::connection_response_logger();
            $log_message = $message;
            if (!empty($data)) {
                $log_message .= "\nResponse Data: " . wp_json_encode($data);
            }
            $logger_data['logger']->info($log_message, $logger_data['context']);
        }
    }

    /**
     * Log API errors using MatchTrader_Helper::connection_response_logger()
     *
     * @param string $message
     * @param array|null $data
     */
    private static function log_api_error($message, $data = null) {
        if (self::$save_logs) {
            $logger_data = MatchTrader_Helper::connection_response_logger();
            $log_message = $message;
            if (!empty($data)) {
                $log_message .= "\nError Details: " . wp_json_encode($data);
            }
            $logger_data['logger']->error($log_message, $logger_data['context']);
        }
    }
}

// Initialize API settings on class load
MatchTrader_API_Helper::initialize_api_settings();