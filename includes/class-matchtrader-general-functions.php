<?php
/**
 * Plugin functions and definitions for General Functions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit;
}

class MatchTrader_General_Functions{

    /**
     * Constructor to initialize the plugin
     */
    public function __construct() {
        // Load required files
        $this->load_helpers();
        $this->load_admin();
        $this->load_public();
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
        require_once MATCHTRADERPLUGIN_PATH . 'includes/admin/class-matchtrader-variable-product-settings.php';
    }

    /**
     * Load public-facing classes
     */
    private function load_public() {        
        require_once MATCHTRADERPLUGIN_PATH . 'includes/public/general/class-matchtrader-public-general.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/public/platform-connection/class-matchtrader-api-get-account-by-uuid.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/public/woocommerce/class-matchtrader-public-woocommerce.php';
        require_once MATCHTRADERPLUGIN_PATH . 'includes/public/woocommerce/class-matchtrader-public-variation-selection.php';
    }

    /**
     * Initialize plugin functionality
     */
    public function init() {
        MatchTrader_Functions::init();
    }
}
