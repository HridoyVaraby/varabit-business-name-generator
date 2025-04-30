<?php
/**
 * The Domain Availability API functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_Name_Generator_Domain_API {

    /**
     * The WHOIS API endpoint.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_endpoint    The API endpoint.
     */
    private $api_endpoint = 'https://domain-availability-api.whoisfreaks.com/v1.0';

    /**
     * Check domain availability for a given domain name.
     *
     * @since    1.0.0
     * @param    string    $domain_name    The domain name to check (without extension).
     * @param    string    $extension      The domain extension (default: com).
     * @return   array                    Domain availability information.
     */
    public function check_domain_availability($domain_name, $extension = 'com') {
        // Default response structure
        $response = array(
            'domain' => $domain_name . '.' . $extension,
            'is_available' => false,
            'message' => ''
        );

        try {
            // First try a simple DNS lookup as a quick check
            $dns_check = !checkdnsrr($domain_name . '.' . $extension, 'ANY');
            
            if ($dns_check) {
                // If DNS check suggests it's available, do a more thorough check
                // using WHOIS API if we have API access
                $options = get_option('varabit-business-name-generator_options');
                
                // For now, we'll use the DNS result but add a note that it's a preliminary check
                $response['is_available'] = true;
                $response['message'] = __('Domain appears to be available (preliminary check)', 'varabit-business-name-generator');
            } else {
                $response['message'] = __('Domain is already registered', 'varabit-business-name-generator');
            }
        } catch (Exception $e) {
            $response['message'] = __('Could not determine domain availability', 'varabit-business-name-generator');
        }

        return $response;
    }

    /**
     * Check availability for multiple domains at once.
     *
     * @since    1.0.0
     * @param    array    $names          Array of business names to check.
     * @param    string   $extension      The domain extension (default: com).
     * @return   array                    Array of domain availability information.
     */
    public function check_multiple_domains($names, $extension = 'com') {
        $results = array();
        
        foreach ($names as $name) {
            // Convert to domain-friendly format
            $domain_name = strtolower(str_replace(' ', '', $name));
            
            // Check availability
            $availability = $this->check_domain_availability($domain_name, $extension);
            
            // Add to results
            $results[] = array(
                'name' => $name,
                'domain' => $availability['domain'],
                'is_available' => $availability['is_available'],
                'message' => $availability['message']
            );
        }
        
        return $results;
    }
}