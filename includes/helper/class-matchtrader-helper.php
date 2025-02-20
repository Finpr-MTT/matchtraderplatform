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

class MatchTrader_Helper {

    /**
     * Logger function to track API responses.
     * Uses WooCommerce logging system if available, falls back to WordPress error log otherwise.
     *
     * @return array Logger and context.
     */
    public static function connection_response_logger() {
        // Ensure WooCommerce is active before using wc_get_logger()
        if (!function_exists('wc_get_logger')) {
            error_log('[MatchTrader] WooCommerce logger is not available. Using WordPress error log instead.');
            return [
                'logger'  => new MatchTrader_WP_Logger(), // Fallback logger
                'context' => ['source' => 'matchtrader_connection_response_log']
            ];
        }

        $logger = wc_get_logger();
        $context = ['source' => 'matchtrader_connection_response_log'];
        return ['logger' => $logger, 'context' => $context];
    }

    /**
     * Masks API Key for logging/debugging purposes.
     *
     * @param string $api_key The API key to be masked.
     * @return string Masked API key.
     */
    public static function connection_mask_api_key($api_key) {
        $key_length = strlen($api_key);
        if ($key_length <= 8) {
            return str_repeat('*', $key_length); // Mask whole key if too short
        }
        $start = substr($api_key, 0, 4);
        $end = substr($api_key, -4);
        $masked = str_repeat('*', $key_length - 8); // Masking middle part
        return $start . $masked . $end;
    }

    public function restrict_frontend_website_access() {
        if (is_admin()) {
            return; // Allow wp-admin access
        }

        // Get the current request URI
        $request_uri = $_SERVER['REQUEST_URI'];

        // Allow WooCommerce API requests
        if (strpos($request_uri, '/wp-json/wc/v3/') === 0) {
            return;
        }

        // Allow REST API authentication requests (optional)
        if (strpos($request_uri, '/wp-json/') === 0 && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return;
        }

        // Allow admin-ajax.php for WooCommerce & WP functions
        if (strpos($request_uri, '/wp-admin/admin-ajax.php') === 0) {
            return;
        }

        // Check if the user is logged in and is an admin
        if (is_user_logged_in() && current_user_can('administrator')) {
            return; // Allow admins to access all pages
        }

        // Block all other requests and return JSON response
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode([
            'status' => 403,
            'error' => 'Access Denied',
            'message' => 'You do not have permission to access this resource.'
        ]);
        exit;
    }
}


/**
 * Fallback WordPress Logger for MatchTrader if WooCommerce Logger is not available.
 */
class MatchTrader_WP_Logger {
    public function info($message, $context = []) {
        error_log('[INFO] ' . $message);
    }

    public function error($message, $context = []) {
        error_log('[ERROR] ' . $message);
    }

    public function warning($message, $context = []) {
        error_log('[WARNING] ' . $message);
    }

    public function debug($message, $context = []) {
        error_log('[DEBUG] ' . $message);
    }
}