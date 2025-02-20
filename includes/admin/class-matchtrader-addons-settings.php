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

class MatchTrader_Addons_Settings{

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

    }

    /**
     * Renders the WooCommerce settings page.
     */
    public static function settings_page() {
        ?>
        <div class="wrap">
            <h1>MTT Addons Settings</h1>
            <p>This section is currently under development.</p>
        </div>
        <?php
    }
}

// Initialize the class instance
new MatchTrader_Addons_Settings();
