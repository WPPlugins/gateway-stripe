<?php
use Stripe\Stripe;
use Stripe\Source;
use Stripe\ExternalAccount;
use Stripe\Error\Base;
use Stripe\Customer;
use Stripe\Card;
use Stripe\BankAccount;
use Stripe\Error\Authentication;
use Stripe\AlipayAccount;

/**
 * Manager class for handling initialization of the plugin and other boiler
 * plate functionality.
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 */
class Stripe_Gateway_Manager
{

    public $version = '1.0.3';

    private $settings;

    private $default_settings;

    private static $instance;

    /**
     *
     * @var Stripe_Gateway_Logger
     */
    public $log;

    public static function instance ()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin.
     */
    public function __construct ()
    {
        define('STRIPE_LICENSE_ACTIVATION_URL', 
                'https://wordpress.paymentplugins.com/');
        define('STRIPE_LICENSE_VERIFICATION_KEY', 'gTys$hsjeScg63dDs35JlWqbx7h');
        $this->includes();
        $this->initialize_settings();
        $this->default_settings = $this->initialize_default_settings();
        $this->add_actions();
        $this->initialize_stripe();
        $this->initialize_logger();
        $this->check_license();
    }

    /**
     * Include all necessary files for initialization.
     */
    public function includes ()
    {
        include_once (STRIPE_GATEWAY_ADMIN . 'abstract/class-stripe-page-api.php');
        include_once (STRIPE_GATEWAY_ADMIN .
                 'abstract/class-stripe-settings-api.php');
        include_once (STRIPE_GATEWAY_ADMIN . 'settings/class-stripe-settings.php');
        include_once (STRIPE_GATEWAY_ADMIN .
                 'settings/class-stripe-donation-settings.php');
        include_once (STRIPE_GATEWAY_ADMIN .
                 'settings/class-stripe-settings-checkout.php');
        include_once (STRIPE_GATEWAY_ADMIN . 'class-stripe-gateway-logger.php');
        include_once (STRIPE_GATEWAY_ADMIN . 'class-stripe-gateway-logs.php');
        include_once (STRIPE_GATEWAY_ADMIN .
                 'settings/class-stripe-subscription-settings.php');
        include_once (STRIPE_GATEWAY_ADMIN .
                 'settings/class-stripe-license-settings.php');
        include_once (STRIPE_GATEWAY_ADMIN . 'class-stripe-updates.php');
        include_once (STRIPE_GATEWAY_PAYMENTS . 'functions/functions.php');
        include_once (STRIPE_GATEWAY_PAYMENTS .
                 'classes/class-stripe-donations.php');
        if ($this->is_woocommerce_active()) {
            $this->wc_includes();
        }
    }

    public function wc_includes ()
    {
        include_once (STRIPE_GATEWAY_ADMIN . 'class-wc-order-capture.php');
    }

    /**
     * Retrieve all saved settings for the gateway.
     *
     * @return mixed
     */
    public function initialize_settings ()
    {
        $this->settings = apply_filters('stripe_gateway_saved_settings', 
                array());
    }

    /**
     * Initialize the Stripe API.
     */
    public function initialize_stripe ()
    {
        Stripe::setApiKey($this->get_secret_key());
    }

    public function initialize_logger ()
    {
        $this->log = new Stripe_Gateway_Logger();
        $this->log->set_debug($this->is_active('debug_enabled'));
    }

    /**
     * Include all classes that depend on WooCommerce to function.
     */
    public function include_wc_dependencies ()
    {
        if ($this->is_woocommerce_active()) {
            include_once STRIPE_GATEWAY_PAYMENTS .
                     'classes/class-stripe-payment-gateway.php';
        }
        if ($this->is_woocommerce_subscriptions_active()) {
            include_once STRIPE_GATEWAY_PAYMENTS .
                     'classes/class-stripe-subscriptions.php';
        }
    }

    /**
     * Retrieve all default settings for the gateway.
     */
    public function initialize_default_settings ()
    {
        return apply_filters('stripe_gateway_default_settings', array());
    }

