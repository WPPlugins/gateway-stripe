<?php

/**
 * Settings specific to the Stripe credit card form.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Stripe_Card_Form_Settings extends Stripe_Gateway_Settings_API
{

    public function __construct ()
    {
        $this->title = false;
        $this->id = 'card_form_settings';
        $this->page = 'stripe-gateway-settings';
        $this->tab = 'checkout-settings';
        add_action('stripe_save_settings_' . $this->tab, 
                array(
                        $this,
                        'save'
                ));
        add_filter('stripe_gateway_validate_card_loader_css', 
                array(
                        $this,
                        'validate_json'
                ), 10, 2);
        parent::__construct();
    }

    public function custom_form_output ($key, $data)
    {
        $data = wp_parse_args($data, $this->get_default_custom_html_args());
        include STRIPE_GATEWAY_ADMIN . 'settings/views/custom-form-settings.php';
    }

    /**
     * Return an array of credit card form options.
     *
     * @return mixed
     */
    public function get_credit_card_form_options ()
    {
        return apply_filters('stripe_gateway_credit_card_form_options', 
                array(
                        'google_material_design' => __('Google Material Design', 
                                'stripe_gateway'),
                        'simple_form' => __('Simple Form', 'stripe_gateway'),
                        'bootstrap_form' => __('Bootstrap Form'),
                        'classic_form' => __('Classic Form', 'stripe_gateway')
                ));
    }

    public function settings ()
    {
        return array(
                'credit_card_form' => array(
                        'title' => __('Credit Card Form', 'stripe_gateway'),
                        'type' => 'select',
                        'default' => 'simple_form',
                        'value' => '',
                        'options' => $this->get_credit_card_form_options(),
                        'description' => __(
                                'This option allows you to select the credit card form that you want to use on your checkout page.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'postal_field_enabled' => array(
                        'title' => __('Enable Postal Code', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'default' => 'yes',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, the credit card form will contain a field for the postal code.'),
                        'tool_tip' => true
                ),
                'cvv_field_enabled' => array(
                        'title' => __('Enable CVV Field', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'default' => 'yes',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, the credit card form will contain a field for the card security code (CVV).'),
                        'tool_tip' => true
                ),
                'card_number_label' => array(
                        'title' => __('Card Number Label', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('Card Number', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a label for the card number.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_expiration_date_label' => array(
                        'title' => __('Exp Date Label', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('Exp Date', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a label for the expiration date.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_expiration_month_label' => array(
                        'title' => __('Exp Month Label', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('MM', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a label for the expiration date.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_expiration_year_label' => array(
                        'title' => __('Exp Year Label', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('YY', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a label for the expiration year. Some forms have seperate expiration years and months.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_cvv_label' => array(
                        'title' => __('CVV Label', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('CVV', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a label for the cvv field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_postal_label' => array(
                        'title' => __('Postal Code Label', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('Postal Code', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a label for the postal code field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_number_placeholder' => array(
                        'title' => __('Card Number Placeholder', 
                                'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('4111 1111 1111 1111', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a placeholder in the card number input field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_expiration_date_placeholder' => array(
                        'title' => __('Exp Date Placeholder', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('12 / 16', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a placeholder in the expiration date input field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_expiration_month_placeholder' => array(
                        'title' => __('Exp Month Placeholder', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('MM', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a placeholder in the expiration date input field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_expiration_year_placeholder' => array(
                        'title' => __('Exp Year Placeholder', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('YY', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a placeholder in the expiration date input field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_cvv_placeholder' => array(
                        'title' => __('CVV Placeholder', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('123', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a placeholder in the card cvv input field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_postal_placeholder' => array(
                        'title' => __('Postal Code Placeholder', 
                                'stripe_gateway'),
                        'type' => 'text',
                        'default' => __('78703', 'stripe_gateway'),
                        'description' => __(
                                'This is the text that will appear as a placeholder in the card postal code input field.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_loader_enabled' => array(
                        'title' => __('Enable Loader', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'default' => 'yes',
                        'value' => 'yes',
                        'description' => __(
                                'If enabled, the credit card form will have a processing overlay display when the payment is being processed during checkout.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_loader_html' => array(
                        'title' => __('Loader HTML', 'stripe_gateway'),
                        'type' => 'textarea',
                        'default' => '<div class="custom-form-checkout-loader"><h1>Processing...</h1></div>',
                        'value' => '',
                        'description' => __(
                                'You can customize the html of the payment form overlay here.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'card_loader_css' => array(
                        'title' => __('Loader Styles', 'stripe_gateway'),
                        'type' => 'textarea',
                        'default' => '{"border":"none", "width":"80%", "max-width":"300px"}',
                        'value' => '',
                        'description' => __(
                                'You can customize the styles of the overlay here. The css must be in json format.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                )
        );
    }
}
//new Stripe_Card_Form_Settings();