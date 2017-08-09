<?php

/**
 * Abstract class for rendering plugin settings and saving settings.
 * @author Payment Plugins
 * @copyright Payment Plugins 2016
 *
 */
abstract class Stripe_Gateway_Settings_API extends Stripe_Gateway_Page_API
{

    /**
     * Id of the settings class extending the settings api.
     *
     * @var string
     */
    public $id = '';

    public $tab = '';

    /**
     * Array of configured settings.
     *
     * @var array
     */
    public $settings = array();

    /**
     * The settings associated with this settings page.
     *
     * @var array
     */
    private $default_settings;

    public $title = array();

    public function __construct ()
    {
        add_filter('stripe_gateway_saved_settings', 
                array(
                        $this,
                        'get_settings'
                ));
        add_filter('stripe_gateway_default_settings', 
                array(
                        $this,
                        'get_default_settings'
                ));
        if($this->title){
            add_filter('stripe_gateway_' . $this->tab . '_title',
                    array(
                            $this,
                            'generate_title_html'
                    ));
        }
        add_filter('stripe_settings_localized_variables',
                array(
                        $this,
                        'set_localized_vars'
                ));
        parent::__construct();
    }

    /**
     * Initialize the settings for the plugin.
     */
    public function init_settings ()
    {
        $this->settings = get_option(
                'stripe_payment_gateway_' . $this->id . '_options', array());
    }

    /**
     * Return the saved settings used by the plugin.
     */
    public function get_settings ($settings = array())
    {
        if ($this->settings == null) {
            $this->init_settings();
        }
        return $this->settings + $settings;
    }

    public function get_default_settings ($default_settings = array())
    {
        if ($this->default_settings == null) {
            $this->default_settings = $this->settings();
        }
        return $this->default_settings + $default_settings;
    }

    public function set_localized_vars($vars)
    {
        return $vars;
    }
    
    /**
     * Given an option name, return the option value associated with the option
     * name.
     *
     * @param unknown $key            
     * @return mixed
     */
    public function get_option ($key)
    {
        $field_key = $this->get_field_key_name($key);
        if (empty($this->settings)) {
            $this->init_settings();
        }
        $value = isset($this->settings[$key]) ? $this->settings[$key] : $this->settings()[$key]['default'];
        $this->settings[$field_key] = $value;
        return $value;
    }

    public function get_field_key_name ($key)
    {
        return 'stripe_' . $this->id . '_' . $key;
    }

    public function generate_text_html ($key, $data)
    {
        $defaults = $this->get_default_text_html_args();
        $data = wp_parse_args($data, $defaults);
        $field_key = $this->get_field_key_name($key);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/text-html.php';
        return ob_get_clean();
    }

    public function generate_checkbox_html ($key, $data)
    {
        $field_key = $this->get_field_key_name($key);
        $defaults = $this->get_default_checkbox_html_args();
        $data = wp_parse_args($data, $defaults);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/checkbox-html.php';
        return ob_get_clean();
    }

    public function generate_radio_html ($key, $data)
    {
        $defaults = $this->get_default_radio_html_args();
        $data = wp_parse_args($data, $defaults);
        $field_key = $this->get_field_key_name($key);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/radio-html.php';
        return ob_get_clean();
    }

    public function generate_textarea_html ($key, $data)
    {
        $defaults = $this->get_default_radio_html_args();
        $data = wp_parse_args($data, $defaults);
        $field_key = $this->get_field_key_name($key);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/textarea-html.php';
        return ob_get_clean();
    }

    /**
     * display html for the select html object.
     *
     * @param unknown $option            
     */
    public function generate_select_html ($key, $data)
    {
        $field_key = $this->get_field_key_name($key);
        $defaults = $this->get_default_select_html_args();
        $data = wp_parse_args($data, $defaults);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/select-html.php';
        return ob_get_clean();
    }

    public function generate_title_html ($option = null)
    {
        if ($option == null) {
            $option = $this->title;
        }
        $args = $this->get_default_select_html_args();
        $data = wp_parse_args($option, $args);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/title-html.php';
        return ob_get_clean();
    }

