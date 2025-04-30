<?php
/**
 * The API integration functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_Name_Generator_API {

    /**
     * The Google Gemini API endpoint.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_endpoint    The API endpoint.
     */
    private $api_endpoint = 'https://generativelanguage.googleapis.com/v1/models/';

    /**
     * Generate business name suggestions using Google Gemini API.
     *
     * @since    1.0.0
     * @param    string    $keywords    The keywords for the business name.
     * @param    string    $tone        The desired tone for the business name.
     * @param    string    $industry    The industry or niche for the business.
     * @return   array|WP_Error        The generated business names or an error.
     */
    public function generate_business_names($keywords, $tone = '', $industry = '') {
        // Get API key and model from settings
        $options = get_option('varabit-business-name-generator_options');
        $api_key = isset($options['gemini_api_key']) ? trim($options['gemini_api_key']) : '';
        $model = isset($options['gemini_model']) ? $options['gemini_model'] : 'gemini-1.0-pro';
        
        // Validate API key
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', __('Google Gemini API key is missing. Please configure it in the plugin settings.', 'varabit-business-name-generator'));
        }
        
        // Validate API key format (basic check)
        if (strlen($api_key) < 10) {
            return new WP_Error('invalid_api_key', __('The Google Gemini API key appears to be invalid. Please check your API key in the plugin settings.', 'varabit-business-name-generator'));
        }
        
        // Build the prompt
        $prompt = $this->build_prompt($keywords, $tone, $industry);
        
        // Make API request
        $response = $this->make_api_request($api_key, $model, $prompt);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Parse the response to extract business names
        $names = $this->parse_response($response);
        
        return $names;
    }

    /**
     * Build the prompt for the API request.
     *
     * @since    1.0.0
     * @param    string    $keywords    The keywords for the business name.
     * @param    string    $tone        The desired tone for the business name.
     * @param    string    $industry    The industry or niche for the business.
     * @return   string                The formatted prompt.
     */
    private function build_prompt($keywords, $tone = '', $industry = '') {
        $prompt = "Generate 15 creative and brandable business name ideas";
        
        // Add keywords
        $prompt .= " based on these keywords: {$keywords}.";
        
        // Add tone if provided
        if (!empty($tone)) {
            $prompt .= " The tone should be {$tone}.";
        }
        
        // Add industry if provided
        if (!empty($industry)) {
            $prompt .= " The business is in the {$industry} industry.";
        }
        
        $prompt .= " Please provide only the business names as a numbered list, without any additional text or explanations.";
        
        return $prompt;
    }

    /**
     * Make the API request to Google Gemini.
     *
     * @since    1.0.0
     * @param    string    $api_key    The Google Gemini API key.
     * @param    string    $model      The model to use.
     * @param    string    $prompt     The prompt for the API.
     * @return   array|WP_Error       The API response or an error.
     */
    private function make_api_request($api_key, $model, $prompt) {
        $url = $this->api_endpoint . $model . ':generateContent?key=' . $api_key;
        
        $body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $prompt
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            )
        );
        
        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body'    => json_encode($body),
            'timeout' => 60,
            'sslverify' => true,
        );
        
        // Log the request for debugging (only in development)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Varabit Name Generator - API Request URL: ' . $url);
            error_log('Varabit Name Generator - API Request Body: ' . json_encode($body));
        }
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            $error_code = $response->get_error_code();
            $error_message = $response->get_error_message();
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Varabit Name Generator - WP Error: ' . $error_code . ' - ' . $error_message);
            }
            
            // Provide more user-friendly error messages
            if (strpos($error_message, 'cURL error 28') !== false) {
                return new WP_Error('api_timeout', __('The request to the Google Gemini API timed out. Please try again later.', 'varabit-business-name-generator'));
            } elseif (strpos($error_message, 'cURL error 6') !== false || strpos($error_message, 'cURL error 7') !== false) {
                return new WP_Error('api_connection', __('Could not connect to the Google Gemini API. Please check your internet connection.', 'varabit-business-name-generator'));
            }
            
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Log the response for debugging (only in development)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Varabit Name Generator - API Response Code: ' . $response_code);
            error_log('Varabit Name Generator - API Response Body: ' . $response_body);
        }
        
        if ($response_code !== 200) {
            $error_message = wp_remote_retrieve_response_message($response);
            $body = json_decode($response_body, true);
            
            if (isset($body['error']['message'])) {
                $error_message = $body['error']['message'];
            }
            
            // Handle specific API error codes
            if ($response_code === 400) {
                return new WP_Error('api_bad_request', sprintf(__('API Error (400): %s - Please check your API key and model settings.', 'varabit-business-name-generator'), $error_message));
            } elseif ($response_code === 401) {
                return new WP_Error('api_unauthorized', __('API Error (401): Invalid API key. Please check your Google Gemini API key in the plugin settings.', 'varabit-business-name-generator'));
            } elseif ($response_code === 403) {
                return new WP_Error('api_forbidden', __('API Error (403): Access denied. Your API key may not have permission to use this model or you may have exceeded your quota.', 'varabit-business-name-generator'));
            } elseif ($response_code === 429) {
                return new WP_Error('api_rate_limit', __('API Error (429): Rate limit exceeded. Please try again later.', 'varabit-business-name-generator'));
            } else {
                return new WP_Error('api_error', sprintf(__('API Error (%s): %s', 'varabit-business-name-generator'), $response_code, $error_message));
            }
        }
        
        $decoded_body = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('api_invalid_json', __('Invalid JSON response from the API. Please try again.', 'varabit-business-name-generator'));
        }
        
        return $decoded_body;
    }

    /**
     * Parse the API response to extract business names.
     *
     * @since    1.0.0
     * @param    array    $response    The API response.
     * @return   array                The extracted business names.
     */
    private function parse_response($response) {
        $names = array();
        
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $response['candidates'][0]['content']['parts'][0]['text'];
            
            // Extract names from the numbered list
            preg_match_all('/\d+\.\s*([^\n]+)/', $text, $matches);
            
            if (!empty($matches[1])) {
                $names = array_map('trim', $matches[1]);
            } else {
                // Fallback: split by newlines and clean up
                $lines = explode("\n", $text);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Remove numbers and dots at the beginning
                        $line = preg_replace('/^\d+\.\s*/', '', $line);
                        $names[] = $line;
                    }
                }
            }
        }
        
        // Limit to 15 names
        return array_slice($names, 0, 15);
    }
}