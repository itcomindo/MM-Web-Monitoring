<?php

/**
 * Simple WordPress integration test
 * Put this file in wp-content/plugins/mm-web-monitoring/ and access via browser
 */

// Load WordPress
require_once('../../../../wp-load.php');

// Check if we can load our plugin
define('MMWM_VERSION', '1.0.0');
define('MMWM_PLUGIN_DIR', plugin_dir_path(__FILE__));

echo "<h1>🧪 MMWM WordPress Integration Test</h1>";

try {
    // Load our core
    require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-core.php';
    $core = new MMWM_Core();
    echo "<p>✅ Core loaded successfully</p>";

    // Test cron
    if (class_exists('MMWM_Cron')) {
        $cron = new MMWM_Cron();
        echo "<p>✅ Cron class available</p>";

        // Test if cron event is scheduled
        $next_run = wp_next_scheduled('mmwm_scheduled_check_event');
        if ($next_run) {
            echo "<p>✅ Cron event scheduled for: " . wp_date('Y-m-d H:i:s', $next_run) . "</p>";
        } else {
            echo "<p>⚠️ No cron event scheduled</p>";
        }
    }

    // Test if we have any websites
    $websites = get_posts([
        'post_type' => 'mmwm_website',
        'post_status' => 'publish',
        'posts_per_page' => 5
    ]);

    echo "<p>📊 Found " . count($websites) . " websites to monitor</p>";

    if (!empty($websites)) {
        echo "<h3>🔍 Testing Scheduler</h3>";
        $scheduler = new MMWM_Scheduler();

        foreach ($websites as $website) {
            $is_due = $scheduler->is_due_for_check($website->ID);
            $next_check = $scheduler->get_next_check_display($website->ID);
            $status = get_post_meta($website->ID, '_mmwm_status', true) ?: 'Unknown';
            $last_check = get_post_meta($website->ID, '_mmwm_last_check', true);

            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>" . esc_html($website->post_title) . "</strong><br>";
            echo "Status: <strong>" . esc_html($status) . "</strong><br>";
            echo "Last Check: " . ($last_check ? human_time_diff($last_check) . ' ago' : 'Never') . "<br>";
            echo "Due for check: " . ($is_due ? '✅ YES' : '❌ NO') . "<br>";
            echo "Next check: " . esc_html($next_check) . "<br>";
            echo "</div>";
        }

        // Test manual check
        if (isset($_GET['test_check']) && $_GET['test_check']) {
            $test_post_id = intval($_GET['test_check']);
            echo "<h3>🧪 Manual Check Test for Post ID: $test_post_id</h3>";

            $checker = new MMWM_Checker();
            $result = $checker->perform_check($test_post_id);

            echo "<pre>" . print_r($result, true) . "</pre>";
        }

        // Add test links
        echo "<h3>🔧 Test Actions</h3>";
        foreach ($websites as $website) {
            echo "<a href='?test_check=" . $website->ID . "' style='margin-right: 10px; padding: 5px 10px; background: #0073aa; color: white; text-decoration: none;'>Test Check: " . esc_html($website->post_title) . "</a>";
        }
    }

    echo "<h3>✅ All tests completed successfully!</h3>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . esc_html($e->getMessage()) . "</p>";
    echo "<p>File: " . esc_html($e->getFile()) . " Line: " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Fatal Error: " . esc_html($e->getMessage()) . "</p>";
    echo "<p>File: " . esc_html($e->getFile()) . " Line: " . $e->getLine() . "</p>";
}
