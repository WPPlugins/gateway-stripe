<?php
use Stripe\Charge;
use Stripe\Error\Base;
use Stripe\Source;
use Stripe\ExternalAccount;
use Stripe\Customer;
use Stripe\Refund;
use Stripe\Card;
use Stripe\BankAccount;

/**
 * Payment Gateway Class that handles order processing, refunds, etc.
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *           
 */
class Stripe_Payment_Gateway extends WC_Payment_Gateway
{

    public static $gateway_name = 'stripe_payment_gateway';

    public $token;

    /**
     *
     * @var WC_Order
     */
    public $order;

    public function __construct ()
    {
        $this->id = self::$gateway_name;
        $this->title = stripe_manager()->get_option('gateway_title');
        $this->method_title = __('Stripe Payment Gateway', 'stripe_gateway');
        $this->add_actions();
        $this->enabled = stripe_manager()->get_option('enabled');
        $this->has_fields = true;
        $this->supports = $this->get_supports();
    }

    /**
     * Initialize any actions etc that are needed for static operations.
     */
    public static function init ()
    {
        add_action('woocommerce_payment_gateways', __CLASS__ . '::add_gateway');
        add_action('woocommerce_created_customer', 
                __CLASS__ . '::create_new_customer', 10, 2);
        add_action('user_register', __CLASS__ . '::user_register', 10, 1);
        add_filter('woocommerce_saved_payment_methods_list', 
                __CLASS__ . '::get_customer_saved_methods_list', 10, 2);
        add_action('init', __CLASS__ . '::maybe_create_customer', 10);
        add_action('woocommerce_order_action_capture_stripe_charge', 
                __CLASS__ . '::capture_charge');
        add_action('init', __CLASS__ . '::maybe_update_payment_methods', 99);
    }

    /**
     * Add all required actions for gateway.
     */
    public function add_actions ()
    {
        add_action('wp_enqueue_scripts', 
                array(
                        $this,
                        'register_scripts'
                ));
        add_action('wp_enqueue_scripts', 
                array(
                        $this,
                        'checkout_variables'
                ));
        add_filter('stripe_process_wc_order', 
                array(
                        $this,
                        'process_wc_order'
                ), 10, 2);
        add_filter('woocommerce_checkout_fields', 
                array(
                        $this,
                        'woocommerce_checkout_fields'
                ));
        add_filter('woocommerce_gateway_title', 
                array(
                        $this,
                        'woocommerce_gateway_title'
                ), 10, 2);
        add_filter('woocommerce_gateway_icon', 
                array(
                        $this,
                        'woocommerce_gateway_icon'
                ), 10, 2);
    }

    /**
     * Add this gateway to the list of accepted gateways.
     *
     * @param array $methods            
     * @return string
     */
    public static function add_gateway ($methods = array())
    {
        if (stripe_manager()->is_active('enabled')) {
            $methods[] = __CLASS__;
        }
        return $methods;
    }

    public function process_payment ($order_id)
    {
        if ($this->cart_contains_subscriptions()) {
            return apply_filters('stripe_process_wc_subscription_order', 
                    array(), $order_id);
        } else {
            if ($this->is_payment_change_request()) {
                $this->order = wc_get_order($order_id);
                return $this->get_success_array();
            } else {
                return apply_filters('stripe_process_wc_order', array(), $order_id);
            }
        }
    }

    /**
     * Process the WC order.
     *
     * @param int $order_id            
     */
    public function process_wc_order ($result, $order_id)
    {
        $user_id = wp_get_current_user()->ID;
        $this->order = wc_get_order($order_id);
        $attrs = array();
        // Dynamically add all charge attributes.
        foreach ($this->charge_attributes() as $attribute) {
            $this->{'add_' . $attribute}($attrs);
        }
        try {
            $response = Charge::create($attrs); // No Exception thrown so
                                                // success.
            stripe_manager()->success(
                    sprintf('Charge for order %s was successful. Response: %s', 
                            $this->order->id, 
                            print_r($response->__toArray(true), true)));
            $this->order->payment_complete($response->id);
            $this->save_payment_meta($response->source, $this->order->id);
            if ($this->use_saved_payment_method()) {
                stripe_manager()->save_last_used_payment_method($user_id, 
                        $response->source->id);
            }
            WC()->cart->empty_cart();
            return $this->get_success_array();
        } catch (Base $e) {
            stripe_manager()->error(
                    sprintf('Error processing charge for order %s. Message: %s', 
                            $this->order->id, $e->getMessage()));
            wc_add_notice($e->getMessage(), 'error');
            return $this->get_error_array();
        }
    }

