<?php
use Stripe\Charge;
use Stripe\Error\Base;

/**
 * Donation class that handles the donation payments.
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *           
 */
class Stripe_Gateway_Donations
{

    public $errors = array();

    public function __construct ()
    {
        add_shortcode('stripe_gateway_donations', 
                array(
                        $this,
                        'output'
                ));
        add_action('wp_enqueue_scripts', 
                array(
                        $this,
                        'register_scripts'
                ));
        add_action('wp_ajax_process_stripe_donation', 
                array(
                        $this,
                        'process_donation'
                ));
        add_action('wp_ajax_nopriv_process_stripe_donation', 
                array(
                        $this,
                        'process_donation'
                ));
        add_action('stripe_donation_field_data', 
                array(
                        $this,
                        'donation_field_default_args'
                ));
    }

    /**
     * Register any necessary js scripts and css files.
     */
    public function register_scripts ()
    {
        wp_register_script('stripe-gateway-donation-js', 
                STRIPE_GATEWAY_ASSETS . 'js/frontend/donations.js', 
                array(
                        'jquery'
                ), stripe_manager()->version, true);
        wp_register_script('stripe-gateway-field-validation', 
                STRIPE_GATEWAY_ASSETS . 'js/frontend/jquery.payment.js', 
                array(
                        'jquery'
                ), stripe_manager()->version, true);
        wp_register_script('stripe-gateway-card-field-validations', 
                STRIPE_GATEWAY_ASSETS . 'js/frontend/card-field-validations.js', 
                array(
                        'stripe-gateway-field-validation'
                ), stripe_manager()->version, true);
        wp_register_script('stripe-gateway-donation-form-js', 
                $this->get_payment_form_js_path(), 
                array(
                        'jquery'
                ), stripe_manager()->version, true);
        wp_register_script('stripe-gateway-js', STRIPE_GATEWAY_JS, 
                array(
                        'jquery'
                ), stripe_manager()->version, true);
        wp_register_script('jquery-block-ui-js', 
                STRIPE_GATEWAY_ASSETS . 'js/jquery/blockUI.min.js', 
                array(
                        'jquery'
                ), stripe_manager()->version, true);
        wp_register_style('stripe-gateway-donation-form-css', 
                $this->get_payment_form_css_path(), null, 
                stripe_manager()->version, null);
        wp_register_style('stripe-gateway-donation-form-external-css', 
                $this->get_payment_form_external_css_path(), null, 
                stripe_manager()->version, null);
        wp_register_style('stripe-gateway-donation-styles', 
                STRIPE_GATEWAY_ASSETS . 'css/frontend/donations.css', null, 
                stripe_manager()->version, null);
        wp_localize_script('stripe-gateway-donation-js', 'stripe_donation_vars', 
                $this->localize_donation_vars());
    }

    public function output ($attrs)
    {
        wp_enqueue_script('stripe-gateway-donation-js');
        wp_enqueue_script('stripe-gateway-donation-form-js');
        wp_enqueue_script('stripe-gateway-field-validation');
        wp_enqueue_script('stripe-gateway-card-field-validations');
        wp_enqueue_script('stripe-gateway-js');
        wp_enqueue_script('jquery-block-ui-js');
        wp_enqueue_style('stripe-gateway-donation-form-css');
        wp_enqueue_style('stripe-gateway-donation-styles');
        wp_enqueue_style('stripe-gateway-donation-form-external-css');
        include STRIPE_GATEWAY_PAYMENTS . 'views/donations/donation-form.php';
    }

    /**
     * Process the donation.
     */
    public function process_donation ()
    {
        $this->validate_donation_fields($this->get_enabled_donation_fields());
        if (! empty($this->errors)) {
            return wp_send_json($this->get_response());
        } else {
            $attrs = array();
            foreach ($this->charge_attributes() as $k => $attribute) {
                $this->{'add_' . $attribute}($attrs);
            }
            try {
                $response = Charge::create($attrs);
            } catch (Base $e) {
                $this->errors[] = sprintf(
                        __(
                                'There was an error processing your donation. Reason: %s', 
                                'stripe_gateway'), $e->getMessage());
                stripe_manager()->error(
                        sprintf(
                                'There was an error processing a donation. Message: %s', 
                                $e->getMessage()));
            }
        }
        wp_send_json($this->get_response());
        exit();
    }

