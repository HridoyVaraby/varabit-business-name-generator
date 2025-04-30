<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_Name_Generator_Admin {

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
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, VARABIT_NAME_GENERATOR_PLUGIN_URL . 'assets/css/varabit-name-generator-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, VARABIT_NAME_GENERATOR_PLUGIN_URL . 'assets/js/varabit-name-generator-admin.js', array('jquery'), $this->version, false);
    }

    /**
     * Add options page to the admin menu.
     *
     * @since    1.0.0
     */
    public function add_options_page() {
        add_options_page(
            __('Varabit Name Generator Settings', 'varabit-business-name-generator'),
            __('Varabit Name Generator', 'varabit-business-name-generator'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_options_page')
        );
    }

    /**
     * Display the options page content.
     *
     * @since    1.0.0
     */
    public function display_options_page() {
        include_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Register settings, sections, and fields.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register setting
        register_setting(
            $this->plugin_name,
            $this->plugin_name . '_options',
            array($this, 'validate_options')
        );

        // Add settings section
        add_settings_section(
            $this->plugin_name . '_general',
            __('General Settings', 'varabit-business-name-generator'),
            array($this, 'general_section_callback'),
            $this->plugin_name
        );

        // Add settings fields
        add_settings_field(
            'gemini_api_key',
            __('Google Gemini API Key', 'varabit-business-name-generator'),
            array($this, 'api_key_field_callback'),
            $this->plugin_name,
            $this->plugin_name . '_general'
        );

        add_settings_field(
            'gemini_model',
            __('Gemini Model', 'varabit-business-name-generator'),
            array($this, 'model_field_callback'),
            $this->plugin_name,
            $this->plugin_name . '_general'
        );

        add_settings_field(
            'domain_check',
            __('Domain Availability Check', 'varabit-business-name-generator'),
            array($this, 'domain_check_field_callback'),
            $this->plugin_name,
            $this->plugin_name . '_general'
        );

        // Add style settings section
        add_settings_section(
            $this->plugin_name . '_style',
            __('Style Settings', 'varabit-business-name-generator'),
            array($this, 'style_section_callback'),
            $this->plugin_name
        );

        // Add style settings fields
        add_settings_field(
            'button_color',
            __('Button Color', 'varabit-business-name-generator'),
            array($this, 'button_color_field_callback'),
            $this->plugin_name,
            $this->plugin_name . '_style'
        );

        add_settings_field(
            'font_family',
            __('Font Family', 'varabit-business-name-generator'),
            array($this, 'font_family_field_callback'),
            $this->plugin_name,
            $this->plugin_name . '_style'
        );
    }

    /**
     * Callback for the general section.
     *
     * @since    1.0.0
     */
    public function general_section_callback() {
        echo '<p>' . __('Configure the API settings for the business name generator.', 'varabit-business-name-generator') . '</p>';
    }

    /**
     * Callback for the style section.
     *
     * @since    1.0.0
     */
    public function style_section_callback() {
        echo '<p>' . __('Customize the appearance of the business name generator.', 'varabit-business-name-generator') . '</p>';
    }

    /**
     * Callback for the API key field.
     *
     * @since    1.0.0
     */
    public function api_key_field_callback() {
        $options = get_option($this->plugin_name . '_options');
        $api_key = isset($options['gemini_api_key']) ? $options['gemini_api_key'] : '';
        
        echo '<input type="password" id="gemini_api_key" name="' . $this->plugin_name . '_options[gemini_api_key]" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your Google Gemini API key. <a href="https://ai.google.dev/" target="_blank">Get an API key</a>', 'varabit-business-name-generator') . '</p>';
    }

    /**
     * Callback for the model field.
     *
     * @since    1.0.0
     */
    public function model_field_callback() {
        $options = get_option($this->plugin_name . '_options');
        $model = isset($options['gemini_model']) ? $options['gemini_model'] : 'gemini-1.0-pro';
        
        $models = array(
            'gemini-1.0-pro' => __('Gemini 1.0 Pro', 'varabit-business-name-generator'),
            'gemini-1.0-pro-vision' => __('Gemini 1.0 Pro Vision', 'varabit-business-name-generator'),
            'gemini-1.5-flash' => __('Gemini 1.5 Flash', 'varabit-business-name-generator'),
        );
        
        echo '<select id="gemini_model" name="' . $this->plugin_name . '_options[gemini_model]">';
        foreach ($models as $model_id => $model_name) {
            echo '<option value="' . esc_attr($model_id) . '" ' . selected($model, $model_id, false) . '>' . esc_html($model_name) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Callback for the domain check field.
     *
     * @since    1.0.0
     */
    public function domain_check_field_callback() {
        $options = get_option($this->plugin_name . '_options');
        $domain_check = isset($options['domain_check']) ? $options['domain_check'] : 0;
        
        echo '<input type="checkbox" id="domain_check" name="' . $this->plugin_name . '_options[domain_check]" value="1" ' . checked(1, $domain_check, false) . ' />';
        echo '<label for="domain_check">' . __('Enable domain availability check', 'varabit-business-name-generator') . '</label>';
    }

    /**
     * Callback for the button color field.
     *
     * @since    1.0.0
     */
    public function button_color_field_callback() {
        $options = get_option($this->plugin_name . '_options');
        $button_color = isset($options['button_color']) ? $options['button_color'] : '#4CAF50';
        
        echo '<input type="color" id="button_color" name="' . $this->plugin_name . '_options[button_color]" value="' . esc_attr($button_color) . '" />';
    }

    /**
     * Callback for the font family field.
     *
     * @since    1.0.0
     */
    public function font_family_field_callback() {
        $options = get_option($this->plugin_name . '_options');
        $font_family = isset($options['font_family']) ? $options['font_family'] : 'sans-serif';
        
        $fonts = array(
            'sans-serif' => __('Sans-serif', 'varabit-business-name-generator'),
            'serif' => __('Serif', 'varabit-business-name-generator'),
            'monospace' => __('Monospace', 'varabit-business-name-generator'),
        );
        
        echo '<select id="font_family" name="' . $this->plugin_name . '_options[font_family]">';
        foreach ($fonts as $font_id => $font_name) {
            echo '<option value="' . esc_attr($font_id) . '" ' . selected($font_family, $font_id, false) . '>' . esc_html($font_name) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Validate options before saving.
     *
     * @since    1.0.0
     * @param    array    $input    The options to validate.
     * @return   array             The validated options.
     */
    public function validate_options($input) {
        $valid = array();
        
        // Validate API key
        $valid['gemini_api_key'] = sanitize_text_field($input['gemini_api_key']);
        
        // Validate model
        $valid_models = array('gemini-1.0-pro', 'gemini-1.0-pro-vision', 'gemini-1.5-flash');
        $valid['gemini_model'] = in_array($input['gemini_model'], $valid_models) ? $input['gemini_model'] : 'gemini-1.0-pro';
        
        // Validate domain check
        $valid['domain_check'] = isset($input['domain_check']) ? 1 : 0;
        
        // Validate button color
        $valid['button_color'] = sanitize_hex_color($input['button_color']);
        
        // Validate font family
        $valid_fonts = array('sans-serif', 'serif', 'monospace');
        $valid['font_family'] = in_array($input['font_family'], $valid_fonts) ? $input['font_family'] : 'sans-serif';
        
        return $valid;
    }
}