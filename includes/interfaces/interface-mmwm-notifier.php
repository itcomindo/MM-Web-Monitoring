<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Interface for notification functionality
 */
interface MMWM_Notifier_Interface
{
    /**
     * Send notification email
     *
     * @param int $post_id Website post ID
     * @param string $old_status Previous status
     * @param string $new_status Current status
     * @param string $reason Check details/reason
     * @return bool True if sent successfully, false otherwise
     */
    public function send_notification($post_id, $old_status, $new_status, $reason);

    /**
     * Check if notification should be sent based on trigger settings
     *
     * @param string $trigger Notification trigger setting
     * @param string $old_status Previous status
     * @param string $new_status Current status
     * @return bool True if should send, false otherwise
     */
    public function should_send_notification($trigger, $old_status, $new_status);

    /**
     * Get notification email address for a website
     *
     * @param int $post_id Website post ID
     * @return string Email address
     */
    public function get_notification_email($post_id);
}
