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
define('MATCHTRADERPLUGIN_VERSION', '1.0.29');
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

function debug_get_term_names_from_attribute() {
    // Define the attribute taxonomy (e.g., 'pa_platform')
    $attribute_taxonomy = 'pa_trading-capital';

    // Check if the taxonomy exists
    if (!taxonomy_exists($attribute_taxonomy)) {
        echo 'Taxonomy ' . esc_html($attribute_taxonomy) . ' does not exist.';
        return;
    }

    // Get all terms for the attribute taxonomy
    $terms = get_terms([
        'taxonomy'   => $attribute_taxonomy,
        'hide_empty' => false, // Include terms even if they have no associated products
    ]);

    // Check if terms were found
    if (is_wp_error($terms)) {
        echo 'Error retrieving terms: ' . esc_html($terms->get_error_message());
        return;
    }

    if (empty($terms)) {
        echo 'No terms found for taxonomy ' . esc_html($attribute_taxonomy);
        return;
    }

    // Debug output: var_dump term names
    echo '<pre>';
    var_dump(wp_list_pluck($terms, 'name')); // Extract and display term names
    echo '</pre>';
}

// Hook the function into the checkout page
add_action('woocommerce_before_checkout_form', 'debug_get_term_names_from_attribute');