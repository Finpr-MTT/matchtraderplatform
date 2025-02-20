<?php
/**
 * Plugin functions and definitions for Public.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MatchTrader_Variation_Manager {
    private $default_product_id;

    public function __construct() {
        if (get_option('matchtrader_enable_checkout_selection', false)) {
            $this->default_product_id = get_option('matchtrader_default_product_cart', 89); // Default fallback to 1202

            add_action('template_redirect', [$this, 'add_default_variation_to_cart'], 5);
            add_filter('woocommerce_checkout_redirect_empty_cart', '__return_false');

            // Initialize variation switcher hooks
            add_action('woocommerce_checkout_before_customer_details', [$this, 'display_variant_selector'], 5);
            add_action('wp_ajax_matchtrader_update_cart', [$this, 'update_cart']);
            add_action('wp_ajax_nopriv_matchtrader_update_cart', [$this, 'update_cart']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        }
    }

    public function enqueue_scripts() {
        if (is_checkout()) {
            wp_enqueue_style('matchtrader-switch-variant-style', MATCHTRADERPLUGIN_URL . 'assets/css/matchtrader-switch-variant.css', [], MATCHTRADERPLUGIN_VERSION );
            wp_enqueue_script('matchtrader-switch-variant', MATCHTRADERPLUGIN_URL . 'assets/js/matchtrader-switch-variant.js', ['jquery'], MATCHTRADERPLUGIN_VERSION, true);            
            wp_localize_script('matchtrader-switch-variant', 'matchtraderAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('matchtrader_nonce'),
            ]);
        }
    }

    public function add_default_variation_to_cart() {
        if (!is_checkout()) {
            return;
        }

        // Get the current endpoint
        $current_endpoint = WC()->query->get_current_endpoint();

        // Exclude 'order-received' and 'order-pay' from triggering this function
        if (in_array($current_endpoint, ['order-received', 'order-pay'])) {
            return;
        }

        if (WC()->cart->is_empty()) {
            $product = wc_get_product($this->default_product_id);

            if ($product && $product->is_type('variable')) {
                $default_attributes = $product->get_default_attributes();

                // Format attributes correctly for WooCommerce
                $formatted_attributes = [];
                foreach ($default_attributes as $key => $value) {
                    $formatted_attributes['attribute_' . $key] = $value;
                }

                $variation_id = $this->get_variation_id_by_attributes($product, $formatted_attributes);

                if ($variation_id) {
                    WC()->cart->add_to_cart($this->default_product_id, 1, $variation_id, $formatted_attributes);
                    wp_safe_redirect(wc_get_checkout_url());
                    exit;
                }
            }
        }
    }

    public function display_variant_selector() {
        if (WC()->cart->is_empty()) return;
        
        $cart_items = WC()->cart->get_cart();
        $product_id = 0;
        $selected_variation_id = 0;
        
        foreach ($cart_items as $cart_item) {
            $product_id = $cart_item['product_id'];
            if (isset($cart_item['variation_id']) && $cart_item['variation_id'] > 0) {
                $selected_variation_id = $cart_item['variation_id'];
            }
            break;
        }
        
        if (!$product_id) return;

        $product = wc_get_product($product_id);
        if (!$product->is_type('variable')) return;

        $variations = $product->get_available_variations();
        $attributes = $product->get_variation_attributes();
        $selected_attributes = [];

        if ($selected_variation_id) {
            foreach ($variations as $variation) {
                if ($variation['variation_id'] == $selected_variation_id) {
                    $selected_attributes = $variation['attributes'];
                    break;
                }
            }
        }

        echo '<div id="matchtrader-variant-switcher">';
        echo '<h3>Select Account</h3>';

        foreach ($attributes as $attribute_name => $options) {
            echo '<strong><label>' . wc_attribute_label($attribute_name) . '</label></strong>';
            echo '<div class="matchtrader-radio-group" data-attribute="' . esc_attr($attribute_name) . '">';

            foreach ($options as $option) {
                $selected = (isset($selected_attributes['attribute_' . sanitize_title($attribute_name)]) && $selected_attributes['attribute_' . sanitize_title($attribute_name)] == $option) ? ' checked' : '';
                echo '<div class="matchtrader-radio-option">';
                echo '<input type="radio" name="' . esc_attr($attribute_name) . '" value="' . esc_attr($option) . '" class="matchtrader-switch"' . $selected . '>';
                echo '<label class="matchtrader-radio-label">' . esc_html($option) . '</label>';
                echo '</div>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    public function update_cart() {
        check_ajax_referer('matchtrader_nonce', 'security');
        
        $variation_attributes = isset($_POST['variation_attributes']) ? $_POST['variation_attributes'] : [];
        if (empty($variation_attributes)) {
            wp_send_json_error(['message' => 'No variation attributes provided.']);
        }

        $product_id = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            break;
        }

        if (!$product_id) {
            wp_send_json_error(['message' => 'No product found in cart.']);
        }

        $product = wc_get_product($product_id);
        if (!$product->is_type('variable')) {
            wp_send_json_error(['message' => 'Selected product is not a variable product.']);
        }

        $variation_id = $this->get_variation_id_by_attributes($product, $variation_attributes);

        if (!$variation_id) {
            wp_send_json_error(['message' => 'Invalid variation selected.']);
        }

        WC()->cart->empty_cart();
        WC()->cart->add_to_cart($product_id, 1, $variation_id);
        wp_send_json_success(['message' => 'Cart updated successfully.']);
    }

    /**
     * Get Variation ID Based on Selected Attributes
     * 
     * @param WC_Product_Variable $product
     * @param array $selected_attributes
     * @return int|false Variation ID or false if not found
     */
    private function get_variation_id_by_attributes($product, $selected_attributes) {
        foreach ($product->get_available_variations() as $variation) {
            $match = true;
            foreach ($selected_attributes as $attribute => $value) {
                $attribute_key = 'attribute_' . sanitize_title($attribute);
                if (!isset($variation['attributes'][$attribute_key]) || $variation['attributes'][$attribute_key] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $variation['variation_id'];
            }
        }
        return false;
    }
}

new MatchTrader_Variation_Manager();