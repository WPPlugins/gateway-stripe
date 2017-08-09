<?php

/**
 * Settings class for configuring subscription options.
 * @author Payment Plugins
 * @copyright Payment Plugins
 *
 */
class Stripe_Subscription_Settings extends Stripe_Gateway_Settings_API
{

    public function __construct ()
    {
        $this->title = array(
                'title' => __('Subscription Settings', 'stripe_gateway'),
                'class' => 'thin',
                'description' => __(
                        '<div>If you have any questions, please email <a href="mailto:support@paymentplugins.com">support@paymentplugins.com</a></div>', 
                        'stripe_gateway')
        );
        $this->id = 'subscription_settings';
        $this->page = 'stripe-gateway-settings';
        $this->tab = 'subscription-settings';
        add_filter('stripe_gateway_' . $this->tab . '_title', 
                array(
                        $this,
                        'generate_title_html'
                ));
        add_action('stripe_save_settings_' . $this->tab, 
                array(
                        $this,
                        'save'
                ));
        add_action('output_stripe_settings_page_' . $this->tab, 
                array(
                        $this,
                        'generate_settings_html'
                ));
        parent::__construct();
    }

    public function set_localized_vars ($vars)
    {
        $field_key = $this->get_field_key_name('subscription_order_meta');
        $vars['keys'][$field_key] = array(
                'options' => $this->settings()['subscription_order_meta']['options'],
                'html' => '<div class="chip">%title%<input type="hidden" name="stripe_' .
                         $this->id .
                         '_subscription_order_meta[%name%]" value=""><i class="remove-meta close material-icons">close</i></div>',
                        'toast' => __('Field %s has already been added.', 
                                'stripe_gateway')
        );
        return $vars;
    }

    /**
     * Render the settings html for the order meta.
     *
     * @param unknown $key            
     * @param unknown $data            
     */
    public function generate_order_meta ($key, $data)
    {
        $field_key = $this->get_field_key_name($key);
        $data = wp_parse_args($data, $this->get_default_custom_html_args());
        include STRIPE_GATEWAY_ADMIN . 'settings/views/order-meta-fields.php';
    }

    public function settings ()
    {
        return array(
                'subscription_prefix' => array(
                        'title' => __('Subscription Prefix', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => 'wc_subscription_',
                        'value' => '',
                        'class' => '',
                        'description' => __(
                                'This is an optional value which if not empty will be appended to the begging of the order id. It can be a good way to differenciate your orders.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'The subscription prefix is appended to the order id sent to Stripe. If left blank, the prefix will not be included.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/subscription_prefix.png'
                        )
                ),
                'subscription_suffix' => array(
                        'title' => __('Subscription Prefix', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => '',
                        'value' => '',
                        'class' => '',
                        'description' => __(
                                'This is an optional value which if not empty will be appended to the end of the order id. It can be a good way to differenciate your orders.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'The subscription suffix is appended to the order id sent to Stripe. If left blank, the prefix will not be included.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/subscription_suffix.png'
                        )
                ),
                'subscription_order_meta' => array(
                        'title' => __('Subscription Meta', 'stripe_gateway'),
                        'type' => 'custom',
                        'function' => array(
                                $this,
                                'generate_order_meta'
                        ),
                        'options' => array(
                                'order_currency' => __('Order Currency', 
                                        'stripe_gateway'),
                                'order_number' => __('Order Number', 
                                        'stripe_gateway'),
                                'total' => __('Order Total', 'stripe_gateway'),
                                'total_tax' => __('Total Tax', 'stripe_gateway'),
                                'subtotal' => __('Subtotal', 'stripe_gateway'),
                                'user_id' => __('User Id', 'stripe_gateway'),
                                'order_discount' => __('Order Discount', 
                                        'stripe_gateway')
                        ),
                        'default' => array(
                                'order_currency' => __('Order Currency', 
                                        'stripe_gateway'),
                                'order_number' => __('Order Number', 
                                        'stripe_gateway'),
                                'total' => __('Order Total', 'stripe_gateway'),
                                'total_tax' => __('Total Tax', 'stripe_gateway'),
                                'subtotal' => __('Subtotal', 'stripe_gateway'),
                                'user_id' => __('User Id', 'stripe_gateway'),
                                'order_discount' => __('Order Discount', 
                                        'stripe_gateway')
                        ),
                        'class' => '',
                        'description' => __(
                                'You can add subscription meta data that will be sent to Stripe during the charge creation. This can be a good way to store information within Stripe that is specific to WooCommerce.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'When viewing a subscription payment, the fields you configure for the order will appear in the metadata section of the payment data.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/subscription_order_meta.png'
                        )
                )
        );
    }
}
new Stripe_Subscription_Settings();