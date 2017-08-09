<?php

/**
 * Update class that performs any number of operations for new verisons.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Stripe_Gateway_Updates
{

    public function __construct ()
    {
        add_action('admin_init', 
                array(
                        $this,
                        'update'
                ));
        add_action(
                'in_plugin_update_message-gateway-stripe/stripe-gateway.php', 
                array(
                        $this,
                        'admin_update_notice'
                ));
    }

    /**
     * Update the Stripe plugin version number.
     */
    public function update ()
    {
        $version = get_option('stripe_payment_gateway_version', true);
        if (! $version ||
                 version_compare($version, stripe_manager()->version, '<')) {
            update_option('stripe_payment_gateway_version', stripe_manager()->version);
            stripe_manager()->add_admin_notice('success', 
                    sprintf(
                            __(
                                    'Thank you for updating Stripe Payment Gateway to version %s', 
                                    'stripe_gateway'), stripe_manager()->version));
            stripe_manager()->info(
                    sprintf(
                            'Your Stripe Gateway plugin has been updated to version %s', 
                    stripe_manager()->version));
        }
    }

    public function admin_update_notice ()
    {
        $response = wp_safe_remote_get(
                'https://plugins.svn.wordpress.org/gateway-stripe/trunk/readme.txt');
        if ($response instanceof WP_Error) {
            stripe_manager()->error(
                    sprintf(
                            'There was an error retrieving the update notices. %s', 
                            print_r($response, true)));
        } else {
            $content = ! empty($response['body']) ? $response['body'] : '';
            $this->parse_notice_content($content);
        }
    }

    public function parse_notice_content ($content)
    {
        $pattern = '/==\s*Upgrade Notice\s*==\s*=\s*([0-9.]*)\s*=\s*(.*)/';
        if (preg_match($pattern, $content, $matches)) {
            $version = $matches[1];
            $notice = $matches[2];
            if (version_compare($version, stripe_manager()->version, '>')) {
                echo '<div class="wc_plugin_upgrade_notice">' . $notice .
                         '</div>';
            }
        }
    }
}
new Stripe_Gateway_Updates();