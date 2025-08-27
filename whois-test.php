<?php
// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Load domain checker class
require_once dirname(__FILE__) . '/includes/monitoring/class-mmwm-domain-checker.php';

// Test domain and API key
$domain = 'pulau-tidung.com';
$api_key = 'at_8QdC0iDevAeMJjTegN162oNkh87oJ';

// Set API key in WordPress options
update_option('mmwm_whoisxml_api_key', $api_key);
echo "API key set in WordPress options: {$api_key}\n";

// Create domain checker instance
$domain_checker = new MMWM_Domain_Checker();

// Check domain expiration
echo "Checking domain expiration for {$domain}...\n";
$result = $domain_checker->check_domain_expiration($domain);

// Output result
echo "Result:\n";
var_dump($result);

// Test direct API call
echo "\nTesting direct API call...\n";
$api_url = 'https://www.whoisxmlapi.com/whoisserver/WhoisService';
$request_url = add_query_arg([
    'apiKey' => $api_key,
    'domainName' => $domain,
    'outputFormat' => 'JSON'
], $api_url);

echo "API URL: {$request_url}\n";

$response = wp_remote_get($request_url, [
    'timeout' => 15,
    'sslverify' => true,
]);

if (is_wp_error($response)) {
    echo "API Error: " . $response->get_error_message() . "\n";
} else {
    $response_code = wp_remote_retrieve_response_code($response);
    echo "API Response Code: {$response_code}\n";
    
    $body = wp_remote_retrieve_body($response);
    echo "API Response Body:\n";
    echo substr($body, 0, 1000) . (strlen($body) > 1000 ? '...' : '') . "\n";
}