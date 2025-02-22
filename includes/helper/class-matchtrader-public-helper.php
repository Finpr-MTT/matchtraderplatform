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
        $logger = wc_get_logger();
        $context = ['source' => 'matchtrader_connection_response_log'];
        return ['logger' => $logger, 'context' => $context];
    }

    /**
     * Log API errors in WooCommerce order notes and logs.
     *
     * @param WC_Order $order WooCommerce Order Object
     * @param string $message The error message to log
     */
    private function log_api_error($order, $message) {
        // Add to WooCommerce Order Notes
        $order->add_order_note($message);

        // Add to WooCommerce Logs
        $logger = wc_get_logger();
        $context = ['source' => 'matchtraderplatform_api_response'];
        $logger->error($message, $context);
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