    /**
     * Return the value for the provided option.
     *
     * @param unknown $key            
     */
    public function get_option ($key)
    {
        if (isset($this->settings[$key])) {
            $value = $this->settings[$key];
        } else {
            $value = isset($this->default_settings[$key]['default']) ? $this->default_settings[$key]['default'] : '';
        }
        $this->settings[$key] = $value;
        return $value;
    }

    public function get_license_status ()
    {
        $status = get_transient(base64_encode('stripe_gateway_status'));
        if (! $status) {
            $status = 'inactive';
        }
        return $status;
    }

    public function filter_gateway_status ($value)
    {
        if ($value) {
            $value = base64_decode($value);
        }
        return $value;
    }

    public function check_license ()
    {
        $status = get_transient(base64_encode('stripe_gateway_status'));
        $license = $this->get_option('license_key');
        if (! $status && ! empty($license)) {
            $url_args = array(
                    'slm_action' => 'slm_check',
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
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_CAINFO => STRIPE_GATEWAY .
                             'ssl/wordpress_paymentplugins_com.crt',
                            CURLOPT_HTTPHEADER => $headers
            );
            $response = $this->execute_curl($options);
            if ($response['result'] === 'success') {
                $status = strtolower($response['status']);
                set_transient(base64_encode('stripe_gateway_status'), 
                        base64_encode(strtolower($response['status'])), 
                        $this->calculate_expiration_time());
            }
        }
    }

    public function execute_curl ($options = array())
    {
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function calculate_expiration_time ()
    {
        $now = new DateTime();
        $future = new DateTime();
        $future->add(new DateInterval('P10D'));
        return strtotime($future->format('Y-m-d H:i:s')) -
                 strtotime($now->format('Y-m-d H:i:s'));
    }

    public function get_mode ()
    {
        $mode = $this->get_option('test_mode') === 'yes' ? 'test' : 'live';
        return $this->get_license_status() !== 'active' ? 'test' : $mode;
    }

    /**
     * Add actions that are required.
     */
    public function add_actions ()
    {
        add_action('admin_notices', 
                array(
                        $this,
                        'display_admin_notices'
                ));
        add_action('plugins_loaded', 
                array(
                        $this,
                        'include_wc_dependencies'
                ), 99);
        add_filter('transient_' . base64_encode('stripe_gateway_status'), 
                array(
                        $this,
                        'filter_gateway_status'
                ));
    }

    /**
     * Return true if the WC plugin is active.
     */
    public function is_woocommerce_active ()
    {
        $plugins = get_option('active_plugins', true);
        return in_array('woocommerce/woocommerce.php', $plugins);
    }

    /**
     * Return true if the WooCommerce Subscriptions plugin is active.
     * WooCommerce must also be
     * active in order for this method to return true;
     *
     * @return boolean
     */
    public function is_woocommerce_subscriptions_active ()
    {
        $plugins = get_option('active_plugins', true);
        return $this->is_woocommerce_active() && in_array(
                'woocommerce-subscriptions/woocommerce-subscriptions.php', 
                $plugins);
    }

    /**
     * adds a message to the admin notices.
     *
     * @param string $type            
     * @param string $message            
     */
    public function add_admin_notice ($type, $message)
    {
        $messages = $this->get_admin_notices();
        $messages[] = array(
                'type' => $type,
                'message' => $message
        );
        set_transient('stripe_gateway_admin_notices', $messages);
    }

    public function get_admin_notices ()
    {
        $messages = get_transient('stripe_gateway_admin_notices');
        return $messages != null ? $messages : array();
    }

    public function delete_admin_notices ()
    {
        delete_transient('stripe_gateway_admin_notices');
    }

    public function get_publishable_key ()
    {
        return $this->get_option($this->get_mode() . '_publishable_key');
    }

    public function get_secret_key ()
    {
        return $this->get_option($this->get_mode() . '_secret_key');
    }

    /**
     * Retrieve the stripe customer id for the user.
     *
     * @param number $user_id            
     * @return mixed|boolean|string|unknown
     */
    public function get_stripe_customer_id ($user_id = 0)
    {
        return get_user_meta($user_id, 
                'stripe_' . $this->get_mode() . '_customer_id', true);
    }

    /**
     * Save the stripe customer id to the user's meta data.
     *
     * @param unknown $user_id            
     * @param string $customer_id            
     */
    public function save_stripe_customer_id ($user_id, $customer_id = '')
    {
        update_user_meta($user_id, 
                'stripe_' . $this->get_mode() . '_customer_id', $customer_id);
    }

    /**
     * Return true if the option is set to 'yes'.
     *
     * @param string $option            
     * @return boolean
     */
    public function is_active ($option)
    {
        return $this->get_option($option) === 'yes';
    }

    /**
     * Return a request parameter.
     *
     * @param string $key            
     * @return string|unknown
     */
    public function get_request_parameter ($key)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
    }

