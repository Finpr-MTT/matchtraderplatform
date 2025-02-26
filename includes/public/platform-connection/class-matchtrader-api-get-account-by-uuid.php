<?php
/**
 * Plugin functions and definitions for Get Account by Uuid.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package matchtraderplatform
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure the helper class is included
require_once MATCHTRADERPLUGIN_PATH . 'includes/helper/class-matchtrader-api-helper.php';

class MatchTrader_Get_Account_By_UUID {

    public function __construct() {
        // Hook into WooCommerce Checkout process
        add_action('init', [$this, 'handle_uuid_session']);

        add_action('woocommerce_checkout_init', [$this, 'handle_uuid_param'], 3);
        if (get_option('matchtrader_enable_mtt_checkout', 'default') === 'default') {add_filter('woocommerce_checkout_fields', [$this, 'prefill_checkout_fields']);
        }        

        add_action('wp_ajax_check_matchtrader_session', [$this, 'check_matchtrader_session']);
        add_action('wp_ajax_nopriv_check_matchtrader_session', [$this, 'check_matchtrader_session']);

        // Hook into order update to save UUID
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_uuid_challenge_id_to_order_meta'], 10, 2);
    }



    public function check_matchtrader_session() {
        check_ajax_referer('matchtrader_nonce', 'security'); // Verify nonce for security

        $account_data = WC()->session->get('matchtrader_account_data');
        $has_session = !empty($account_data);

        wp_send_json_success(['has_session' => $has_session]);
    }

    /**
     * Handle UUID session and reset fields if the UUID changes.
     */
    public function handle_uuid_session() {
        if (isset($_GET['uuid']) && !empty($_GET['uuid'])) {
            $new_uuid = sanitize_text_field($_GET['uuid']);
            $stored_data = WC()->session->get('matchtrader_account_data', []);
            $stored_uuid = $stored_data['uuid'] ?? '';

            // If the new UUID is different from the stored UUID, reset session and fields
            if ($new_uuid !== $stored_uuid) {
                WC()->session->__unset('matchtrader_account_data');

                // Clear WooCommerce customer fields
                $customer = WC()->customer;
                $customer->set_billing_first_name('');
                $customer->set_billing_last_name('');
                $customer->set_billing_address_1('');
                $customer->set_billing_address_2('');
                $customer->set_billing_city('');
                $customer->set_billing_state('');
                $customer->set_billing_postcode('');
                $customer->set_billing_country('');
                $customer->set_billing_phone('');
                $customer->set_billing_email('');
                $customer->save();

                // Store the new UUID in session
                WC()->session->set('matchtrader_uuid', $new_uuid);
            }
        }
    }


    /**
     * Handle the UUID parameter in URL and fetch account details.
     */
    public function handle_uuid_param() {
        $uuid = WC()->session->get('matchtrader_uuid');    
        if (!empty($uuid)) {
            // Fetch new account data
            $account_data = $this->get_account_by_uuid($uuid);

            if ($account_data) {
                WC()->session->set('matchtrader_account_data', $account_data);

                // Set WooCommerce customer data
                $customer = WC()->customer;

                if (!empty($account_data['addressDetails']['country'])) {
                    $customer->set_billing_country(sanitize_text_field($account_data['addressDetails']['country']));
                } else {
                    $customer->set_billing_country('US'); // Default to United States if empty
                }

                if (!empty($account_data['addressDetails']['state'])) {
                    $customer->set_billing_state(sanitize_text_field($account_data['addressDetails']['state']));
                }

                if (!empty($account_data['personalDetails']['firstname'])) {
                    $customer->set_billing_first_name(sanitize_text_field($account_data['personalDetails']['firstname']));
                }

                if (!empty($account_data['personalDetails']['lastname'])) {
                    $customer->set_billing_last_name(sanitize_text_field($account_data['personalDetails']['lastname']));
                }

                if (!empty($account_data['email'])) {
                    $customer->set_billing_email(sanitize_email($account_data['email']));
                }

                if (!empty($account_data['addressDetails']['address'])) {
                    $customer->set_billing_address_1(sanitize_text_field($account_data['addressDetails']['address']));
                }

                if (!empty($account_data['addressDetails']['city'])) {
                    $customer->set_billing_city(sanitize_text_field($account_data['addressDetails']['city']));
                }

                if (!empty($account_data['addressDetails']['postCode'])) {
                    $customer->set_billing_postcode(sanitize_text_field($account_data['addressDetails']['postCode']));
                }

                if (!empty($account_data['contactDetails']['phoneNumber'])) {
                    $customer->set_billing_phone(sanitize_text_field($account_data['contactDetails']['phoneNumber']));
                }

                // Save customer data
                $customer->save();
            }
        }
    }

    /**
     * Fetch account details by UUID using Centralized API Helper.
     *
     * @param string $uuid
     * @return array|null
     */
    private function get_account_by_uuid($uuid) {
        $endpoint = "v1/accounts/by-uuid/{$uuid}";
        return MatchTrader_API_Helper::get_request($endpoint);
    }

    /**
     * Get UUID by checking email.
     *
     * @param string $email
     * @return string|null
     */
    private function get_account_uuid_by_email($email) {
        $endpoint = "v1/accounts/by-email/" . urlencode($email);
        $response = MatchTrader_API_Helper::get_request($endpoint);

        return $response['uuid'] ?? null;
    }

    public function save_uuid_challenge_id_to_order_meta($order_id, $data) {
        $uuid = '';
        $challenge_id = '';

        // Check if UUID exists in the URL
        if (isset($_GET['uuid']) && !empty($_GET['uuid'])) {
            $uuid = sanitize_text_field($_GET['uuid']);
        }

        // Check if UUID is in WooCommerce session (API response)
        $account_data = WC()->session->get('matchtrader_account_data');
        if ($account_data && isset($account_data['uuid'])) {
            $uuid = sanitize_text_field($account_data['uuid']);
        }

        // If UUID is still empty, try to fetch it by email
        if (empty($uuid)) {
            $order = wc_get_order($order_id);
            $email = $order->get_billing_email();

            if (!empty($email)) {
                $uuid = $this->get_account_uuid_by_email($email);

                // If UUID is found, update order meta
                if (!empty($uuid)) {
                    update_post_meta($order_id, '_matchtrader_account_uuid', $uuid);
                    $order->add_order_note(__('MatchTrader UUID Retrieved by Email: ' . $uuid, 'matchtraderplatform'));
                }
            }
        }

        // Save UUID as order meta if available
        if (!empty($uuid)) {
            update_post_meta($order_id, '_matchtrader_account_uuid', $uuid);
        }

        // Get Challenge ID from the cart
        foreach (WC()->cart->get_cart() as $cart_item) {
            if (isset($cart_item['variation_id']) && !empty($cart_item['variation_id'])) {
                $challenge_id = get_post_meta($cart_item['variation_id'], '_matchtrader_challenge_id', true);
                break; // Save only the first match (assuming 1 product in cart)
            }
        }

        // Save Challenge ID as order meta if available
        if (!empty($challenge_id)) {
            update_post_meta($order_id, '_matchtrader_challenge_id', $challenge_id);
        }

        update_post_meta($order_id, '_matchtrader_connection_completed', 0);
    }

    /**
 * Prefill WooCommerce checkout fields with API response data.
 *
 * @param array $fields
 * @return array
 */
