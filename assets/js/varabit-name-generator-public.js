/**
 * Public-facing JavaScript for the Varabit Business Name Generator.
 *
 * @since      1.0.0
 */
(function($) {
    'use strict';

    // Store the form data for "Generate More" functionality
    let lastFormData = {};

    /**
     * Initialize the generator form.
     */
    function initGenerator() {
        const $form = $('#varabit-name-generator-form');
        const $results = $('#varabit-name-generator-results');
        const $namesList = $('#varabit-names-list');
        const $loading = $('#varabit-loading');
        const $error = $('#varabit-error');
        const $generateMoreBtn = $('#varabit-generate-more-btn');
        const $copyAllBtn = $('#varabit-copy-all-btn');

        // Form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            generateNames();
        });

        // Generate more button
        $generateMoreBtn.on('click', function() {
            generateNames(true);
        });

        // Copy all button
        $copyAllBtn.on('click', function() {
            copyAllNames();
        });

        // Individual copy buttons (delegated)
        $namesList.on('click', '.varabit-copy-btn', function() {
            const name = $(this).data('name');
            copyToClipboard(name);
            showCopyFeedback($(this));
        });

        /**
         * Generate business names via AJAX.
         * 
         * @param {boolean} isMore Whether this is a "Generate More" request.
         */
        function generateNames(isMore = false) {
            // Show loading
            $loading.show();
            $error.hide();
            
            if (!isMore) {
                // Clear previous results if not generating more
                $namesList.empty();
                $results.show();
                
                // Store form data for "Generate More"
                lastFormData = {
                    keywords: $('#varabit-keywords').val(),
                    tone: $('#varabit-tone').val(),
                    industry: $('#varabit-industry').val()
                };
            }

            // Prepare data
            const data = {
                action: 'varabit_generate_names',
                nonce: varabit_name_generator.nonce,
                keywords: lastFormData.keywords,
                tone: lastFormData.tone,
                industry: lastFormData.industry
            };

            // Make AJAX request
            $.ajax({
                url: varabit_name_generator.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    $loading.hide();
                    
                    if (response.success) {
                        displayNames(response.data.names, isMore);
                    } else {
                        showError(response.data.message || 'An error occurred while generating names.');
                    }
                },
                error: function() {
                    $loading.hide();
                    showError('Failed to connect to the server. Please try again.');
                }
            });
        }

        /**
         * Display the generated names.
         * 
         * @param {Array} names The generated business names.
         * @param {boolean} append Whether to append to existing names.
         */
        function displayNames(names, append = false) {
            if (!append) {
                $namesList.empty();
            }

            if (!names || names.length === 0) {
                showError('No name suggestions were generated. Please try different keywords.');
                return;
            }

            // Create HTML for each name
            names.forEach(function(item) {
                let nameHtml = '';
                let name = '';
                let domainInfo = '';
                
                // Check if the item is an object (with domain info) or just a string
                if (typeof item === 'object') {
                    name = item.name;
                    
                    // Add domain availability if enabled
                    if (varabit_name_generator.domain_check === '1') {
                        const availabilityClass = item.is_available ? 'varabit-domain-available' : 'varabit-domain-unavailable';
                        const availabilityText = item.is_available ? 'Available' : 'Unavailable';
                        const availabilityMessage = item.message ? ` (${item.message})` : '';
                        domainInfo = `<div class="varabit-domain-info">
                            <span class="${availabilityClass}">${item.domain} - ${availabilityText}${availabilityMessage}</span>
                            ${item.is_available ? '<a href="https://www.namecheap.com/domains/registration/results/?domain=' + item.domain + '" target="_blank" class="varabit-register-link">Register</a>' : ''}
                        </div>`;
                    }
                } else {
                    name = item;
                }
                
                nameHtml = `
                    <div class="varabit-name-item">
                        <div>
                            <div class="varabit-name-text">${name}</div>
                            ${domainInfo}
                        </div>
                        <button class="varabit-copy-btn" data-name="${name}">Copy</button>
                    </div>
                `;
                
                $namesList.append(nameHtml);
            });
        }

        /**
         * Copy all generated names to clipboard.
         */
        function copyAllNames() {
            const names = [];
            
            $('.varabit-name-text').each(function() {
                names.push($(this).text());
            });
            
            if (names.length > 0) {
                const text = names.join('\n');
                copyToClipboard(text);
                
                // Show feedback
                const $btn = $('#varabit-copy-all-btn');
                showCopyFeedback($btn);
            }
        }

        /**
         * Copy text to clipboard.
         * 
         * @param {string} text The text to copy.
         */
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }

        /**
         * Show copy feedback on button.
         * 
         * @param {jQuery} $button The button element.
         */
        function showCopyFeedback($button) {
            const originalText = $button.text();
            $button.text('Copied!');
            
            setTimeout(function() {
                $button.text(originalText);
            }, 1500);
        }

        /**
         * Show error message.
         * 
         * @param {string} message The error message.
         */
        function showError(message) {
            $error.text(message).show();
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initGenerator();
    });

})(jQuery);