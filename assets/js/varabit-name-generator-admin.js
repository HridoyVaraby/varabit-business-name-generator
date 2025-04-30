/**
 * Admin-specific JavaScript for the Varabit Business Name Generator.
 *
 * @since      1.0.0
 */
(function($) {
    'use strict';

    /**
     * Initialize the admin settings page.
     */
    function initAdminSettings() {
        // Color picker for button color
        if ($.fn.wpColorPicker) {
            $('.varabit-color-picker').wpColorPicker();
        }

        // API key field toggle
        const $apiKeyField = $('#varabit-gemini-api-key');
        const $toggleBtn = $('#varabit-toggle-api-key');

        $toggleBtn.on('click', function(e) {
            e.preventDefault();
            
            if ($apiKeyField.attr('type') === 'password') {
                $apiKeyField.attr('type', 'text');
                $toggleBtn.text('Hide');
            } else {
                $apiKeyField.attr('type', 'password');
                $toggleBtn.text('Show');
            }
        });

        // Test API connection
        $('#varabit-test-api-btn').on('click', function(e) {
            e.preventDefault();
            
            const $resultContainer = $('#varabit-api-test-result');
            const apiKey = $apiKeyField.val();
            const model = $('#varabit-gemini-model').val();
            
            if (!apiKey) {
                $resultContainer.html('<div class="notice notice-error"><p>Please enter an API key first.</p></div>');
                return;
            }
            
            // Show loading
            $resultContainer.html('<p><span class="spinner is-active"></span> Testing API connection...</p>');
            
            // Make AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'varabit_test_api_connection',
                    nonce: varabit_admin.nonce,
                    api_key: apiKey,
                    model: model
                },
                success: function(response) {
                    if (response.success) {
                        $resultContainer.html('<div class="notice notice-success"><p>API connection successful!</p></div>');
                    } else {
                        $resultContainer.html('<div class="notice notice-error"><p>API connection failed: ' + response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    $resultContainer.html('<div class="notice notice-error"><p>Failed to connect to the server. Please try again.</p></div>');
                }
            });
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initAdminSettings();
    });

})(jQuery);