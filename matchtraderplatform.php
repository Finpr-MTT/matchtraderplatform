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
 * Author:            Finpr x MTT Team
 * Author URI:        https://finpr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       matchtraderplatform
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Match_Trader_Platform_Setup {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'matchtraderplatform_enqueue_scripts']);
    }

    public function matchtraderplatform_enqueue_scripts() {
        
    }
}

new Match_Trader_Platform_Setup();