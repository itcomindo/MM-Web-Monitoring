<?php

if (!defined('ABSPATH')) {
    exit;
}

class MMWM_Domain_Checker implements MMWM_Domain_Checker_Interface
{
    /**
     * Public suffix list for accurate domain extraction
     * This is a simplified version - in production, you'd want to use the full PSL
     */
    private $public_suffixes = [
        // Generic TLDs
        'com',
        'org',
        'net',
        'edu',
        'gov',
        'mil',
        'int',
        'biz',
        'info',
        'name',
        'pro',
        'museum',
        'coop',
        'aero',

        // Country code TLDs with common second-level domains
        'co.uk',
        'org.uk',
        'ac.uk',
        'gov.uk',
        'net.uk',
        'co.id',
        'or.id',
        'ac.id',
        'go.id',
        'web.id',
        'my.id',
        'co.jp',
        'or.jp',
        'ac.jp',
        'go.jp',
        'ne.jp',
        'com.au',
        'org.au',
        'net.au',
        'edu.au',
        'gov.au',
        'co.nz',
        'org.nz',
        'net.nz',
        'ac.nz',
        'govt.nz',
        'co.za',
        'org.za',
        'net.za',
        'ac.za',
        'gov.za',
        'com.br',
        'org.br',
        'net.br',
        'edu.br',
        'gov.br',
        'co.in',
        'org.in',
        'net.in',
        'ac.in',
        'edu.in',
        'gov.in',

        // Single country TLDs
        'uk',
        'de',
        'fr',
        'it',
        'es',
        'nl',
        'be',
        'ch',
        'at',
        'se',
        'no',
        'dk',
        'fi',
        'pl',
        'cz',
        'hu',
        'ru',
        'ua',
        'cn',
        'jp',
        'kr',
        'in',
        'sg',
        'th',
        'my',
        'id',
        'ph',
        'vn',
        'au',
        'nz',
        'za',
        'br',
        'ar',
        'mx',
        'ca',
        'us'
    ];

    /**
     * Check domain expiration status
     *
     * @param string $domain The domain to check
     * @return array Domain expiration information
     */
    public function check_domain_expiration($domain)
    {
        $root_domain = $this->extract_root_domain($domain);

        if (empty($root_domain)) {
            return [
                'success' => false,
                'error' => 'Invalid domain',
                'domain' => $domain,
                'root_domain' => null,
                'expiry_date' => null,
                'days_until_expiry' => null,
                'registrar' => null
            ];
        }

        try {
            $whois_data = $this->get_whois_data($root_domain);

            if (!$whois_data['success']) {
                return [
                    'success' => false,
                    'error' => $whois_data['error'],
                    'domain' => $domain,
                    'root_domain' => $root_domain,
                    'expiry_date' => null,
                    'days_until_expiry' => null,
                    'registrar' => null
                ];
            }

            $expiry_timestamp = strtotime($whois_data['expiry_date']);
            $current_timestamp = time();
            $days_until_expiry = ceil(($expiry_timestamp - $current_timestamp) / 86400);

            return [
                'success' => true,
                'error' => null,
                'domain' => $domain,
                'root_domain' => $root_domain,
                'expiry_date' => date('Y-m-d H:i:s', $expiry_timestamp),
                'expiry_timestamp' => $expiry_timestamp,
                'days_until_expiry' => $days_until_expiry,
                'registrar' => $whois_data['registrar'],
                'is_expired' => $days_until_expiry < 0,
                'is_expiring_soon' => $days_until_expiry <= 10 && $days_until_expiry > 0
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'domain' => $domain,
                'root_domain' => $root_domain,
                'expiry_date' => null,
                'days_until_expiry' => null,
                'registrar' => null
            ];
        }
    }

    /**
     * Extract root domain from URL or domain
     *
     * @param string $url_or_domain URL or domain
     * @return string Root domain
     */
    public function extract_root_domain($url_or_domain)
    {
        // Remove protocol if present
        $domain = preg_replace('#^https?://#i', '', $url_or_domain);

        // Remove path, query, fragment
        $domain = explode('/', $domain)[0];
        $domain = explode('?', $domain)[0];
        $domain = explode('#', $domain)[0];

        // Remove port
        $domain = explode(':', $domain)[0];

        // Convert to lowercase
        $domain = strtolower(trim($domain));

        // Remove www prefix
        $domain = preg_replace('/^www\./', '', $domain);

        if (empty($domain)) {
            return '';
        }

        // Split domain into parts
        $parts = explode('.', $domain);
        $num_parts = count($parts);

        if ($num_parts < 2) {
            return ''; // Invalid domain
        }

        // Check for multi-level TLDs first (longest match)
        usort($this->public_suffixes, function ($a, $b) {
            return substr_count($b, '.') - substr_count($a, '.');
        });

        foreach ($this->public_suffixes as $suffix) {
            $suffix_parts = explode('.', $suffix);
            $suffix_count = count($suffix_parts);

            if ($num_parts >= $suffix_count + 1) {
                $domain_suffix = implode('.', array_slice($parts, -$suffix_count));

                if ($domain_suffix === $suffix) {
                    // Found matching suffix, return domain + suffix
                    $domain_index = $num_parts - $suffix_count - 1;
                    if ($domain_index >= 0 && isset($parts[$domain_index])) {
                        return $parts[$domain_index] . '.' . $suffix;
                    }
                }
            }
        }

        // Fallback: assume single TLD
        return $parts[$num_parts - 2] . '.' . $parts[$num_parts - 1];
    }

