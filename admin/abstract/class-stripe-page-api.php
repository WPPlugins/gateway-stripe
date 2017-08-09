<?php

abstract class Stripe_Gateway_Page_API
{

    public $page = '';

    public function __construct ()
    {
        add_action('admin_enqueue_scripts', 
                array(
                        $this,
                        'enqueue_scripts'
                ));
    }

    public function initialize_globals ()
    {
        global $current_tab, $current_page;
        $current_page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
        $current_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
    }

    public function enqueue_scripts ()
    {
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
        if ($page === $this->page) {
            wp_enqueue_style('stripe-materialize-style', 
                    STRIPE_GATEWAY_ASSETS . 'css/admin/materialize.css', null, 
                    stripe_manager()->version, null);
            wp_enqueue_script('stripe-materialize-js', 
                    STRIPE_GATEWAY_ASSETS . 'js/admin/materialize.min.js', 
                    array(
                            'jquery'
                    ), stripe_manager()->version, false);
            wp_enqueue_script('stripe-admin-settings', 
                    STRIPE_GATEWAY_ASSETS . 'js/admin/admin-settings.js', 
                    array(
                            'jquery',
                            'stripe-materialize-js'
                    ), stripe_manager()->version, false);
            wp_enqueue_style('stripe-materialize-graphics', 
                    'https://fonts.googleapis.com/icon?family=Material+Icons', 
                    null, stripe_manager()->version, null);
            $this->localize_variables();
        }
    }

    public function localize_variables ()
    {
        wp_localize_script('stripe-admin-settings', 'stripe_settings_vars', 
                apply_filters('stripe_settings_localized_variables', array('keys')));
    }
}