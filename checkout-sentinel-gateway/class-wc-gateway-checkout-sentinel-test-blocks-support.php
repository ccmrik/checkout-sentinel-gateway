<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class WC_Gateway_Checkout_Sentinel_Test_Blocks_Support extends AbstractPaymentMethodType {
    private $gateway;

    protected $name = 'checkout_sentinel_test';

    public function initialize() {
        $this->gateway = new WC_Gateway_Checkout_Sentinel_Test();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'checkout-sentinel-test-blocks',
            plugins_url('checkout-sentinel-test-blocks.js', __FILE__),
            array('wc-blocks-registry'),
            '1.0.0',
            true
        );
        return array('checkout-sentinel-test-blocks');
    }

    public function get_payment_method_data() {
        return array(
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
        );
    }
}