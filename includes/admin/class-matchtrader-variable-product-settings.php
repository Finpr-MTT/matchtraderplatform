<?php
/**
 * Plugin functions and definitions for Admin.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MatchTrader_Variable_Product_Settings {

    public function __construct() {
        // Only apply if MatchTrader is enabled and product configuration is set to "variable"
        if ($this->is_matchtrader_enabled_for_variable_products()) {
            add_action('woocommerce_product_after_variable_attributes', [$this, 'add_matchtrader_challenge_id_field'], 10, 3);
            add_action('woocommerce_save_product_variation', [$this, 'save_matchtrader_challenge_id'], 10, 2);
        }
    }

    /**
     * Check if MatchTrader is enabled and Product Configuration is "variable".
     *
     * @return bool
     */
    private function is_matchtrader_enabled_for_variable_products() {
        $enabled = get_option('matchtrader_enable_mtt_plugin', false);
        $product_config = get_option('matchtrader_product_configuration', '');

        return $enabled && $product_config === 'variable';
    }

    /**
     * Add "MatchTrader Challenge ID" field under variation description.
     *
     * @param int    $loop Variation loop index.
     * @param array  $variation_data Variation data.
     * @param object $variation WooCommerce product variation object.
     */
    public function add_matchtrader_challenge_id_field($loop, $variation_data, $variation) {
        woocommerce_wp_text_input([
            'id'          => 'matchtrader_challenge_id_' . $variation->ID,
            'name'        => 'matchtrader_challenge_id[' . $variation->ID . ']',
            'value'       => get_post_meta($variation->ID, '_matchtrader_challenge_id', true),
            'label'       => __('MatchTrader Challenge ID', 'matchtraderplatform'),
            'desc_tip'    => true,
            'description' => __('Enter the MatchTrader Challenge ID for this variation.', 'matchtraderplatform'),
            'type'        => 'text',
            'wrapper_class' => 'form-row form-row-full',
        ]);
    }

    /**
     * Save "MatchTrader Challenge ID" field when variations are saved.
     *
     * @param int   $variation_id WooCommerce variation ID.
     * @param int   $i Loop index.
     */
    public function save_matchtrader_challenge_id($variation_id, $i) {
        if (isset($_POST['matchtrader_challenge_id'][$variation_id])) {
            $challenge_id = sanitize_text_field($_POST['matchtrader_challenge_id'][$variation_id]);
            update_post_meta($variation_id, '_matchtrader_challenge_id', $challenge_id);
        }
    }
}

// Initialize the class
new MatchTrader_Variable_Product_Settings();