<?php

/**
 * Logger class that captures all activity for the Stripe gateway.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Stripe_Gateway_Logger
{

    const post_type = 'stripe_gateway_log';

    const MAX_SIZE = 200;

    private $debug = false;

    private $posts = array();

    /**
     * Current post id that can accept log entries.
     *
     * @var unknown
     */
    private $current_post;

    public $logs = array();

    public function __construct ()
    {
        // $this->initialize_log_entries();
        $this->add_actions();
    }

    public function set_debug ($bool = false)
    {
        $this->debug = $bool;
    }

    /**
     * Add necesssary actions that the log will need.
     */
    public function add_actions ()
    {
        add_action('init', 
                array(
                        $this,
                        'register_log_type'
                ));
    }

    /**
     * Register the log post type.
     */
    public function register_log_type ()
    {
        register_post_type(self::post_type, 
                array(
                        'labels' => array(
                                'name' => __('Stripe Log Entry', 
                                        'stripe_gateway'),
                                'singular_name' => __('Stripe Log Entry', 
                                        'stripe_gateway')
                        ),
                        'description' => __(
                                'Stripe Gateway log entries. Used to log activity in the plugin.'),
                        'public' => false,
                        'show_in_menu' => false,
                        'show_in_nav_menus' => false,
                        'show_in_admin_bar' => false
                ));
    }

    /**
     * Return the current post id for the log that can accept entries.
     *
     * @return mixed|boolean
     */
    public function get_current_post_id ()
    {
        $post_id = get_option('stripe_gateway_log_current_post', false);
        // No log entries yet, insert a new one.
        if (! $post_id) {
            $this->insert_log_entry();
            return $this->get_current_post_id();
        }
        return $post_id;
    }

    /**
     * Delete all log entries.
     */
    public function delete_log_entries ()
    {
        $posts = get_posts(
                array(
                        'post_type' => self::post_type,
                        'posts_per_page' => -1,
                        'post_status' => 'any',
                        'orderby' => 'date',
                        'order' => 'DESC'
                ));
        foreach ($posts as $post) {
            wp_delete_post($post->ID);
        }
       $this->insert_log_entry();
    }

    public function insert_log_entry ()
    {
        $post_id = wp_insert_post(
                array(
                        'post_type' => self::post_type,
                        'post_title' => __('Stripe Gateway Log', 
                                'stripe_gateway')
                ));
        update_option('stripe_gateway_log_current_post', $post_id);
    }

    /**
     * Return all of the log entries stored in the database.
     */
    public function initialize_log_entries ()
    {
        $this->posts = get_posts(
                array(
                        'post_type' => self::post_type,
                        'posts_per_page' => - 1,
                        'post_status'=>'any',
                        'orderby' => 'date',
                        'order' => 'DESC'
                ));
        foreach ($this->posts as $post) {
            $meta = get_post_meta($post->ID, 'stripe_log_entry', true);
            if(empty($meta)){
                $meta = array();
            }
            $this->logs[] = $meta;
        }
    }

    /**
     * Add a log entry to the database in the for of post_meta.
     *
     * @param string $message            
     * @param string $type            
     */
    public function add_message ($message = '', $type = 'error')
    {
        if ($this->debug) {
            $log_entry = array(
                    'type' => $type,
                    'message' => $message,
                    'time' => date('m-d-Y H:i:s')
            );
            $logs = get_post_meta($this->get_current_post_id(), 
                    'stripe_log_entry', true);
            if (! $logs || empty($logs)) {
                $logs = array();
            }
            $logs[] = $log_entry;
            update_post_meta($this->get_current_post_id(), 'stripe_log_entry', 
                    $logs);
        }
        if (count($logs) == self::MAX_SIZE) {
            $this->insert_log_entry();
        }
    }

    public function error ($message)
    {
        $this->add_message($message, 'error');
    }

    public function success ($message)
    {
        $this->add_message($message, 'success');
    }

    public function info ($message)
    {
        $this->add_message($message, 'info');
    }
}