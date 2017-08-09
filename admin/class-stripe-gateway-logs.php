<?php

/**
 * Log class that renders all logs for the admin.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Stripe_Admin_Gateway_Logs extends Stripe_Gateway_Page_API
{

    public function __construct ()
    {
        $this->page = 'stripe-gateway-logs';
        add_action('admin_menu', 
                array(
                        $this,
                        'admin_menu'
                ));
        add_action('admin_enqueue_scripts', 
                array(
                        $this,
                        'enqueue_log_scripts'
                ));
        parent::__construct();
    }

    public function enqueue_log_scripts ()
    {
        if (isset($_REQUEST['page']) && $_REQUEST['page'] === $this->page) {
            wp_enqueue_script('stripe-dataTables-js', 
                    STRIPE_GATEWAY_ASSETS . 'js/jquery/dataTables.min.js', 
                    array(
                            'jquery'
                    ), stripe_manager()->version, true);
            wp_enqueue_script('stripe-logs-js', 
                    STRIPE_GATEWAY_ASSETS . 'js/admin/logs.js', 
                    array(
                            'stripe-dataTables-js'
                    ), stripe_manager()->version, true);
            wp_enqueue_style('stripe-dataTables-css', 
                    STRIPE_GATEWAY_ASSETS . 'css/jquery/dataTables.css', null, 
                    stripe_manager()->version, null);
        }
    }

    public function admin_menu ()
    {
        add_submenu_page('stripe-gateway-page', 
                __('Log Entries', 'stripe_gateway'), 
                __('Log Entries', 'stripe_gateway'), 'manage_options', 
                'stripe-gateway-logs', 
                array(
                        $this,
                        'output'
                ));
    }

    public function output ()
    {
        // stripe_manager()->add_admin_notice('success', 'This is a test.');
        $this->initialize_globals();
        global $current_tab, $current_page;
        if (isset($_POST['stripe_gateway_delete_logs'])) {
            $this->maybe_delete_logs();
        }
        include STRIPE_GATEWAY_ADMIN . 'views/log-entries.php';
    }

    /**
     * Maybe delete all log entries.
     */
    private function maybe_delete_logs ()
    {
        stripe_manager()->log->delete_log_entries();
    }
}
new Stripe_Admin_Gateway_Logs();