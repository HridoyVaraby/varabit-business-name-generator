<?php
/**
 * The main plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 */
class Varabit_Name_Generator {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Varabit_Name_Generator_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'varabit-business-name-generator';
        $this->version = VARABIT_NAME_GENERATOR_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Varabit_Name_Generator_Loader. Orchestrates the hooks of the plugin.
     * - Varabit_Name_Generator_i18n. Defines internationalization functionality.
     * - Varabit_Name_Generator_Admin. Defines all hooks for the admin area.
     * - Varabit_Name_Generator_Public. Defines all hooks for the public side of the site.
     * - Varabit_Name_Generator_API. Handles API interactions.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'includes/class-varabit-name-generator-loader.php';

        // The class responsible for defining internationalization functionality of the plugin.
        require_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'includes/class-varabit-name-generator-i18n.php';

        // The class responsible for defining all actions that occur in the admin area.
        require_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'includes/admin/class-varabit-name-generator-admin.php';

        // The class responsible for defining all actions that occur in the public-facing side of the site.
        require_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'includes/public/class-varabit-name-generator-public.php';

        // The class responsible for handling API interactions.
        require_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'includes/api/class-varabit-name-generator-api.php';

        $this->loader = new Varabit_Name_Generator_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Varabit_Name_Generator_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Varabit_Name_Generator_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Varabit_Name_Generator_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_options_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Varabit_Name_Generator_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // AJAX handlers
        $this->loader->add_action('wp_ajax_varabit_generate_names', $plugin_public, 'generate_names');
        $this->loader->add_action('wp_ajax_nopriv_varabit_generate_names', $plugin_public, 'generate_names');
    }

    /**
     * Register all shortcodes.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_shortcodes() {
        $plugin_public = new Varabit_Name_Generator_Public($this->get_plugin_name(), $this->get_version());
        add_shortcode('varabit_name_generator', array($plugin_public, 'render_generator_form'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Varabit_Name_Generator_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}