    /**
     * Generate custom html for the settings.
     *
     * @param unknown $option            
     */
    public function generate_custom_html ($key, $data = null)
    {
        $defaults = $this->get_default_custom_html_args();
        $data = wp_parse_args($data, $defaults);
        $field_key = $this->get_field_key_name($key);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/custom-html.php';
        return ob_get_clean();
    }

    public function generate_button_html ($key, $data = null)
    {
        $defaults = $this->get_default_button_html_args();
        $data = wp_parse_args($data, $defaults);
        $field_key = $this->get_field_key_name($key);
        ob_start();
        include STRIPE_GATEWAY_ADMIN . 'settings/views/button-html.php';
        return ob_get_clean();
    }

    /**
     * Generate html for an option tool tip;
     *
     * @param unknown $data            
     * @return string
     */
    public function get_tooltip_html ($data)
    {
        $html = '';
        if ($data['tool_tip']) {
            $html .= '<i class="material-icons tooltipped right" data-position="right" data-delay="50" data-tooltip="' .
                     $data['description'] . '">help</i> ';
        }
        return $html;
    }

    /**
     * Generates html for a modal helper.
     *
     * @param unknown $key            
     * @param unknown $data            
     */
    public function generate_helper_modal ($key, $data)
    {
        if ($data['helper']['enabled']) {
            $field_key = $this->get_field_key_name($key);
            include STRIPE_GATEWAY_ADMIN . 'settings/views/modal-helper.php';
        }
    }

    public function cleanse_text_field_data ($value)
    {
        return sanitize_text_field($value);
    }

    public function cleanse_checkbox_field_data ($value)
    {
        return sanitize_text_field($value);
    }

    public function cleanse_select_field_data ($value)
    {
        return sanitize_text_field($value);
    }

    public function cleanse_textarea_field_data ($value)
    {
        return trim(stripslashes($value));
    }

    public function cleanse_radio_field_data ($value)
    {
        return sanitize_text_field($value);
    }

