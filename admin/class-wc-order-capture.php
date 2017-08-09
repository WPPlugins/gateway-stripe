<?php
use Stripe\Error\Base;
use Stripe\Charge;

/**
 * Class used to ouput the charge capture html.
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *           
 */
class Stripe_WC_Capture_Charge
{

    private $id = 'stripe_payment_gateway';

    public function __construct ()
    {
        add_action('add_meta_boxes', 
                array(
                        $this,
                        'add_meta_box'
                ));
        add_filter('woocommerce_order_actions', 
                array(
                        $this,
                        'add_order_actions'
                ));
        add_action('admin_enqueue_scripts', 
                array(
                        $this,
                        'enqueue_scripts'
                ));
    }

    public function enqueue_scripts ()
    {
        $screen = get_current_screen();
        $screen_id = $screen->id ? $screen->id : '';
        if (in_array($screen_id, wc_get_order_types('order-meta-boxes'))) {
            wp_enqueue_script('stripe-order-actions-js', 
                    STRIPE_GATEWAY_ASSETS . 'js/admin/order-edit.js', 
                    array(
                            'jquery'
                    ), stripe_manager()->version, true);
            wp_enqueue_style('stripe-order-edit-css', 
                    STRIPE_GATEWAY_ASSETS . 'css/admin/order-edit.css', null, 
                    stripe_manager()->version, null);
        }
    }

    public function add_meta_box ()
    {
        add_meta_box('stripe-capture-order-charge', __('Capture Charge'), 
                array(
                        $this,
                        'output'
                ), 'shop_order', 'side');
    }

    /**
     * Output the order capture html.
     */
    public function output ($post)
    {
        $order = wc_get_order($post->ID);
        if ($order->payment_method !== $this->id) {
            return;
        }
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'meta-box/wc-capture-charge.php';
        echo ob_get_clean();
    }

    public function add_order_actions ($actions = array())
    {
        if (! isset($_REQUEST['post'])) {
            return $actions;
        }
        $order = wc_get_order($_REQUEST['post']);
        if ($order->payment_method !== $this->id) {
            return $actions;
        }
        try {
            $charge = Charge::retrieve($order->get_transaction_id());
            if ($charge->captured || $charge->refunded) {
                return $actions;
            }else{
                $actions['capture_stripe_charge'] = __('Capture Charge', 
                        'stripe_gateway');
            }
            return $actions;
        } catch (Base $e) {
            stripe_manager()->error(
                    sprintf(
                            __(
                                    'There was an error retrieiving order %s from Stripe. Message: %s', 
                                    'stripe_gateway'), $order->id, 
                            $e->getMessage()));
        }
        return $actions;
    }
}
new Stripe_WC_Capture_Charge();