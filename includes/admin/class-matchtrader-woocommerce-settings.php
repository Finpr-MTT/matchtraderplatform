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
    exit;
}

class MatchTrader_WooCommerce_Settings {

    /**
     * Constructor to initialize WooCommerce settings.
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Registers WooCommerce settings in WordPress.
     */
    public function register_settings() {
        register_setting('matchtrader_woo_options', 'matchtrader_skip_cart_page');
        register_setting('matchtrader_woo_options', 'matchtrader_disable_shop_page');
        register_setting('matchtrader_woo_options', 'matchtrader_disable_product_page');
        register_setting('matchtrader_woo_options', 'matchtrader_enable_mtt_checkout');
        register_setting('matchtrader_woo_options', 'matchtrader_enable_checkout_selection');
        register_setting('matchtrader_woo_options', 'matchtrader_checkout_mode');
        register_setting('matchtrader_woo_options', 'matchtrader_product_configuration');        
        register_setting('matchtrader_woo_options', 'matchtrader_default_product_cart');

        add_settings_section('matchtrader_woo_section', 'WooCommerce Configuration', null, 'matchtraderplatform-woo-settings');

        add_settings_field('matchtrader_skip_cart_page', 'Skip Cart Page', function() {
            $value = get_option('matchtrader_skip_cart_page', '');
            echo '<input type="checkbox" name="matchtrader_skip_cart_page" value="1" ' . checked(1, $value, false) . '>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');

        add_settings_field('matchtrader_disable_shop_page', 'Disable Shop Page', function() {
            $value = get_option('matchtrader_disable_shop_page', '');
            echo '<input type="checkbox" name="matchtrader_disable_shop_page" value="1" ' . checked(1, $value, false) . '>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');

        add_settings_field('matchtrader_disable_product_page', 'Disable Product Page', function() {
            $value = get_option('matchtrader_disable_product_page', '');
            echo '<input type="checkbox" name="matchtrader_disable_product_page" value="1" ' . checked(1, $value, false) . '>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');

        add_settings_field('matchtrader_enable_mtt_checkout', 'Enable Match Trader Checkout', function() {
            $value = get_option('matchtrader_enable_mtt_checkout', 'default'); // Default value

            echo '<select name="matchtrader_enable_mtt_checkout">';
            echo '<option value="none" ' . selected($value, 'none', false) . '>None</option>';
            echo '<option value="default" ' . selected($value, 'default', false) . '>Default</option>';
            echo '<option value="multi-step" ' . selected($value, 'multi-step', false) . '>Multi-Step</option>';
            echo '</select>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');

        add_settings_field('matchtrader_enable_checkout_selection', 'Enable Product Selection at Checkout', function() {
            $value = get_option('matchtrader_enable_checkout_selection', '');
            echo '<input type="checkbox" name="matchtrader_enable_checkout_selection" value="1" ' . checked(1, $value, false) . '>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');

        add_settings_field('matchtrader_checkout_mode', 'Checkout Mode', function() {
            $value = get_option('matchtrader_checkout_mode', 'single');
            echo '<select name="matchtrader_checkout_mode">
                    <option value="single" ' . selected('single', $value, false) . '>Single Product</option>
                  </select>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');

        add_settings_field('matchtrader_product_configuration', 'Product Configuration', function() {
            $value = get_option('matchtrader_product_configuration', 'simple');
            echo '<select name="matchtrader_product_configuration">
                    <option value="simple" ' . selected('simple', $value, false) . '>Simple Product</option>
                    <option value="variable" ' . selected('variable', $value, false) . '>Variable Product</option>
                  </select>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');

        add_settings_field('matchtrader_default_product_cart', 'Default Product on The Cart', function() {
            $value = get_option('matchtrader_default_product_cart', '');
            $products = wc_get_products(['status' => 'publish', 'limit' => -1]);

            echo '<select name="matchtrader_default_product_cart">';
            echo '<option value="">Select Product</option>';
            foreach ($products as $product) {
                echo '<option value="' . $product->get_id() . '" ' . selected($value, $product->get_id(), false) . '>' . esc_html($product->get_name()) . '</option>';
            }
            echo '</select>';
        }, 'matchtraderplatform-woo-settings', 'matchtrader_woo_section');
    }

    /**
     * Renders the WooCommerce settings page.
     */
    public static function settings_page() {
        ?>
        <div class="wrap">
            <h1>MTT Woo Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('matchtrader_woo_options');
                do_settings_sections('matchtraderplatform-woo-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the class instance
new MatchTrader_WooCommerce_Settings();