    /**
     * Display any admin message that are saved.
     */
    public function display_admin_notices ($header = false)
    {
        $messages = $this->get_admin_notices();
        if (! empty($messages)) {
            foreach ($messages as $message) {
                if ($header) {
                    $class = $message['type'] === 'error' ? 'stripe-admin-notice red lighten-2' : 'stripe-admin-notice green darken-2';
                    echo '<div class="' . $class . '"><p class="white-text">' .
                             $message['message'] .
                             '</p><a href="#" class="close-stripe-admin-notice right-absolute white-text"><i class="material-icons">close</i></a></div>';
                } else {
                    echo '<div class="notice notice-' . $message['type'] .
                             '"><p>' . $message['message'] . '</p></div>';
                }
            }
        }
        $this->delete_admin_notices();
    }

    /**
     * Return true of there are admin notices.
     *
     * @return boolean
     */
    public function has_admin_notices ()
    {
        $messages = $this->get_admin_notices();
        return ! empty($messages);
    }

    /**
     * Add an error message to the log.
     *
     * @param unknown $message            
     */
    public function error ($message)
    {
        $this->log->error($message);
    }

    /**
     * Add a success message to the log.
     *
     * @param unknown $message            
     */
    public function success ($message)
    {
        $this->log->success($message);
    }

    /**
     * Add an info message to the log.
     *
     * @param unknown $message            
     */
    public function info ($message)
    {
        $this->log->info($message);
    }

    /**
     * Save the payment method information to the postmeta of the WC order.
     *
     * @param ExternalAccount $source            
     * @param unknown $order_id            
     */
    public function save_payment_meta ($source, $order_id)
    {
        update_post_meta($order_id, '_payment_method_title', 
                $this->get_payment_method_title($source));
        update_post_meta($order_id, '_payment_method_token', $source->id);
        do_action('stripe_save_payment_meta', $source, $order_id);
    }

    /**
     *
     * @param ExternalAccount $source            
     * @return string
     */
    public function get_payment_method_title ($payment_method)
    {
        if($payment_method instanceof Card){
            return sprintf('%s **** **** **** %s', $payment_method->brand, 
                $payment_method->last4);
        }
        elseif($payment_method instanceof AlipayAccount){
            return sprintf('Alipay %s', $payment_method->username);
        }else{
            if($payment_method instanceof Source){
                if($payment_method->type === 'bitcoin'){
                    return sprintf('Bitcoin %s', $payment_method->owner->email);
                }
            }
        }
    }

    /**
     * Return a Stripe\Customer object.
     *
     * @param number $user_id            
     * @return \Stripe\Customer
     */
    public function get_stripe_customer ($user_id = 0)
    {
        $customer = null;
        try {
            $customer_id = $this->get_stripe_customer_id($user_id);
            if ($customer_id) {
                $customer = Customer::retrieve($customer_id);
            }
        } catch (Base $e) {
            stripe_manager()->error(
                    sprintf(
                            __(
                                    'Error retrieving Stripe customer %s for user_id %s. Message: %s', 
                                    'stripe_gateway'), $customer_id, $user_id, 
                            $e->getMessage()));
        }
        return $customer;
    }

