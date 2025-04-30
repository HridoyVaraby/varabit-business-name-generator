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
        $api_key = isset($options['gemini_api_key']) ? $options['gemini_api_key'] : '';
        $model = isset($options['gemini_model']) ? $options['gemini_model'] : 'gemini-1.0-pro';
        
        // Validate API key
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', __('Google Gemini API key is missing. Please configure it in the plugin settings.', 'varabit-business-name-generator'));
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
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            $error_message = wp_remote_retrieve_response_message($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($body['error']['message'])) {
                $error_message = $body['error']['message'];
            }
            
            return new WP_Error('api_error', sprintf(__('API Error: %s', 'varabit-business-name-generator'), $error_message));
        }
        
        return json_decode(wp_remote_retrieve_body($response), true);
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