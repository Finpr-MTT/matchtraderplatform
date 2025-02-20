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
     * Uses WooCommerce logging system.
     *
     * @return array Logger and context.
     */
    public static function connection_response_logger() {
        $logger = wc_get_logger();
        $context = array('source' => 'matchtrader_connection_response_log');
        return array('logger' => $logger, 'context' => $context);
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
}
