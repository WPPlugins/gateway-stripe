<?php

/**
 * Main settings page that renders all other settings pages.
 * @author Payment Plugins
 * @copyright 2016
 *
 */
class Stripe_Gateway_Settings extends Stripe_Gateway_Settings_API
{

    public function __construct ()
    {
        $this->title = array(
                'title' => __('Stripe Settings', 'stripe_gateway'),
                'class' => 'thin',
                'description' => __(
                        'On this page you configure your API keys which are required in order for your Wordpress site to communicate with Stripe. You can test this plugin using your Stripe test environment. 
                        In order to use the Live mode, you will need to purchase a license key from <a href="https://wordpress.paymentplugins.com/product/stripe-gateway/" target="_blank">Payment Plugins</a>.
                        <div>If you have any questions, please email <a href="mailto:support@paymentplugins.com">support@paymentplugins.com</a></div>', 
                        'stripe_gateway')
        );
        $this->id = 'settings';
        $this->page = 'stripe-gateway-settings';
        add_action('admin_menu', 
                array(
                        $this,
                        'settings_menu'
                ));
        add_filter('stripe_gateway_validate_live_secret_key', 
                array(
                        $this,
                        'validate_live_secret_key'
                ));
        add_filter('stripe_gateway_validate_live_publishable_key', 
                array(
                        $this,
                        'validate_live_publishable_key'
                ));
        parent::__construct();
    }

    public function settings_menu ()
    {
        add_menu_page('stripe_payment_gateway', 
                __('Stripe Gateway', 'stripe_gateway'), 'manage_options', 
                'stripe-gateway-page', null, null, 8.345);
        add_submenu_page('stripe-gateway-page', 
                __('Settings', 'stripe_gateway'), 
                __('Settings', 'stripe_gateway'), 'manage_options', 
                'stripe-gateway-settings', 
                array(
                        $this,
                        'output'
                ));
        remove_submenu_page('stripe-gateway-page', 'stripe-gateway-page');
    }

    public function set_localized_vars ($vars)
    {
        return $vars;
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
                ),
                'test_mode' => array(
                        'title' => __('Test Mode', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'class' => 'filled-in stripe-mode test-mode',
                        'default' => '',
                        'value' => 'yes',
                        'description' => __(
                                'When enabled, your wordpress site will connect using your Test API Keys. Make sure you have enabled test mode in your Stripe Dashboard.'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'Login to your Stripe account. In the upper left hand corner, you can set the environment that you wish to use. Test mode will allow you to run test transactions.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/live_mode.png'
                        )
                ),
                'test_secret_key' => array(
                        'title' => __('Test Secret Key', 'stripe_gateway'),
                        'type' => 'text',
                        'class' => '',
                        'default' => '',
                        'description' => __(
                                'The test secret key is used to authenticate requests made from your Wordpress site to Stripe.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'Login to your Stripe account. In the upper right hand corner, click Your Account > Account Settings > API Keys.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/api_keys.png'
                        )
                ),
                'test_publishable_key' => array(
                        'title' => __('Test Publishable Key', 'stripe_gateway'),
                        'type' => 'text',
                        'class' => '',
                        'default' => '',
                        'description' => __(
                                'The test publishable key is used to authenticate requests made from your Wordpress site to Stripe.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'test_mode_connection_test' => array(
                        'title' => __('Test Mode Connection', 'stripe_gateway'),
                        'type' => 'button',
                        'class' => 'btn cyan darken-1',
                        'label' => __('Test Connection', 'stripe_gateway'),
                        'value' => 'stripe_test_mode_connection',
                        'description' => __(
                                'By clicking the connection test button, you can test the connection to Stripe.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'live_mode' => array(
                        'title' => __('Live Mode', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'class' => 'filled-in stripe-mode live-mode live-option',
                        'default' => '',
                        'value' => 'yes',
                        'description' => __(
                                'When enabled, your wordpress site will connect using your Live API Keys. Make sure you have enabled live mode in your Stripe Dashboard.'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'Login to your Stripe account. In the upper left hand corner, you can set the environment that you wish to use. Live mode will allow you to process real transactions.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/live_mode.png'
                        )
                ),
                'live_secret_key' => array(
                        'title' => __('Live Secret Key', 'stripe_gateway'),
                        'type' => 'text',
                        'class' => 'live-option',
                        'default' => '',
                        'description' => __(
                                'You must activate a valid license to enter your Live Secret Key.', 
                                'stripe_gateway'),
                        'tool_tip' => true,
                        'helper' => array(
                                'enabled' => true,
                                'description' => __(
                                        'Login to your Stripe account. In the upper right hand corner, click Your Account > Account Settings > API Keys.', 
                                        'stripe_gateway'),
                                'url' => 'https://wordpress.paymentplugins.com/stripe-gateway/api_keys.png'
                        )
                ),
                'live_publishable_key' => array(
                        'title' => __('Live Publishable Key', 'stripe_gateway'),
                        'type' => 'text',
                        'class' => 'live-option',
                        'default' => '',
                        'description' => __(
                                'You must activate a valid license to enter your Live Publishable Key.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'live_mode_connection_test' => array(
                        'title' => __('Live Connection Test', 'stripe_gateway'),
                        'type' => 'button',
                        'label' => __('Test Connection', 'stripe_gateway'),
                        'class' => 'btn cyan darken-1 live-option',
                        'value' => 'stripe_live_mode_connection',
                        'description' => __(
                                'By clicking the connection test button, you can test the connection to Stripe.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                ),
                'debug_enabled' => array(
                        'title' => __('Debug Log Enabled', 'stripe_gateway'),
                        'type' => 'checkbox',
                        'default' => 'yes',
                        'value' => 'yes',
                        'description' => __(
                                'When enabled, the plugin will log all transaction activity. This is very helpful when troubleshooting payment errors.', 
                                'stripe_gateway'),
                        'tool_tip' => true
                )
        );
    }

    public function validate_live_secret_key ($value)
    {
        $status = stripe_manager()->get_option('license_status');
        if (! empty($value)) {
            if ($status === 'inactive') {
                stripe_manager()->add_admin_notice('error', 
                        __(
                                'Your license status is inactive. You cannot save your live secrey key until your license has been activated.', 
                                'stripe_gateway'));
                return false;
            }
        }
        return $value;
    }

    public function validate_live_publishable_key ($value)
    {
        $status = stripe_manager()->get_license_status();
        if (! empty($value)) {
            if ($status === 'inactive') {
                stripe_manager()->add_admin_notice('error', 
                        __(
                                'Your license status is inactive. You cannot save your live publishable key until your license has been activated.', 
                                'stripe_gateway'));
                return false;
            }
        }
        return $value;
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
                 $class . '">' . $status .
                 '</h5><input type="hidden" id="stripe_gateway_license_status" value="' .
                 stripe_manager()->get_license_status() . '"/></div></div>';
    }
}
new Stripe_Gateway_Settings();