<?php
/**
 * Plugin Name: Stripe Payment Gateway
 * Plugin URI: https://wordpress.paymentplugins.com
 * Description: SAQ A Stripe Payment Gateway that integrates with WooCommerce, WooCommerce Subscriptions and has a stand alone donation feature.
 * Version: 1.0.3
 * Author: Payment Plugins
 * Author URI: https://wordpress.paymentplugins.com
 * Requires at least: 3.4
 * Tested up to: 4.6.1
 * Text Domain: stripe-gateway
 * Domain Path: /i18n/languages/
 *
 * @package PaymentPlugins
 * @category Core
 * @author Payment Plugins
 */
if (! defined('ABSPATH')) {
    exit(); // Exit if accessed directly.
}

if (version_compare(PHP_VERSION, '5.4', '<')) {
    add_action('admin_notices', 
            function  ()
            {
                $message = sprintf(
                        __(
                                'Your PHP version is %s but Stripe Payment Gateway requires version 5.4+.', 
                                'stripe_gateway'), PHP_VERSION);
                echo '<div class="notice notice-error"><p style="font-size: 16px">' .
                         $message . '</p></div>';
            });
    return;
}

define('STRIPE_GATEWAY', plugin_dir_path(__FILE__));
define('STRIPE_GATEWAY_ADMIN', plugin_dir_path(__FILE__) . 'admin/');
define('STRIPE_GATEWAY_PAYMENTS', plugin_dir_path(__FILE__) . 'payments/');
define('STRIPE_GATEWAY_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
define('STRIPE_GATEWAY_JS', 'https://js.stripe.com/v2/');
define('STRIPE_CHECKOUT_JS', 'https://checkout.stripe.com/checkout.js');
require STRIPE_GATEWAY . 'stripe-php-lib/init.php'; // include stripe library.
require STRIPE_GATEWAY_ADMIN . 'class-stripe-gateway-manager.php';