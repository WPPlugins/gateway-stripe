<?php

/**
 * Settings class that extends the Stripe_Settings_API.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Stripe_Checkout_Settings extends Stripe_Gateway_Settings_API
{

    private $custom_form_settings;

    private $checkout_form_settings;

    public function __construct ()
    {
        $this->id = 'checkout_settings';
        $this->page = 'stripe-gateway-settings';
        $this->tab = 'checkout-settings';
        $this->title = array(
                'title' => __('Checkout Settings', 'stripe_gateway'),
                'class' => 'thin',
                'description' => __(
                        '<div>If you have any questions, please email <a href="mailto:support@paymentplugins.com">support@paymentplugins.com</a></div>', 
                        'stripe_gateway')
        );
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
        add_filter('stripe_gateway_default_settings_' . $this->id, 
                array(
                        $this,
                        'get_default_settings'
                ));
        add_filter('stripe_gateway_validate_statement_descriptor', 
                array(
                        $this,
                        'validate_statement_descriptor'
                ));
        $this->includes();
        $this->custom_form_settings = new Stripe_Card_Form_Settings();
        $this->checkout_form_settings = new Stripe_Checkout_Flow_Settings();
        parent::__construct();
    }

    public function includes ()
    {
        include_once (STRIPE_GATEWAY_ADMIN .
                 'settings/class-stripe-card-settings.php');
        include_once (STRIPE_GATEWAY_ADMIN .
                 'settings/class-stripe-checkout-flow-settings.php');
    }

    public function set_localized_vars ($vars)
    {
        $field_key = $this->get_field_key_name('order_meta');
        $vars['keys'][$field_key] = array(
                'options' => $this->settings()['order_meta']['options'],
                'html' => '<div class="chip">%title%<input type="hidden" name="stripe_' .
                         $this->id .
                         '_order_meta[%name%]" value=""><i class="remove-settings-chip close material-icons">close</i></div>',
                        'toast' => __('Meta field %s has already been added.', 
                                'stripe_gateway')
        );
        $field_key = $this->get_field_key_name('card_form_icons');
        $vars['keys'][$field_key] = array(
                'options' => $this->settings()['card_form_icons']['options'],
                'html' => '<div class="chip">%title%<input type="hidden" name="' .
                         $field_key .
                         '[%name%]"><i class="close remove-settings-chip material-icons">close</i></div>',
                        'toast' => __('Card type %s has already been added.', 
                                'stripe_gateway')
        );
        return $vars;
        return $vars;
    }

    public function generate_order_meta ($key, $data)
    {
        $field_key = $this->get_field_key_name($key);
        $data = wp_parse_args($data, $this->get_default_custom_html_args());
        include STRIPE_GATEWAY_ADMIN . 'settings/views/order-meta-fields.php';
    }

    public function generate_card_icon_options ($key, $data)
    {
        $field_key = $this->get_field_key_name($key);
        $data = wp_parse_args($data, $this->get_default_custom_html_args());
        include STRIPE_GATEWAY_ADMIN . 'settings/views/card-icon-fields.php';
    }

    /**
     * Return an array of settings required for plugin configuration.
     *
     * {@inheritDoc}
     *
     * @see Stripe_Settings_API::settings()
     */
    public function settings ()
    {
        return array(
                'enabled' => array(
                        'title' => __('Enabled', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'default' => 'yes',
                        'value' => 'yes',
                        'description' => __(
                                'Set this option to enable the Stripe Payment Gateway. When enabled, the gateway will appear on the checkout page as a payment option.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'gateway_title' => array(
                        'title' => __('Title', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('Stripe Payment Gateway', 'stripe_gateway'),
                        'description' => __(
                                'This is the title of the gateway as it appears on the checkout page.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'order_prefix' => array(
                        'title' => __('Order Prefix', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => '',
                        'placeholder' => __('Order Prefix', 'stripe_gateway'),
                        'description' => __(
                                'The order prefix is appended to the order id sent to Stripe. If left blank, the prefix will not be included.'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'When viewing a payment, the custom order id in the payment metadata will consist of the order suffix + order Id + order prefix.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/order_prefix.png'
                        )
                ),
                'order_suffix' => array(
                        'title' => __('Order Suffix', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => '',
                        'placeholder' => __('Order Suffix', 'stripe_gateway'),
                        'description' => __(
                                'The order suffix is appended to the end of the order id. If left blank, the suffix will not be included.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'When viewing a payment, the custom order id in the payment metadata will consist of the order suffix + order Id + order prefix.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/order_suffix.png'
                        )
                ),
                'authorize_charge' => array(
                        'title' => __('Authorize Charge', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'default' => '',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, the order amount will be authorized on the customer\'s card but not settled. You will need to capture the charge within the WooCommerce order screen.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/capture_charge.png',
                                'description' => __(
                                        'To capture a charge, go to the order screen and under order actions select capture charge.', 
                                        'stripe_gateway')
                        )
                ),
                'statement_descriptor' => array(
                        'title' => __('Statement Descriptor', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => '',
                        'maxlength' => '22',
                        'description' => __(
                                'The descriptor is the value that appears on your customer\'s credit card statement. Max length is 22 characters.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_flow' => array(
                        'type' => 'select',
                        'title' => __('Checkout Flow', 'stripe_gateway'),
                        'default' => 'custom_form',
                        'options' => array(
                                'custom_form' => __('Custom Form', 
                                        'stripe_gateway'),
                                'checkout_form' => __('Checkout Form', 
                                        'stripe_gateway')
                        ),
                        'description' => __(
                                'The checkout flow options allows you to configure whether you want to use a custom credit card form or Stripe\'s modal checkout form. When using Stripe\'s checkout form, you have the option of enabling Bitcoin and Alipay.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'custom_form_options' => array(
                        'title' => __('Custom Form Options', 'stripe_gateway'),
                        'type' => 'custom',
                        'function' => array(
                                $this->custom_form_settings,
                                'custom_form_output'
                        ),
                        'class' => 'custom-form-subitem',
                        'description' => __(
                                'This settings allows you to customize the credit card form options.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'checkout_form_options' => array(
                        'title' => __('Checkout Flow Options', 'stripe_gateway'),
                        'type' => 'custom',
                        'function' => array(
                                $this->checkout_form_settings,
                                'output_checkout_flow'
                        ),
                        'class' => 'checkout-form-subitem',
                        'description' => __(
                                'This settings allows you to customize the credit card form options.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/checkout_form.png',
                                'description' => __(
                                        'If enabled, this option will display the Stripe payment form on your checkout page. With Stripe\'s checkout form, you can enable Alipay and Bitcoin. You can customize the logo that appears on the checkout form.', 
                                        'stripe_gateway')
                        )
                ),
                'order_meta' => array(
                        'title' => __('Order Meta', 'stripe_gateway'),
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
                                'You can add order meta that will be sent to Stripe during the charge creation. This can be a good way to store information within Stripe that is specific to WooCommerce.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'When viewing a payment, the fields you configure for the order will appear in the metadata section of the payment data.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/order_metadata.png'
                        )
                ),
                'card_form_icons' => array(
                        'title' => __('Card Icons', 'stripe_gateway'),
                        'type' => 'custom',
                        'function' => array(
                                $this,
                                'generate_card_icon_options'
                        ),
                        'default' => array(
                                'visa' => '',
                                'amex' => ''
                        ),
                        'options' => array(
                                'visa' => __('Visa', 'stripe_gateway'),
                                'amex' => __('American Express', 
                                        'stripe_gateway'),
                                'discover' => __('Discover', 'stripe_gateway'),
                                'mastercard' => __('MasterCard', 
                                        'stripe_gateway'),
                                'maestro' => __('Maestro', 'stripe_gateway'),
                                'jcb' => __('JCB', 'stripe_gateway'),
                                'diners' => __('Diners', 'stripe_gateway')
                        ),
                        'description' => __(
                                'This option allows you to add card types that will appear as accepted payment methods on the checkout page.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'If you add payment methods here, they will appear as icons on the payment form for the Stripe Gateway.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/card_icons.png'
                        )
                ),
                'payment_icons_location' => array(
                        'title' => __('Payment Icon Location', 'stripe_gateway'),
                        'type' => 'select',
                        'options' => array(
                                'inside' => __('Inside', 'stripe_gateway'),
                                'outside' => __('Outside', 'stripe_gateway')
                        ),
                        'default' => 'outside',
                        'value' => '',
                        'description' => __(
                                'This settings allows you to control if the accepted payment method icons appear next to the gateway title on the checkout page or on the payment form.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                )
        );
    }

    public function validate_statement_descriptor ($value)
    {
        if (preg_match('/[<>"\']/', $value, $matches)) {
            stripe_manager()->add_admin_notice('error', 
                    __(
                            'The statement descriptor can have a max length of 22 characters and cannot include <>"\' characters.', 
                            'stripe_gateway'));
            $value = false;
        }
        return $value;
    }
}
new Stripe_Checkout_Settings();