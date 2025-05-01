<?php
/**
 * The Domain Availability API functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_Name_Generator_Domain_API {


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
            'message' => '',
            'check_method' => 'dns'
        );

        try {
            $full_domain = $domain_name . '.' . $extension;
            $dns_check = !checkdnsrr($full_domain, 'ANY');
            $response['is_available'] = $dns_check;
            
            if ($dns_check) {
                $response['message'] = __('Domain appears to be available', 'varabit-business-name-generator');
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
            
            // Check availability with retry logic
            $max_attempts = 3;
            $attempt = 0;
            $availability = null;
            
            do {
                $attempt++;
                $availability = $this->check_domain_availability($domain_name, $extension);
                
                // If domain is available or we've reached max attempts, break the loop
                if ($availability['is_available'] || $attempt >= $max_attempts) {
                    break;
                }
                
                // Generate a new variation of the name for next attempt
                $domain_name = $domain_name . rand(1, 99);
                
            } while (true);
            
            // Add to results
            $results[] = array(
                'name' => $name,
                'domain' => $availability['domain'],
                'is_available' => $availability['is_available'],
                'message' => $availability['message'],
                'check_method' => $availability['check_method'] // Include which method was used
            );
        }
        
        return $results;
    }
}