    /**
     * Returns an array of saved customer sources from the Wordpress database.
     *
     * @param number $user_id            
     */
    public function get_user_sources_from_meta ($user_id = 0)
    {
        return get_user_meta($user_id, 
                'stripe_' . $this->get_mode() . '_sources', true);
    }

    /**
     * Save the user's Stripe sources in the database.
     * <div>Format of array is</div>
     * <code>array('last_updated'=>'2016-09-18
     * 23:59:59','payment_methods'=>array(0=>array(),1=>array()))</code>
     *
     * @param number $user_id            
     * @param array $methods            
     */
    public function save_payment_methods ($user_id = 0, $methods = array())
    {
        update_user_meta($user_id, 'stripe_' . $this->get_mode() . '_sources', 
                $methods);
    }

    /**
     * Return an array of Stripe source objects for the user.
     *
     * @param int $user_id            
     * @return \Stripe\BankAccount[]
     */
    public function get_stripe_source_objects ($user_id)
    {
        $sources = $this->get_user_sources_from_meta($user_id);
        $methods = array();
        if ($sources) {
            
            foreach ($sources['payment_methods'] as $source) {
                $methods[] = $this->get_stripe_source_from_array($source);
            }
        }
        return $methods;
    }

    /**
     * Give an array, convert it to the approproate Stripe payment method.
     *
     * @param array $method            
     * @return \Stripe\BankAccount
     */
    public function get_stripe_source_from_array ($method = array())
    {
        switch ($method['object']) {
            case 'card':
                $source = new Card();
                $source->refreshFrom($method, array());
                break;
            case 'bank_account':
                $source = new BankAccount();
                $source->refreshFrom($method, array());
                break;
        }
        return $source;
    }

    /**
     * Return true if the user has saved payment methods.
     *
     * @param number $user_id            
     * @return boolean
     */
    public function user_has_saved_methods ($user_id = 0)
    {
        $methods = $this->get_user_sources_from_meta($user_id);
        return ! empty($methods['payment_methods']);
    }

    /**
     * Return the id of the last used payment method.
     *
     * @param number $user_id            
     * @return mixed|boolean|string|unknown
     */
    public function get_last_used_payment_method ($user_id = 0)
    {
        return get_user_meta($user_id, 
                'stripe_' . $this->get_mode() . '_last_used', true);
    }

    /**
     * Save the last used payment method id in the user meta.
     *
     * @param number $user_id            
     * @param string $id            
     */
    public function save_last_used_payment_method ($user_id = 0, $id)
    {
        update_user_meta($user_id, 'stripe_' . $this->get_mode() . '_last_used', 
                $id);
    }

    /**
     * Test the connection to Stripe for the given mode.
     *
     * @param string $mode            
     */
    public function connection_test ($mode)
    {
        Stripe::setApiKey($this->get_option($mode . '_secret_key'));
        try {
            $customer = Customer::retrieve('some_customer_h93jg78skbj296d');
        } catch (Base $e) {
            if ($e instanceof Authentication) {
                $this->add_admin_notice('error', 
                        sprintf(
                                __(
                                        'The connection test for your %s environment failed. Reason: %s', 
                                        'stripe_gateway'), $mode, 
                                $e->getMessage()));
            } else {
                $this->add_admin_notice('success', 
                        sprintf(
                                __(
                                        'The connection test for your %s environment was successful.', 
                                        'stripe_gateway'), $mode));
            }
        }
        $this->initialize_stripe(); // Reset the values for Stripe after
                                        // connection test.
    }
}

/**
 * Return an instance of the Stripe_Gateway_Manager class;
 *
 * @return Stripe_Gateway_Manager
 */
function stripe_manager ()
{
    return Stripe_Gateway_Manager::instance();
}
stripe_manager();