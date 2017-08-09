<?php
use Stripe\Error\Base;
use Stripe\ExternalAccount;
use Stripe\Charge;

class Stripe_WC_Subscriptions extends Stripe_Payment_Gateway
{

    public $token;

    private $subscriptions = array();

    public function __construct ()
    {
        $this->add_actions();
    }

    public function add_actions ()
    {
        add_filter('stripe_process_wc_subscription_order', 
                array(
                        $this,
                        'process_subscription'
                ), 10, 2);
        add_action('stripe_save_payment_meta', 
                array(
                        $this,
                        'save_subscription_meta'
                ), 10, 2);
        add_action(
                'woocommerce_scheduled_subscription_payment_' .
                         self::$gateway_name, 
                        array(
                                $this,
                                'scheduled_subscription_payment'
                        ), 10, 2);
        add_action(
                'woocommerce_subscription_payment_method_updated_to_' .
                         self::$gateway_name, 
                        array(
                                $this,
                                'payment_method_updated'
                        ), 10, 2);
        add_filter('woocommerce_subscription_payment_method_to_display', 
                array(
                        $this,
                        'display_payment_method'
                ), 10, 2);
    }

    /**
     * Process the WC Subscription.
     * If the payment method is new, it is saved first. Then, the
     * standard process_wc_order method is called.
     *
     * @param int $order_id            
     * @return string[]
     */
    public function process_subscription ($result, $order_id)
    {
        $user_id = wp_get_current_user()->ID;
        $this->order = wc_get_order($order_id);
        $subscriptions = wcs_get_subscriptions_for_order($order_id);
        
        // If customer is using a new payment method, then save it before
        // proceeding.
        if (! $this->use_saved_payment_method()) {
            $response = $this->save_payment_method(
                    stripe_manager()->get_request_parameter(
                            'stripe_payment_token'));
            if (! $response) { // There was an error, return error array.
                return $this->get_error_array();
            } else {
                $this->token = $response->id;
            }
        } else {
            $this->token = stripe_manager()->get_request_parameter(
                    'stripe_saved_method_token');
        }
        $_REQUEST['stripe_saved_method_token'] = $this->token;
        
        // If order total is zero, then save the payment data. No need to create
        // a charge.
        if ($this->order->get_total() == 0) {
            $user_sources = stripe_manager()->get_user_sources_from_meta(
                    $user_id);
            $index = sg_get_index_of_payment_method(
                    $user_sources['payment_methods'], $this->token);
            $payment_method = stripe_manager()->get_stripe_source_from_array(
                    $user_sources['payment_methods'][$index]);
            $this->save_payment_meta($payment_method, $order_id);
            $this->order->payment_complete();
            $result = $this->get_success_array();
        } else {
            $result = $this->process_wc_order($result, $order_id);
        }
        return $result;
    }

    /**
     * Process the recurring payment for the subscription.
     *
     * @param float $amount            
     * @param WC_Order $order            
     */
    public function scheduled_subscription_payment ($amount, WC_Order $order)
    {
        $this->order = $order;
        $subscription = $order->subscription_renewal;
        $attrs = array();
        foreach ($this->subscription_recurring_charge_attributes() as $attribute) {
            $this->{'add_' . $attribute}($attrs);
        }
        $attrs['amount'] = $amount *
                 pow(10, sg_get_currency_code_exponent($order->order_currency));
        try {
            $response = Charge::create($attrs);
            $this->save_payment_meta($response->source, $this->order->id);
            $message = sprintf(
                    __('Recurring payment processed for order %s', 
                            'stripe_gateway'), $order->id);
            $order->add_order_note($message);
            stripe_manager()->success($message);
            $this->order->payment_complete($response->id);
        } catch (Base $e) {
            $message = sprintf(
                    __(
                            'There was an error processing subscription %s. Message: %s. Attributes: %s', 
                            'stripe_gateway'), $order->id, $e->getMessage(), 
                    print_r($attrs, true));
            stripe_manager()->error($message);
            $order->add_order_note($message);
            $order->update_status('failed');
        }
    }

