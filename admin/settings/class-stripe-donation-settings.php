<?php

/**
 * Settings class that controls donation settings.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Stripe_Dontation_Settings extends Stripe_Gateway_Settings_API
{

    public $card_settings;

    public function __construct ()
    {
        $this->id = 'donation_settings';
        $this->page = 'stripe-gateway-settings';
        $this->tab = 'donation-settings';
        $this->title = array(
                'title' => __('Dontation Settings', 'stripe_gateway'),
                'class' => 'thin',
                'description' => __(
                        'In order to use the donation functionality, you must place short code <strong>[stripe_gateway_donations]</strong> on the page you wish to accept donations. To render the donation amount field as a drop down, you must provide a value and a label for each dropdown option as follows. <strong>[stripe_gateway_donations 5="$ 5" 10="$ 10" 15="$ 15"]</strong>', 
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
        $this->includes();
        $this->card_settings = new Stripe_Donation_Payment_Settings();
        parent::__construct();
    }

    public function includes ()
    {
        include STRIPE_GATEWAY_ADMIN .
                 'settings/class-donation-payment-settings.php';
    }

    public function set_localized_vars ($vars)
    {
        $field_key = $this->get_field_key_name('donation_fields');
        $vars['keys'][$field_key] = array(
                'options' => $this->settings()['donation_fields']['options'],
                'html' => '<div class="chip">%title%<input type="hidden" name="stripe_' .
                         $this->id .
                         '_donation_fields[%name%]" value=""><i class="remove-settings-chip close material-icons">close</i></div>',
                        'toast' => __(
                                'Donation field %s has already been added.', 
                                'stripe_gateway')
        );
        $field_key = $this->get_field_key_name('donation_form_card_icons');
        $vars['keys'][$field_key] = array(
                'options' => $this->settings()['donation_form_card_icons']['options'],
                'html' => '<div class="chip">%title%<input type="hidden" name="stripe_' .
                         $this->id .
                         '_donation_form_card_icons[%name%]" value=""><i class="remove-settings-chip close material-icons">close</i></div>',
                        'toast' => __(
                                'Payment method %s has already been added.', 
                                'stripe_gateway')
        );
        return $vars;
    }

    public function generate_donation_fields ($key, $data)
    {
        $field_key = $this->get_field_key_name($key);
        include STRIPE_GATEWAY_ADMIN . 'settings/views/donation-fields.php';
    }

    public function generate_card_icon_options ($key, $data)
    {
        $field_key = $this->get_field_key_name($key);
        include STRIPE_GATEWAY_ADMIN . 'settings/views/card-icon-fields.php';
    }

    public function settings ()
    {
        return array(
                'donation_message' => array(
                        'title' => __('Donation Message', 'stripe_gateway'),
                        'type' => 'textarea',
                        'default' => __('Thank you for your donation!', 
                                'stripe_gateway'),
                        'description' => __(
                                'This is the message that your donors will see after a successful donation is made.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'donation_button_text' => array(
                        'title' => __('Button Text', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('Donate', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear on the donation submit button.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'donation_button_style' => array(
                        'title' => __('Donation Button Style', 'stripe_gateway'),
                        'type' => 'textarea',
                        'default' => 'border-radius: 4px; background-color: #4fc3f7; color: #ffffff; border: none; transition: all .3s ease-out; letter-spacing: 0.5px; height: 36px; line-height: 36px; padding: 0 2em;',
                        'tool_tip' => true,
                        'description' => __(
                                'You can enter the css style of the donation button here.', 
                                'stripe_gateway')
                ),
                'donation_fields' => array(
                        'title' => __('Donation Fields', 'stripe_gateway'),
                        'type' => 'custom',
                        'function' => array(
                                $this,
                                'generate_donation_fields'
                        ),
                        'default' => array(),
                        'options' => array(
                                'first_name' => __('First Name', 
                                        'stripe_gateway'),
                                'last_name' => __('Last Name', 'stripe_gateway'),
                                'billing_address' => __('Billing Address', 
                                        'stripe_gateway'),
                                'email_address' => __('Email Address', 
                                        'stripe_gateway'),
                                'donation_message' => __('Donation Message', 
                                        'stripe_gateway')
                        ),
                        'description' => __(
                                'This setting allows you to add fields that you want to appear on the donation form such as donor name, billing address, etc.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => __('', 'stripe_gateway')
                        )
                ),
                'card_options' => array(
                        'title' => __('Payment Form Options', 'stripe_gateway'),
                        'type' => 'custom',
                        'function' => array(
                                $this->card_settings,
                                'output_form_settings'
                        ),
                        'description' => __(
                                'This settings allows you to customize the credit card form options.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'donation_form_card_icons' => array(
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
                'donation_descriptor' => array(
                        'title' => __('Donation Descriptor', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => '',
                        'value' => '',
                        'attributes' => array(
                                'maxlength' => '22'
                        ),
                        'description' => __(
                                'The donation descriptor is what will appear on your customer\'s credit card statement.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'donation_description' => array(
                        'title' => __('Donation Description', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => get_bloginfo('blogname') . ' donation',
                        'value' => '',
                        'description' => __(
                                'The donation description is a way to distinguish this charge from other charges.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'donation_currency' => array(
                        'title' => __('Donation Currency', 'stripe_gateway'),
                        'type' => 'select',
                        'default' => 'USD',
                        'options' => sg_get_currency_codes(),
                        'description' => __(
                                'This is the currency that your donations will be processed in.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                )
        );
    }
}
new Stripe_Dontation_Settings();