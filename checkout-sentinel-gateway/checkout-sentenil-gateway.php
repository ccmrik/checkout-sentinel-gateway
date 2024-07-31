<?php
/*
Plugin Name: Checkout Sentinel Test Gateway
Plugin URI: https://paladine.com.au
Description: A test payment gateway for Checkout Sentinel
Version: 3.3
Author: Paladine Pty Ltd
Author URI: https://paladine.com.au
*/

if (!defined('ABSPATH')) {
    exit;
}

// Debug logging function
function cs_log($message) {
    if (WP_DEBUG === true) {
        error_log(print_r($message, true));
    }
}

// Ensure this runs after WooCommerce is loaded
add_action('plugins_loaded', 'init_checkout_sentinel_test_gateway', 20);

function init_checkout_sentinel_test_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        cs_log('WC_Payment_Gateway class does not exist. WooCommerce might not be active.');
        return;
    }

    class WC_Gateway_Checkout_Sentinel_Test extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'checkout_sentinel_test';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = 'Checkout Sentinel Test';
            $this->method_description = 'A test payment gateway for Checkout Sentinel';

            $this->title = '🛡️ Checkout Sentinel Test (Select Me!)';
            $this->description = 'This is a test gateway for Checkout Sentinel. Select this option to test the plugin functionality.';
            $this->enabled = 'yes';

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function payment_fields() {
            echo '<div style="background-color: #f0f0f0; padding: 15px; border: 2px solid #ddd; border-radius: 5px; margin-bottom: 15px;">';
            echo '<h3 style="color: #0073aa;">🛡️ Checkout Sentinel Test Gateway</h3>';
            echo wpautop(wp_kses_post($this->description));
            echo '<div id="checkout-sentinel-test-form">';
            echo '<label for="checkout_sentinel_test_requests"><strong>Number of simulated requests:</strong></label>';
            echo '<input type="number" id="checkout_sentinel_test_requests" name="checkout_sentinel_test_requests" min="1" max="20" value="5" style="width: 60px; margin-left: 10px;">';
            echo '</div>';
            echo '</div>';
        }

        public function process_payment($order_id) {
            cs_log('Starting process_payment for order ' . $order_id);
            
            $order = wc_get_order($order_id);
            $num_requests = isset($_POST['checkout_sentinel_test_requests']) ? intval($_POST['checkout_sentinel_test_requests']) : 5;

            cs_log('Number of simulated requests: ' . $num_requests);

            $attack_detected = false;
            for ($i = 1; $i <= $num_requests; $i++) {
                if ($this->simulate_checkout_attempt()) {
                    $attack_detected = true;
                    cs_log('Attack detected on attempt ' . $i);
                    break;
                }
            }

            if ($attack_detected) {
                cs_log('Attack detected, returning failure');
                wc_add_notice(__('Checkout Sentinel Test: Attack detected! Payment blocked.', 'woocommerce'), 'error');
                return array(
                    'result' => 'failure',
                    'redirect' => '',
                    'messages' => __('Checkout Sentinel Test: Attack detected! Payment blocked.', 'woocommerce')
                );
            } else {
                cs_log('No attack detected, completing payment');
                $order->payment_complete();
                $order->add_order_note('Payment completed via Checkout Sentinel Test Gateway.');
                
                cs_log('Payment completed, returning success');
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            }
        }

        private function simulate_checkout_attempt() {
            if (function_exists('checkout_sentinel_try_debounce')) {
                $result = checkout_sentinel_try_debounce();
                cs_log('checkout_sentinel_try_debounce result: ' . ($result ? 'true' : 'false'));
                return $result;
            }
            
            cs_log('Checkout Sentinel function not found. Using fallback.');
            return (rand(1, 10) > 8); // 20% chance of simulated attack
        }

        public function is_available() {
            return true; // Always available
        }
    }
}

function add_checkout_sentinel_test_gateway($methods) {
    $methods[] = 'WC_Gateway_Checkout_Sentinel_Test';
    return $methods;
}
add_filter('woocommerce_payment_gateways', 'add_checkout_sentinel_test_gateway');

// Register the block integration
function checkout_sentinel_test_gateway_block_support() {
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        require_once plugin_dir_path(__FILE__) . 'class-wc-gateway-checkout-sentinel-test-blocks-support.php';
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function(Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                $payment_method_registry->register(new WC_Gateway_Checkout_Sentinel_Test_Blocks_Support());
            }
        );
    }
}
add_action('woocommerce_blocks_loaded', 'checkout_sentinel_test_gateway_block_support');

// Add custom error handling
add_action('woocommerce_before_checkout_process', 'checkout_sentinel_test_before_checkout');
function checkout_sentinel_test_before_checkout() {
    if ($_POST['payment_method'] === 'checkout_sentinel_test') {
        cs_log('Checkout Sentinel Test Gateway selected for checkout');
    }
}

add_action('woocommerce_checkout_process', 'checkout_sentinel_test_checkout_process');
function checkout_sentinel_test_checkout_process() {
    if ($_POST['payment_method'] === 'checkout_sentinel_test') {
        cs_log('Processing checkout with Checkout Sentinel Test Gateway');
    }
}