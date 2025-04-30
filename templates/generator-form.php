<?php
/**
 * Frontend generator form template.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="varabit-name-generator-container">
    <div class="varabit-name-generator-form-container">
        <form id="varabit-name-generator-form" class="varabit-name-generator-form">
            <div class="form-group">
                <label for="varabit-keywords"><?php _e('Keywords', 'varabit-business-name-generator'); ?></label>
                <input type="text" id="varabit-keywords" name="keywords" placeholder="<?php _e('Enter keywords related to your business', 'varabit-business-name-generator'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="varabit-tone"><?php _e('Tone', 'varabit-business-name-generator'); ?></label>
                <select id="varabit-tone" name="tone">
                    <option value=""><?php _e('Select a tone (optional)', 'varabit-business-name-generator'); ?></option>
                    <option value="Fun"><?php _e('Fun', 'varabit-business-name-generator'); ?></option>
                    <option value="Professional"><?php _e('Professional', 'varabit-business-name-generator'); ?></option>
                    <option value="Trendy"><?php _e('Trendy', 'varabit-business-name-generator'); ?></option>
                    <option value="Luxurious"><?php _e('Luxurious', 'varabit-business-name-generator'); ?></option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="varabit-industry"><?php _e('Industry', 'varabit-business-name-generator'); ?></label>
                <select id="varabit-industry" name="industry">
                    <option value=""><?php _e('Select an industry (optional)', 'varabit-business-name-generator'); ?></option>
                    <option value="Technology"><?php _e('Technology', 'varabit-business-name-generator'); ?></option>
                    <option value="Fashion"><?php _e('Fashion', 'varabit-business-name-generator'); ?></option>
                    <option value="Health"><?php _e('Health', 'varabit-business-name-generator'); ?></option>
                    <option value="Food"><?php _e('Food', 'varabit-business-name-generator'); ?></option>
                    <option value="Finance"><?php _e('Finance', 'varabit-business-name-generator'); ?></option>
                    <option value="Education"><?php _e('Education', 'varabit-business-name-generator'); ?></option>
                    <option value="Entertainment"><?php _e('Entertainment', 'varabit-business-name-generator'); ?></option>
                    <option value="Travel"><?php _e('Travel', 'varabit-business-name-generator'); ?></option>
                    <option value="Real Estate"><?php _e('Real Estate', 'varabit-business-name-generator'); ?></option>
                    <option value="Fitness"><?php _e('Fitness', 'varabit-business-name-generator'); ?></option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" id="varabit-generate-btn"><?php _e('Generate Names', 'varabit-business-name-generator'); ?></button>
            </div>
        </form>
    </div>
    
    <div id="varabit-name-generator-results" class="varabit-name-generator-results" style="display: none;">
        <h3><?php _e('Business Name Ideas', 'varabit-business-name-generator'); ?></h3>
        
        <div id="varabit-results-actions" class="varabit-results-actions">
            <button id="varabit-copy-all-btn" class="varabit-secondary-btn"><?php _e('Copy All', 'varabit-business-name-generator'); ?></button>
            <button id="varabit-generate-more-btn" class="varabit-secondary-btn"><?php _e('Generate More', 'varabit-business-name-generator'); ?></button>
        </div>
        
        <div id="varabit-names-list" class="varabit-names-list"></div>
        
        <div id="varabit-loading" class="varabit-loading">
            <div class="varabit-spinner"></div>
            <p><?php _e('Generating creative business names...', 'varabit-business-name-generator'); ?></p>
        </div>
        
        <div id="varabit-error" class="varabit-error" style="display: none;"></div>
    </div>
</div>