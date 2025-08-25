<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Interface for scheduling functionality
 */
interface MMWM_Scheduler_Interface
{
    /**
     * Schedule checks for all active websites
     *
     * @return bool True if scheduled successfully
     */
    public function schedule_checks();

    /**
     * Get websites that need to be checked
     *
     * @return array Array of website post IDs
     */
    public function get_websites_to_check();

    /**
     * Calculate next check time for a website
     *
     * @param int $post_id Website post ID
     * @param int $interval Interval in minutes
     * @return int Timestamp of next check
     */
    public function calculate_next_check($post_id, $interval);

    /**
     * Check if a website is due for checking
     *
     * @param int $post_id Website post ID
     * @return bool True if due for check
     */
    public function is_due_for_check($post_id);
}
