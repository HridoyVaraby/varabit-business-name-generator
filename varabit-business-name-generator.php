<?php
/**
 * Plugin Name: Varabit Business Name Generator
 * Plugin URI: https://varabit.com
 * Description: Generate creative business name ideas using Google Gemini 2.0 Flash API based on keywords, tone, and industry.
 * Version: 1.0.0
 * Author: Varabit
 * Author URI: https://varabit.com
 * Text Domain: varabit-business-name-generator
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('VARABIT_NAME_GENERATOR_VERSION', '1.0.0');
define('VARABIT_NAME_GENERATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VARABIT_NAME_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function varabit_name_generator_activate() {
    // Activation tasks if needed
}

/**
 * The code that runs during plugin deactivation.
 */
function varabit_name_generator_deactivate() {
    // Deactivation tasks if needed
}

register_activation_hook(__FILE__, 'varabit_name_generator_activate');
register_deactivation_hook(__FILE__, 'varabit_name_generator_deactivate');

/**
 * Include required files
 */
require_once VARABIT_NAME_GENERATOR_PLUGIN_DIR . 'includes/class-varabit-name-generator.php';

/**
 * Begins execution of the plugin.
 */
function varabit_name_generator_run() {
    $plugin = new Varabit_Name_Generator();
    $plugin->run();
}
varabit_name_generator_run();