    public function get_default_button_html_args ()
    {
        return apply_filters('stripe_gateway_default_button_args', 
                array(
                        'title' => '',
                        'disabled' => false,
                        'class' => '',
                        'css' => '',
                        'label' => '',
                        'value' => '',
                        'placeholder' => '',
                        'type' => 'text',
                        'tool_tip' => false,
                        'description' => '',
                        'default' => '',
                        'attributes' => array(),
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    public function get_default_select_html_args ()
    {
        return apply_filters('stripe_gateway_default_select_args', 
                array(
                        'title' => '',
                        'disabled' => false,
                        'class' => '',
                        'css' => '',
                        'placeholder' => '',
                        'type' => 'text',
                        'tool_tip' => false,
                        'description' => '',
                        'attributes' => array(),
                        'options' => array(),
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    public function get_default_textarea_html_args ()
    {
        return apply_filters('stripe_gateway_default_textarea_args', 
                array(
                        'title' => '',
                        'disabled' => false,
                        'class' => '',
                        'css' => '',
                        'placeholder' => '',
                        'type' => 'text',
                        'tool_tip' => false,
                        'description' => '',
                        'attributes' => array(),
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    public function get_default_checkbox_html_args ()
    {
        return apply_filters('stripe_gateway_default_input_args', 
                array(
                        'title' => '',
                        'disabled' => false,
                        'class' => 'filled-in',
                        'css' => '',
                        'placeholder' => '',
                        'type' => 'text',
                        'tool_tip' => false,
                        'description' => '',
                        'attributes' => array(),
                        'default' => 'yes',
                        'value' => 'yes',
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    public function get_default_radio_html_args ()
    {
        return apply_filters('stripe_gateway_default_radio_args', 
                array(
                        'title' => '',
                        'disabled' => false,
                        'class' => '',
                        'css' => '',
                        'placeholder' => '',
                        'type' => 'text',
                        'tool_tip' => false,
                        'description' => '',
                        'default' => '',
                        'attributes' => array(),
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    public function get_default_text_html_args ()
    {
        return apply_filters('stripe_gateway_default_text_args', 
                array(
                        'title' => '',
                        'disabled' => false,
                        'class' => '',
                        'css' => '',
                        'placeholder' => '',
                        'type' => 'text',
                        'tool_tip' => false,
                        'description' => '',
                        'default' => '',
                        'maxlength'=>'',
                        'attributes' => array(),
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    public function get_default_title_html_args ()
    {
        return apply_filters('stripe_gateway_default_text_args', 
                array(
                        'title' => '',
                        'disabled' => false,
                        'class' => '',
                        'css' => '',
                        'tool_tip' => false,
                        'description' => '',
                        'attributes' => array(),
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    public function get_default_custom_html_args ()
    {
        return apply_filters('stripe_gateway_default_custom_args', 
                array(
                        'title' => '',
                        'function'=>'',
                        'class' => '',
                        'default'=>'',
                        'helper' => array(
                                'enabled' => false,
                                'url' => '',
                                'description' => ''
                        )
                ));
    }

    /**
     * Abstract method that is to be implemented by settings classes.
     * Each class that implements
     * the Stripe_Settings_API must include the settings options in this method.
     * These settings are reqiured
     * for the plugin to function.
     */
    public abstract function settings ();

    /**
     * Output html for the settings page.
     */
    public function output ()
    {
        $this->initialize_globals();
        global $current_tab, $current_page;
        if (! empty($_POST)) { // Post has data so save first.
            if (! empty($current_tab)) {
                do_action('stripe_save_settings_' . $current_tab);
            } else {
                $this->save();
            }
        }
        include_once STRIPE_GATEWAY_ADMIN . 'settings/views/admin-settings.php';
        // exit();
    }

    /**
     * Save the settings for the page.
     */
    public function save ()
    {
        foreach ($this->settings() as $key => $setting) {
            if ($setting['type'] === 'button') {
                continue;
            }
            $value = $this->get_field_value($key);
            
            if ($setting['type'] !== 'custom') {
                $value = $this->{'cleanse_' . $setting['type'] . '_field_data'}(
                        $value);
            }
            
            $value = apply_filters('stripe_gateway_validate_' . $key, $value, 
                    $key);
            if ($value !== false) {
                $this->settings[$key] = $value;
            }
        }
        update_option('stripe_payment_gateway_' . $this->id . '_options', 
                $this->settings);
        
        // Make sure any functionality using saved values gets refreshed.
        stripe_manager()->initialize_settings();
        
        $this->maybe_test_connection();
    }

    public function maybe_test_connection ()
    {
        if (isset($_POST['stripe_settings_test_mode_connection_test'])) {
            stripe_manager()->connection_test('test');
        }
        if (isset($_POST['stripe_settings_live_mode_connection_test'])) {
            stripe_manager()->connection_test('live');
        }
    }

    /**
     * Get the field value from the $_POST data.
     *
     * @param string $key            
     * @return string|unknown
     */
    public function get_field_value ($key)
    {
        $key = $this->get_field_key_name($key);
        return isset($_POST[$key]) ? $_POST[$key] : '';
    }

    /**
     * Generate the html for the gateway settings.
     */
    public function generate_settings_html ($echo = false)
    {
        $html = '';
        foreach ($this->settings() as $k => $v) {
            $html .= $this->{'generate_' . $v['type'] . '_html'}($k, $v);
        }
        if ($echo) {
            echo $html;
        } else {
            return $html;
        }
    }

    public function display_settings_title ()
    {
        global $current_tab;
        return empty($current_tab) ? $this->generate_title_html($this->title) : apply_filters(
                'stripe_gateway_' . $current_tab . '_title', null);
    }

    /**
     * Return true if the value is valid json.
     *
     * @param string $key            
     * @param string $value            
     */
    public function validate_json ($value, $key)
    {
        $array = json_decode($value, true);
        if (! $array) {
            stripe_manager()->add_admin_notice('error', 
                    sprintf(
                            __(
                                    'Field %s contains invalid json. Please verify your entries.', 
                                    'stripe_gateway'), $key));
            $value = false;
        }
        return $value;
    }
}
