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
            // Style
            wp_enqueue_style('matchtrader-bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css', [], '5.3.0-alpha3' );           
            wp_enqueue_style('matchtrader-platform-style', MATCHTRADERPLUGIN_URL . 'assets/css/matchtraderplatform-public.css', [], MATCHTRADERPLUGIN_VERSION );
            wp_enqueue_style('matchtrader-switch-variant-style', MATCHTRADERPLUGIN_URL . 'assets/css/matchtrader-switch-variant.css', [], MATCHTRADERPLUGIN_VERSION );

            // Script
            wp_enqueue_script('matchtrader-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.3.0-alpha3', true);
            wp_enqueue_script('matchtrader-platform-script', MATCHTRADERPLUGIN_URL . 'assets/js/matchtraderplatform-public.js', ['jquery'], MATCHTRADERPLUGIN_VERSION, true);            
            wp_enqueue_script('matchtrader-switch-variant', MATCHTRADERPLUGIN_URL . 'assets/js/matchtrader-switch-variant.js', ['jquery'], MATCHTRADERPLUGIN_VERSION, true);
            wp_enqueue_script('matchtrader-woocommerce-script', MATCHTRADERPLUGIN_URL . 'assets/js/matchtraderplatform-woocommerce.js', ['jquery'], MATCHTRADERPLUGIN_VERSION, true);
            wp_enqueue_script('matchtrader-coupon-ajax', MATCHTRADERPLUGIN_URL . 'assets/js/matchtraderplatform-coupon.js', array('jquery'), MATCHTRADERPLUGIN_VERSION, true);
            wp_enqueue_script('matchtrader-addons-script', MATCHTRADERPLUGIN_URL . 'assets/js/matchtraderplatform-addons.js', ['jquery'], MATCHTRADERPLUGIN_VERSION, true);        

            // Wp Localize
            wp_localize_script('matchtrader-woocommerce-script', 'wc_country_states', [
                'countries' => WC()->countries->get_allowed_countries(),
                'states' => WC()->countries->get_states(),
            ]);
            wp_localize_script('matchtrader-switch-variant', 'matchtraderAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('matchtrader_nonce'),
            ]);
            wp_localize_script('matchtrader-coupon-ajax', 'ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
            ]);
            wp_localize_script('matchtrader-addons-script', 'mtt_addons_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('matchtrader_nonce'),
            ]);            
        }
    }
}

new MatchTrader_Enqueue_Manager();