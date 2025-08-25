<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Scheduler class implementing scheduler interface
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
        $this->checker = new MMWM_Checker();
        $this->notifier = new MMWM_Notifier();
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
            $this->process_website_check($post_id);
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
        $current_time = time();
        $websites_to_check = [];

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

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return $websites_to_check;
        }

        foreach ($query->posts as $post_id) {
            if ($this->is_due_for_check($post_id)) {
                $websites_to_check[] = $post_id;
            }
        }

        return $websites_to_check;
    }

    /**
     * Calculate next check time for a website
     *
     * @param int $post_id Website post ID
     * @param int $interval Interval in minutes
     * @return int Timestamp of next check
     */
    public function calculate_next_check($post_id, $interval)
    {
        $last_check = get_post_meta($post_id, '_mmwm_last_check', true) ?: time();
        $interval_seconds = $interval * 60;

        return $last_check + $interval_seconds;
    }

    /**
     * Check if a website is due for checking
     *
     * @param int $post_id Website post ID
     * @return bool True if due for check
     */
    public function is_due_for_check($post_id)
    {
        $current_time = time();
        $last_check = get_post_meta($post_id, '_mmwm_last_check', true) ?: 0;
        $interval = intval(get_post_meta($post_id, '_mmwm_interval', true)) ?: 15;
        $interval_seconds = $interval * 60;

        return ($current_time - $last_check) >= $interval_seconds;
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
        $result = $this->checker->perform_check($post_id);

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
        $reason = $result['reason'];
        $timestamp = $result['timestamp'];

        // Update meta fields
        update_post_meta($post_id, '_mmwm_last_check', $timestamp);
        update_post_meta($post_id, '_mmwm_status', $new_status);
        update_post_meta($post_id, '_mmwm_status_reason', $reason);

        // Handle notifications
        $this->handle_notification($post_id, $old_status, $new_status, $reason);

        // Log the check
        $this->log_check($post_id, $old_status, $new_status, $reason);
    }

    /**
     * Handle notification sending
     *
     * @param int $post_id Website post ID
     * @param string $old_status Previous status
     * @param string $new_status Current status
     * @param string $reason Check reason
     */
    private function handle_notification($post_id, $old_status, $new_status, $reason)
    {
        $notification_trigger = get_post_meta($post_id, '_mmwm_notification_trigger', true) ?: 'always';

        if ($this->notifier->should_send_notification($notification_trigger, $old_status, $new_status)) {
            $email_sent = $this->notifier->send_notification($post_id, $old_status, $new_status, $reason);

            $email_log = $email_sent ?
                'Email notification sent successfully at ' . wp_date('Y-m-d H:i:s') :
                'Failed to send email notification at ' . wp_date('Y-m-d H:i:s');
        } else {
            $email_log = 'Email not sent (conditions not met)';
        }

        update_post_meta($post_id, '_mmwm_email_log', $email_log);
    }

    /**
     * Log check result
     *
     * @param int $post_id Website post ID
     * @param string $old_status Previous status
     * @param string $new_status Current status
     * @param string $reason Check reason
     */
    private function log_check($post_id, $old_status, $new_status, $reason)
    {
        $website_title = get_the_title($post_id);
        $message = sprintf(
            'MMWM Check: %s (%d) - %s → %s - %s',
            $website_title,
            $post_id,
            $old_status ?: 'N/A',
            $new_status,
            $reason
        );

        error_log($message);
    }

    /**
     * Get next check time for display
     *
     * @param int $post_id Website post ID
     * @return string Human readable next check time
     */
    public function get_next_check_display($post_id)
    {
        $interval = intval(get_post_meta($post_id, '_mmwm_interval', true)) ?: 15;
        $next_check = $this->calculate_next_check($post_id, $interval);
        $current_time = time();

        if ($next_check <= $current_time) {
            return __('Due now', 'mm-web-monitoring');
        }

        $time_diff = $next_check - $current_time;

        if ($time_diff < 3600) {
            $minutes = ceil($time_diff / 60);
            return sprintf(_n('%d minute', '%d minutes', $minutes, 'mm-web-monitoring'), $minutes);
        } else {
            $hours = ceil($time_diff / 3600);
            return sprintf(_n('%d hour', '%d hours', $hours, 'mm-web-monitoring'), $hours);
        }
    }

    /**
     * Get next check timestamp
     *
     * @param int $post_id Website post ID
     * @return int Timestamp of next check
     */
    public function get_next_check_timestamp($post_id)
    {
        $interval = intval(get_post_meta($post_id, '_mmwm_interval', true)) ?: 15;
        return $this->calculate_next_check($post_id, $interval);
    }
}