    /**
     * Update the payment method.
     * If it's a new payment method, it will need to be saved.
     *
     * @param WC_Subscription $subscription            
     * @param string $old_payment_method            
     */
    public function payment_method_updated ($subscription, $old_payment_method)
    {
        $user_id = wp_get_current_user()->ID;
        $source = null;
        if ($this->use_saved_payment_method()) {
            $token = stripe_manager()->get_request_parameter(
                    'stripe_saved_method_token');
            $user_sources = stripe_manager()->get_user_sources_from_meta(
                    $user_id);
            $index = sg_get_index_of_payment_method(
                    $user_sources['payment_methods'], $token);
            $source = stripe_manager()->get_stripe_source_from_array(
                    $user_sources['payment_methods'][$index]);
            stripe_manager()->save_payment_meta($source, $subscription->id);
            wc_add_notice(
                    __(
                            'Your payment method has been changed for the subscription.', 
                            'stripe_gateway'));
        } else {
            $source = $this->save_payment_method(
                    stripe_manager()->get_request_parameter(
                            'stripe_payment_token'));
            if (! $source) {
                return;
            }
            stripe_manager()->save_payment_meta($source, $subscription->id);
            wc_add_notice(
                    __(
                            'Your payment method has been changed for the subscription.', 
                            'stripe_gateway'));
        }
        stripe_manager()->success(
                sprintf(
                        __('Payment method changed for subscription %s.', 
                                'stripe_gateway'), $subscription->id));
    }

    /**
     *
     * @param string $payment_method_to_display            
     * @param WC_Order $subscription            
     */
    public function display_payment_method ($payment_method_to_display, 
            $subscription)
    {
        $payment_method_to_display = $subscription->payment_method_title;
        return $payment_method_to_display;
    }

    /**
     *
     * @param ExternalAccount $source            
     * @param int $order_id            
     */
    public function save_subscription_meta ($source, $order_id)
    {
        $order = wc_get_order($order_id);
        $subscriptions = wcs_get_subscriptions_for_order($order);
        foreach ($subscriptions as $subscription) {
            update_post_meta($subscription->id, '_payment_method_title', 
                    $order->payment_method_title);
            update_post_meta($subscription->id, '_payment_method_token', 
                    $source->id);
            update_post_meta($subscription->id, '_transaction_id', 
                    $order->get_transaction_id());
            $subscription->update_manual(false);
            update_post_meta($this->id, '_payment_method', 
                    $order->payment_method);
        }
    }

    public function add_source (&$attrs)
    {
        $attrs['source'] = $this->token;
    }

    public function add_customer (&$attrs)
    {
        $attrs['customer'] = stripe_manager()->get_stripe_customer_id(
                wp_get_current_user()->ID);
    }

    public function add_recurring_source (&$attrs)
    {
        $attrs['source'] = $this->order->payment_method_token;
    }

    public function add_recurring_currency (&$attrs)
    {
        $attrs['currency'] = $this->order->order_currency;
    }

    public function add_recurring_customer (&$attrs)
    {
        $attrs['customer'] = stripe_manager()->get_stripe_customer_id(
                $this->order->customer_user);
    }
    
    public function add_metadata (&$attrs)
    {
        $attrs['metadata'] = array();
        $options = stripe_manager()->get_option('subscription_order_meta');
        if (! empty($options)) {
            foreach ($options as $k => $v) {
                $method_name = 'get_' . $k;
                if (method_exists($this->order, $method_name)) {
                    $attrs['metadata'][$k] = $this->order->{$method_name}();
                } else {
                    $attrs['metadata'][$k] = $this->order->{$k};
                }
            }
        }
        $attrs['metadata']['subscription_id'] = stripe_manager()->get_option('subscription_prefix') .
                 $this->order->id . stripe_manager()->get_option('subscription_suffix');
    }

    public function add_recurring_metadata (&$attrs)
    {
        $this->add_metadata($attrs);
    }

    /**
     * Return an array of recurring charge attributes.
     *
     * @return string[]
     */
    public function subscription_recurring_charge_attributes ()
    {
        return array(
                'recurring_source',
                'recurring_currency',
                'charge_or_capture',
                'recurring_customer',
                'recurring_metadata',
                'receipt_email',
                'shipping_address',
                'statement_descriptor',
                'description'
        );
    }
}
new Stripe_WC_Subscriptions();