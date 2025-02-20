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

class MatchTrader_Api_Settings {

    public static function settings_page() {
        ?>
        <div class="wrap">
            <h1>MatchTrader API Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('matchtrader_api_options');
                do_settings_sections('matchtraderplatform-api-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function register_settings() {
        register_setting('matchtrader_api_options', 'matchtrader_enable_plugin');
        register_setting('matchtrader_api_options', 'matchtrader_env');
        register_setting('matchtrader_api_options', 'matchtrader_sandbox_url');
        register_setting('matchtrader_api_options', 'matchtrader_sandbox_key');
        register_setting('matchtrader_api_options', 'matchtrader_live_url');
        register_setting('matchtrader_api_options', 'matchtrader_live_key');
        register_setting('matchtrader_api_options', 'matchtrader_save_logs');

        add_settings_section('matchtrader_api_section', 'API Configuration', null, 'matchtraderplatform-api-settings');

        add_settings_field('matchtrader_enable_plugin', 'Enable Plugin', function() {
            $value = get_option('matchtrader_enable_plugin', '');
            echo '<input type="checkbox" name="matchtrader_enable_plugin" value="1" '.checked(1, $value, false).'>';
        }, 'matchtraderplatform-api-settings', 'matchtrader_api_section');

        add_settings_field('matchtrader_env', 'Environment', function() {
            $value = get_option('matchtrader_env', 'sandbox');
            echo '<select name="matchtrader_env">
                    <option value="sandbox" '.selected('sandbox', $value, false).'>Sandbox Version</option>
                    <option value="live" '.selected('live', $value, false).'>Live Version</option>
                  </select>';
        }, 'matchtraderplatform-api-settings', 'matchtrader_api_section');

        add_settings_field('matchtrader_sandbox_url', 'Sandbox Endpoint URL', function() {
            $value = get_option('matchtrader_sandbox_url', '');
            echo '<input type="text" name="matchtrader_sandbox_url" value="'.$value.'" class="regular-text">';
        }, 'matchtraderplatform-api-settings', 'matchtrader_api_section');

        add_settings_field('matchtrader_sandbox_key', 'Sandbox Test Key', function() {
            $value = get_option('matchtrader_sandbox_key', '');
            echo '<input type="text" name="matchtrader_sandbox_key" value="'.$value.'" class="regular-text">';
        }, 'matchtraderplatform-api-settings', 'matchtrader_api_section');

        add_settings_field('matchtrader_live_url', 'Live Endpoint URL', function() {
            $value = get_option('matchtrader_live_url', '');
            echo '<input type="text" name="matchtrader_live_url" value="'.$value.'" class="regular-text">';
        }, 'matchtraderplatform-api-settings', 'matchtrader_api_section');

        add_settings_field('matchtrader_live_key', 'Live API Key', function() {
            $value = get_option('matchtrader_live_key', '');
            echo '<input type="text" name="matchtrader_live_key" value="'.$value.'" class="regular-text">';
        }, 'matchtraderplatform-api-settings', 'matchtrader_api_section');

        add_settings_field('matchtrader_save_logs', 'Save Log Response', function() {
            $value = get_option('matchtrader_save_logs', '');
            echo '<input type="checkbox" name="matchtrader_save_logs" value="1" '.checked(1, $value, false).'>';
        }, 'matchtraderplatform-api-settings', 'matchtrader_api_section');
    }
}

add_action('admin_init', ['MatchTrader_Api_Settings', 'register_settings']);
