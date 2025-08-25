<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Data sanitization utility class
 */
class MMWM_Sanitizer
{
    /**
     * Sanitize URL
     *
     * @param string $url The URL to sanitize
     * @return string Sanitized URL
     */
    public static function sanitize_url($url)
    {
        return esc_url_raw(trim($url));
    }

    /**
     * Sanitize email address
     *
     * @param string $email The email to sanitize
     * @return string Sanitized email
     */
    public static function sanitize_email($email)
    {
        return sanitize_email(trim($email));
    }

    /**
     * Sanitize text field
     *
     * @param string $text The text to sanitize
     * @return string Sanitized text
     */
    public static function sanitize_text($text)
    {
        return sanitize_text_field(trim($text));
    }

    /**
     * Sanitize textarea content
     *
     * @param string $content The content to sanitize
     * @return string Sanitized content
     */
    public static function sanitize_textarea($content)
    {
        return sanitize_textarea_field(trim($content));
    }

    /**
     * Sanitize integer value
     *
     * @param mixed $value The value to sanitize
     * @param int $min Minimum allowed value
     * @param int $max Maximum allowed value
     * @return int Sanitized integer
     */
    public static function sanitize_integer($value, $min = 1, $max = 9999)
    {
        $value = intval($value);
        return max($min, min($max, $value));
    }

    /**
     * Sanitize monitoring interval
     *
     * @param mixed $interval The interval to sanitize
     * @return int Sanitized interval in minutes
     */
    public static function sanitize_interval($interval)
    {
        return self::sanitize_integer($interval, 1, 1440); // 1 minute to 24 hours
    }

    /**
     * Sanitize HTML selector
     *
     * @param string $selector The HTML selector to sanitize
     * @return string Sanitized selector
     */
    public static function sanitize_html_selector($selector)
    {
        // Remove potentially dangerous characters but keep valid CSS selector chars
        $selector = preg_replace('/[^a-zA-Z0-9._#\-\s\[\]=":()>+~]/', '', $selector);
        return trim($selector);
    }
}
