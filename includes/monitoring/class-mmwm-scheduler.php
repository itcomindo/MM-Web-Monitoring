<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Scheduler class
 * 
 * Handles website monitoring schedule and checks
 */
class MMWM_Scheduler implements MMWM_Scheduler_Interface
{
    /**
     * Checker instance
     *
     * @var MMWM_Checker
     */
    private $checker;

    /**
     * Notifier instance
     *
     * @var MMWM_Notifier
     */
    private $notifier;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Use lazy loading to avoid dependency issues
    }

    /**
     * Get checker instance (lazy loading)
     *
     * @return MMWM_Checker
     */
    private function get_checker()
    {
        if (!$this->checker) {
            $this->checker = new MMWM_Checker();
        }
        return $this->checker;
    }

    /**
     * Get notifier instance (lazy loading)
     *
     * @return MMWM_Notifier
     */
    private function get_notifier()
    {
        if (!$this->notifier) {
            $this->notifier = new MMWM_Notifier();
        }
        return $this->notifier;
    }

    /**
     * Schedule checks for all active websites
     *
     * @return bool True if scheduled successfully
     */
    public function schedule_checks()
    {
        $websites = $this->get_websites_to_check();

        if (empty($websites)) {
            return true; // No websites to check
        }

        foreach ($websites as $post_id) {
            if ($this->is_due_for_check($post_id)) {
                $this->process_website_check($post_id);
            }
        }

        return true;
    }

    /**
     * Get websites that need to be checked
     *
     * @return array Array of website post IDs
     */
    public function get_websites_to_check()
    {
        $args = [
            'post_type'      => 'mmwm_website',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'     => '_mmwm_monitoring_status',
                    'value'   => 'active',
                    'compare' => '='
                ]
            ],
            'fields' => 'ids'
        ];

        $websites = get_posts($args);
        return is_array($websites) ? $websites : [];
    }

    /**
     * Calculate next check time for a website
     *
     * @param int $post_id Website post ID
     * @param string $interval_string Interval string like '15min'
     * @return int Timestamp of next check
     */
    public function calculate_next_check($post_id, $interval_string = '15min')
    {
        $last_check = get_post_meta($post_id, '_mmwm_last_check', true) ?: time();
        $interval_seconds = $this->convert_interval_to_seconds($interval_string);

        return $last_check + $interval_seconds;
    }

    /**
     * Check if website is due for check
     *
     * @param int $post_id Website post ID
     * @return bool True if due for check
     */
    public function is_due_for_check($post_id)
    {
        $current_time = time();
        $last_check = get_post_meta($post_id, '_mmwm_last_check', true) ?: 0;
        $interval_string = get_post_meta($post_id, '_mmwm_monitoring_interval', true) ?: '15min';
        $interval_seconds = $this->convert_interval_to_seconds($interval_string);

        return ($current_time - $last_check) >= $interval_seconds;
    }

    /**
     * Convert interval string to seconds
     *
     * @param string $interval_string Interval string like '5min', '1hour', etc.
     * @return int Interval in seconds
     */
    private function convert_interval_to_seconds($interval_string)
    {
        $intervals_map = array(
            '1min' => 60,
            '3min' => 180,
            '5min' => 300,
            '7min' => 420,
            '10min' => 600,
            '15min' => 900,
            '25min' => 1500,
            '30min' => 1800,
            '45min' => 2700,
            '60min' => 3600,
            '1hour' => 3600,
            '6hour' => 21600,
            '12hour' => 43200,
            '24hour' => 86400
        );

        return isset($intervals_map[$interval_string]) ? $intervals_map[$interval_string] : 900; // Default to 15 minutes
    }

    /**
     * Process check for a single website
     *
     * @param int $post_id Website post ID
     */
    private function process_website_check($post_id)
    {
        $old_status = get_post_meta($post_id, '_mmwm_status', true);

        // Perform the check
        $result = $this->get_checker()->perform_check($post_id);

        // Update website status
        $this->update_website_status($post_id, $result, $old_status);
    }

    /**
     * Update website status after check
     *
     * @param int $post_id Website post ID
     * @param array $result Check result
     * @param string $old_status Previous status
     */
    private function update_website_status($post_id, $result, $old_status)
    {
        $new_status = $result['status'];

        // Update last check timestamp
        update_post_meta($post_id, '_mmwm_last_check', time());

        // Update status if changed
        if ($old_status !== $new_status) {
            update_post_meta($post_id, '_mmwm_status', $new_status);
            update_post_meta($post_id, '_mmwm_status_changed', time());

            // Send notification if status changed
            $this->get_notifier()->send_notification($post_id, $old_status, $new_status);
        }

        // Update response time
        if (isset($result['response_time'])) {
            update_post_meta($post_id, '_mmwm_response_time', $result['response_time']);
        }

        // Update response code
        if (isset($result['response_code'])) {
            update_post_meta($post_id, '_mmwm_response_code', $result['response_code']);
        }

        // Log the check
        $this->log_check($post_id, $result);
    }

    /**
     * Log check result
     *
     * @param int $post_id Website post ID
     * @param array $result Check result
     */
    private function log_check($post_id, $result)
    {
        $log_entry = [
            'timestamp' => time(),
            'status' => $result['status'],
            'response_time' => $result['response_time'] ?? 0,
            'response_code' => $result['response_code'] ?? 0,
            'message' => $result['message'] ?? ''
        ];

        // Get existing logs
        $logs = get_post_meta($post_id, '_mmwm_check_logs', true) ?: [];

        // Add new log entry
        $logs[] = $log_entry;

        // Keep only last 50 log entries
        if (count($logs) > 50) {
            $logs = array_slice($logs, -50);
        }

        // Update logs
        update_post_meta($post_id, '_mmwm_check_logs', $logs);
    }

    /**
     * Get scheduled checks
     *
     * @return array Array of scheduled checks
     */
    public function get_scheduled_checks()
    {
        $websites = $this->get_websites_to_check();
        $scheduled = [];

        foreach ($websites as $post_id) {
            $interval_string = get_post_meta($post_id, '_mmwm_monitoring_interval', true) ?: '15min';
            $next_check = $this->calculate_next_check($post_id, $interval_string);

            $scheduled[] = [
                'post_id' => $post_id,
                'title' => get_the_title($post_id),
                'next_check' => $next_check,
                'interval' => $interval_string
            ];
        }

        // Sort by next check time
        usort($scheduled, function ($a, $b) {
            return $a['next_check'] - $b['next_check'];
        });

        return $scheduled;
    }

    /**
     * Get next check time for a website
     *
     * @param int $post_id Website post ID
     * @return int Next check timestamp
     */
    public function get_next_check_time($post_id)
    {
        $interval_string = get_post_meta($post_id, '_mmwm_monitoring_interval', true) ?: '15min';
        return $this->calculate_next_check($post_id, $interval_string);
    }

    /**
     * Force check for a website
     *
     * @param int $post_id Website post ID
     * @return array Check result
     */
    public function force_check($post_id)
    {
        $this->process_website_check($post_id);

        return [
            'status' => get_post_meta($post_id, '_mmwm_status', true),
            'response_time' => get_post_meta($post_id, '_mmwm_response_time', true),
            'response_code' => get_post_meta($post_id, '_mmwm_response_code', true),
            'last_check' => get_post_meta($post_id, '_mmwm_last_check', true)
        ];
    }
}
