<?php
/**
 * Admin settings page template.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get plugin options
$options = get_option('varabit-business-name-generator_options');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        // Output security fields
        settings_fields('varabit-business-name-generator');
        
        // Output setting sections and their fields
        do_settings_sections('varabit-business-name-generator');
        
        // Output save settings button
        submit_button(__('Save Settings', 'varabit-business-name-generator'));
        ?>
    </form>
    
    <div class="varabit-name-generator-info">
        <h2><?php _e('How to Use', 'varabit-business-name-generator'); ?></h2>
        <p><?php _e('Add the business name generator to any page or post using the shortcode:', 'varabit-business-name-generator'); ?></p>
        <code>[varabit_name_generator]</code>
        
        <h3><?php _e('Requirements', 'varabit-business-name-generator'); ?></h3>
        <ul>
            <li><?php _e('Google Gemini API Key - Get one from <a href="https://ai.google.dev/" target="_blank">Google AI Studio</a>', 'varabit-business-name-generator'); ?></li>
        </ul>
        
        <h3><?php _e('Support', 'varabit-business-name-generator'); ?></h3>
        <p><?php _e('For support or feature requests, please contact us at <a href="mailto:support@varabit.com">support@varabit.com</a>', 'varabit-business-name-generator'); ?></p>
    </div>
</div>