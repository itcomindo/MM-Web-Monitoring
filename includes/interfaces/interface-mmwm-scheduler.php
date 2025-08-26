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
     * Calculate next check time for a website
     *
     * @param int $post_id Website post ID
     * @param string $interval_string Interval string like '15min'
     * @return int Timestamp of next check
     */
    public function calculate_next_check($post_id, $interval_string);

    /**
     * Check if a website is due for checking
     *
     * @param int $post_id Website post ID
     * @return bool True if due for check
     */
    public function is_due_for_check($post_id);
}
