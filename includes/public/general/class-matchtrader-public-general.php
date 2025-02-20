<?php
/**
 * Plugin functions and definitions for Public General.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MatchTrader_Public_General {
    /**
     * Constructor to initialize the public WooCommerce modifications.
     */
    public function __construct() {
        // Restrict Frontend Website Access
        if (get_option('matchtrader_disable_frontend_route', false)) {
            add_action('template_redirect', [$this, 'restrict_frontend_website_access']);
        }
    }

    /**
     * Restrict Frontend Website Access if matchtrader_disable_frontend_route is enabled
     */
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

        // Allow Checkout Page
        if (strpos($request_uri, '/checkout/') === 0) {
            return;
        }

        // Allow admins to access all pages
        if (is_user_logged_in() && current_user_can('administrator')) {
            return;
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

// Initialize the class instance
new MatchTrader_Public_General();