    /**
     * Process the refund using Stripe.
     *
     * {@inheritDoc}
     *
     * @see WC_Payment_Gateway::process_refund()
     */
    public function process_refund ($order_id, $amount = null, $reason = '')
    {
        $order = wc_get_order($order_id);
        if (! $this->can_refund_order($order)) {
            return new WP_Error('refund_error', 
                    sprintf(
                            __(
                                    'Order %s cannot be refunded. There is no valid transaction Id associated with the order.', 
                                    'stripe_gateway'), $order_id));
        }
        try {
            $user = wp_get_current_user();
            $response = Refund::create(
                    array(
                            'charge' => $order->get_transaction_id(),
                            'amount' => $amount * pow(10, 
                                    sg_get_currency_code_exponent(
                                            $order->order_currency)),
                            'metadata' => array(
                                    'order_id' => $order->id,
                                    'refunded_by' => $user->first_name . ' ' .
                                             $user->last_name
                            )
                    ));
            $refunds = get_post_meta($order_id, 'stripe_refunds', true);
            if (empty($refunds)) {
                $refunds = array();
            }
            $refunds[] = $response->id;
            update_post_meta($order_id, 'stripe_refunds', $refunds);
            stripe_manager()->success(
                    sprintf(
                            __(
                                    'Order %s has been refunded in the amount of %s %s.', 
                                    'stripe_gateway'), $order_id, 
                            get_woocommerce_currency_symbol(
                                    $order->order_currency), $amount));
            return true;
        } catch (Base $e) {
            $message = sprintf(
                    __('Order %s could not be refunded. Reason: %s', 
                            'stripe_gateway'), $order_id, $e->getMessage());
            stripe_manager()->error($message);
            $order->add_order_note($message);
            return new WP_Error('refund_error', $message);
        }
    }

    public function admin_options ()
    {
        include STRIPE_GATEWAY_ADMIN . 'views/admin-options.php';
    }

    /**
     * Render the credit card forms necessary for checkout.
     *
     * {@inheritDoc}
     *
     * @see WC_Payment_Gateway::payment_fields()
     */
    public function payment_fields ()
    {
        if (is_checkout()) {
            if ($this->is_custom_form()) {
                include STRIPE_GATEWAY_PAYMENTS . 'views/custom-form.php';
            } else {
                include STRIPE_GATEWAY_PAYMENTS . 'views/checkout-form.php';
            }
            include STRIPE_GATEWAY_PAYMENTS . 'views/saved-methods.php';
        } elseif (is_add_payment_method_page()) {
            if ($this->is_custom_form()) {
                include STRIPE_GATEWAY_PAYMENTS . 'views/add-payment-method.php';
            }else{
                include STRIPE_GATEWAY_PAYMENTS . 'views/add-payment-method-checkout.php';
            }
            
        }
    }

    /**
     * Register all the scripts required for checkout to take place.
     */
    public function register_scripts ()
    {
        if ($this->is_enabled()) {
            if ($this->is_custom_form()) {
                wp_enqueue_script('stripe-gateway-js', STRIPE_GATEWAY_JS, 
                        array(), stripe_manager()->version, true);
                wp_enqueue_script('stripe-gateway-form-js', 
                        STRIPE_GATEWAY_ASSETS . 'js/frontend/custom-form.js', 
                        array(
                                is_checkout() ? 'wc-checkout' : 'jquery'
                        ), stripe_manager()->version, true);
                wp_enqueue_script('stripe-gateway-validation-js', 
                        STRIPE_GATEWAY_ASSETS . 'js/frontend/jquery.payment.js', 
                        array(
                                'jquery'
                        ), stripe_manager()->version, true);
                wp_enqueue_script('stripe-gateway-card-field-validations-js', 
                        STRIPE_GATEWAY_ASSETS .
                                 'js/frontend/card-field-validations.js', 
                                array(
                                        'stripe-gateway-validation-js'
                                ), stripe_manager()->version, true);
                $this->register_form_scripts();
            } else {
                wp_enqueue_script('stripe-checkout-js', STRIPE_CHECKOUT_JS, 
                        array(), stripe_manager()->version, true);
                wp_enqueue_script('stripe-gateway-form-js', 
                        STRIPE_GATEWAY_ASSETS . 'js/frontend/checkout-form.js', 
                        array(
                                is_checkout() ? 'wc-checkout' : 'jquery'
                        ), stripe_manager()->version, true);
                wp_localize_script('stripe-gateway-form-js', 
                        'stripe_checkout_vars', $this->get_checkout_flow_vars());
            }
            wp_enqueue_style('stripe-gateway-style', 
                    STRIPE_GATEWAY_ASSETS . 'css/frontend/stripe-gateway.css', 
                    null, stripe_manager()->version, null);
        }
    }

