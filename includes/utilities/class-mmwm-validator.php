<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Data validation utility class
 */
class MMWM_Validator
{
    /**
     * Validate URL format
     *
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_url($url)
    {
        if (empty($url)) {
            return false;
        }

        $url = MMWM_WP_Compat::esc_url_raw(trim($url));
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate email address
     *
     * @param string $email The email to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_email($email)
    {
        if (empty($email)) {
            return false;
        }

        // Use WordPress function if available, otherwise use PHP filter
        if (function_exists('is_email')) {
            return is_email($email) !== false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate interval value
     *
     * @param mixed $interval The interval to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_interval($interval)
    {
        $interval = intval($interval);
        return $interval >= 1 && $interval <= 1440; // 1 minute to 24 hours
    }

    /**
     * Validate check type
     *
     * @param string $check_type The check type to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_check_type($check_type)
    {
        $allowed_types = ['response_code', 'fetch_html'];
        return in_array($check_type, $allowed_types, true);
    }

    /**
     * Validate notification trigger
     *
     * @param string $trigger The trigger to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_notification_trigger($trigger)
    {
        $allowed_triggers = ['always', 'when_error_only'];
        return in_array($trigger, $allowed_triggers, true);
    }

    /**
     * Validate monitoring status
     *
     * @param string $status The status to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_monitoring_status($status)
    {
        $allowed_statuses = ['active', 'paused', 'stopped'];
        return in_array($status, $allowed_statuses, true);
    }
}
