<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_Name_Generator_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, VARABIT_NAME_GENERATOR_PLUGIN_URL . 'assets/css/varabit-name-generator-public.css', array(), $this->version, 'all');
        
        // Apply custom styles from settings
        $options = get_option($this->plugin_name . '_options');
        $button_color = isset($options['button_color']) ? $options['button_color'] : '#4CAF50';
        $font_family = isset($options['font_family']) ? $options['font_family'] : 'sans-serif';
        
        $custom_css = "
            .varabit-name-generator-form button {
                background-color: {$button_color};
            }
            .varabit-name-generator-container {
                font-family: {$font_family};
            }
        ";
        
        wp_add_inline_style($this->plugin_name, $custom_css);
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, VARABIT_NAME_GENERATOR_PLUGIN_URL . 'assets/js/varabit-name-generator-public.js', array('jquery'), $this->version, false);
        
        // Localize the script with data for AJAX
        wp_localize_script($this->plugin_name, 'varabit_name_generator', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('varabit_name_generator_nonce'),
            'domain_check' => $this->is_domain_check_enabled(),
            'is_admin' => current_user_can('manage_options'),
            'admin_url' => admin_url('options-general.php?page=' . $this->plugin_name),
        ));
    }

    /**
     * Check if domain availability check is enabled.
     *
     * @since    1.0.0
     * @return   boolean    True if enabled, false otherwise.
     */
    private function is_domain_check_enabled() {
        $options = get_option($this->plugin_name . '_options');
        return isset($options['domain_check']) && $options['domain_check'] == 1;
    }

    /**
     * Render the generator form.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The HTML output for the form.
     */
    public function render_generator_form($atts) {
        // Start output buffering
        ob_start();
        
        // Include the form template
        include VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'templates/generator-form.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Generate business name suggestions via AJAX.
     *
     * @since    1.0.0
     */
    public function generate_names() {
        // Check nonce for security
        check_ajax_referer('varabit_name_generator_nonce', 'nonce');
        
        // Get and sanitize input data
        $keywords = isset($_POST['keywords']) ? sanitize_text_field($_POST['keywords']) : '';
        $tone = isset($_POST['tone']) ? sanitize_text_field($_POST['tone']) : '';
        $industry = isset($_POST['industry']) ? sanitize_text_field($_POST['industry']) : '';
        
        // Validate input
        if (empty($keywords)) {
            wp_send_json_error(array('message' => __('Please enter keywords for your business name.', 'varabit-business-name-generator')));
            return;
        }
        
        try {
            // Check if API key is configured
            $options = get_option($this->plugin_name . '_options');
            $api_key = isset($options['gemini_api_key']) ? trim($options['gemini_api_key']) : '';
            
            if (empty($api_key)) {
                wp_send_json_error(array('message' => __('Google Gemini API key is not configured. Please set it in the plugin settings.', 'varabit-business-name-generator')));
                return;
            }
            
            // Initialize API class
            $api = new Varabit_Name_Generator_API();
            
            // Generate names
            $result = $api->generate_business_names($keywords, $tone, $industry);
            
            if (is_wp_error($result)) {
                $error_code = $result->get_error_code();
                $error_message = $result->get_error_message();
                
                // Log error for debugging
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Varabit Name Generator - Error: ' . $error_code . ' - ' . $error_message);
                }
                
                wp_send_json_error(array('message' => $error_message));
                return;
            }
            
            // Check if we got valid results
            if (empty($result) || !is_array($result)) {
                wp_send_json_error(array('message' => __('No name suggestions were generated. Please try different keywords.', 'varabit-business-name-generator')));
                return;
            }
            
            // Check domain availability if enabled
            if ($this->is_domain_check_enabled()) {
                $result = $this->check_domain_availability($result);
            }
            
            wp_send_json_success(array('names' => $result));
            
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Varabit Name Generator - Exception: ' . $e->getMessage());
            }
            wp_send_json_error(array('message' => __('An unexpected error occurred. Please try again later.', 'varabit-business-name-generator')));
        }
    }

    /**
     * Check domain availability for generated names.
     *
     * @since    1.0.0
     * @param    array    $names    The generated business names.
     * @return   array              The names with domain availability information.
     */
    private function check_domain_availability($names) {
        // Initialize the domain API class
        require_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'includes/api/class-varabit-name-generator-domain-api.php';
        $domain_api = new Varabit_Name_Generator_Domain_API();
        
        // Check availability for all names with retry logic
        $result = $domain_api->check_multiple_domains($names);
        
        // Filter to only return available domains
        $available_domains = array_filter($result, function($item) {
            return $item['is_available'];
        });
        
        // If no available domains found, generate new variations and retry
        if (empty($available_domains)) {
            $new_names = array_map(function($name) {
                return $name . rand(1, 999); // Generate new variations
            }, $names);
            
            $result = $domain_api->check_multiple_domains($new_names);
            $available_domains = array_filter($result, function($item) {
                return $item['is_available'];
            });
        }
        
        return $available_domains;
    }
}