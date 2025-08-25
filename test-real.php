<?php

// Test dengan mock data WordPress
define('ABSPATH', true);
define('MMWM_PLUGIN_DIR', __DIR__ . '/');

// Mock WordPress functions
function get_post_meta($post_id, $key, $single = false)
{
    $mock_data = [
        1 => [
            '_mmwm_target_url' => 'https://httpbin.org/status/200',
            '_mmwm_check_type' => 'response_code',
            '_mmwm_interval' => 15,
            '_mmwm_last_check' => time() - 3600, // 1 hour ago
            '_mmwm_monitoring_status' => 'active'
        ],
        2 => [
            '_mmwm_target_url' => 'https://httpbin.org/status/404',
            '_mmwm_check_type' => 'response_code',
            '_mmwm_interval' => 30,
            '_mmwm_last_check' => time() - 1800, // 30 minutes ago
            '_mmwm_monitoring_status' => 'active'
        ]
    ];

    if (isset($mock_data[$post_id][$key])) {
        return $mock_data[$post_id][$key];
    }

    return $single ? '' : [];
}

function update_post_meta($post_id, $key, $value)
{
    echo "UPDATE: Post $post_id -> $key = $value\n";
    return true;
}

// Load our system
require_once 'includes/class-mmwm-core.php';
$core = new MMWM_Core();

echo "=== REAL FUNCTIONALITY TEST ===\n\n";

// Test checker
echo "🔍 Testing Checker...\n";
$checker = new MMWM_Checker();

$result1 = $checker->perform_check(1); // Should succeed (200)
echo "Test 1 (httpbin.org/status/200): " . $result1['status'] . " - " . $result1['reason'] . "\n";

$result2 = $checker->perform_check(2); // Should fail (404)
echo "Test 2 (httpbin.org/status/404): " . $result2['status'] . " - " . $result2['reason'] . "\n";

// Test scheduler
echo "\n📅 Testing Scheduler...\n";
$scheduler = new MMWM_Scheduler();

echo "Is post 1 due for check: " . ($scheduler->is_due_for_check(1) ? 'YES' : 'NO') . "\n";
echo "Is post 2 due for check: " . ($scheduler->is_due_for_check(2) ? 'YES' : 'NO') . "\n";

// Test cron integration
echo "\n⏰ Testing Cron Integration...\n";
$cron = new MMWM_Cron();

echo "Testing perform_check method...\n";
$cron_result = $cron->perform_check(1);
echo "Cron check result: " . (is_array($cron_result) ? $cron_result['status'] : 'ERROR') . "\n";

echo "\n✅ All real functionality tests completed!\n";
