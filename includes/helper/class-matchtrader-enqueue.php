<?php
/**
 * Plugin functions and definitions for Helper Enqueue.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MatchTrader_Enqueue_Manager {

    public function __construct() {
        if (get_option('matchtrader_enable_checkout_selection', false)) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        }
    }

    public function enqueue_scripts() {
        if (is_checkout()) {
            wp_enqueue_style('matchtrader-switch-variant-style', MATCHTRADERPLUGIN_URL . 'assets/css/matchtrader-switch-variant.css', [], MATCHTRADERPLUGIN_VERSION );
            wp_enqueue_script('matchtrader-switch-variant', MATCHTRADERPLUGIN_URL . 'assets/js/matchtrader-switch-variant.js', ['jquery'], MATCHTRADERPLUGIN_VERSION, true);            
            wp_localize_script('matchtrader-switch-variant', 'matchtraderAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('matchtrader_nonce'),
            ]);
        }
    }
}

new MatchTrader_Enqueue_Manager();