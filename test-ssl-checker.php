<?php
// Test SSL checker functionality
require_once 'includes/utilities/class-mmwm-wp-compat.php';
require_once 'includes/interfaces/interface-mmwm-ssl-checker.php';
require_once 'includes/monitoring/class-mmwm-ssl-checker.php';

echo "Testing SSL Checker...\n\n";

$ssl_checker = new MMWM_SSL_Checker();

// Test websites
$test_urls = [
    'https://google.com',
    'https://github.com',
    'https://wordpress.org',
    'http://example.com', // Non-HTTPS
    'https://invalid-domain-for-testing.com' // Invalid domain
];

foreach ($test_urls as $url) {
    echo "Testing: $url\n";
    echo str_repeat('-', 50) . "\n";

    $result = $ssl_checker->check_ssl_status($url);

    echo "SSL Active: " . ($result['is_active'] ? 'YES' : 'NO') . "\n";

    if ($result['error']) {
        echo "Error: " . $result['error'] . "\n";
    }

    if ($result['expiry_date']) {
        echo "Expiry Date: " . $result['expiry_date'] . "\n";
        echo "Days Until Expiry: " . $result['days_until_expiry'] . "\n";
        echo "Issuer: " . ($result['issuer'] ?? 'Unknown') . "\n";

        if (isset($result['is_expiring_soon'])) {
            echo "Expiring Soon: " . ($result['is_expiring_soon'] ? 'YES' : 'NO') . "\n";
        }
    }

    echo "\n";
}

echo "✅ SSL Checker test completed!\n";