    /**
     * Register scripts and styles for the credit card form.
     */
    public function register_form_scripts ()
    {
        $form_id = stripe_manager()->get_option('credit_card_form');
        $form_data = $this->get_credit_card_forms()[$form_id];
        wp_enqueue_style('stripe-form-' . $form_id . 'css', 
                $form_data['css_path'], null, stripe_manager()->version, null);
        if (isset($form_data['css_external'])) {
            wp_enqueue_style('stripe-form-' . $form_id . 'external-css', 
                    $form_data['css_external'], null, stripe_manager()->version, 
                    null);
        }
        ;
        wp_enqueue_script('stripe-form-' . $form_id . 'js', 
                $form_data['js_path'], 
                array(
                        'stripe-gateway-form-js'
                ), stripe_manager()->version, true);
    }

    /**
     * Localize an javascript variables needed by the plugin during checkout.
     */
    public function checkout_variables ()
    {
        if ($this->is_enabled()) {
            wp_localize_script('stripe-gateway-form-js', 
                    'stripe_gateway_checkout_vars', 
                    array(
                            'publishable_key' => stripe_manager()->get_publishable_key(),
                            'card_form_options' => $this->get_card_options()
                    ));
        }
    }

    /**
     * Return an array of card options.
     *
     * @return boolean[]|NULL[]|mixed[]
     */
    public function get_card_options ()
    {
        return array(
                'card_loader_enabled' => stripe_manager()->is_active(
                        'card_loader_enabled'),
                'card_loader_html' => stripe_manager()->get_option(
                        'card_loader_html'),
                'card_loader_css' => json_decode(
                        stripe_manager()->get_option('card_loader_css'), true)
        );
    }

    /**
     * Add shipping address;
     *
     * @param unknown $attrs            
     */
    public function add_shipping_address (&$attrs)
    {
        $attrs['shipping'] = array(
                'name' => $this->order->shipping_first_name . ' ' .
                         $this->order->shipping_last_name,
                        'phone' => $this->order->billing_phone,
                        'address' => array(
                                'line1' => $this->order->shipping_address_1,
                                'line2' => $this->order->shipping_address_2,
                                'country' => $this->order->shipping_city,
                                'postal_code' => $this->order->shipping_postcode,
                                'state' => $this->order->shipping_state
                        )
        );
    }

    public function add_receipt_email (&$attrs)
    {
        $attrs['receipt_email'] = $this->order->billing_email;
    }

    public function add_metadata (&$attrs)
    {
        $attrs['metadata'] = array();
        $options = stripe_manager()->get_option('order_meta');
        if (! empty($options)) {
            foreach ($options as $k => $v) {
                $method = 'get_' . $k;
                if (method_exists($this->order, $method)) {
                    $attrs['metadata'][$k] = $this->order->{$method}();
                } else {
                    $attrs['metadata'][$k] = $this->order->{$k};
                }
            }
        }
        $attrs['metadata']['custom_order_id'] = stripe_manager()->get_option(
                'order_prefix') . $this->order->id .
                 stripe_manager()->get_option('order_suffix');
    }

    /**
     * Return true of the gateway is enabled.
     *
     * @return boolean
     */
    public function is_enabled ()
    {
        return $this->enabled === 'yes';
    }

    /**
     * Add the order amount to the attrs array.
     *
     * @param unknown $attrs            
     */
    public function add_amount (&$attrs)
    {
        $amount = $this->order->get_total();
        $amount = $amount * pow(10, 
                sg_get_currency_code_exponent(get_woocommerce_currency()));
        $attrs['amount'] = $amount;
    }

    public function add_currency (&$attrs)
    {
        $attrs['currency'] = get_woocommerce_currency();
    }

