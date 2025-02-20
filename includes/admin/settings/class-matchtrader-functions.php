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
            'MTT General Settings',
            'MTT General Settings',
            'manage_options',
            'matchtraderplatform-general-settings',
            ['MatchTrader_General_Settings', 'settings_page']
        );
    }

    public static function dashboard_page() {
        echo '<h1>MatchTrader Platform Dashboard</h1>';
        echo '<p>Manage your Match Trader integration here.</p>';
    }
}

MatchTrader_Functions::init();
