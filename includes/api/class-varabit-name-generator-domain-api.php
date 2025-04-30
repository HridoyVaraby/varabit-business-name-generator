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
            'message' => '',
            'check_method' => 'unknown' // Track which method was used for checking
        );

        try {
            $full_domain = $domain_name . '.' . $extension;
            
            // First try WHOIS verification
            $whois_result = $this->verify_domain_availability($full_domain);
            
            if ($whois_result !== null) {
                // WHOIS check was successful
                $response['is_available'] = $whois_result;
                $response['check_method'] = 'whois';
                
                if ($whois_result) {
                    $response['message'] = __('Domain appears to be available (WHOIS check)', 'varabit-business-name-generator');
                } else {
                    $response['message'] = __('Domain is already registered (WHOIS check)', 'varabit-business-name-generator');
                }
            } else {
                // WHOIS check failed, fall back to DNS-only check
                $dns_check = !checkdnsrr($full_domain, 'ANY');
                $response['is_available'] = $dns_check;
                $response['check_method'] = 'dns';
                
                if ($dns_check) {
                    $response['message'] = __('Domain appears to be available (DNS check only)', 'varabit-business-name-generator');
                } else {
                    $response['message'] = __('Domain is already registered (DNS check only)', 'varabit-business-name-generator');
                }
            }
        } catch (Exception $e) {
            // Last resort fallback to DNS-only on any exception
            try {
                $dns_check = !checkdnsrr($full_domain, 'ANY');
                $response['is_available'] = $dns_check;
                $response['check_method'] = 'dns_fallback';
                
                if ($dns_check) {
                    $response['message'] = __('Domain appears to be available (DNS check only)', 'varabit-business-name-generator');
                } else {
                    $response['message'] = __('Domain is already registered (DNS check only)', 'varabit-business-name-generator');
                }
            } catch (Exception $dns_error) {
                $response['message'] = __('Could not determine domain availability', 'varabit-business-name-generator');
            }
        }

        return $response;
    }

    /**
     * Verify domain availability using WHOIS servers
     * 
     * @since    1.0.1
     * @param    string    $domain        The full domain name to check.
     * @return   boolean|null             True if domain appears available, false if registered, null if WHOIS check failed.
     */
    private function verify_domain_availability($domain) {
        // Common WHOIS servers for different TLDs
        $whois_servers = array(
            'com' => 'whois.verisign-grs.com',
            'net' => 'whois.verisign-grs.com',
            'org' => 'whois.pir.org',
            'info' => 'whois.afilias.net',
            'io' => 'whois.nic.io'
        );
        
        // Extract TLD from domain
        $domain_parts = explode('.', $domain);
        $tld = end($domain_parts);
        
        // Default to com WHOIS server if TLD not found
        $whois_server = isset($whois_servers[$tld]) ? $whois_servers[$tld] : 'whois.verisign-grs.com';
        
        // Try DNS check first as a quick filter
        if (checkdnsrr($domain, 'ANY')) {
            return false; // Domain has DNS records, so it's registered
        }
        
        // For domains that pass DNS check, do an additional verification with WHOIS
        try {
            // Try to connect to the WHOIS server
            $conn = @fsockopen($whois_server, 43, $errno, $errstr, 10);
            if (!$conn) {
                // If we can't connect to WHOIS, return null to trigger DNS fallback
                return null;
            }
            
            // Send the domain query
            fputs($conn, $domain . "\r\n");
            
            // Read the response
            $response = '';
            while (!feof($conn)) {
                $response .= fgets($conn, 128);
            }
            fclose($conn);
            
            // Check for common phrases indicating domain is not available
            $not_available_phrases = array(
                'Domain Name:', // Standard WHOIS response for registered domains
                'No match for', // Negative match still means domain exists in registry
                'is registered', 
                'is already registered',
                'is not available',
                'domain exists',
                'domain name is not available'
            );
            
            foreach ($not_available_phrases as $phrase) {
                if (stripos($response, $phrase) !== false) {
                    return false; // Domain is registered
                }
            }
            
            // If we've made it here, the domain might be available
            // However, we should be conservative - if we can't positively confirm availability,
            // assume it's taken to avoid misleading users
            $available_phrases = array(
                'No match', 
                'NOT FOUND',
                'No entries found',
                'Domain not found',
                'is available'
            );
            
            foreach ($available_phrases as $phrase) {
                if (stripos($response, $phrase) !== false) {
                    return true; // Domain is likely available
                }
            }
            
            // If we can't determine status, be conservative
            return false;
            
        } catch (Exception $e) {
            // On any error, return null to trigger DNS fallback
            return null;
        }
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
                'message' => $availability['message'],
                'check_method' => $availability['check_method'] // Include which method was used
            );
        }
        
        return $results;
    }
}