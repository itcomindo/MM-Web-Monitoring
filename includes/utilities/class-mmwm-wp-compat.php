<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * WordPress compatibility functions
 * Provides fallbacks when WordPress functions are not available
 */
class MMWM_WP_Compat
{
    /**
     * Fallback for esc_url_raw()
     */
    public static function esc_url_raw($url)
    {
        if (function_exists('esc_url_raw')) {
            return esc_url_raw($url);
        }

        // Simple fallback
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    /**
     * Fallback for sanitize_text_field()
     */
    public static function sanitize_text_field($str)
    {
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($str);
        }

        // Simple fallback
        return trim(strip_tags($str));
    }

    /**
     * Fallback for sanitize_email()
     */
    public static function sanitize_email($email)
    {
        if (function_exists('sanitize_email')) {
            return sanitize_email($email);
        }

        // Simple fallback
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Fallback for sanitize_textarea_field()
     */
    public static function sanitize_textarea_field($str)
    {
        if (function_exists('sanitize_textarea_field')) {
            return sanitize_textarea_field($str);
        }

        // Simple fallback
        return trim(strip_tags($str));
    }

    /**
     * Fallback for home_url()
     */
    public static function home_url()
    {
        if (function_exists('home_url')) {
            return home_url();
        }

        // Simple fallback
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Fallback for wp_remote_get()
     */
    public static function wp_remote_get($url, $args = [])
    {
        if (function_exists('wp_remote_get')) {
            return wp_remote_get($url, $args);
        }

        // Use cURL as fallback
        return self::curl_request($url, $args);
    }

    /**
     * Fallback for wp_remote_retrieve_response_code()
     */
    public static function wp_remote_retrieve_response_code($response)
    {
        if (function_exists('wp_remote_retrieve_response_code')) {
            return wp_remote_retrieve_response_code($response);
        }

        // Handle our custom response format
        if (is_array($response) && isset($response['response']['code'])) {
            return $response['response']['code'];
        }

        return 0;
    }

    /**
     * Fallback for wp_remote_retrieve_body()
     */
    public static function wp_remote_retrieve_body($response)
    {
        if (function_exists('wp_remote_retrieve_body')) {
            return wp_remote_retrieve_body($response);
        }

        // Handle our custom response format
        if (is_array($response) && isset($response['body'])) {
            return $response['body'];
        }

        return '';
    }

    /**
     * Fallback for is_wp_error()
     */
    public static function is_wp_error($thing)
    {
        if (function_exists('is_wp_error')) {
            return is_wp_error($thing);
        }

        // Simple check for error
        return is_array($thing) && isset($thing['error']);
    }

    /**
     * Get error message from response
     */
    public static function get_error_message($response)
    {
        if (is_object($response) && method_exists($response, 'get_error_message')) {
            return $response->get_error_message();
        }

        if (is_array($response) && isset($response['error'])) {
            return $response['error'];
        }

        return 'Unknown error';
    }

    /**
     * cURL implementation for HTTP requests
     */
    private static function curl_request($url, $args = [])
    {
        $ch = curl_init();

        // Default settings
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $args['timeout'] ?? 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => $args['sslverify'] ?? false,
            CURLOPT_USERAGENT => $args['user-agent'] ?? (defined('MMWM_USER_AGENT') ? MMWM_USER_AGENT : 'MM-Web-Monitoring/1.0.8'),
            CURLOPT_HEADER => true,
        ]);

        // Set headers if provided
        if (isset($args['headers']) && is_array($args['headers'])) {
            $headers = [];
            foreach ($args['headers'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        if (curl_error($ch)) {
            curl_close($ch);
            return [
                'error' => curl_error($ch)
            ];
        }

        curl_close($ch);

        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        return [
            'response' => [
                'code' => $http_code
            ],
            'body' => $body,
            'headers' => $header
        ];
    }
}
