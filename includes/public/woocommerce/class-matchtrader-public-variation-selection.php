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

                // Use the product data store to find the matching variation
                $data_store = WC_Data_Store::load('product');
                $variation_id = $data_store->find_matching_product_variation($product, $formatted_attributes);

                if ($variation_id) {
                    WC()->cart->add_to_cart($this->default_product_id, 1, $variation_id, $formatted_attributes);
                    wp_safe_redirect(wc_get_checkout_url());
                    exit;
                }
            }
        }
    }

    public function display_variant_selector() {
        $taxonomy = 'pa_trading-capital';

if (taxonomy_exists($taxonomy)) {
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false, // Show all terms
        'orderby'    => 'name',
        'order'      => 'ASC'
    ]);

    if (!empty($terms) && !is_wp_error($terms)) {
        echo '<h3>Attribute Terms for pa_trading-capital:</h3>';
        echo '<ul>';
        foreach ($terms as $term) {
            $term_name = $term->name; // Name
            $term_slug = $term->slug; // Slug
            $term_id   = $term->term_id; // ID
            $term_link = get_term_link($term); // Term Link

            // Print the details
            echo '<li>';
            echo '<strong>Name:</strong> ' . esc_html($term_name) . '<br>';
            echo '<strong>Slug:</strong> ' . esc_html($term_slug) . '<br>';
            echo '<strong>ID:</strong> ' . esc_html($term_id) . '<br>';
            echo '<strong>Link:</strong> <a href="' . esc_url($term_link) . '" target="_blank">' . esc_html($term_link) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No terms found for pa_trading-capital.</p>';
    }
} else {
    echo '<p>Taxonomy pa_trading-capital does not exist.</p>';
}

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