    /**
     * Add customer id to charge attributes.
     *
     * @param unknown $attrs            
     */
    public function add_customer (&$attrs)
    {
        $customer_id = stripe_manager()->get_stripe_customer_id(
                wp_get_current_user()->ID);
        if ($customer_id) {
            if ($this->use_saved_payment_method()) {
                $attrs['customer'] = $customer_id;
            }
        }
    }

    public function add_charge_or_capture (&$attrs)
    {
        $source = stripe_manager()->get_request_parameter('stripe_payment_type');
        if($source !== 'source_bitcoin' && $source !== 'alipay_account'){
            $attrs['capture'] = ! stripe_manager()->is_active('authorize_charge');
        }
    }

    /**
     * Add the statement descriptor.
     *
     * @param unknown $attrs            
     */
    public function add_statement_descriptor (&$attrs)
    {
        $descriptor = stripe_manager()->get_option('statement_descriptor');
        if (! empty($descriptor)) {
            $attrs['statement_descriptor'] = $descriptor;
        }
    }

    public function add_description (&$attrs)
    {
        $attrs['description'] = get_bloginfo('blogname') . ' ' .
                 sprintf(__('Order %s', 'stripe_gateway'), $this->order->id);
    }

    /**
     * Add the payment source to the array of attributes.
     * Can be either a saved source or a token returned from Stripe.
     *
     * @param unknown $attrs            
     */
    public function add_source (&$attrs)
    {
        if ($this->use_saved_payment_method()) {
            $attrs['source'] = stripe_manager()->get_request_parameter(
                    'stripe_saved_method_token');
        } else {
            $attrs['source'] = stripe_manager()->get_request_parameter(
                    'stripe_payment_token');
        }
    }

    /**
     * Return an array of attributes to be added to the Stripe charge object.
     *
     * @return string[]
     */
    private function charge_attributes ()
    {
        return apply_filters('stripe_wc_get_charge_attributes', 
                array(
                        'amount',
                        'currency',
                        'charge_or_capture',
                        'customer',
                        'metadata',
                        'receipt_email',
                        'shipping_address',
                        'statement_descriptor',
                        'description',
                        'source'
                ));
    }

