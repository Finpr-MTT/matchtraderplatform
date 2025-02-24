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
        // Check if we are on the checkout page
        if (!is_checkout()) {
            return;
        }

        // Get the current endpoint
        $current_endpoint = WC()->query->get_current_endpoint();

        // Exclude 'order-received' and 'order-pay' from triggering this function
        if (in_array($current_endpoint, ['order-received', 'order-pay'])) {
            return;
        }

        // Check if the cart is empty
        if (WC()->cart->is_empty()) {
            // Get the product ID from the URL parameter
            $product_id = isset($_GET['add-to-cart']) ? absint($_GET['add-to-cart']) : 0;

            // If no product ID is provided, use the default product ID
            if (!$product_id) {
                $product_id = $this->default_product_id;
            }

            // Get the product object
            $product = wc_get_product($product_id);

            // Check if the product is a variable product
            if ($product && $product->is_type('variable')) {
                // Get the default attributes for the product
                $default_attributes = $product->get_default_attributes();

                // Format attributes correctly for WooCommerce
                $formatted_attributes = [];
                foreach ($default_attributes as $key => $value) {
                    $formatted_attributes['attribute_' . $key] = $value;
                }

                // Use the product data store to find the matching variation
                $data_store = WC_Data_Store::load('product');
                $variation_id = $data_store->find_matching_product_variation($product, $formatted_attributes);

                // If a matching variation is found, add it to the cart
                if ($variation_id) {
                    WC()->cart->add_to_cart($product_id, 1, $variation_id, $formatted_attributes);

                    // Redirect to the checkout page to prevent duplicate items
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

        foreach ($attributes as $attribute_name => $options) {
            $raw_attribute_name = str_replace('pa_', '', $attribute_name);
            $taxonomy = wc_attribute_taxonomy_name($raw_attribute_name);
            if (taxonomy_exists($taxonomy)) {
                // Get terms and sort them by name
                $terms = get_terms([
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                    'orderby'    => 'menu_order',
                    'order'      => 'ASC'
                ]);

                // Extract sorted term slugs
                $options = wp_list_pluck($terms, 'slug'); // Use slugs to fetch term names later
                // Debug: var_dump $options after wp_list_pluck
                // echo '<pre>Debug: $options after wp_list_pluck: ';
                // var_dump($options);
                // echo '</pre>';
            } else {
                natcasesort($options); // Sort case-insensitively
                // Debug: var_dump $options after natcasesort
                // echo '<pre>Debug: $options after natcasesort: ';
                // var_dump($options);
                // echo '</pre>';
            }

            echo '<strong><label>' . wc_attribute_label($attribute_name) . '</label></strong>';
            echo '<div class="matchtrader-radio-group" data-attribute="' . esc_attr($attribute_name) . '">';

            foreach ($options as $option) {
                $term_name = $option; // Default to the option value if it's not a taxonomy
                $term_description = ''; // Initialize term description as empty

                if (taxonomy_exists($taxonomy)) {
                    // Fetch the term object by slug
                    $term = get_term_by('slug', $option, $taxonomy);
                    if ($term) {
                        $term_name = $term->name; // Use the term name
                        $term_description = $term->description; // Use the term description if it exists
                    }
                }

                $selected = (isset($selected_attributes['attribute_' . sanitize_title($attribute_name)]) && $selected_attributes['attribute_' . sanitize_title($attribute_name)] == $option) ? ' checked' : '';
                echo '<div class="matchtrader-radio-option">';
                echo '<input type="radio" name="' . esc_attr($attribute_name) . '" value="' . esc_attr($option) . '" class="matchtrader-switch"' . $selected . '>';
                echo '<label class="matchtrader-radio-label">' . esc_html($term_name);
                
                echo '</label>';
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