public function prefill_checkout_fields($fields) {
    $account_data = WC()->session->get('matchtrader_account_data');
    if (!$account_data || !isset($account_data['personalDetails'])) {
        return $fields;
    }

    // Define custom attributes for read-only fields
    $readonly_attrs = ['readonly' => 'readonly', 'class' => 'matchtrader-readonly'];

    // Field mappings
    $field_mappings = [
        'billing_first_name' => ['personalDetails', 'firstname'],
        'billing_last_name' => ['personalDetails', 'lastname'],
        'billing_email' => ['email'],
        'billing_phone' => ['contactDetails', 'phoneNumber'],
        'billing_country' => ['addressDetails', 'country'],
        'billing_state' => ['addressDetails', 'state'],
        'billing_city' => ['addressDetails', 'city'],
        'billing_postcode' => ['addressDetails', 'postCode'],
        'billing_address_1' => ['addressDetails', 'address']
    ];

    // Process each field
    foreach ($field_mappings as $field_key => $data_path) {
        $value = $account_data;
        foreach ($data_path as $path) {
            if (!isset($value[$path])) {
                continue 2;
            }
            $value = $value[$path];
        }

        if (!empty($value)) {
            $fields['billing'][$field_key]['default'] = sanitize_text_field($value);
            // Add readonly attributes if value exists
            $fields['billing'][$field_key]['custom_attributes'] = $readonly_attrs;
        }
    }

    // Handle email separately due to different sanitization
    if (!empty($account_data['email'])) {
        $fields['billing']['billing_email']['default'] = sanitize_email($account_data['email']);
        $fields['billing']['billing_email']['custom_attributes'] = $readonly_attrs;
    }

    return $fields;
}
}

// Initialize the class
new MatchTrader_Get_Account_By_UUID();