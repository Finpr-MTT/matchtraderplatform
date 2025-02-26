<?php
/**
 * @link              https://finpr.com
 * @since             1.0.1
 * @package           matchtraderplatform
 * GitHub Plugin URI: https://github.com/Finpr-MTT/matchtraderplatform
 * GitHub Branch: develop
 * @wordpress-plugin
 * Plugin Name:       Match Trader Connection Dashboard
 * Plugin URI:        https://finpr.com
 * Description:       This Plugin to Create User and Account to Dashboard Match Trader
 * Version:           1.0.12
 * Author:            Finpr x Match Trader Team
 * Author URI:        https://finpr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       matchtraderplatform
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define('MATCHTRADERPLUGIN_VERSION', '1.0.529');
define('MATCHTRADERPLUGIN_PATH', plugin_dir_path(__FILE__));
define('MATCHTRADERPLUGIN_URL', plugin_dir_url(__FILE__));

class MatchTraderPlatform {

    /**
     * Constructor to initialize the plugin
     */
    public function __construct() {
        $this->load_general_functions();
        // Hook into WordPress
        add_action('plugins_loaded', [$this, 'init']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    /**
    * Load general functions classes
    */
    private function load_general_functions() {
        require_once MATCHTRADERPLUGIN_PATH . 'includes/class-matchtrader-general-functions.php';
    }

    /**
     * Initialize plugin functionality
     */
    public function init() {        
        MatchTrader_Functions::init();
    }

    /**
     * Activation Hook: Run when the plugin is activated
     */
    public function activate() {
        // Set default options if not already set
        if (get_option('matchtrader_enable_plugin') === false) {
            update_option('matchtrader_enable_plugin', 1);
        }
        if (get_option('matchtrader_env') === false) {
            update_option('matchtrader_env', 'sandbox');
        }
        if (get_option('matchtrader_save_logs') === false) {
            update_option('matchtrader_save_logs', 0);
        }
    }

    /**
     * Deactivation Hook: Run when the plugin is deactivated
     */
    public function deactivate() {
        // No immediate cleanup needed
    }
}

// Initialize the plugin
new MatchTraderPlatform();

add_action('init', function () {
    if (!WC()->session) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }
});


add_action('woocommerce_before_checkout_form', function () {
    $matchtrader_temp_uuid = WC()->session->get('matchtrader_temp_uuid', []);
    $matchtrader_account_data = WC()->session->get('matchtrader_account_data', []);

    echo '<p><hr>matchtrader_temp_uuid</hr></p>';
    echo '<pre>';
    var_dump($matchtrader_temp_uuid);
    echo '</pre>';

    echo '<p><hr>matchtrader_account_data</hr></p>';

    echo '<pre>';
    var_dump($matchtrader_account_data);
    echo '</pre>';
});


add_action('init', function () {
    if (isset($_GET['uuid']) && !empty($_GET['uuid'])) {
        WC()->session->set('matchtrader_uuid', sanitize_text_field($_GET['uuid']));
    }
});

add_action('woocommerce_checkout_init', function () {
    $uuid = WC()->session->get('matchtrader_uuid');
    
    if (!empty($uuid)) {
        // Fetch new account data
        $account_data = get_account_by_uuid($uuid); // Ensure this function exists and retrieves API data

        if ($account_data) {
            WC()->session->set('matchtrader_account_data', $account_data);

            // Set WooCommerce customer data
            $customer = WC()->customer;

            if (!empty($account_data['addressDetails']['country'])) {
                $customer->set_billing_country(sanitize_text_field($account_data['addressDetails']['country']));
            }

            if (!empty($account_data['addressDetails']['state'])) {
                $customer->set_billing_state(sanitize_text_field($account_data['addressDetails']['state']));
            }

            if (!empty($account_data['personalDetails']['firstname'])) {
                $customer->set_billing_first_name(sanitize_text_field($account_data['personalDetails']['firstname']));
            }

            if (!empty($account_data['personalDetails']['lastname'])) {
                $customer->set_billing_last_name(sanitize_text_field($account_data['personalDetails']['lastname']));
            }

            if (!empty($account_data['email'])) {
                $customer->set_billing_email(sanitize_email($account_data['email']));
            }

            if (!empty($account_data['addressDetails']['address'])) {
                $customer->set_billing_address_1(sanitize_text_field($account_data['addressDetails']['address']));
            }

            if (!empty($account_data['addressDetails']['city'])) {
                $customer->set_billing_city(sanitize_text_field($account_data['addressDetails']['city']));
            }

            if (!empty($account_data['addressDetails']['postCode'])) {
                $customer->set_billing_postcode(sanitize_text_field($account_data['addressDetails']['postCode']));
            }

            if (!empty($account_data['contactDetails']['phoneNumber'])) {
                $customer->set_billing_phone(sanitize_text_field($account_data['contactDetails']['phoneNumber']));
            }

            // Save customer data
            $customer->save();
        }
    }
});

