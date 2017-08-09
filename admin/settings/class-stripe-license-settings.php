<?php

class Stripe_Gateway_License_Settings extends Stripe_Gateway_Settings_API
{

    public function __construct ()
    {
        $this->title = array(
                'title' => __('Stripe License', 'stripe_gateway'),
                'class' => 'thin',
                'description' => __(
                        'On this page you can activate your license key so that you can begin accepting live payments using your Stripe gateway. To purchase a license 
                        go to <a href="https://wordpress.paymentplugins.com/product/stripe-gateway/" target="_blank">Payment Plugins</a>. If your license status shows as expired, you will need to update your
                        billing information at Payment Plugins so your yearly license fee can be processed.', 
                        'stripe_gateway')
        );
        $this->id = 'license_settings';
        $this->page = 'stripe-gateway-settings';
        $this->tab = 'license-settings';
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
        add_filter('stripe_gateway_validate_license_key', 
                array(
                        $this,
                        'validate_license'
                ));
        add_filter('stripe_gateway_validate_license_status_expired_button', 
                array(
                        $this,
                        'check_expired_license'
                ));
        parent::__construct();
    }

    public function settings ()
    {
        return array(
                'license_key' => array(
                        'title' => __('License Key', 'stripe_gateway'),
                        'type' => 'text',
                        'default' => '',
                        'value' => '',
                        'description' => __(
                                'In this field you enter your license key. To activate your license, enter the license key from your Payment Plugins order and save your settings.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'license_status_notice' => array(
                        'title' => __('License Status', 'stripe_gateway'),
                        'type' => 'custom',
                        'function' => array(
                                $this,
                                'output_license_notice'
                        ),
                        'default' => '',
                        'class' => '',
                        'description' => __(
                                'In order for your license status to show as active, you must purchase a license and activate it.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                )
        );
    }

    public function output_license_notice ($key, $data)
    {
        $status = stripe_manager()->get_license_status();
        $class = '';
        if ($status === 'inactive') {
            $class = 'red-text text-lighten-2';
        } elseif ($status === 'active') {
            $class = 'green-text text-lighten-2';
        } elseif ($status === 'expired') {
            $class = 'orange-text';
        }
        echo '<div class="row"><div class="input-field col s12"><h5 class="' .
                 $class . '">' . $status . '</h5></div></div>';
        if ($status === 'expired') {
            echo '<div class="row"><div class="input-field col s12"><button class="btn  deep-orange darken-1" name="' .
                     $this->get_field_key_name('license_status_expired_button') .
                     '">' . __('Refresh Key', 'stripe_gateway') .
                     '</button></div></div>';
        }
    }

    public function validate_license ($license)
    {
        if (empty($license)) {
            return false;
        }
        $status = stripe_manager()->get_license_status();
        $license_key = stripe_manager()->get_option('license_key');
        if ($status === 'active' && $license_key === $license) {
            return false; // No need to validate and existing license. Only
                          // validate if a new license.
        }
        $attempt = 0;
        $args = array(
                'timeout' => 60
        );
        $url_args = array(
                'slm_action' => 'slm_activate',
                'secret_key' => STRIPE_LICENSE_VERIFICATION_KEY,
                'license_key' => $license
        );
        $url = add_query_arg($url_args, STRIPE_LICENSE_ACTIVATION_URL);
        $headers = array(
                'Content-type: text/html'
        );
        $options = array(
                CURLOPT_URL => $url,
                CURLOPT_CONNECTTIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_CAINFO => STRIPE_GATEWAY .
                         'ssl/wordpress_paymentplugins_com.crt',
                        CURLOPT_HTTPHEADER => $headers
        );
        $response = stripe_manager()->execute_curl($options);
        if ($response['result'] === 'error') {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;
            $response = stripe_manager()->execute_curl($options);
            if ($response['result'] === 'error') {
                stripe_manager()->add_admin_notice('error', 
                        sprintf(
                                __(
                                        'Your license could not be activated at this time. Reason: %s', 
                                        'stripe_gateway'), $response['message']));
                $license = false;
            } else 
                if ($response['result'] === 'success') {
                    stripe_manager()->add_admin_notice('success', 
                            sprintf(
                                    __(
                                            'Your license has been activated successfully. You can now configure your Live API keys and begin accepting real payments.', 
                                            'stripe_gateway')));
                    set_transient(base64_encode('stripe_gateway_status'), 
                            base64_encode('active'), 
                            stripe_manager()->calculate_expiration_time());
                }
        } else {
            if ($response['result'] === 'success') {
                stripe_manager()->add_admin_notice('success', 
                        sprintf(
                                __(
                                        'Your license has been activated successfully. You can now configure your Live API keys and begin accepting real payments.', 
                                        'stripe_gateway')));
                set_transient(base64_encode('stripe_gateway_status'), 
                        base64_encode('active'), 
                        stripe_manager()->calculate_expiration_time());
            }
        }
        return $license;
    }

    /**
     * Check if the user is trying to check an expired license first.
     *
     * {@inheritDoc}
     *
     * @see Stripe_Gateway_Settings_API::save()
     */
    public function save ()
    {
        $field_key = $this->get_field_key_name('license_status_expired_button');
        if (isset($_POST[$field_key])) {
            $license = $this->get_field_value(
                    $this->get_field_key_name('license_key'));
            delete_transient(base64_encode('stripe_gateway_status'));
            stripe_manager()->check_license();
            if (stripe_manager()->get_license_status() === 'expired') {
                stripe_manager()->add_admin_notice('error', 
                        __(
                                'Your license key is expired. To change your license to
                        active, you must update your billing information with Payment Plugins to ensure that your subscription can be processed.', 
                                'stripe_gateway'));
            }
        } else {
            parent::save();
        }
    }

    public function check_expired_license ($value)
    {
        $value = false;
        $license = $this->get_field_value(
                $this->get_field_key_name('license_key'));
    }
}
new Stripe_Gateway_License_Settings();