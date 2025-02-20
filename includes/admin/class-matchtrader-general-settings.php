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

class MatchTrader_General_Settings {

    /**
     * Constructor to initialize API settings.
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Registers API-related settings in WordPress.
     */
    public function register_settings() {
        register_setting('matchtrader_general_options', 'matchtrader_disable_frontend_route');

        add_settings_section('matchtrader_general_options', 'General Configuration', null, 'matchtraderplatform');

        add_settings_field('matchtrader_disable_frontend_route', 'Disable FrontEnd Routes', function() {
            $value = get_option('matchtrader_disable_frontend_route', '');
            echo '<input type="checkbox" name="matchtrader_disable_frontend_route" value="1" '.checked(1, $value, false).'>';
        }, 'matchtraderplatform', 'matchtrader_general_options');

    }

    /**
     * Renders the API settings page.
     */
    public static function settings_page() {
        ?>
        <div class="wrap">
            <h1>MatchTrader General Configuration</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('matchtrader_general_options');
                do_settings_sections('matchtraderplatform');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the class instance
new MatchTrader_General_Settings();