    public function get_response ()
    {
        $response = array();
        if (empty($this->errors)) {
            $response['result'] = 'success';
            $response['message'] = stripe_manager()->get_option(
                    'donation_message');
        } else {
            $response['result'] = 'failure';
            $response['messages'] = $this->errors;
        }
        return $response;
    }

    public function validate_donation_fields ($fields = array())
    {
        foreach ($fields as $k => $data) {
            if (is_array($data['type'])) {
                $this->validate_donation_fields($data['type']);
            } else {
                $value = stripe_manager()->get_request_parameter($k);
                if ($data['required']) {
                    if (empty($value)) {
                        $this->errors[] = sprintf(
                                __('%s cannot be empty.', 'stripe_gateway'), 
                                $data['label']);
                    }
                }
            }
        }
    }

    /**
     * Generate html for the donation fields.
     *
     * @param array $fields            
     */
    public function generate_donation_fields ($fields)
    {
        foreach ($fields as $k => $data) {
            if (is_array($data['type'])) {
                $this->generate_donation_fields($data['type']);
            } else {
                echo $this->{'generate_' . $data['type'] . '_html'}($k, $data);
            }
        }
    }

    /**
     * Returns an array of enabled donation fields.
     * This array contains information
     * for each field such as html type, placeholders, etc.
     *
     * @return mixed[]
     */
    public function get_enabled_donation_fields ()
    {
        $fields = stripe_manager()->get_option('donation_fields');
        $enabled_fields = array();
        $donation_fields = $this->donation_fields();
        if (! empty($fields)) {
            foreach ($fields as $k => $v) {
                if (array_key_exists($k, $donation_fields)) {
                    $enabled_fields[$k] = $donation_fields[$k];
                }
            }
        }
        return $enabled_fields;
    }

    /**
     * Return an array of donation fields.
     *
     * @return string[][]
     */
    public function donation_fields ()
    {
        return apply_filters('stripe_donation_field_data', 
                array(
                        'first_name' => array(
                                'type' => 'text',
                                'label' => __('First Name', 'stripe_gateway'),
                                'placeholder' => __('First Name', 
                                        'stripe_gateway'),
                                'class' => '',
                                'value' => '',
                                'required' => true
                        ),
                        'last_name' => array(
                                'type' => 'text',
                                'label' => __('Last Name', 'stripe_gateway'),
                                'placeholder' => __('Last Name', 
                                        'stripe_gateway'),
                                'class' => '',
                                'value' => '',
                                'required' => true
                        ),
                        'billing_address' => array(
                                'type' => array(
                                        'billing_address1' => array(
                                                'type' => 'text',
                                                'label' => __('Address', 
                                                        'stripe_gateway'),
                                                'placeholder' => __('Address', 
                                                        'stripe_gateway'),
                                                'class' => '',
                                                'value' => '',
                                                'attributes' => array(
                                                        'data-stripe' => 'address_line1'
                                                ),
                                                'required' => true
                                        ),
                                        'billing_address2' => array(
                                                'type' => 'text',
                                                'label' => __('Address 2', 
                                                        'stripe_gateway'),
                                                'placeholder' => __('Address2', 
                                                        'stripe_gateway'),
                                                'class' => '',
                                                'value' => '',
                                                'attributes' => array(
                                                        'data-stripe' => 'address_line2'
                                                ),
                                                'required' => false
                                        ),
                                        'billing_city' => array(
                                                'type' => 'text',
                                                'label' => __('Billing Address', 
                                                        'stripe_gateway'),
                                                'placeholder' => __('City', 
                                                        'stripe_gateway'),
                                                'class' => '',
                                                'value' => '',
                                                'attributes' => array(
                                                        'data-stripe' => 'address_city'
                                                ),
                                                'required' => true
                                        ),
                                        'billing_country' => array(
                                                'type' => 'text',
                                                'label' => __('Country', 
                                                        'stripe_gateway'),
                                                'placeholder' => __('Country', 
                                                        'stripe_gateway'),
                                                'class' => '',
                                                'value' => '',
                                                'attributes' => array(
                                                        'data-stripe' => 'address_country'
                                                ),
                                                'required' => true
                                        ),
                                        'billing_state' => array(
                                                'type' => 'text',
                                                'label' => __('State', 
                                                        'stripe_gateway'),
                                                'placeholder' => __('State', 
                                                        'stripe_gateway'),
                                                'class' => '',
                                                'value' => '',
                                                'attributes' => array(
                                                        'data-stripe' => 'address_state'
                                                ),
                                                'required' => false
                                        ),
                                        'billing_postalcode' => array(
                                                'type' => 'text',
                                                'label' => __('Postal Code', 
                                                        'stripe_gateway'),
                                                'placeholder' => __(
                                                        'Postal Code', 
                                                        'stripe_gateway'),
                                                'class' => '',
                                                'value' => '',
                                                'required' => true
                                        )
                                )
                        ),
                        'email_address' => array(
                                'type' => 'text',
                                'label' => __('Email Address', 'stripe_gateway'),
                                'placeholder' => __('Email Address', 
                                        'stripe_gateway'),
                                'class' => '',
                                'value' => '',
                                'required' => true
                        ),
                        'donation_message' => array(
                                'type' => 'textarea',
                                'label' => __('Donation Message', 
                                        'stripe_gateway'),
                                'placeholder' => __('Donation Message', 
                                        'stripe_gateway'),
                                'class' => '',
                                'value' => '',
                                'required' => false
                        )
                ));
    }