    /**
     * Return an array of credit card forms that are available on the checkout
     * page.
     *
     * @return mixed
     */
    public function get_credit_card_forms ()
    {
        return apply_filters('stripe_gateway_credit_card_forms', 
                
                array(
                        'google_material_design' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                         'views/google-form.php',
                                        'js_path' => '',
                                        'css_path' => STRIPE_GATEWAY_ASSETS .
                                         'css/frontend/google-material-design.css'
                        ),
                        'simple_form' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                 'views/simple-form.php',
                                'js_path' => '',
                                'css_path' => STRIPE_GATEWAY_ASSETS .
                                 'css/frontend/simple-form.css'
                        ),
                        'bootstrap_form' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                 'views/bootstrap-form.php',
                                'js_path' => '',
                                'css_path' => STRIPE_GATEWAY_ASSETS .
                                 'css/frontend/bootstrap-form.css',
                                'css_external' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
                        ),
                        'classic_form' => array(
                                'dir_path' => STRIPE_GATEWAY_PAYMENTS .
                                 'views/classic-form.php',
                                'js_path' => STRIPE_GATEWAY_ASSETS .
                                 'js/frontend/classic-form.js',
                                'css_path' => STRIPE_GATEWAY_ASSETS .
                                 'css/frontend/classic-form.css'
                        )
                ));
    }

    /**
     * Return an array containg all of the feaures that this gateway supports.
     *
     * @return array
     */
    public function get_supports ()
    {
        return apply_filters('stripe_gateway_supports', 
                array(
                        'subscriptions',
                        'products',
                        'add_payment_method',
                        'subscription_cancellation',
                        'multiple_subscriptions',
                        'subscription_amount_changes',
                        'subscription_date_changes',
                        'default_credit_card_form',
                        'refunds',
                        'pre-orders',
                        'subscription_payment_method_change_admin',
                        // 'gateway_scheduled_payments',
                        'subscription_reactivation',
                        'subscription_suspension',
                        'subscription_payment_method_change_customer'
                ));
    }

    public function get_error_array ()
    {
        return array(
                'result' => 'failure',
                'redirect' => ''
        );
    }

    public function get_success_array ()
    {
        return array(
                'result' => 'success',
                'redirect' => $this->order->get_checkout_order_received_url()
        );
    }

    /**
     * Save the payment meta for the payment method.
     *
     * @param ExternalAccount $source            
     * @param int $order_id            
     */
    public function save_payment_meta ($source, $order_id)
    {
        stripe_manager()->save_payment_meta($source, $order_id);
    }

    /**
     * Add required data-stripe attributes to the checkout fields.
     *
     * @param array $fields            
     */
    public function woocommerce_checkout_fields ($fields = array())
    {
        $checkout_fields = array(
                'billing_address_1' => 'address_line1',
                'billing_address_2' => 'address_line2',
                'billing_city' => 'address_city',
                'billing_state' => 'address_state',
                'billing_country' => 'address_country'
        );
        foreach ($checkout_fields as $field_name => $stripe_name) {
            if (! isset($fields['billing'][$field_name]['custom_attributes'])) {
                $fields['billing'][$field_name]['custom_attributes'] = array();
            }
            $this->add_data_stripe_attribute(
                    $fields['billing'][$field_name]['custom_attributes'], 
                    $stripe_name);
        }
        return $fields;
    }

    public function add_data_stripe_attribute (&$array, $stripe_name)
    {
        $array['data-stripe'] = $stripe_name;
    }

    /**
     * Create a new Stripe customer when WC creates a customer.
     */
    public static function create_new_customer ($user_id, 
            $new_customer_data = array())
    {
        try {
            $customer = Customer::create(
                    array(
                            'description' => sprintf(
                                    __('WooCommerce user id %s', 
                                            'stripe_gateway'), $user_id),
                            'email' => $new_customer_data['user_email'],
                            'metadata' => array(
                                    'user_id' => $user_id,
                                    'creation_date' => date('Y-m-d H:i:s'),
                                    'user_login' => $new_customer_data['user_login'],
                                    'user_email' => $new_customer_data['user_email']
                            )
                    ));
            stripe_manager()->save_stripe_customer_id($user_id, $customer->id);
            stripe_manager()->success(
                    sprintf(
                            __('Customer %s created in Stripe for user_id %s.', 
                                    'stripe_gateway'), $customer->id, $user_id));
        } catch (Base $e) {
            stripe_manager()->error(
                    sprintf('Error creating customer %s in Stripe. Message: %s', 
                            $user_id, $e->getMessage()));
        }
    }

    /**
     * Create a Stripe customer when a user is registered.
     */
    public static function user_register ($user_id)
    {
        $customer_id = stripe_manager()->get_stripe_customer_id($user_id);
        if (! $customer_id) {
            try {
                $user = get_user_by('id', $user_id);
                $customer = Customer::create(
                        array(
                                'description' => sprintf(
                                        __('WooCommerce user id %s', 
                                                'stripe_gateway'), $user_id),
                                'email' => $user->user_email,
                                'metadata' => array(
                                        'user_id' => $user_id,
                                        'creation_date' => date('m-d-Y H:i:s'),
                                        'first_name' => get_user_meta($user_id, 
                                                'first_name', true),
                                        'last_name' => get_user_meta($user_id, 
                                                'last_name', true),
                                        'user_login' => $user->user_login
                                )
                        ));
                stripe_manager()->save_stripe_customer_id($user_id, 
                        $customer->id);
                stripe_manager()->success(
                        sprintf(
                                __(
                                        'Customer %s created in Stripe for user_id %s.', 
                                        'stripe_gateway'), $customer->id, 
                                $user_id));
            } catch (Base $e) {
                stripe_manager()->error(
                        sprintf(
                                'Error creating customer %s in Stripe. Message: %s', 
                                $user_id, $e->getMessage()));
            }
        }
    }

    /**
     * Add a payment method to the Stripe customer's account.
     */
    public function add_payment_method ()
    {
        $user_id = wp_get_current_user()->ID;
        $customer_id = stripe_manager()->get_stripe_customer_id($user_id);
        if (! $customer_id) {
            self::user_register($user_id);
            $customer_id = stripe_manager()->get_stripe_customer_id($user_id);
        }
        $customer = stripe_manager()->get_stripe_customer($user_id);
        if ($customer) {
            try {
                $card = $customer->sources->create(
                        array(
                                'source' => stripe_manager()->get_request_parameter(
                                        'stripe_payment_token')
                        ));
                $user_sources = stripe_manager()->get_user_sources_from_meta(
                        $user_id);
                if (empty($user_sources)) {
                    $user_sources = array(
                            'payment_methods' => array()
                    );
                }
                $user_sources['payment_methods'][] = $card->__toArray(true);
                stripe_manager()->save_payment_methods($user_id, $user_sources);
                stripe_manager()->save_last_used_payment_method($user_id, 
                        $card->id);
                stripe_manager()->success(
                        sprintf(
                                __('Payment method %s saved for user %s', 
                                        'stripe_gateway'), $card->id, $user_id));
                wc_add_notice(
                        __('Your payment method has been saved', 
                                'stripe_gateway'), 'success');
            } catch (Base $e) {
                stripe_manager()->error(
                        sprintf(
                                __(
                                        'Error creating payment method for user %s. Message: %s', 
                                        'stripe_gateway'), $user_id, 
                                $e->getMessage()));
                wc_add_notice(
                        sprintf(
                                __(
                                        'Your payment method could not be added. Reason: %s', 
                                        'stripe_gateway'), $e->getMessage()), 
                        'error');
            }
        }
    }

    /**
     * Retrieve a list of customer payment methods for use on the
     * payment-methods.php page.
     *
     * @param int $user_id            
     */
    public static function get_customer_saved_methods_list ($saved_methods, 
            $user_id)
    {
        $customer = stripe_manager()->get_stripe_customer($user_id);
        if ($customer == null) {
            return $saved_methods;
        }
        self::maybe_delete_payment_method();
        $saved_methods[self::$gateway_name] = array();
        // $sources = $customer->sources;
        $sources = stripe_manager()->get_user_sources_from_meta($user_id);
        // foreach ($customer->sources->data as $source) {
        foreach ($sources['payment_methods'] as $save_method) {
            switch ($save_method['object']) {
                case 'card':
                    $source = new Card();
                    $source->refreshFrom($save_method, array());
                    break;
                case 'bank_account':
                    $source = new BankAccount();
                    $source->refreshFrom($save_method, $opts);
                    break;
            }
            $method = array(
                    'method' => array(
                            'last4' => $source->object === 'card' ? $source->last4 : '',
                            'brand' => $source->object === 'card' ? $source->brand : ''
                    ),
                    'expires' => $source->object === 'card' ? $source->exp_month .
                             ' / ' . $source->exp_year : '',
                            'actions' => array(
                                    'delete' => array(
                                            'name' => __('Delete', 
                                                    'stripe_gateway'),
                                            'url' => self::get_delete_payment_method_url(
                                                    $source->id)
                                    )
                            )
            );
            $saved_methods[self::$gateway_name][] = $method;
        }
        return $saved_methods;
    }

    /**
     * Generate the url used for deleting payment methods.
     * url is generated on permalink structure.
     *
     * @param string $id            
     */
    public static function get_delete_payment_method_url ($id)
    {
        $permalink = get_permalink(wc_get_page_id('myaccount'));
        $url = wc_get_endpoint_url('payment-methods', '', $permalink);
        $url = add_query_arg('delete-stripe-payment-method', $id, $url);
        return wp_nonce_url($url);
    }

    public static function maybe_create_customer ()
    {
        if (! is_user_logged_in()) {
            return; // User is not logged in so exit.
        }
        $customer_id = stripe_manager()->get_stripe_customer_id(
                wp_get_current_user()->ID);
        if ($customer_id) {
            return;
        }
        self::user_register(wp_get_current_user()->ID);
    }

    /**
     * Potentially delete the customer's payment method.
     */
    public static function maybe_delete_payment_method ()
    {
        if (! isset($_REQUEST['delete-stripe-payment-method'])) {
            return; // request parameter not set so
        }
        if (! wp_verify_nonce($_GET['_wpnonce'])) {
            return; // invalid nonce.
        }
        $token = $_REQUEST['delete-stripe-payment-method'];
        $user_id = wp_get_current_user()->ID;
        try {
            $customer = stripe_manager()->get_stripe_customer($user_id);
            if ($customer) {
                $response = $customer->sources->retrieve($token)->delete();
                stripe_manager()->success(
                        sprintf(
                                __('Payment method %s was delete for user %s.', 
                                        'stripe_gateway'), $token, 
                                wp_get_current_user()->ID));
                wc_add_notice(
                        __('Your payment method has been deleted.', 
                                'stripe_gateway'), 'success');
                $sources = stripe_manager()->get_user_sources_from_meta(
                        $user_id);
                $index = sg_get_index_of_payment_method(
                        $sources['payment_methods'], $token);
                if ($index !== null) {
                    unset($sources['payment_methods'][$index]);
                    stripe_manager()->save_payment_methods($user_id, $sources);
                }
            }
        } catch (Base $e) {
            stripe_manager()->error(
                    sprintf(
                            __(
                                    'There was an error deleting payment method %s. Message: %s', 
                                    'stripe_gateway'), $token, $e->getMessage()));
            wc_add_notice(
                    sprintf(
                            __(
                                    'There was an error deleting your payment method. Reason: %s', 
                                    'stripe_gateway'), $e->getMessage()), 'error');
        }
    }

    /**
     * Capture the charge for the WC order.
     *
     * @param WC_Order $order            
     */
    public static function capture_charge ($order)
    {
        if (self::$gateway_name !== $order->payment_method) {
            return;
        }
        $amount = stripe_manager()->get_request_parameter(
                'stripe_capture_amount');
        $capture_amount = $amount *
                 pow(10, sg_get_currency_code_exponent($order->order_currency));
        try {
            $charge = Charge::retrieve($order->get_transaction_id());
            $charge->capture(
                    array(
                            'amount' => $capture_amount
                    ));
            $message = sprintf(
                    __(
                            'Charge capture for order %s was successful. Amount %s%s.', 
                            'stripe_gateway'), $order->id, 
                    get_woocommerce_currency_symbol($order->order_currency), 
                    $amount);
            $order->add_order_note($message);
            stripe_manager()->success($message);
        } catch (Base $e) {
            $message = sprintf(
                    __('Order %s could not be captured. Message: %s', 
                            'stripe_gateway'), $order->id, $e->getMessage());
            stripe_manager()->error($message);
            $order->add_order_note($message);
        }
    }

    /**
     * Return true of the order has a transaction id.
     *
     * @param WC_Order $order            
     */
    public function can_refund_order ($order)
    {
        $id = $order->get_transaction_id();
        return ! empty($id);
    }

    /**
     * If an update is required on the customer's payment methods, then retrieve
     * them from Stripe and update the user's meta data.
     */
    public static function maybe_update_payment_methods ()
    {
        if (! is_user_logged_in()) { // Not logged in, exit method.
            return;
        }
        $update_needed = false;
        $user_id = wp_get_current_user()->ID;
        $user_sources = stripe_manager()->get_user_sources_from_meta($user_id);
        if (! $user_sources) {
            $user_sources = array();
            $update_needed = true;
        }
        $current_time = new DateTime();
        $last_update = isset($user_sources['last_update']) ? $user_sources['last_update'] : new DateTime();
        $last_update->add(new DateInterval('P1D')); // Add one day.
        
        if ($update_needed || $last_update <= $current_time) {
            $customer_id = stripe_manager()->get_stripe_customer_id($user_id);
            if ($customer_id) {
                $user_sources = array(
                        'last_update' => '',
                        'payment_methods' => array()
                );
                try {
                    $customer = Customer::retrieve($customer_id);
                    foreach ($customer->sources->data as $source) {
                        $user_sources['payment_methods'][] = $source->__toArray(
                                true);
                    }
                    $user_sources['last_update'] = $current_time;
                    stripe_manager()->save_payment_methods($user_id, 
                            $user_sources);
                } catch (Base $e) {
                    stripe_manager()->error(
                            sprintf(
                                    __(
                                            'There was an error retrieving customer %s for user %s. Message: $s', 
                                            'stripe_gateway'), $customer_id, 
                                    $user_id, $e->getMessage()));
                }
            }
        }
    }

    /**
     * Return true if the user has selected to use a saved payment method.
     *
     * @return boolean
     */
    public function use_saved_payment_method ()
    {
        $value = stripe_manager()->get_request_parameter(
                'stripe_saved_method_token');
        return ! empty($value);
    }

    /**
     * Method that checks if WC Subscriptions is active and if so, does the cart
     * contain a subscription.
     */
    public function cart_contains_subscriptions ()
    {
        if (stripe_manager()->is_woocommerce_subscriptions_active()) {
            if (WC_Subscriptions_Cart::cart_contains_subscription()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Save the payment method before a subscription is processed.
     * If there is a failure,
     * method will return false, otherwise true.
     *
     * @param string $token            
     * @return ExternalAccount|boolean
     */
    public function save_payment_method ($token)
    {
        $user_id = wp_get_current_user()->ID;
        $customer_id = stripe_manager()->get_stripe_customer_id($user_id);
        $customer = stripe_manager()->get_stripe_customer($user_id);
        if (! $customer) {
            wc_add_notice(
                    __(
                            'There was an error retrieving your customer data. A Subscription cannot be processed at this time using a new card. Try using a saved card.', 
                            'stripe_gateway'), 'error');
            return false;
        } else {
            try {
                $payment_method = $customer->sources->create(
                        array(
                                'source' => $token
                        ));
                $user_sources = stripe_manager()->get_user_sources_from_meta(
                        $user_id);
                $user_sources['payment_methods'][] = $payment_method->__toArray();
                stripe_manager()->save_payment_methods($user_id, $user_sources);
                stripe_manager()->save_last_used_payment_method($user_id, 
                        $payment_method->id);
                $this->token = $payment_method->id;
                return $payment_method;
            } catch (Base $e) {
                wc_add_notice(
                        sprintf(
                                __(
                                        'There was an error saving your payment method. Reason: %s', 
                                        'stripe_gateway'), $e->getMessage()), 
                        'error');
                stripe_manager()->error(
                        sprintf(
                                __(
                                        'There was an error saving the payment method for user %s. Message: %s', 
                                        'stripe_gateway'), $user_id, 
                                $e->getMessage()));
                return false;
            }
        }
    }

    /**
     * Return true if the request is for a payment method change.
     *
     * @return boolean
     */
    public function is_payment_change_request ()
    {
        if (stripe_manager()->is_woocommerce_subscriptions_active()) {
            if (isset($_REQUEST['change_payment_method'])) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    public function woocommerce_gateway_title ($title, $id)
    {
        if ($this->id === $id) {
            if (stripe_manager()->get_mode() === 'test') {
                $title = sprintf('%s (%s)', $title, __('Test Mode'));
            }
        }
        return $title;
    }

    /**
     * If icons are set to outside, return the html for the accepted payment
     * methods.
     *
     * @param unknown $icon            
     * @param unknown $id            
     * @return string
     */
    public function woocommerce_gateway_icon ($icon, $id)
    {
        if ($this->id === $id) {
            if (is_checkout()) {
                if (stripe_manager()->get_option('payment_icons_location') ===
                         'outside') {
                    ob_start();
                    include STRIPE_GATEWAY_PAYMENTS .
                             'views/payment-method-icons.php';
                    $icon = ob_get_clean();
                }
            }
        }
        return $icon;
    }

    /**
     * Returns true of the custom form has been selected for the checkout flow.
     *
     * @return boolean
     */
    public function is_custom_form ()
    {
        return stripe_manager()->get_option('checkout_flow') === 'custom_form';
    }

    /**
     * Prepare an array of localized variables use in the Stripe checkout flow.
     *
     * @return string[]|boolean[][]|string[][]|NULL[][]
     */
    public function get_checkout_flow_vars ()
    {
        $args = array(
                'options' => array(
                        'key' => stripe_manager()->get_publishable_key(),
                        'image' => stripe_manager()->get_option(
                                'checkout_image_url'),
                        'name' => stripe_manager()->get_option(
                                'checkout_company_name'),
                        'zipCode' => stripe_manager()->is_active(
                                'checkout_validate_zipcode'),
                        'billingAddress' => stripe_manager()->is_active(
                                'checkout_collect_billing_address'),
                        'panelLabel' => stripe_manager()->get_option(
                                'checkout_panel_label'),
                        'bitcoin' => stripe_manager()->is_active(
                                'checkout_bitcoin_enabled'),
                        'alipay' => stripe_manager()->is_active(
                                'checkout_alipay_enabled'),
                        'currency' => get_woocommerce_currency(),
                        'amount' => is_checkout() ? WC()->cart->total * pow(10, 
                                sg_get_currency_code_exponent(
                                        get_woocommerce_currency())) : 0
                )
        );
        $args['gateway'] = $this->id;
        if ($this->cart_contains_subscriptions()) {
            $args['has_subscriptions'] = true;
            $args['options']['bitcoin'] = false;
            $args['options']['alipay'] = false;
        } else {
            $args['has_subscriptions'] = false;
        }
        if (! is_checkout()) {
            $args['options']['bitcoin'] = false;
            $args['options']['alipay'] = false; // Don't enable bitcoin or alipay if
                                                 // page is not checkout.
        }
        return $args;
    }
}
Stripe_Payment_Gateway::init();