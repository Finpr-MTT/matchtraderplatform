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
 * Version:           1.0.1
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
define('MATCHTRADERPLUGIN_VERSION', '1.0.1');
define('MATCHTRADERPLUGIN_PATH', plugin_dir_path(__FILE__));
define('MATCHTRADERPLUGIN_URL', plugin_dir_url(__FILE__));

class MatchTraderPlatform {

    /**
     * Constructor to initialize the plugin
     */
    public function __construct() {
        // Load required files
        $this->load_helpers();
        $this->load_admin();
        $this->load_public();

        // Hook into WordPress
        add_action('plugins_loaded', [$this, 'init']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    /**
    * Load helper classes
    */
    private function load_helpers() {
        require_once MATCHTRADERPLUGIN_PATH . 'includes/helper/class-matchtrader-helper.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/helper/class-matchtrader-enqueue.php';
    }

    /**
     * Load admin classes
     */
    private function load_admin() {
        require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/class-matchtrader-functions.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/class-matchtrader-api-settings.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/class-matchtrader-addons-settings.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/class-matchtrader-prctable-settings.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/class-matchtrader-woocommerce-settings.php';
    }

    /**
     * Load public-facing classes
     */
    private function load_public() {        
        require_once MATCHTRADERPLUGIN_PATH . 'includes/public/general/class-matchtrader-public-general.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/public/woocommerce/class-matchtrader-public-woocommerce.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/public/woocommerce/class-matchtrader-public-variation-selection-checkout.php';
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
