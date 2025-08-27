<?php
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}

// Load WordPress
require_once(ABSPATH . 'wp-load.php');

// Set API key
$api_key = 'at_8QdC0iDevAeMJjTegN162oNkh87oJ';
update_option('mmwm_whoisxml_api_key', $api_key);

// Domain to test
$domain = 'pulau-tidung.com';

// Test API directly
$api_url = 'https://www.whoisxmlapi.com/whoisserver/WhoisService';
$request_url = add_query_arg([
    'apiKey' => $api_key,
    'domainName' => $domain,
    'outputFormat' => 'JSON'
], $api_url);

echo "Testing WHOIS API directly...\n";
echo "API URL: {$request_url}\n";

// Use cURL directly with longer timeout
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds timeout
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if ($error) {
    echo "cURL Error: {$error}\n";
} else {
    echo "Response HTTP Code: {$info['http_code']}\n";
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error: " . json_last_error_msg() . "\n";
        echo "Raw Response: " . substr($response, 0, 500) . "...\n";
    } else {
        echo "API Response:\n";
        print_r($data);
    }
}