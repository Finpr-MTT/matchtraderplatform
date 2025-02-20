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

class MatchTrader_Functions {
    
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }

    public static function add_admin_menu() {
        add_menu_page(
            'MatchTrader Platform',
            'MatchTrader',
            'manage_options',
            'matchtraderplatform',
            [__CLASS__, 'dashboard_page'],
            'dashicons-chart-area',
             3 // Position
        );

        add_submenu_page(
            'matchtraderplatform',
            'MTT API Settings',
            'MTT API Settings',
            'manage_options',
            'matchtraderplatform-api-settings',
            ['MatchTrader_Api_Settings', 'settings_page']
        );

        add_submenu_page(
            'matchtraderplatform',
            'MTT Addons Settings',
            'MTT Addons Settings',
            'manage_options',
            'matchtraderplatform-addons-settings',
            ['MatchTrader_Addons_Settings', 'settings_page']
        );

        add_submenu_page(
            'matchtraderplatform',
            'MTT Pricing Table',
            'MTT Pricing Table',
            'manage_options',
            'matchtraderplatform-prctable-settings',
            ['MatchTrader_PrcTable_Settings', 'settings_page']
        );

        add_submenu_page(
            'matchtraderplatform',
            'MTT Woo Settings',
            'MTT Woo Settings',
            'manage_options',
            'matchtraderplatform-woo-settings',
            ['MatchTrader_WooCommerce_Settings', 'settings_page']
        );

        add_submenu_page(
            'matchtraderplatform',
            'MTT Woo Settings',
            'MTT Woo Settings',
            'manage_options',
            'matchtraderplatform-woo-settings',
            ['MatchTrader_WooCommerce_Settings', 'settings_page']
        );
    }

    public static function register_settings() {
        register_setting('matchtrader_dashboard_options', 'matchtrader_disable_frontend_route');
    }

    public static function dashboard_page() {
        echo '<h1>MatchTrader Platform Dashboard</h1>';
        echo '<p>Manage your Match Trader integration here.</p>';
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields('matchtrader_dashboard_options');
                do_settings_sections('matchtraderplatform-dashboard');
                submit_button();
                ?>
            </form>
            <h2>Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('matchtrader_dashboard_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Disable Frontend Routes</th>
                        <td>
                            <?php $value = get_option('matchtrader_disable_frontend_route', 0); ?>
                            <input type="checkbox" name="matchtrader_disable_frontend_route" value="1" <?php checked(1, $value, true); ?>>
                            <p class="description">Check this to disable frontend routing.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

MatchTrader_Functions::init();