    /**
     * Check if domain will expire soon
     *
     * @param string $domain The domain to check
     * @param int $days Number of days before expiration to warn
     * @return bool True if domain expires within specified days
     */
    public function is_domain_expiring_soon($domain, $days = 10)
    {
        $result = $this->check_domain_expiration($domain);

        if (!$result['success'] || $result['days_until_expiry'] === null) {
            return false;
        }

        return $result['days_until_expiry'] <= $days && $result['days_until_expiry'] > 0;
    }

    /**
     * Get WHOIS data for domain
     *
     * @param string $domain Domain name
     * @return array WHOIS result
     */
    private function get_whois_data($domain)
    {
        // Try to get WHOIS data using various methods
        $methods = [
            'whois_command',
            'whois_api',
            'dns_lookup'
        ];

        foreach ($methods as $method) {
            $result = $this->{"try_" . $method}($domain);
            if ($result['success']) {
                return $result;
            }
        }

        return [
            'success' => false,
            'error' => 'Could not retrieve domain expiration data',
            'expiry_date' => null,
            'registrar' => null
        ];
    }

    /**
     * Try WHOIS command if available
     *
     * @param string $domain Domain name
     * @return array Result
     */
    private function try_whois_command($domain)
    {
        if (!function_exists('exec') || !function_exists('shell_exec')) {
            return ['success' => false, 'error' => 'exec/shell_exec not available'];
        }

        $whois_servers = $this->get_whois_servers();
        $tld = substr(strrchr($domain, '.'), 1);

        if (!isset($whois_servers[$tld])) {
            return ['success' => false, 'error' => 'No WHOIS server for TLD'];
        }

        $server = $whois_servers[$tld];
        $command = "whois -h {$server} {$domain} 2>/dev/null";

        $output = @shell_exec($command);

        if (empty($output)) {
            return ['success' => false, 'error' => 'WHOIS command failed'];
        }

        return $this->parse_whois_output($output);
    }

    /**
     * Try WHOIS API using WHOISXML API service
     *
     * @param string $domain Domain name
     * @return array Result
     */
    private function try_whois_api($domain)
    {
        // Check if we have the API key in options
        $api_key = get_option('mmwm_whoisxml_api_key', '');
        
        if (empty($api_key)) {
            return ['success' => false, 'error' => 'WHOIS API key not configured', 'whois_url' => $this->get_public_whois_url($domain)];
        }
        
        $api_url = 'https://www.whoisxmlapi.com/whoisserver/WhoisService';
        $request_url = add_query_arg([
            'apiKey' => $api_key,
            'domainName' => $domain,
            'outputFormat' => 'JSON'
        ], $api_url);
        
        // Use cURL directly with longer timeout for better reliability
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds timeout
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $body = curl_exec($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            if ($error) {
                return ['success' => false, 'error' => 'WHOIS API request failed: ' . $error];
            }
            
            if ($info['http_code'] !== 200) {
                return ['success' => false, 'error' => 'WHOIS API returned error code: ' . $info['http_code']];
            }
        } else {
            // Fallback to wp_remote_get if cURL is not available
            $response = wp_remote_get($request_url, [
                'timeout' => 30, // Increased timeout
                'sslverify' => true,
            ]);
            
            if (is_wp_error($response)) {
                return ['success' => false, 'error' => 'WHOIS API request failed: ' . $response->get_error_message()];
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                return ['success' => false, 'error' => 'WHOIS API returned error code: ' . $response_code];
            }
            
            $body = wp_remote_retrieve_body($response);
        }
        
        $data = json_decode($body, true);
        
        if (empty($data) || !isset($data['WhoisRecord'])) {
            return ['success' => false, 'error' => 'Invalid WHOIS API response format'];
        }
        
        $whois_record = $data['WhoisRecord'];
        
        // Extract expiry date
        $expiry_date = null;
        $registrar = null;
        
        if (isset($whois_record['registryData']['expiresDate'])) {
            $expiry_date = $whois_record['registryData']['expiresDate'];
        } elseif (isset($whois_record['expiresDate'])) {
            $expiry_date = $whois_record['expiresDate'];
        } elseif (isset($whois_record['expiresDateNormalized'])) {
            $expiry_date = $whois_record['expiresDateNormalized'];
        }
        
        if (isset($whois_record['registrarName'])) {
            $registrar = $whois_record['registrarName'];
        }
        
        if ($expiry_date) {
            return [
                'success' => true,
                'expiry_date' => date('Y-m-d H:i:s', strtotime($expiry_date)),
                'registrar' => $registrar ?: 'Unknown'
            ];
        }
        
        return ['success' => false, 'error' => 'Could not extract expiration date from API response'];
    }

