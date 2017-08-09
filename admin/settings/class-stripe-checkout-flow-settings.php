<?php

/**
 * Class that pertains to the Stripe checkout flow options. The checkout flow is stripes pre-built checkout form
 * that can be used for cards, bitcoin, and alipay.
 * @author Payment Plugins
 * @copyright 2016
 */
class Stripe_Checkout_Flow_Settings extends Stripe_Gateway_Settings_API
{

    public function __construct ()
    {
        $this->title = false;
        $this->id = 'checkout_form_settings';
        $this->page = 'stripe-gateway-settings';
        $this->tab = 'checkout-settings';
        add_action('stripe_save_settings_' . $this->tab, 
                array(
                        $this,
                        'save'
                ));
        parent::__construct();
    }

    public function output_checkout_flow ($key, $data)
    {
        $data = wp_parse_args($data, $this->get_default_custom_html_args());
        include STRIPE_GATEWAY_ADMIN .
                 'settings/views/checkout-form-settings.php';
    }

    public function settings ()
    {
        return array(
                'checkout_bitcoin_enabled' => array(
                        'type' => 'checkbox',
                        'title' => __('Bitcoin Enabled', 'stripe_gateway'),
                        'default' => '',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, your customers can make purchases using Bitcoin. Bitcoin cannot be used for Subscription products.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_alipay_enabled' => array(
                        'type' => 'checkbox',
                        'title' => __('Alipay Enabled', 'stripe_gateway'),
                        'default' => '',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, your customers can make purchases using Alipay. Alipay cannot be used for subscriptions at this time.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_image_url' => array(
                        'title' => __('Logo Url', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => '',
                        'value' => '',
                        'description' => __(
                                'This is the url of your companies logo. The suggested logo size is 128px X 128px. This logo will appear in the center of the Stripe checkout form.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_company_name' => array(
                        'title' => __('Company Name', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => get_bloginfo('blogname'),
                        'value' => '',
                        'description' => __(
                                'This is the name that will appear on the Stripe checkout form.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_validate_zipcode' => array(
                        'type' => 'checkbox',
                        'title' => __('Validate Zip', 'stripe_gateway'),
                        'default' => 'yes',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, the zip code entered on the credit card form will be validated.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_collect_billing_address' => array(
                        'type' => 'checkbox',
                        'title' => __('Collect Billing Address', 
                                'stripe_gateway'),
                        'default' => 'yes',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, the customer\'s billing address will be collected.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_button_label' => array(
                        'title' => __('Button Label', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('Pay With Card', 'stripe_gateway'),
                        'value' => '',
                        'description' => __(
                                'This is the label that will appear on the checkout form button.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_add_payment_button_label' => array(
                        'title' => __('Add Payment Button Label', 
                                'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('Add Credit Card', 'stripe_gateway'),
                        'value' => '',
                        'description' => __(
                                'This is the label that will appear on the checkout form button when a user is attempting to add a payment method to their account.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                )
        );
    }
}