    /**
     * Generate html for the text field.
     *
     * @param string $key            
     * @param array $data            
     */
    public function generate_text_html ($key, $data)
    {
        ob_start();
        include STRIPE_GATEWAY_PAYMENTS . 'views/donations/text-html.php';
        return ob_get_clean();
    }

    /**
     * Generate html for the select field.
     *
     * @param unknown $key            
     * @param unknown $data            
     */
    public function generate_select_html ($key, $data)
    {
        ob_start();
        include STRIPE_GATEWAY_PAYMENTS . 'views/donations/select-html.php';
        return ob_get_clean();
    }

    public function generate_textarea_html ($key, $data)
    {
        ob_start();
        include STRIPE_GATEWAY_PAYMENTS . 'views/donations/textarea-html.php';
        return ob_get_clean();
    }

    /**
     * Return an array of payment methods along with their paths.
     *
     * @return mixed
     */
    public function donation_payment_forms ()
    {
        return apply_filters('stripe_gateway_donation_payment_forms', 
                array(
                        'google_material_design' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                         'views/donations/google-form.php',
                                        'js_path' => '',
                                        'css_path' => STRIPE_GATEWAY_ASSETS .
                                         'css/frontend/google-material-design.css'
                        ),
                        'simple_form' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                 'views/donations/simple-form.php',
                                'js_path' => '',
                                'css_path' => STRIPE_GATEWAY_ASSETS .
                                 'css/frontend/simple-form.css'
                        ),
                        'bootstrap_form' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                 'views/donations/bootstrap-form.php',
                                'js_path' => '',
                                'css_path' => STRIPE_GATEWAY_ASSETS .
                                 'css/frontend/bootstrap-form.css',
                                'css_external' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
                        ),
                        'classic_form' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                 'views/donations/classic-form.php',
                                'js_path' => STRIPE_GATEWAY_ASSETS .
                                 'js/frontend/classic-form.js',
                                'css_path' => STRIPE_GATEWAY_ASSETS .
                                 'css/frontend/classic-form.css'
                        )
                ));
    }

    /**
     * Return a path for the enabled payment forms css.
     *
     * @return mixed
     */
    public function get_payment_form_css_path ()
    {
        $key = stripe_manager()->get_option('donation_credit_card_form');
        $form = wp_parse_args($this->donation_payment_forms()[$key], 
                array(
                        'css_path' => '',
                        'css_external' => '',
                        'dir_path' => '',
                        'js_path' => ''
                ));
        return $form['css_path'];
    }

    public function get_payment_form_external_css_path ()
    {
        $key = stripe_manager()->get_option('donation_credit_card_form');
        $form = wp_parse_args($this->donation_payment_forms()[$key], 
                array(
                        'css_path' => '',
                        'css_external' => '',
                        'dir_path' => '',
                        'js_path' => ''
                ));
        return $form['css_external'];
    }

    /**
     * Return a path for the enabled payment forms js
     *
     * @return mixed
     */
    public function get_payment_form_js_path ()
    {
        $key = stripe_manager()->get_option('donation_credit_card_form');
        $form = $this->donation_payment_forms()[$key];
        return $form['js_path'];
    }

    public function localize_donation_vars ()
    {
        return array(
                'publishable_key' => stripe_manager()->get_publishable_key(),
                'card_form_options' => $this->get_card_options(),
                'ajax_url' => admin_url() .
                         'admin-ajax.php?action=process_stripe_donation'
        );
    }

    /**
     * Return an array of card options.
     *
     * @return boolean[]|NULL[]|mixed[]
     */
    public function get_card_options ()
    {
        return array(
                'donation_card_loader_enabled' => stripe_manager()->is_active(
                        'card_loader_enabled'),
                'donation_card_loader_html' => stripe_manager()->get_option(
                        'card_loader_html'),
                'donation_card_loader_css' => json_decode(
                        stripe_manager()->get_option('card_loader_css'), true)
        );
    }

    public function get_amount_input_field ($attrs)
    {
        if (empty($attrs)) {
            $input = array(
                    'type' => 'text',
                    'label' => __('Donation Amount', 'stripe_gateway'),
                    'placeholder' => __('Amount', 'stripe_gateway'),
                    'value' => '',
                    'class' => '',
                    'required' => true,
                    'attributes' => array()
            );
        } else {
            $input = array(
                    'type' => 'select',
                    'label' => __('Donation Amount', 'stripe_gateway'),
                    'placeholder' => __('Amount', 'stripe_gateway'),
                    'value' => '',
                    'class' => '',
                    'required' => true,
                    'attributes' => array()
            );
            foreach ($attrs as $amount => $label) {
                $input['options'][$amount] = $label;
            }
        }
        return $input;
    }

    public function charge_attributes ()
    {
        return array(
                'source',
                'amount',
                'receipt_email',
                'currency',
                'metadata',
                'description',
                'descriptor'
        );
    }

    public function add_source (&$attrs)
    {
        $attrs['source'] = stripe_manager()->get_request_parameter(
                'stripe_payment_token');
    }

    public function add_amount (&$attrs)
    {
        $amount = stripe_manager()->get_request_parameter('donation_amount');
        $attrs['amount'] = $amount * pow(10, 
                sg_get_currency_code_exponent(
                        stripe_manager()->get_option('donation_currency')));
    }

    public function add_currency (&$attrs)
    {
        $attrs['currency'] = stripe_manager()->get_option('donation_currency');
    }

    public function add_description (&$attrs)
    {
        $attrs['description'] = stripe_manager()->get_option(
                'donation_description');
    }

    public function add_descriptor (&$attrs)
    {
        $value = stripe_manager()->get_option('donation_descriptor');
        if (! empty($value)) {
            $attrs['statement_descriptor'] = $value;
        }
    }

    public function add_metadata (&$attrs)
    {
        $attrs['metadata'] = array();
        if ($this->is_field_enabled('billing_address')) {
            foreach ($this->donation_fields() as $key => $field) {
                $attrs['metadata'][$key] = stripe_manager()->get_request_parameter(
                        $key);
            }
        }
    }

    public function add_receipt_email (&$attrs)
    {
        if ($this->is_field_enabled('email_address')) {
            $attrs['receipt_email'] = stripe_manager()->get_option(
                    'email_address');
        }
    }

    /**
     * Return true if the provided field name is enabled.
     *
     * @param unknown $field            
     * @return boolean
     */
    public function is_field_enabled ($field)
    {
        $fields = stripe_manager()->get_option('donation_fields');
        return array_key_exists($field, $fields);
    }

    public function donation_field_default_args ($fields)
    {
        foreach ($fields as $k => $data) {
            if (is_array($data['type'])) {
                $fields[$k]['type'] = $this->donation_field_default_args(
                        $data['type']);
            } else {
                $fields[$k] = wp_parse_args($data, 
                        array(
                                'type' => '',
                                'label' => '',
                                'placeholder' => '',
                                'attributes' => array(),
                                'required' => false
                        ));
            }
        }
        return $fields;
    }
}
new Stripe_Gateway_Donations();