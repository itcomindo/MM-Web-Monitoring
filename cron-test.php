<?php

/**
 * Manual cron test script
 */

// Load WordPress
require_once('../../../../wp-load.php');

// Load our plugin
define('MMWM_VERSION', '1.0.0');
define('MMWM_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-core.php';
$core = new MMWM_Core();

echo "<h1>⏰ Manual Cron Test</h1>";

// Get current time
echo "<p>Current time: " . wp_date('Y-m-d H:i:s') . "</p>";

// Test scheduler
$scheduler = new MMWM_Scheduler();
$websites_to_check = $scheduler->get_websites_to_check();

echo "<h3>Websites due for checking: " . count($websites_to_check) . "</h3>";

if (!empty($websites_to_check)) {
    foreach ($websites_to_check as $post_id) {
        $title = get_the_title($post_id);
        $last_check = get_post_meta($post_id, '_mmwm_last_check', true);
        $interval = get_post_meta($post_id, '_mmwm_interval', true) ?: 15;

        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
        echo "<strong>$title</strong> (ID: $post_id)<br>";
        echo "Last check: " . ($last_check ? human_time_diff($last_check) . ' ago' : 'Never') . "<br>";
        echo "Interval: {$interval} minutes<br>";
        echo "</div>";
    }

    // Run manual check
    if (isset($_GET['run_checks'])) {
        echo "<h3>🚀 Running checks...</h3>";
        $result = $scheduler->schedule_checks();
        echo "<p>✅ Checks completed. Result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";

        // Show updated results
        echo "<h4>Updated status:</h4>";
        foreach ($websites_to_check as $post_id) {
            $title = get_the_title($post_id);
            $status = get_post_meta($post_id, '_mmwm_status', true);
            $reason = get_post_meta($post_id, '_mmwm_status_reason', true);
            $last_check = get_post_meta($post_id, '_mmwm_last_check', true);

            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
            echo "<strong>$title</strong><br>";
            echo "Status: <strong style='color: " . ($status === 'UP' ? 'green' : 'red') . ";'>$status</strong><br>";
            echo "Reason: $reason<br>";
            echo "Checked: " . human_time_diff($last_check) . " ago<br>";
            echo "</div>";
        }
    } else {
        echo "<p><a href='?run_checks=1' style='padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 3px;'>🚀 Run Manual Checks</a></p>";
    }
} else {
    echo "<p>No websites are due for checking at this time.</p>";

    // Show all websites and their next check times
    $all_websites = get_posts([
        'post_type' => 'mmwm_website',
        'post_status' => 'publish',
        'posts_per_page' => -1
    ]);

    if (!empty($all_websites)) {
        echo "<h3>All websites schedule:</h3>";
        foreach ($all_websites as $website) {
            $status = get_post_meta($website->ID, '_mmwm_monitoring_status', true);
            $next_check = $scheduler->get_next_check_display($website->ID);
            $last_check = get_post_meta($website->ID, '_mmwm_last_check', true);

            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
            echo "<strong>" . esc_html($website->post_title) . "</strong><br>";
            echo "Monitoring: $status<br>";
            echo "Last check: " . ($last_check ? human_time_diff($last_check) . ' ago' : 'Never') . "<br>";
            echo "Next check: $next_check<br>";
            echo "</div>";
        }
    }
}