    /**
     * Try DNS SOA record lookup as fallback
     *
     * @param string $domain Domain name
     * @return array Result
     */
    private function try_dns_lookup($domain)
    {
        if (!function_exists('dns_get_record')) {
            return ['success' => false, 'error' => 'DNS functions not available'];
        }

        $records = @dns_get_record($domain, DNS_SOA);

        if (empty($records)) {
            return ['success' => false, 'error' => 'No DNS SOA record found'];
        }

        // SOA record doesn't contain expiration date
        // This is just a connectivity test
        return [
            'success' => true,
            'expiry_date' => date('Y-m-d H:i:s', strtotime('+365 days')), // Fallback estimate
            'registrar' => 'Unknown (DNS Lookup)'
        ];
    }

    /**
     * Parse WHOIS output to extract expiration date
     *
     * @param string $output WHOIS output
     * @return array Parsed result
     */
    private function parse_whois_output($output)
    {
        $patterns = [
            '/Registry Expiry Date:\s*(.+)/i',
            '/Expiration Date:\s*(.+)/i',
            '/Expiry Date:\s*(.+)/i',
            '/Expire Date:\s*(.+)/i',
            '/Expires:\s*(.+)/i',
            '/expire:\s*(.+)/i',
        ];

        $registrar_patterns = [
            '/Registrar:\s*(.+)/i',
            '/Sponsoring Registrar:\s*(.+)/i',
        ];

        $expiry_date = null;
        $registrar = null;

        // Look for expiration date
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $output, $matches)) {
                $date_string = trim($matches[1]);
                $timestamp = strtotime($date_string);
                if ($timestamp !== false) {
                    $expiry_date = date('Y-m-d H:i:s', $timestamp);
                    break;
                }
            }
        }

        // Look for registrar
        foreach ($registrar_patterns as $pattern) {
            if (preg_match($pattern, $output, $matches)) {
                $registrar = trim($matches[1]);
                break;
            }
        }

        if ($expiry_date) {
            return [
                'success' => true,
                'expiry_date' => $expiry_date,
                'registrar' => $registrar ?: 'Unknown'
            ];
        }

        return ['success' => false, 'error' => 'Could not parse expiration date'];
    }

    /**
     * Get WHOIS servers for common TLDs
     *
     * @return array TLD => server mapping
     */
    private function get_whois_servers()
    {
        return [
            'com' => 'whois.verisign-grs.com',
            'net' => 'whois.verisign-grs.com',
            'org' => 'whois.pir.org',
            'info' => 'whois.afilias.net',
            'biz' => 'whois.neulevel.biz',
            'us' => 'whois.nic.us',
            'uk' => 'whois.nic.uk',
            'de' => 'whois.denic.de',
            'fr' => 'whois.afnic.fr',
            'it' => 'whois.nic.it',
            'nl' => 'whois.domain-registry.nl',
            'be' => 'whois.dns.be',
            'ch' => 'whois.nic.ch',
            'at' => 'whois.nic.at',
            'se' => 'whois.iis.se',
            'no' => 'whois.norid.no',
            'dk' => 'whois.dk-hostmaster.dk',
            'fi' => 'whois.fi',
            'pl' => 'whois.dns.pl',
            'cz' => 'whois.nic.cz',
            'ru' => 'whois.tcinet.ru',
            'cn' => 'whois.cnnic.net.cn',
            'jp' => 'whois.jprs.jp',
            'kr' => 'whois.kr',
            'in' => 'whois.inregistry.net',
            'au' => 'whois.aunic.net',
            'nz' => 'whois.srs.net.nz',
            'br' => 'whois.registro.br',
            'mx' => 'whois.mx',
            'ca' => 'whois.cira.ca',
            'id' => 'whois.id',
        ];
    }
    
    /**
     * Get public WHOIS lookup URL for a domain
     * 
     * @param string $domain Domain name
     * @return string Public WHOIS URL
     */
    public function get_public_whois_url($domain)
    {
        // Sanitize domain for URL
        $domain = trim(preg_replace('/[^a-z0-9\-\.]/i', '', $domain));
        
        // List of popular WHOIS lookup services
        $whois_services = [
            'https://www.whois.com/whois/',
            'https://who.is/whois/',
            'https://lookup.icann.org/en/lookup?q=',
            'https://www.godaddy.com/whois/results.aspx?domain='
        ];
        
        // Use the first service by default
        return $whois_services[0] . $domain;
    }
}
